<?php

namespace App\Services;

use App\Models\ArchivedChart;
use App\Models\BackupConfiguration;
use App\Models\BackupLog;
use App\Models\CheckoutHistory;
use App\Models\FolderBox;
use App\Models\SystemSetting;
use Carbon\Carbon;

class SystemHealthService
{
    const LOG_LEVELS   = ['EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR', 'WARNING'];
    const MAX_LOG_READ = 512000; // 500KB

    // ─── Laravel Log Scanner ─────────────────────────────────────────────────

    public function scanLaravelLogs(int $maxEntries = 30): array
    {
        $logPath = storage_path('logs/laravel.log');
        if (!file_exists($logPath)) return [];

        $handle = fopen($logPath, 'r');
        fseek($handle, max(0, filesize($logPath) - self::MAX_LOG_READ));
        $content = fread($handle, self::MAX_LOG_READ);
        fclose($handle);

        $pattern = '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.('
                 . implode('|', self::LOG_LEVELS)
                 . '): (.+?)(?=\[\d{4}-\d{2}-\d{2}|\z)/s';

        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        $entries = [];
        foreach (array_slice(array_reverse($matches), 0, $maxEntries) as $match) {
            $fullMessage = trim($match[3]);
            $lines       = explode("\n", $fullMessage);
            $message     = trim($lines[0]);
            $stackTrace  = count($lines) > 1
                ? trim(implode("\n", array_slice($lines, 1, 10)))
                : '';

            $fileRef = '';
            if (preg_match('/in (.+?) on line (\d+)/', $fullMessage, $fm)) {
                $fileRef = basename($fm[1]) . ':' . $fm[2];
            }

            $entries[] = [
                'source'      => 'log',
                'issue_type'  => 'laravel_' . strtolower($match[2]),
                'severity'    => in_array($match[2], ['EMERGENCY', 'ALERT', 'CRITICAL']) ? 'critical'
                               : (strtolower($match[2]) === 'warning' ? 'warning' : 'error'),
                'timestamp'   => $match[1],
                'level'       => $match[2],
                'message'     => $message,
                'stack_trace' => $stackTrace,
                'file_ref'    => $fileRef,
            ];
        }

        // Deduplicate same error message, keep latest
        $seen    = [];
        $deduped = [];
        foreach ($entries as $e) {
            $key = md5($e['message']);
            if (!in_array($key, $seen)) {
                $seen[]    = $key;
                $deduped[] = $e;
            }
        }

        return $deduped;
    }

    // ─── Database Integrity Scanner ───────────────────────────────────────────

    public function scanDatabaseIntegrity(): array
    {
        $issues = [];

        // 1. Overdue checkouts still marked 'active'
        $overdueCount = CheckoutHistory::where('status', 'active')
            ->where('expected_return_date', '<', Carbon::today())
            ->count();

        if ($overdueCount > 0) {
            $issues[] = [
                'source'      => 'database',
                'issue_type'  => 'overdue_checkouts_not_marked',
                'severity'    => 'warning',
                'description' => "{$overdueCount} checkout(s) are past their return date but still marked 'active' instead of 'overdue'.",
                'affected'    => $overdueCount,
                'fix_action'  => 'fix_overdue_checkouts',
            ];
        }

        // 2. Active backup schedules with null next_run_at
        $missingNextRun = BackupConfiguration::where('is_active', true)
            ->whereNull('next_run_at')
            ->count();

        if ($missingNextRun > 0) {
            $issues[] = [
                'source'      => 'database',
                'issue_type'  => 'backup_missing_next_run',
                'severity'    => 'error',
                'description' => "{$missingNextRun} active backup schedule(s) have no next_run_at — they will never run automatically.",
                'affected'    => $missingNextRun,
                'fix_action'  => 'fix_backup_next_run',
            ];
        }

        // 3. Backup retention_count = 0 or null
        $badRetention = BackupConfiguration::where(function ($q) {
            $q->whereNull('retention_count')->orWhere('retention_count', '<=', 0);
        })->count();

        if ($badRetention > 0) {
            $issues[] = [
                'source'      => 'database',
                'issue_type'  => 'backup_invalid_retention',
                'severity'    => 'warning',
                'description' => "{$badRetention} backup schedule(s) have an invalid retention_count (0 or null) — all backups may be deleted.",
                'affected'    => $badRetention,
                'fix_action'  => 'fix_backup_retention',
            ];
        }

        // 4. Recently failed backups (last 24h)
        $failedBackups = BackupLog::where('status', 'failed')
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->count();

        if ($failedBackups > 0) {
            $issues[] = [
                'source'      => 'database',
                'issue_type'  => 'recent_backup_failures',
                'severity'    => 'critical',
                'description' => "{$failedBackups} backup(s) failed in the last 24 hours.",
                'affected'    => $failedBackups,
                'fix_action'  => 'retry_failed_backups',
            ];
        }

        // 5. Boxes with capacity = 0
        $zeroCap    = FolderBox::where('is_active', true)->where('capacity', 0)->count();
        if ($zeroCap > 0) {
            $defaultCap = (int) SystemSetting::getValue('box_default_capacity', 50);
            $issues[] = [
                'source'      => 'database',
                'issue_type'  => 'box_zero_capacity',
                'severity'    => 'warning',
                'description' => "{$zeroCap} active box(es) have a capacity of 0 — charts cannot be assigned. Default capacity is {$defaultCap}.",
                'affected'    => $zeroCap,
                'fix_action'  => 'fix_box_zero_capacity',
                'meta'        => ['default_capacity' => $defaultCap],
            ];
        }

        // 6. Archived charts with no physical location (orphaned)
        $orphaned = ArchivedChart::where('status', 'archived')
            ->whereNull('physical_location_id')
            ->count();

        if ($orphaned > 0) {
            $issues[] = [
                'source'      => 'database',
                'issue_type'  => 'orphaned_charts',
                'severity'    => 'warning',
                'description' => "{$orphaned} archived chart(s) have no physical location assigned.",
                'affected'    => $orphaned,
                'fix_action'  => 'flag_orphaned_charts',
            ];
        }

        // 7. Charts 30+ days past retention end date, not destroyed
        $expiredRetention = ArchivedChart::whereNotNull('retention_end_date')
            ->where('retention_end_date', '<', Carbon::today()->subDays(30))
            ->whereIn('status', ['archived', 'checked_out'])
            ->count();

        if ($expiredRetention > 0) {
            $issues[] = [
                'source'      => 'database',
                'issue_type'  => 'expired_retention_charts',
                'severity'    => 'warning',
                'description' => "{$expiredRetention} chart(s) are 30+ days past their retention end date and have not been marked for destruction.",
                'affected'    => $expiredRetention,
                'fix_action'  => 'notify_expired_retention',
            ];
        }

        return $issues;
    }
}