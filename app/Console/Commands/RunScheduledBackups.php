<?php

namespace App\Console\Commands;

use App\Models\BackupConfiguration;
use App\Models\BackupLog;
use App\Models\Notification;
use App\Services\BackupService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RunScheduledBackups extends Command
{
    protected $signature   = 'backups:run-scheduled';
    protected $description = 'Run all backup configurations that are due';

    /**
     * How many minutes late a backup must be before an overdue alert is sent.
     * Anything within this window is considered "normal scheduler jitter".
     */
    private const OVERDUE_THRESHOLD_MINUTES = 60;

    /**
     * How many minutes a backup log can stay in "running" status before it is
     * considered a stale/crashed run and forcibly marked as failed.
     */
    private const STALE_RUNNING_THRESHOLD_MINUTES = 120;

    public function __construct(private BackupService $backupService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        // ── Step 1: Clean up any stale "running" logs left by crashed processes ──
        $this->resolveStaleRunningLogs();

        // ── Step 2: Find all due configurations ──────────────────────────────────
        $due = BackupConfiguration::with(['sourceDrives'])
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('next_run_at')
                  ->orWhere('next_run_at', '<=', Carbon::now());
            })
            ->get();

        if ($due->isEmpty()) {
            $this->info('No backups due at this time.');
            return Command::SUCCESS;
        }

        // ── Step 3: Run each due backup ──────────────────────────────────────────
        foreach ($due as $config) {
            $this->notifyIfOverdue($config);

            $this->info("Running backup: {$config->name}");

            $log = $this->backupService->runBackup($config);

            if ($log->status === 'success') {
                $this->info("  ✓ Success — {$log->files_count} file(s), duration: {$log->duration}");
            } elseif ($log->status === 'warning') {
                $this->warn("  ⚠ Warning — {$log->error_message}");
            } else {
                $this->error("  ✗ Failed: {$log->error_message}");
            }
        }

        return Command::SUCCESS;
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────────

    /**
     * If a backup's next_run_at is more than OVERDUE_THRESHOLD_MINUTES in the
     * past, notify admins before running so they know it ran late.
     */
    private function notifyIfOverdue(BackupConfiguration $config): void
    {
        if (!$config->next_run_at) {
            return; // First-ever run — not overdue, just unscheduled
        }

        $overdueMinutes = (int) $config->next_run_at->diffInMinutes(Carbon::now());

        if ($overdueMinutes < self::OVERDUE_THRESHOLD_MINUTES) {
            return;
        }

        $hours   = floor($overdueMinutes / 60);
        $mins    = $overdueMinutes % 60;
        $howLate = $hours > 0 ? "{$hours}h {$mins}m" : "{$mins}m";

        $this->warn("  ⚠ Backup '{$config->name}' is {$howLate} overdue — running now.");

        Notification::sendToAdmins(
            'backup_overdue',
            'Backup Ran Late',
            "Backup '{$config->name}' was {$howLate} overdue when it finally ran. "
                . "Scheduled: {$config->next_run_at->format('Y-m-d H:i')} — "
                . "Actual: " . Carbon::now()->format('Y-m-d H:i') . ". "
                . "Check that the cron job (schedule:run) is running every minute.",
            'both'
        );

        Log::warning('backup_overdue', [
            'config_id'       => $config->id,
            'config_name'     => $config->name,
            'scheduled_at'    => $config->next_run_at->toDateTimeString(),
            'actual_run_at'   => Carbon::now()->toDateTimeString(),
            'overdue_minutes' => $overdueMinutes,
        ]);
    }

    /**
     * Find backup logs that have been stuck in "running" status for longer than
     * STALE_RUNNING_THRESHOLD_MINUTES. This happens when a process crashes mid-
     * backup and never writes the end_time/status. Without this cleanup those
     * logs would stay "running" forever and confuse the dashboard.
     */
    private function resolveStaleRunningLogs(): void
    {
        $cutoff = Carbon::now()->subMinutes(self::STALE_RUNNING_THRESHOLD_MINUTES);

        $staleLogs = BackupLog::where('status', 'running')
            ->where('start_time', '<=', $cutoff)
            ->with('configuration')
            ->get();

        if ($staleLogs->isEmpty()) {
            return;
        }

        foreach ($staleLogs as $staleLog) {
            $staleLog->update([
                'status'        => 'failed',
                'end_time'      => Carbon::now()->utc(),
                'error_message' => 'Backup process appears to have crashed — '
                    . 'log was stuck in "running" status for more than '
                    . self::STALE_RUNNING_THRESHOLD_MINUTES . ' minutes '
                    . 'and was automatically marked as failed.',
            ]);

            $configName = $staleLog->configuration?->name ?? "ID #{$staleLog->backup_configuration_id}";

            $this->warn("  ⚠ Marked stale running log #{$staleLog->id} ({$configName}) as failed.");

            Notification::sendToAdmins(
                'backup_failed',
                'Backup Process Crashed',
                "A backup run for '{$configName}' appears to have crashed. "
                    . "It was stuck in 'running' status since "
                    . $staleLog->start_time->format('Y-m-d H:i')
                    . " and has been automatically marked as failed.",
                'both'
            );

            Log::error('backup_stale_log_resolved', [
                'log_id'      => $staleLog->id,
                'config_name' => $configName,
                'started_at'  => $staleLog->start_time->toDateTimeString(),
            ]);
        }
    }
}