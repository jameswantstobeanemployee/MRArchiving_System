<?php

namespace App\Services;

use App\Models\ArchivedChart;
use App\Models\Patient;
use App\Models\FolderBox;
use App\Models\ExternalDrive;
use App\Models\LocationHistory;
use App\Models\SystemSetting;
use App\Models\AuditLog;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ArchiveService
{
    /**
     * Ghostscript PDF settings presets.
     * Maps to the -dPDFSETTINGS parameter.
     *
     *  screen   – 72 dpi,  smallest file, screen viewing only
     *  ebook    – 150 dpi, good balance for digital archiving   ← default
     *  printer  – 300 dpi, high quality for printing
     *  prepress – 300 dpi, colour-managed, largest file
     */
    private const GS_PRESETS = ['screen', 'ebook', 'printer', 'prepress'];

    // =========================================================================
    // NEW: Save first, compress later
    //
    // Called by ChartController::store().
    // Moves the temp file to the archive drive and saves the DB record
    // synchronously — takes < 1 second. Ghostscript is NOT called here.
    // The controller dispatches CompressAndArchiveChart after this returns.
    // =========================================================================

    public function archiveChartSync(array $data, ?string $tempFilePath): ArchivedChart
    {
        return DB::transaction(function () use ($data, $tempFilePath) {

            // ── Validate box capacity ────────────────────────────────────
            $box = !empty($data['physical_location_id'])
                ? FolderBox::findOrFail($data['physical_location_id'])
                : null;

            if ($box && !$box->canAcceptChart()) {
                throw new Exception(
                    "Box {$box->box_number} is full (≥"
                    . SystemSetting::getValue('box_block_threshold', 95)
                    . "% capacity). Please select another box."
                );
            }

            // ── Move temp file to the archive drive (no compression yet) ─
            $digitalPath = null;
            $digitalSize = 0;

            if ($tempFilePath && file_exists($tempFilePath)) {

                $patient    = Patient::findOrFail($data['patient_id']);
                $filename   = $this->generateFilename($patient, $data);
                $drive      = ExternalDrive::getPrimary();

                if (!$drive) {
                    throw new Exception('No primary archive drive configured.');
                }
                if ($drive->status !== 'active') {
                    throw new Exception("Primary drive '{$drive->name}' is not active.");
                }

                $drivePath  = rtrim($drive->drive_path, '/\\');
                $archiveDir = $drivePath . DIRECTORY_SEPARATOR . 'archives';

                if (!is_dir($archiveDir)) {
                    if (!mkdir($archiveDir, 0755, true)) {
                        throw new Exception("Cannot create archive directory: {$archiveDir}");
                    }
                }

                $fullPath = $archiveDir . DIRECTORY_SEPARATOR . $filename;

                // rename() is instant on the same volume; falls back to copy+delete
                // across filesystem boundaries (e.g. temp on C:, drive on D:)
                if (!rename($tempFilePath, $fullPath)) {
                    if (!copy($tempFilePath, $fullPath)) {
                        throw new Exception("Failed to move file to archive drive: {$fullPath}");
                    }
                    @unlink($tempFilePath);
                }

                $digitalPath = $fullPath;
                $digitalSize = filesize($fullPath);

                // Track uncompressed size now; compressChartFile() will correct
                // it after Ghostscript runs in the background.
                $drive->increment('used_space', $digitalSize);
            }

            // ── Save the archive record immediately ──────────────────────
            $retentionEndDate = $this->calculateRetentionDate($data);

            $chart = ArchivedChart::create([
                'patient_id'             => $data['patient_id'],
                'case_number'            => $data['case_number'],
                'admission_date'         => $data['admission_date'],
                'discharge_date'         => $data['discharge_date'] ?? null,
                'archived_date'          => Carbon::today(),
                'physical_location_id'   => $data['physical_location_id'] ?? null,
                'digital_copy_path'      => $digitalPath,
                'digital_copy_size'      => $digitalSize,
                'total_pages'            => $data['total_pages'] ?? 0,
                'archived_by'            => $data['archived_by'],
                'status'                 => 'archived',
                'compression_status'     => $digitalPath ? 'pending' : 'none',
                'retention_period_years' => isset($data['retention_period']) && $data['retention_period'] === 'permanent'
                                                ? null
                                                : ($data['retention_period'] ?? null),
                'retention_end_date'     => $retentionEndDate,
                'notes'                  => $data['notes'] ?? null,
            ]);

            // ── Initial location history ─────────────────────────────────
            if ($box) {
                LocationHistory::create([
                    'archived_chart_id' => $chart->id,
                    'from_box_id'       => null,
                    'to_box_id'         => $box->id,
                    'reason'            => 'Initial archive',
                    'moved_by'          => $data['archived_by'],
                    'moved_at'          => now(),
                ]);
            }

            AuditLog::record('archive_chart', 'archived_charts', $chart->id, null, $chart->toArray());

            if ($box) {
                $this->checkBoxCapacity($box);
            }

            return $chart;
        });
    }

    // =========================================================================
    // NEW: Background compression — called exclusively by CompressAndArchiveChart
    //
    // Runs Ghostscript on the already-saved file, updates digital_copy_size
    // and compression_status on the chart record, and corrects used_space
    // on the drive.
    // =========================================================================

    public function compressChartFile(ArchivedChart $chart): void
    {
        if (!$chart->digital_copy_path || !file_exists($chart->digital_copy_path)) {
            Log::warning('compressChartFile: file not found, marking skipped.', [
                'chart_id' => $chart->id,
                'path'     => $chart->digital_copy_path,
            ]);
            $chart->update(['compression_status' => 'skipped']);
            return;
        }

        $ext = strtolower(pathinfo($chart->digital_copy_path, PATHINFO_EXTENSION));

        if ($ext !== 'pdf') {
            $chart->update(['compression_status' => 'skipped']);
            return;
        }

        $chart->update(['compression_status' => 'processing']);

        try {
            $sizeBefore = filesize($chart->digital_copy_path);

            $this->compressPdf($chart->digital_copy_path); // modifies file in-place

            $sizeAfter = filesize($chart->digital_copy_path);

            // Correct used_space on the drive (delta is negative when space was saved)
            $drive = ExternalDrive::getPrimary();
            if ($drive && $sizeAfter !== $sizeBefore) {
                $drive->increment('used_space', $sizeAfter - $sizeBefore);
            }

            $chart->update([
                'digital_copy_size'  => $sizeAfter,
                'compression_status' => 'done',
            ]);

        } catch (Exception $e) {
            Log::error('compressChartFile: Ghostscript failed.', [
                'chart_id' => $chart->id,
                'error'    => $e->getMessage(),
            ]);
            $chart->update(['compression_status' => 'failed']);
            throw $e; // bubble up so the job can retry
        }
    }

    // =========================================================================
    // EXISTING: archiveChart — kept intact for any callers outside the form
    // =========================================================================

    public function archiveChart(array $data, ?UploadedFile $file): ArchivedChart
    {
        return DB::transaction(function () use ($data, $file) {
            $box = !empty($data['physical_location_id'])
                ? FolderBox::findOrFail($data['physical_location_id'])
                : null;

            if ($box && !$box->canAcceptChart()) {
                throw new Exception("Box {$box->box_number} is full (≥" . SystemSetting::getValue('box_block_threshold', 95) . "% capacity). Please select another box.");
            }

            $digitalPath = null;
            $digitalSize = 0;

            if ($file) {
                $patient  = Patient::findOrFail($data['patient_id']);
                $filename = $this->generateFilename($patient, $data);
                $drive    = ExternalDrive::getPrimary();

                if (!$drive) {
                    throw new Exception('No primary archive drive configured.');
                }
                if ($drive->status !== 'active') {
                    throw new Exception("Primary drive '{$drive->name}' is not active.");
                }

                $drivePath  = rtrim($drive->drive_path, '/\\');
                $archiveDir = $drivePath . DIRECTORY_SEPARATOR . 'archives';

                if (!is_dir($archiveDir)) {
                    if (!mkdir($archiveDir, 0755, true)) {
                        throw new Exception("Cannot create archive directory: {$archiveDir}");
                    }
                }

                $fullPath = $archiveDir . DIRECTORY_SEPARATOR . $filename;

                if (!$file->move($archiveDir, $filename)) {
                    throw new Exception("Failed to save file to drive: {$fullPath}");
                }

                if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'pdf') {
                    $fullPath = $this->compressPdf($fullPath);
                }

                $digitalPath = $fullPath;
                $digitalSize = filesize($fullPath);
                $drive->increment('used_space', $digitalSize);
            }

            $retentionEndDate = $this->calculateRetentionDate($data);

            $chart = ArchivedChart::create([
                'patient_id'            => $data['patient_id'],
                'case_number'           => $data['case_number'],
                'admission_date'        => $data['admission_date'],
                'discharge_date'        => $data['discharge_date'] ?? null,
                'archived_date'         => Carbon::today(),
                'physical_location_id'  => $data['physical_location_id'] ?? null,
                'digital_copy_path'     => $digitalPath,
                'digital_copy_size'     => $digitalSize,
                'total_pages'           => $data['total_pages'] ?? 0,
                'archived_by'           => Auth::id(),
                'status'                => 'archived',
                'compression_status'    => 'done',
                'retention_period_years'=> isset($data['retention_period']) && $data['retention_period'] === 'permanent' ? null : ($data['retention_period'] ?? null),
                'retention_end_date'    => $retentionEndDate,
                'notes'                 => $data['notes'] ?? null,
            ]);

            if ($box) {
                LocationHistory::create([
                    'archived_chart_id' => $chart->id,
                    'from_box_id'       => null,
                    'to_box_id'         => $box->id,
                    'reason'            => 'Initial archive',
                    'moved_by'          => Auth::id(),
                    'moved_at'          => now(),
                ]);
            }

            AuditLog::record('archive_chart', 'archived_charts', $chart->id, null, $chart->toArray());

            if ($box) {
                $this->checkBoxCapacity($box);
            }

            return $chart;
        });
    }

    // =========================================================================
    // EXISTING: archiveChartWithProgress — kept intact
    // =========================================================================

    public function archiveChartWithProgress(array $data, ?string $tempFilePath, callable $emit): void
    {
        $emit('step', ['step' => 'validate', 'state' => 'active', 'label' => 'Checking box capacity…']);

        $box = !empty($data['physical_location_id'])
            ? FolderBox::findOrFail($data['physical_location_id'])
            : null;

        if ($box && !$box->canAcceptChart()) {
            $emit('step', ['step' => 'validate', 'state' => 'error', 'label' => 'Box capacity check failed']);
            $emit('error', ['message' => "Box {$box->box_number} is full (≥" . SystemSetting::getValue('box_block_threshold', 95) . "% capacity). Please select another box."]);
            return;
        }

        $emit('step', ['step' => 'validate', 'state' => 'done', 'label' => 'Validation passed']);

        $digitalPath = null;
        $digitalSize = 0;

        if ($tempFilePath && file_exists($tempFilePath)) {

            $emit('step', ['step' => 'file', 'state' => 'active', 'label' => 'Moving file to archive drive…']);

            try {
                $patient  = Patient::findOrFail($data['patient_id']);
                $filename = $this->generateFilename($patient, $data);
                $drive    = ExternalDrive::getPrimary();

                if (!$drive) throw new Exception('No primary archive drive configured.');
                if ($drive->status !== 'active') throw new Exception("Primary drive '{$drive->name}' is not active.");

                $drivePath  = rtrim($drive->drive_path, '/\\');
                $archiveDir = $drivePath . DIRECTORY_SEPARATOR . 'archives';

                if (!is_dir($archiveDir)) {
                    if (!mkdir($archiveDir, 0755, true)) throw new Exception("Cannot create archive directory: {$archiveDir}");
                }

                $fullPath = $archiveDir . DIRECTORY_SEPARATOR . $filename;

                if (!rename($tempFilePath, $fullPath)) {
                    if (!copy($tempFilePath, $fullPath)) throw new Exception("Failed to move file to drive: {$fullPath}");
                    @unlink($tempFilePath);
                }

                $emit('step', ['step' => 'file', 'state' => 'done', 'label' => 'File saved to archive drive', 'detail' => basename($fullPath)]);

            } catch (Exception $e) {
                $emit('step', ['step' => 'file', 'state' => 'error', 'label' => 'File move failed']);
                $emit('error', ['message' => $e->getMessage()]);
                return;
            }

            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

            if ($ext === 'pdf') {
                $emit('step', ['step' => 'compress', 'state' => 'active', 'label' => 'Compressing PDF with Ghostscript…']);

                try {
                    $sizeBefore = filesize($fullPath);
                    $fullPath   = $this->compressPdf($fullPath);
                    $sizeAfter  = filesize($fullPath);
                    $saved      = $sizeBefore > $sizeAfter
                        ? round((1 - $sizeAfter / $sizeBefore) * 100, 1) . '% smaller'
                        : 'already optimised';
                    $emit('step', ['step' => 'compress', 'state' => 'done', 'label' => 'PDF compressed', 'detail' => $saved]);
                } catch (Exception $e) {
                    Log::warning('archiveChartWithProgress compress stage: ' . $e->getMessage());
                    $emit('step', ['step' => 'compress', 'state' => 'skipped', 'label' => 'Compression skipped', 'detail' => 'Ghostscript unavailable']);
                }
            } else {
                $emit('step', ['step' => 'compress', 'state' => 'skipped', 'label' => 'Compression skipped', 'detail' => 'Not a PDF']);
            }

            $digitalPath = $fullPath;
            $digitalSize = filesize($fullPath);
            $drive->increment('used_space', $digitalSize);

        } else {
            $emit('step', ['step' => 'file',     'state' => 'skipped', 'label' => 'No digital copy']);
            $emit('step', ['step' => 'compress', 'state' => 'skipped', 'label' => 'No digital copy']);
        }

        $emit('step', ['step' => 'record', 'state' => 'active', 'label' => 'Saving archive record…']);

        try {
            $chart = DB::transaction(function () use ($data, $box, $digitalPath, $digitalSize) {
                $retentionEndDate = $this->calculateRetentionDate($data);

                $chart = ArchivedChart::create([
                    'patient_id'             => $data['patient_id'],
                    'case_number'            => $data['case_number'],
                    'admission_date'         => $data['admission_date'],
                    'discharge_date'         => $data['discharge_date'] ?? null,
                    'archived_date'          => Carbon::today(),
                    'physical_location_id'   => $data['physical_location_id'] ?? null,
                    'digital_copy_path'      => $digitalPath,
                    'digital_copy_size'      => $digitalSize,
                    'total_pages'            => $data['total_pages'] ?? 0,
                    'archived_by'            => $data['archived_by'],
                    'status'                 => 'archived',
                    'compression_status'     => 'done',
                    'retention_period_years' => isset($data['retention_period']) && $data['retention_period'] === 'permanent'
                                                    ? null : ($data['retention_period'] ?? null),
                    'retention_end_date'     => $retentionEndDate,
                    'notes'                  => $data['notes'] ?? null,
                ]);

                if ($box) {
                    LocationHistory::create([
                        'archived_chart_id' => $chart->id,
                        'from_box_id'       => null,
                        'to_box_id'         => $box->id,
                        'reason'            => 'Initial archive',
                        'moved_by'          => $data['archived_by'],
                        'moved_at'          => now(),
                    ]);
                }

                AuditLog::record('archive_chart', 'archived_charts', $chart->id, null, $chart->toArray());

                return $chart;
            });
        } catch (Exception $e) {
            $emit('step', ['step' => 'record', 'state' => 'error', 'label' => 'Database error']);
            $emit('error', ['message' => $e->getMessage()]);
            return;
        }

        if ($box) {
            try { $this->checkBoxCapacity($box); } catch (Exception $e) { /* non-fatal */ }
        }

        $emit('step', ['step' => 'record', 'state' => 'done', 'label' => 'Archive record saved']);
        $emit('done', ['redirect' => route('charts.show', $chart), 'message' => 'Chart archived successfully.']);
    }

    // =========================================================================
    // EXISTING: moveChart / destroyChart — unchanged
    // =========================================================================

    public function moveChart(ArchivedChart $chart, int $newBoxId, string $reason, ?string $notes = null): void
    {
        DB::transaction(function () use ($chart, $newBoxId, $reason, $notes) {
            $newBox = FolderBox::findOrFail($newBoxId);

            if (!$newBox->canAcceptChart()) {
                throw new Exception("Box {$newBox->box_number} is full. Please select another box.");
            }

            $oldBoxId = $chart->physical_location_id;
            $chart->update(['physical_location_id' => $newBoxId]);

            LocationHistory::create([
                'archived_chart_id' => $chart->id,
                'from_box_id'       => $oldBoxId,
                'to_box_id'         => $newBoxId,
                'reason'            => $reason,
                'notes'             => $notes,
                'moved_by'          => Auth::id(),
                'moved_at'          => now(),
            ]);

            AuditLog::record('move_chart', 'archived_charts', $chart->id,
                ['physical_location_id' => $oldBoxId],
                ['physical_location_id' => $newBoxId, 'reason' => $reason]
            );

            $this->checkBoxCapacity($newBox);
        });
    }

    public function destroyChart(ArchivedChart $chart, string $reason): void
    {
        DB::transaction(function () use ($chart, $reason) {
            $oldValues = $chart->toArray();

            if ($chart->digital_copy_path && file_exists($chart->digital_copy_path)) {
                $driveDir   = dirname(dirname($chart->digital_copy_path));
                $deletedDir = $driveDir . DIRECTORY_SEPARATOR . 'deleted';
                if (!is_dir($deletedDir)) {
                    mkdir($deletedDir, 0755, true);
                }
                $destFile = $deletedDir . DIRECTORY_SEPARATOR . basename($chart->digital_copy_path);
                rename($chart->digital_copy_path, $destFile);
            }

            $chart->update([
                'status'           => 'destroyed',
                'destroyed_date'   => Carbon::today(),
                'destroyed_reason' => $reason,
                'destroyed_by'     => Auth::id(),
            ]);

            AuditLog::record('destroy_chart', 'archived_charts', $chart->id, $oldValues, $chart->fresh()->toArray());
        });
    }

    // =========================================================================
    // Ghostscript — private, used by both archiveChart() and compressChartFile()
    // =========================================================================

    private function compressPdf(string $inputPath): string
    {
        $gsPath = $this->resolveGhostscriptBinary();

        $preset = SystemSetting::getValue('gs_pdf_preset', 'ebook');
        if (!in_array($preset, self::GS_PRESETS, true)) {
            $preset = 'ebook';
        }

        $tmpPath = $inputPath . '.gs_tmp.pdf';

        $cmd = sprintf(
            '%s -q -dNOPAUSE -dBATCH -dSAFER -sDEVICE=pdfwrite'
            . ' -dPDFSETTINGS=/%s'
            . ' -dCompatibilityLevel=1.4'
            . ' -sOutputFile=%s %s 2>&1',
            escapeshellarg($gsPath),
            escapeshellarg($preset),
            escapeshellarg($tmpPath),
            escapeshellarg($inputPath)
        );

        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0 || !file_exists($tmpPath)) {
            Log::warning('Ghostscript compression failed; keeping original file.', [
                'input'    => $inputPath,
                'preset'   => $preset,
                'exitCode' => $exitCode,
                'output'   => implode("\n", $output),
            ]);
            @unlink($tmpPath);
            return $inputPath;
        }

        $originalSize   = filesize($inputPath);
        $compressedSize = filesize($tmpPath);

        if ($compressedSize >= $originalSize) {
            @unlink($tmpPath);
            Log::info('Ghostscript: compressed file was not smaller; original kept.', [
                'original_bytes'   => $originalSize,
                'compressed_bytes' => $compressedSize,
                'file'             => basename($inputPath),
            ]);
            return $inputPath;
        }

        rename($tmpPath, $inputPath);

        Log::info('Ghostscript: PDF compressed successfully.', [
            'file'             => basename($inputPath),
            'preset'           => $preset,
            'original_bytes'   => $originalSize,
            'compressed_bytes' => $compressedSize,
            'savings_pct'      => round((1 - $compressedSize / $originalSize) * 100, 1),
        ]);

        return $inputPath;
    }

    private function resolveGhostscriptBinary(): string
    {
        $configured = SystemSetting::getValue('gs_binary_path', null);
        if ($configured && file_exists($configured)) {
            return $configured;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $searchRoots = [
                'C:\\Program Files\\gs',
                'C:\\Program Files (x86)\\gs',
                'C:\\gs',
            ];

            foreach ($searchRoots as $root) {
                if (!is_dir($root)) continue;
                $versionDirs = glob($root . '\\gs*', GLOB_ONLYDIR);
                if (!$versionDirs) continue;
                rsort($versionDirs);
                foreach ($versionDirs as $vDir) {
                    foreach (['gswin64c.exe', 'gswin32c.exe', 'gs.exe'] as $bin) {
                        $candidate = $vDir . '\\bin\\' . $bin;
                        if (file_exists($candidate)) return $candidate;
                    }
                }
            }

            $found = trim((string) shell_exec('where gswin64c.exe 2>NUL'));
            if (!$found) $found = trim((string) shell_exec('where gswin32c.exe 2>NUL'));
            if (!$found) $found = trim((string) shell_exec('where gs.exe 2>NUL'));
            $found = strtok($found, "\n\r");
            if ($found && file_exists($found)) return $found;

            throw new Exception(
                'Ghostscript is not installed or cannot be found. '
                . 'Download the Windows installer from https://ghostscript.com/releases/ '
                . 'and set the \'gs_binary_path\' system setting to the full path of '
                . 'gswin64c.exe, e.g. C:\\Program Files\\gs\\gs10.04.0\\bin\\gswin64c.exe'
            );
        }

        $candidates = ['/usr/bin/gs', '/usr/local/bin/gs', '/opt/homebrew/bin/gs', '/usr/local/opt/ghostscript/bin/gs'];
        foreach ($candidates as $path) {
            if (file_exists($path)) return $path;
        }

        $found = trim((string) shell_exec('which gs 2>/dev/null'));
        if ($found && file_exists($found)) return $found;

        throw new Exception(
            'Ghostscript is not installed or cannot be found. '
            . 'Install it (e.g. `apt install ghostscript`) or set the '
            . "'gs_binary_path' system setting to its full path."
        );
    }

    // =========================================================================
    // Shared helpers
    // =========================================================================

    private function generateFilename(Patient $patient, array $data): string
    {
        $lastName  = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $patient->last_name));
        $firstName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $patient->first_name));
        $mrNumber  = preg_replace('/[^a-zA-Z0-9]/', '_', $patient->medical_record_number);
        $admDate   = Carbon::parse($data['admission_date'])->format('Y-m-d');

        return "{$lastName}_{$firstName}_{$mrNumber}_{$admDate}.pdf";
    }

    private function calculateRetentionDate(array $data): ?Carbon
    {
        if (!isset($data['retention_period']) || $data['retention_period'] === 'permanent') {
            return null;
        }

        $years         = (int) $data['retention_period'];
        $calculateFrom = SystemSetting::getValue('retention_calculate_from', 'discharge_date');
        $baseDate      = $calculateFrom === 'admission_date'
            ? Carbon::parse($data['admission_date'])
            : Carbon::parse($data['discharge_date'] ?? $data['admission_date']);

        return $baseDate->addYears($years);
    }

    private function checkBoxCapacity(FolderBox $box): void
    {
        $box->refresh();
        $pct            = $box->fill_percentage;
        $warnThreshold  = SystemSetting::getValue('box_warning_threshold', 80);
        $blockThreshold = SystemSetting::getValue('box_block_threshold', 95);

        if ($pct >= $blockThreshold) {
            Notification::sendToAll(
                'box_full',
                'Box Full',
                "Box {$box->box_number} ({$box->box_code}) is now full ({$pct}%). Please use another box.",
                'both'
            );
        } elseif ($pct >= $warnThreshold) {
            Notification::sendToAll(
                'box_warning',
                'Box Nearly Full',
                "Box {$box->box_number} ({$box->box_code}) is {$pct}% full.",
                'dashboard'
            );
        }
    }
}