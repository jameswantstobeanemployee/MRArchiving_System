<?php

namespace App\Services;

use App\Models\BackupConfiguration;
use App\Models\BackupLog;
use App\Models\ExternalDrive;
use App\Models\Notification;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use ZipArchive;

class BackupService
{
    /**
     * Minimum free disk space (in bytes) required on the destination drive
     * before we even attempt to write. Default: 500 MB.
     */
    private const MIN_FREE_SPACE_BYTES = 500 * 1024 * 1024;

    /**
     * How many consecutive failures trigger an escalation notification.
     */
    private const CONSECUTIVE_FAILURE_ALERT_THRESHOLD = 3;

    // ─────────────────────────────────────────────────────────────────────────────
    // Public API
    // ─────────────────────────────────────────────────────────────────────────────

    public function runBackup(BackupConfiguration $config): BackupLog
    {
        $log = BackupLog::create([
            'backup_configuration_id' => $config->id,
            'status'      => 'running',
            'start_time'  => now()->utc(),
            'files_count' => 0,
            'total_size'  => 0,
        ]);

        try {
            $filesCount = 0;
            $totalSize  = 0;
            $drive      = $config->destinationDrive;

            // ── Guard: destination drive record must exist ────────────────────────
            if (!$drive) {
                throw new \Exception('No destination drive configured for this backup.');
            }

            // ── Guard: destination path must be physically accessible ─────────────
            $drivePath = rtrim($drive->drive_path, '/\\');
            if (!is_dir($drivePath)) {
                $drive->update(['status' => 'error']);

                Notification::sendToAdmins(
                    'drive_unreachable',
                    'Backup Destination Unreachable',
                    "Backup '{$config->name}' could not run — destination drive '{$drive->name}' "
                        . "({$drive->drive_path}) is not connected.",
                    'both'
                );

                throw new \Exception(
                    "Destination drive '{$drive->name}' ({$drive->drive_path}) is not connected or accessible."
                );
            }

            // ── Guard: enough free space on destination ───────────────────────────
            $freeBytes = disk_free_space($drivePath);
            if ($freeBytes !== false && $freeBytes < self::MIN_FREE_SPACE_BYTES) {
                $freeMB = round($freeBytes / 1048576, 1);

                Notification::sendToAdmins(
                    'drive_low_space',
                    'Backup Destination Low on Space',
                    "Backup '{$config->name}' aborted — destination drive '{$drive->name}' "
                        . "has only {$freeMB} MB free (minimum required: "
                        . round(self::MIN_FREE_SPACE_BYTES / 1048576) . " MB).",
                    'both'
                );

                throw new \Exception(
                    "Destination drive '{$drive->name}' has insufficient free space ({$freeMB} MB). "
                        . "Minimum required: " . round(self::MIN_FREE_SPACE_BYTES / 1048576) . " MB."
                );
            }

            // ── Prepare backup directory ──────────────────────────────────────────
            $backupDir  = $drivePath . DIRECTORY_SEPARATOR . 'backups';
            $timestamp  = Carbon::now()->format('Y-m-d_His');
            $backupName = "backup_{$config->id}_{$timestamp}";
            $backupPath = $backupDir . DIRECTORY_SEPARATOR . $backupName;

            if (!is_dir($backupDir)) {
                if (!mkdir($backupDir, 0755, true)) {
                    throw new \Exception("Cannot create backup directory: {$backupDir}");
                }
            }

            // ── Database export ───────────────────────────────────────────────────
            $dbFile = $backupPath . '_db.sql';
            $this->exportDatabase($dbFile);
            $filesCount++;
            $totalSize += filesize($dbFile);

            // ── Files export (if configured) ──────────────────────────────────────
            $skippedDrives = [];

            if ($config->backup_type === 'database_files') {
                $sourceDrives = $config->sourceDrives()->get();

                if ($sourceDrives->isEmpty()) {
                    $skippedDrives[] = '(no source drives selected)';
                }

                foreach ($sourceDrives as $sourceDrive) {
                    $sourcePath  = rtrim($sourceDrive->drive_path, '/\\');
                    $archivePath = $sourcePath . DIRECTORY_SEPARATOR . 'archives';

                    if (!is_dir($sourcePath)) {
                        $skippedDrives[] = $sourceDrive->name;
                        $sourceDrive->update(['status' => 'error']);
                        continue;
                    }

                    if (!is_dir($archivePath)) {
                        continue;
                    }

                    $zipFile = $backupPath . '_files_drive' . $sourceDrive->id . '.zip';
                    $this->zipDirectory($archivePath, $zipFile);
                    $filesCount++;
                    $totalSize += filesize($zipFile);
                }
            }

            // ── Prune old backups ─────────────────────────────────────────────────
            $this->pruneOldBackups($config, $backupDir);

            // ── Determine final status ────────────────────────────────────────────
            $finalStatus = empty($skippedDrives) ? 'success' : 'warning';
            $driveList   = implode(', ', $skippedDrives);
            $warningMsg  = !empty($skippedDrives)
                ? "Backup completed but these drives were unreachable and skipped: {$driveList}"
                : null;

            $log->update([
                'status'        => $finalStatus,
                'end_time'      => now()->utc(),
                'files_count'   => $filesCount,
                'total_size'    => $totalSize,
                'error_message' => $warningMsg,
            ]);

            // ── Advance next_run_at anchored to the ORIGINAL scheduled slot ───────
            // This prevents drift when a backup runs late. We advance from
            // next_run_at (the slot it was supposed to hit), not from now().
            $config->update([
                'last_run_at' => now(),
                'next_run_at' => $this->calculateNextRun($config, $config->next_run_at ?? now()),
            ]);

            $drive->increment('used_space', $totalSize);

            // ── Notifications ─────────────────────────────────────────────────────
            if (!empty($skippedDrives)) {
                Notification::sendToAdmins(
                    'drive_unreachable',
                    'Backup Completed With Warnings',
                    "Backup '{$config->name}' completed but skipped disconnected drives: {$driveList}",
                    'both'
                );
            }

            AuditLog::record('backup_success', 'backup_logs', $log->id, null, [
                'config'         => $config->name,
                'size'           => $totalSize,
                'skipped_drives' => $skippedDrives,
            ]);

        } catch (\Throwable $e) {
            $log->update([
                'status'        => 'failed',
                'end_time'      => now()->utc(),
                'error_message' => $e->getMessage(),
            ]);

            // Still advance next_run_at so the scheduler doesn't retry the
            // same failed slot in a tight loop on the next cron tick.
            $config->update([
                'next_run_at' => $this->calculateNextRun($config, $config->next_run_at ?? now()),
            ]);

            Notification::sendToAdmins(
                'backup_failed',
                'Backup Failed',
                "Backup '{$config->name}' failed: " . $e->getMessage(),
                'both'
            );

            AuditLog::record('backup_failed', 'backup_logs', $log->id, null, [
                'error' => $e->getMessage(),
            ]);

            // ── Escalation: alert if N consecutive failures ───────────────────────
            $this->checkConsecutiveFailures($config);
        }

        return $log;
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // Scheduling helpers
    // ─────────────────────────────────────────────────────────────────────────────

    /**
     * Calculate the next scheduled run date/time for a backup configuration.
     *
     * @param BackupConfiguration $config
     * @param Carbon|null         $anchor  The slot to advance FROM (defaults to now).
     *                                     Pass $config->next_run_at to avoid drift.
     */
    public function calculateNextRun(BackupConfiguration $config, ?Carbon $anchor = null): Carbon
    {
        $anchor = ($anchor ?? Carbon::now())->copy();
        $time   = Carbon::createFromFormat('H:i:s', $config->time_of_day);

        // Base candidate: the anchor date with the scheduled clock time
        $candidate = $anchor->copy()->setTime($time->hour, $time->minute, 0);

        return match ($config->frequency) {
            'daily'   => $this->nextDailyRun($candidate, $anchor),
            'weekly'  => $this->nextWeeklyRun($config, $candidate, $anchor),
            'monthly' => $this->nextMonthlyRun($config, $candidate, $anchor),
            default   => $candidate->isPast() ? $candidate->addDay() : $candidate,
        };
    }

    private function nextDailyRun(Carbon $candidate, Carbon $anchor): Carbon
    {
        return $candidate->lte($anchor) ? $candidate->addDay() : $candidate;
    }

    private function nextWeeklyRun(BackupConfiguration $config, Carbon $candidate, Carbon $anchor): Carbon
    {
        $dayOfWeekMap = [
            0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday',
            3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday',
        ];

        $targetDayName   = $dayOfWeekMap[$config->day_of_week ?? 0];
        $targetDayOfWeek = $config->day_of_week ?? 0;

        // If today is the target day and the candidate time is still in the future, use today
        if ($anchor->dayOfWeek == $targetDayOfWeek && $candidate->gt($anchor)) {
            return $candidate;
        }

        $next = $anchor->copy()->next($targetDayName);
        return $next->setTime($candidate->hour, $candidate->minute, 0);
    }

    private function nextMonthlyRun(BackupConfiguration $config, Carbon $candidate, Carbon $anchor): Carbon
    {
        $targetDay = $config->day_of_month ?? 1;

        $thisMonthCandidate = $anchor->copy()
            ->startOfMonth()
            ->setDay($targetDay)
            ->setTime($candidate->hour, $candidate->minute, 0);

        if ($thisMonthCandidate->gt($anchor)) {
            return $thisMonthCandidate;
        }

        return $anchor->copy()
            ->startOfMonth()
            ->addMonth()
            ->setDay($targetDay)
            ->setTime($candidate->hour, $candidate->minute, 0);
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // Failure escalation
    // ─────────────────────────────────────────────────────────────────────────────

    /**
     * Count how many of the most recent logs for this config are failures.
     * If it reaches the threshold, fire an escalation notification.
     */
    private function checkConsecutiveFailures(BackupConfiguration $config): void
    {
        $recentStatuses = BackupLog::where('backup_configuration_id', $config->id)
            ->whereIn('status', ['success', 'warning', 'failed'])
            ->latest('start_time')
            ->limit(self::CONSECUTIVE_FAILURE_ALERT_THRESHOLD)
            ->pluck('status')
            ->toArray();

        // Only escalate when we have exactly N consecutive failures
        if (count($recentStatuses) < self::CONSECUTIVE_FAILURE_ALERT_THRESHOLD) {
            return;
        }

        $allFailed = collect($recentStatuses)->every(fn($s) => $s === 'failed');

        if (!$allFailed) {
            return;
        }

        $n = self::CONSECUTIVE_FAILURE_ALERT_THRESHOLD;

        Notification::sendToAdmins(
            'backup_consecutive_failures',
            'Backup Failing Repeatedly',
            "Backup '{$config->name}' has failed {$n} times in a row. "
                . "Immediate attention is required — this backup is not protecting your data.",
            'both'
        );

        Log::critical('backup_consecutive_failures', [
            'config_id'   => $config->id,
            'config_name' => $config->name,
            'failures'    => $n,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // Database export
    // ─────────────────────────────────────────────────────────────────────────────

    private function exportDatabase(string $outputFile): void
    {
        $db   = config('database.connections.' . config('database.default'));
        $host = $db['host'];
        $port = $db['port'] ?? 3306;
        $user = $db['username'];
        $pass = $db['password'];
        $name = $db['database'];

        $cmd = "mysqldump --host={$host} --port={$port} --user={$user} --password={$pass} {$name} > \"{$outputFile}\" 2>&1";
        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0) {
            // Fallback: export via PDO for environments without mysqldump
            $this->exportDatabaseViaPDO($outputFile);
        }
    }

    private function exportDatabaseViaPDO(string $outputFile): void
    {
        $pdo    = DB::getPdo();
        $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
        $sql    = "-- Medical Records Archive Backup\n-- Generated: " . now() . "\n\n";
        $sql   .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $sql .= "-- Table: {$table}\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $createRow = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
            $sql .= $createRow['Create Table'] . ";\n\n";

            $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $values = array_map(fn($v) => $v === null ? 'NULL' : $pdo->quote($v), $row);
                $sql .= "INSERT INTO `{$table}` VALUES (" . implode(', ', $values) . ");\n";
            }
            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        file_put_contents($outputFile, $sql);
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // File archiving
    // ─────────────────────────────────────────────────────────────────────────────

    private function zipDirectory(string $source, string $destination): void
    {
        $zip = new ZipArchive();
        if ($zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Cannot create ZIP archive at {$destination}");
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath     = $file->getRealPath();
                $relativePath = substr($filePath, strlen($source) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // Retention / pruning
    // ─────────────────────────────────────────────────────────────────────────────

    private function pruneOldBackups(BackupConfiguration $config, string $backupDir): void
    {
        if (!is_dir($backupDir)) {
            return;
        }

        $prefix  = "backup_{$config->id}_";
        $files   = glob($backupDir . DIRECTORY_SEPARATOR . $prefix . '*');
        $grouped = [];

        foreach ($files as $f) {
            $base = preg_replace('/_(?:db\.sql|files_drive\d+\.zip)$/', '', basename($f));
            $grouped[$base][] = $f;
        }

        krsort($grouped); // newest first

        Log::debug('pruneOldBackups', [
            'config_id'    => $config->id,
            'files_found'  => count($files),
            'groups_found' => count($grouped),
            'keep'         => $config->retention_count,
            'grouped_keys' => array_keys($grouped),
        ]);

        $keep = (int) $config->retention_count;
        $idx  = 0;

        foreach ($grouped as $base => $group) {
            if ($idx >= $keep) {
                foreach ($group as $f) {
                    @unlink($f);
                }
            }                                                           
            $idx++;                 
        }                   
    }                                       
}                                                                                                                                                                                                                                           