<?php

namespace App\Console\Commands;

use App\Models\ArchivedChart;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PurgeDestroyedCharts extends Command
{
    protected $signature   = 'charts:purge-destroyed';
    protected $description = 'Permanently delete charts destroyed more than 30 days ago';

    public function handle(): int
    {
        $cutoff = Carbon::now()->subDays(30);

        $charts = ArchivedChart::withTrashed()
            ->where('status', 'destroyed')
            ->where('destroyed_date', '<=', $cutoff)
            ->get();

        $count = 0;
        foreach ($charts as $chart) {
            // Clean up digital file from drive
            if ($chart->digital_copy_path) {
                $filename   = basename($chart->digital_copy_path);
                $driveDir   = dirname(dirname($chart->digital_copy_path));
                $deletedDir = $driveDir . DIRECTORY_SEPARATOR . 'deleted';
                $deletedFile= $deletedDir . DIRECTORY_SEPARATOR . $filename;

                if (file_exists($chart->digital_copy_path)) {
                    // Still in archives folder - move to deleted then remove
                    if (!is_dir($deletedDir)) mkdir($deletedDir, 0755, true);
                    rename($chart->digital_copy_path, $deletedFile);
                    $this->line("  Moved to deleted/: {$filename}");
                }

                if (file_exists($deletedFile)) {
                    unlink($deletedFile);
                    $this->line("  Permanently deleted: {$filename}");
                }
            }

            // Audit before permanent delete
            AuditLog::create([
                'user_id'    => null,
                'action'     => 'purge_chart',
                'table_name' => 'archived_charts',
                'record_id'  => $chart->id,
                'old_values' => [
                    'patient_id'      => $chart->patient_id,
                    'case_number'     => $chart->case_number,
                    'destroyed_date'  => $chart->destroyed_date?->toDateString(),
                    'destroyed_reason'=> $chart->destroyed_reason,
                ],
                'new_values'  => ['action' => 'permanent_deletion'],
                'ip_address'  => '127.0.0.1',
                'user_agent'  => 'Scheduler',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            $chart->forceDelete();
            $count++;
        }

        if ($count > 0) {
            $this->info("Purged {$count} destroyed chart(s).");
        } else {
            $this->info('No charts eligible for purging.');
        }

        return Command::SUCCESS;
    }
}