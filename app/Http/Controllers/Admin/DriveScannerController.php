<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExternalDrive;
use App\Models\ArchivedChart;
use App\Models\AuditLog;
use App\Services\DriveScanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DriveScannerController extends Controller
{
    public function __construct(private DriveScanner $scanner) {}

    public function index()
    {
        $drives = ExternalDrive::orderByDesc('is_primary')->orderBy('name')->get();
        return view('admin.scanner.index', compact('drives'));
    }

    public function scan(ExternalDrive $drive)
    {
        $result = $this->scanner->scan($drive);
        AuditLog::record('drive_scan', 'external_drives', $drive->id, null, $result['summary']);

        // Store in session, then redirect to a GET URL
        session(['scanner_result_' . $drive->id => $result]);
        return redirect()->route('admin.scanner.result', $drive);
    }

    public function result(ExternalDrive $drive)
    {
        $result = session('scanner_result_' . $drive->id);
        if (!$result) {
            return redirect()->route('admin.scanner.index')
                ->withErrors(['error' => 'No scan result. Please run a scan first.']);
        }
        return view('admin.scanner.result', compact('result'));
    }

    public function search(Request $request, ExternalDrive $drive): mixed
    {
        $query   = $request->get('q', '');
        $type    = $request->get('type', 'all');

        $drivePath   = rtrim($drive->drive_path, '/\\');
        $archiveDir  = $drivePath . DIRECTORY_SEPARATOR . 'archives';
        $archiveAccessible = is_dir($archiveDir);

        $filesOnDisk = collect();
        $dbResults   = collect();

        if (strlen($query) >= 2) {

            // ── Search physical files on this drive ───────────────────────────
            if (($type === 'filename' || $type === 'all') && $archiveAccessible) {
                foreach (scandir($archiveDir) as $file) {
                    if ($file === '.' || $file === '..') continue;
                    if (!str_contains(strtolower($file), strtolower($query))) continue;

                    $fullPath = $archiveDir . DIRECTORY_SEPARATOR . $file;
                    if (!is_file($fullPath)) continue;

                    $size  = filesize($fullPath);
                    $mb    = $size / 1048576;
                    $szFmt = $mb >= 1024 ? number_format($mb / 1024, 2) . ' GB' : number_format($mb, 2) . ' MB';

                    // Try to find matching DB record by path or filename
                    $chart = \App\Models\ArchivedChart::where('digital_copy_path', $fullPath)
                        ->orWhere('digital_copy_path', 'like', '%' . $file)
                        ->with('patient')
                        ->first();

                    $filesOnDisk->push([
                        'filename'       => $file,
                        'full_path'      => $fullPath,
                        'size'           => $size,
                        'size_formatted' => $szFmt,
                        'modified'       => date('m/d/Y H:i', filemtime($fullPath)),
                        'chart'          => $chart,
                    ]);
                }
            }

            // ── Search DB records ─────────────────────────────────────────────
            $dbQuery = \App\Models\ArchivedChart::with(['patient', 'physicalLocation'])
                ->where(function ($q) use ($query, $type, $drive) {
                    // Limit to files on this drive
                    $q->where('digital_copy_path', 'like', '%' . rtrim($drive->drive_path, '/\\') . '%');

                    if ($type === 'filename' || $type === 'all') {
                        $q->where(function ($sq) use ($query) {
                            $sq->where('digital_copy_path', 'like', '%' . $query . '%');
                        });
                    }
                    if ($type === 'patient' || $type === 'all') {
                        $q->orWhereHas('patient', function ($sq) use ($query) {
                            $sq->where('first_name', 'like', '%' . $query . '%')
                               ->orWhere('last_name', 'like', '%' . $query . '%')
                               ->orWhere('medical_record_number', 'like', '%' . $query . '%');
                        });
                    }
                    if ($type === 'case' || $type === 'all') {
                        $q->orWhere('case_number', 'like', '%' . $query . '%');
                    }
                });

            $dbResults = $dbQuery->orderByDesc('archived_date')->get();
        }

        return view('admin.scanner.search', compact(
            'drive', 'query', 'type',
            'filesOnDisk', 'dbResults',
            'archiveDir', 'archiveAccessible'
        ));
    }

    public function download(Request $request, ExternalDrive $drive): mixed
    {
        $path = $request->get('path');

        if (!$path || !file_exists($path)) {
            abort(404, 'File not found on drive.');
        }

        // Security: file must be within this drive's path
        $drivePath = rtrim($drive->drive_path, '/\\');
        if (!str_starts_with(realpath($path), realpath($drivePath))) {
            abort(403, 'Access denied.');
        }

        return response()->download($path, basename($path));
    }

    /**
     * Fix a missing file path — update DB to point to correct location
     */
    public function fixPath(Request $request)
    {
        $data = $request->validate([
            'chart_id'  => 'required|exists:archived_charts,id',
            'new_path'  => 'required|string',
        ]);

        if (!file_exists($data['new_path'])) {
            return back()->withErrors(['error' => 'File does not exist at: ' . $data['new_path']]);
        }

        $chart = ArchivedChart::findOrFail($data['chart_id']);
        $old   = $chart->digital_copy_path;

        $chart->update([
            'digital_copy_path' => $data['new_path'],
            'digital_copy_size' => filesize($data['new_path']),
        ]);

        AuditLog::record('fix_file_path', 'archived_charts', $chart->id,
            ['digital_copy_path' => $old],
            ['digital_copy_path' => $data['new_path']]
        );

        return back()->with('success', "Path updated for chart {$chart->case_number}.");
    }

    /**
     * Clear a broken DB path (file confirmed missing)
     */
    public function clearPath(Request $request)
    {
        $data = $request->validate([
            'chart_id' => 'required|exists:archived_charts,id',
        ]);

        $chart = ArchivedChart::findOrFail($data['chart_id']);
        $old   = $chart->digital_copy_path;

        $chart->update([
            'digital_copy_path' => null,
            'digital_copy_size' => 0,
        ]);

        AuditLog::record('clear_file_path', 'archived_charts', $chart->id,
            ['digital_copy_path' => $old],
            ['digital_copy_path' => null]
        );

        return back()->with('success', "Cleared missing path for chart {$chart->case_number}.");
    }

    public function deleteOrphan(Request $request)
    {
        $data = $request->validate([
            'full_path' => 'required|string',
        ]);

        $path = $data['full_path'];

        // Safety: only allow deletion from known drive paths
        $knownPaths = ExternalDrive::pluck('drive_path')->map(fn($p) => rtrim($p, '/\\'))->toArray();
        $allowed = false;
        foreach ($knownPaths as $drivePath) {
            if (str_starts_with($path, $drivePath)) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            return back()->withErrors(['error' => 'Path is not on a known drive.']);
        }

        if (!file_exists($path)) {
            return back()->withErrors(['error' => 'File not found: ' . $path]);
        }

        unlink($path);
        AuditLog::record('delete_orphan_file', null, null, null, ['path' => $path]);

        // Re-scan so the result page reflects the deletion
        $drive = ExternalDrive::all()->first(function ($d) use ($path) {
            $dp = rtrim($d->drive_path, '/\\');
            return str_starts_with($path, $dp);
        });

        if ($drive) {
            $result = $this->scanner->scan($drive);
            session(['scanner_result_' . $drive->id => $result]);
            return redirect()->route('admin.scanner.result', $drive)
                ->with('success', 'Orphaned file deleted: ' . basename($path));
        }

        return redirect()->route('admin.scanner.index')
            ->with('success', 'Orphaned file deleted: ' . basename($path));
    }
}