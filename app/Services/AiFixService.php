<?php

namespace App\Services;

use App\Models\AiHealthLog;
use App\Models\ArchivedChart;
use App\Models\BackupConfiguration;
use App\Models\BackupLog;
use App\Models\CheckoutHistory;
use App\Models\FolderBox;
use App\Models\Notification;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AiFixService
{
    public function __construct(private BackupService $backupService) {}

    // ─── Main Entry Point ─────────────────────────────────────────────────────

    public function runFullScan(?int $triggeredBy = null): array
    {
        $scanId    = (string) Str::uuid();
        $health    = app(SystemHealthService::class);
        $logIssues = $health->scanLaravelLogs();
        $dbIssues  = $health->scanDatabaseIntegrity();
        $allIssues = array_merge($dbIssues, $logIssues);

        if (empty($allIssues)) {
            return [
                'scan_id' => $scanId,
                'total'   => 0,
                'fixed'   => 0,
                'skipped' => 0,
                'failed'  => 0,
                'logs'    => [],
                'healthy' => true,
            ];
        }

        $aiAnalysis = $this->analyseWithClaude($allIssues);

        $results = [];
        foreach ($allIssues as $index => $issue) {
            $reasoning = $aiAnalysis[$index] ?? [
                'reasoning'  => 'The AI analysis service is temporarily unavailable. The system will still attempt to apply the fix automatically.',
                'should_fix' => true,
                'severity'   => $issue['severity'] ?? 'error',
            ];

            $fixResult = $this->applyFix($issue);

            $log = AiHealthLog::create([
                'scan_id'           => $scanId,
                'source'            => $issue['source'],
                'issue_type'        => $issue['issue_type'],
                'severity'          => $reasoning['severity'] ?? $issue['severity'],
                'issue_description' => $issue['description'] ?? $issue['message'] ?? 'An issue was detected but no description is available.',
                'ai_reasoning'      => $reasoning['reasoning'],
                'fix_action'        => $issue['fix_action'] ?? null,
                'fix_payload'       => $fixResult['payload'] ?? null,
                'was_fixed'         => $fixResult['success'],
                'fix_status'        => $fixResult['status'],
                'fix_error'         => isset($fixResult['error']) ? $this->humaniseError($fixResult['error']) : null,
                'triggered_by'      => $triggeredBy,
            ]);

            $results[] = $log;
        }

        $fixed   = collect($results)->where('fix_status', 'success')->count();
        $failed  = collect($results)->where('fix_status', 'failed')->count();
        $skipped = collect($results)->where('fix_status', 'skipped')->count();

        return [
            'scan_id' => $scanId,
            'total'   => count($results),
            'fixed'   => $fixed,
            'skipped' => $skipped,
            'failed'  => $failed,
            'logs'    => $results,
            'healthy' => false,
        ];
    }

    // ─── Gemini API Analysis ──────────────────────────────────────────────────

    private function analyseWithClaude(array $issues): array
    {
        $issueList = '';
        foreach ($issues as $i => $issue) {
            $desc = $issue['description'] ?? $issue['message'] ?? 'Unknown';
            $issueList .= ($i + 1) . ". [{$issue['source']}] [{$issue['severity']}] {$issue['issue_type']}: {$desc}\n";
            if (!empty($issue['stack_trace'])) {
                $issueList .= "   Stack: " . substr($issue['stack_trace'], 0, 300) . "\n";
            }
        }

        $count  = count($issues);
        $prompt = <<<PROMPT
You are an AI system health assistant for a medical records archive system built with Laravel.
The system manages: patients, archived medical charts, physical storage boxes, checkout history, backup schedules, and external drives.

Analyse these {$count} system issues and return ONLY a valid JSON array with exactly {$count} elements.
Each element must have:
- "reasoning": string — plain English explanation of the issue and what fix will be applied
- "should_fix": boolean — whether the system should attempt to auto-fix
- "severity": string — one of: critical, error, warning, info

Do NOT include any text outside the JSON array. No markdown, no code fences, no explanation.

Issues to analyse:
{$issueList}
PROMPT;

        try {
            $apiKey  = config('services.gemini.key');
            $model   = 'gemini-1.5-flash';
            $url     = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            $response = \Illuminate\Support\Facades\Http::timeout(30)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature'     => 0.2,
                    'maxOutputTokens' => 2000,
                ],
            ]);

            if (!$response->successful()) {
                \Illuminate\Support\Facades\Log::warning('AI Health: Gemini API returned an error response.', [
                    'status' => $response->status(),
                ]);
                return $this->fallbackAnalysis($issues, 'The AI analysis service returned an error. Fixes were applied using default rules.');
            }

            $content = $response->json('candidates.0.content.parts.0.text', '');

            if (empty(trim($content))) {
                \Illuminate\Support\Facades\Log::warning('AI Health: Gemini returned an empty response. Falling back to default analysis.');
                return $this->fallbackAnalysis($issues, 'The AI analysis service returned an empty response. Fixes were applied using default rules.');
            }

            // Strip markdown code fences if present
            $content = preg_replace('/```json\s*|\s*```/', '', trim($content));
            $parsed  = json_decode($content, true);

            if (!is_array($parsed) || count($parsed) !== $count) {
                \Illuminate\Support\Facades\Log::warning('AI Health: Gemini response could not be parsed. Falling back to default analysis.', [
                    'expected_count' => $count,
                    'actual_count'   => is_array($parsed) ? count($parsed) : 'not an array',
                ]);
                return $this->fallbackAnalysis($issues, 'The AI analysis service returned an unexpected format. Fixes were applied using default rules.');
            }

            return $parsed;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Illuminate\Support\Facades\Log::error('AI Health: Could not connect to Gemini API. ' . $e->getMessage());
            return $this->fallbackAnalysis($issues, 'The AI analysis service could not be reached (connection timeout). Fixes were applied using default rules.');

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('AI Health: Unexpected error during Gemini analysis. ' . $e->getMessage());
            return $this->fallbackAnalysis($issues, 'The AI analysis service encountered an unexpected error. Fixes were applied using default rules.');
        }
    }

    /**
     * Generate a user-friendly fallback analysis when Gemini is unavailable.
     * Instead of cryptic log warnings, each issue gets a plain-English description.
     */
    private function fallbackAnalysis(array $issues, string $reason): array
    {
        return array_map(function ($issue) use ($reason) {
            $type = ucwords(str_replace('_', ' ', $issue['issue_type'] ?? 'unknown issue'));
            return [
                'reasoning'  => "{$reason} Issue detected: {$type}.",
                'should_fix' => true,
                'severity'   => $issue['severity'] ?? 'error',
            ];
        }, $issues);
    }

    /**
     * Convert a raw PHP exception message into a sentence a non-developer can understand.
     */
    private function humaniseError(string $error): string
    {
        $map = [
            'SQLSTATE'                    => 'A database error occurred while applying the fix. Please try running the scan again.',
            'Connection refused'          => 'The system could not connect to the database. Please check your server connection.',
            'No query results for model'  => 'The record being fixed no longer exists — it may have already been resolved.',
            'Illuminate\Auth'             => 'A permission error occurred. Please make sure you are logged in as an admin.',
            'cURL error'                  => 'A network error occurred while connecting to an external service.',
            'Class not found'             => 'A system configuration error occurred. Please contact your system administrator.',
            'Disk not configured'         => 'The backup destination drive could not be found. Please check your storage settings.',
        ];

        foreach ($map as $pattern => $friendlyMessage) {
            if (str_contains($error, $pattern)) {
                return $friendlyMessage;
            }
        }

        // Generic fallback — still friendlier than a raw exception
        return 'An unexpected error occurred while applying the fix. Please try again or contact your system administrator if the problem persists.';
    }

    // ─── Fix Dispatcher ───────────────────────────────────────────────────────

    private function applyFix(array $issue): array
    {
        $action = $issue['fix_action'] ?? null;

        if ($issue['source'] === 'log') {
            return [
                'success' => false,
                'status'  => 'skipped',
                'payload' => ['reason' => 'This issue was detected in the application logs and requires manual review. No automated fix can be applied for code-level errors.'],
            ];
        }

        try {
            return match($action) {
                'fix_overdue_checkouts'    => $this->fixOverdueCheckouts(),
                'fix_backup_next_run'      => $this->fixBackupNextRun(),
                'fix_backup_retention'     => $this->fixBackupRetention(),
                'retry_failed_backups'     => $this->retryFailedBackups(),
                'fix_box_zero_capacity'    => $this->fixBoxZeroCapacity($issue['meta'] ?? []),
                'flag_orphaned_charts'     => $this->flagOrphanedCharts(),
                'notify_expired_retention' => $this->notifyExpiredRetention(),
                default                    => [
                    'success' => false,
                    'status'  => 'skipped',
                    'payload' => ['reason' => 'No automated fix is available for this issue type. Please review it manually.'],
                ],
            };
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'status'  => 'failed',
                'error'   => $e->getMessage(),
                'payload' => [],
            ];
        }
    }

    // ─── Fix Actions ──────────────────────────────────────────────────────────

    private function fixOverdueCheckouts(): array
    {
        $updated = CheckoutHistory::where('status', 'active')
            ->where('expected_return_date', '<', Carbon::today())
            ->update(['status' => 'overdue']);

        return [
            'success' => true,
            'status'  => 'success',
            'payload' => [
                'records_updated' => $updated,
                'action'          => $updated === 1
                    ? '1 overdue checkout was updated.'
                    : "{$updated} overdue checkouts were updated.",
            ],
        ];
    }

    private function fixBackupNextRun(): array
    {
        $configs = BackupConfiguration::where('is_active', true)->whereNull('next_run_at')->get();
        $fixed   = 0;

        foreach ($configs as $config) {
            $config->update(['next_run_at' => $this->backupService->calculateNextRun($config)]);
            $fixed++;
        }

        return [
            'success' => true,
            'status'  => 'success',
            'payload' => [
                'records_updated' => $fixed,
                'action'          => $fixed === 1
                    ? '1 backup schedule was given a next run time.'
                    : "{$fixed} backup schedules were given a next run time.",
            ],
        ];
    }

    private function fixBackupRetention(): array
    {
        $defaultRetention = 10;
        $updated = BackupConfiguration::where(function ($q) {
            $q->whereNull('retention_count')->orWhere('retention_count', '<=', 0);
        })->update(['retention_count' => $defaultRetention]);

        return [
            'success' => true,
            'status'  => 'success',
            'payload' => [
                'records_updated' => $updated,
                'action'          => "Retention period set to {$defaultRetention} backups for {$updated} schedule(s).",
            ],
        ];
    }

    private function retryFailedBackups(): array
    {
        $failedConfigs = BackupConfiguration::whereHas('logs', function ($q) {
            $q->where('status', 'failed')
              ->where('created_at', '>=', Carbon::now()->subHours(24));
        })->where('is_active', true)->get();

        $retried = 0;
        $errors  = [];

        foreach ($failedConfigs as $config) {
            try {
                $log = $this->backupService->runBackup($config);
                if ($log->status === 'success') {
                    $retried++;
                } else {
                    $errors[] = "\"{$config->name}\" could not be completed: {$log->error_message}";
                }
            } catch (\Throwable $e) {
                $errors[] = "\"{$config->name}\" failed unexpectedly. Please check your backup settings.";
            }
        }

        return [
            'success' => $retried > 0 || empty($errors),
            'status'  => empty($errors) ? 'success' : ($retried > 0 ? 'success' : 'failed'),
            'payload' => [
                'retried' => $retried,
                'action'  => $retried > 0
                    ? "{$retried} backup(s) re-ran successfully."
                    : 'No backups were retried successfully.',
                'errors'  => $errors,
            ],
        ];
    }

    private function fixBoxZeroCapacity(array $meta): array
    {
        $defaultCap = $meta['default_capacity'] ?? (int) SystemSetting::getValue('box_default_capacity', 50);
        $updated    = FolderBox::where('is_active', true)->where('capacity', 0)->update(['capacity' => $defaultCap]);

        return [
            'success' => true,
            'status'  => 'success',
            'payload' => [
                'records_updated' => $updated,
                'action'          => "{$updated} box(es) had their capacity reset to {$defaultCap} charts.",
            ],
        ];
    }

    private function flagOrphanedCharts(): array
    {
        $count = ArchivedChart::where('status', 'archived')
            ->whereNull('physical_location_id')
            ->count();

        Notification::sendToAdmins(
            'orphaned_charts',
            'Orphaned Charts Detected',
            "{$count} archived chart(s) have no physical location assigned. Please assign them via Charts > Orphaned.",
            'both'
        );

        return [
            'success' => true,
            'status'  => 'success',
            'payload' => [
                'affected' => $count,
                'action'   => "{$count} chart(s) without a physical location were found. An admin notification has been sent — please assign their locations manually.",
            ],
        ];
    }

    private function notifyExpiredRetention(): array
    {
        $count = ArchivedChart::whereNotNull('retention_end_date')
            ->where('retention_end_date', '<', Carbon::today()->subDays(30))
            ->whereIn('status', ['archived', 'checked_out'])
            ->count();

        Notification::sendToAdmins(
            'retention_expired',
            'Charts Past Retention Date',
            "{$count} chart(s) are 30+ days past their retention end date. Review them in Reports > Retention.",
            'both'
        );

        return [
            'success' => true,
            'status'  => 'success',
            'payload' => [
                'affected' => $count,
                'action'   => "{$count} chart(s) are overdue for destruction review. An admin notification has been sent.",
            ],
        ];
    }
}