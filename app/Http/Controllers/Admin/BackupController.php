<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BackupConfiguration;
use App\Models\BackupLog;
use App\Models\ExternalDrive;
use App\Models\AuditLog;
use App\Services\BackupService;
use Illuminate\Http\Request;

class BackupController extends Controller
{
    public function __construct(private BackupService $backupService) {}

    public function index()
    {
        $configs    = BackupConfiguration::with(['destinationDrive', 'latestLog'])->get();
        $recentLogs = BackupLog::with('configuration')->latest('start_time')->take(20)->get();

        return view('admin.backup.index', compact('configs', 'recentLogs'));
    }

    public function create()
    {
        $drives = ExternalDrive::where('status', 'active')->orderBy('name')->get();
        return view('admin.backup.create', compact('drives'));
        // same $drives used for both destination and source checkboxes
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                 => 'required|string|max:100',
            'backup_type'          => 'required|in:database,database_files',
            'frequency'            => 'required|in:daily,weekly,monthly',
            'day_of_week'          => 'nullable|integer|min:0|max:6',
            'day_of_month'         => 'nullable|integer|min:1|max:28',
            'time_of_day'          => 'required|date_format:H:i',
            'destination_drive_id' => 'required|exists:external_drives,id',
            'retention_count'      => 'required|integer|min:1|max:100',
            'is_active'            => 'boolean',
            'source_drive_ids'     => 'nullable|array',
            'source_drive_ids.*'   => 'exists:external_drives,id',
        ]);

        $data['time_of_day'] = $data['time_of_day'] . ':00';
        $data['is_active']   = $request->boolean('is_active');

        $config = BackupConfiguration::create($data);
        $config->update(['next_run_at' => $this->backupService->calculateNextRun($config)]);

        if ($data['backup_type'] === 'database_files') {
            $config->sourceDrives()->sync($request->input('source_drive_ids', []));
        }

        AuditLog::record('create_backup_config', 'backup_configurations', $config->id, null, $config->toArray());

        return redirect()->route('admin.backup.index')->with('success', 'Backup schedule created.');
    }

    public function edit(BackupConfiguration $backup)
    {
        $drives = ExternalDrive::where('status', 'active')->orderBy('name')->get();
        $selectedSourceIds = $backup->sourceDrives->pluck('id')->toArray();
        return view('admin.backup.edit', compact('backup', 'drives', 'selectedSourceIds'));
    }

    public function update(Request $request, BackupConfiguration $backup)
    {
        $data = $request->validate([
            'name'                 => 'required|string|max:100',
            'backup_type'          => 'required|in:database,database_files',
            'frequency'            => 'required|in:daily,weekly,monthly',
            'day_of_week'          => 'nullable|integer|min:0|max:6',
            'day_of_month'         => 'nullable|integer|min:1|max:28',
            'time_of_day'          => 'required|date_format:H:i',
            'destination_drive_id' => 'required|exists:external_drives,id',
            'retention_count'      => 'required|integer|min:1|max:100',
            'is_active'            => 'boolean',
            'source_drive_ids'     => 'nullable|array',
            'source_drive_ids.*'   => 'exists:external_drives,id',
        ]);

        $data['time_of_day'] = $data['time_of_day'] . ':00';
        $data['is_active']   = $request->boolean('is_active');

        $old = $backup->toArray();
        $backup->update($data);
        $backup->update(['next_run_at' => $this->backupService->calculateNextRun($backup)]);

        // Sync source drives — clear if switched back to database-only
        if ($data['backup_type'] === 'database_files') {
            $backup->sourceDrives()->sync($request->input('source_drive_ids', []));
        } else {
            $backup->sourceDrives()->detach();
        }

        AuditLog::record('update_backup_config', 'backup_configurations', $backup->id, $old, $backup->toArray());

        return redirect()->route('admin.backup.index')->with('success', 'Backup schedule updated.');
    }

    public function destroy(BackupConfiguration $backup)
    {
        AuditLog::record('delete_backup_config', 'backup_configurations', $backup->id, $backup->toArray());
        $backup->delete();
        return redirect()->route('admin.backup.index')->with('success', 'Backup schedule deleted.');
    }

    public function runNow(BackupConfiguration $backup)
    {
        try {
            $log = $this->backupService->runBackup($backup);
            $msg = $log->status === 'success'
                ? "Backup completed successfully. Files: {$log->files_count}, Duration: {$log->duration}"
                : "Backup failed: {$log->error_message}";

            return back()->with($log->status === 'success' ? 'success' : 'error', $msg);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function logs(BackupConfiguration $backup)
    {
        $logs = $backup->logs()->latest('start_time')->paginate(30);
        return view('admin.backup.logs', compact('backup', 'logs'));
    }
}
