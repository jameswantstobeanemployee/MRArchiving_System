<?php

namespace App\Console\Commands;

use App\Models\ArchivedChart;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendRetentionAlerts extends Command
{
    protected $signature   = 'retention:send-alerts';
    protected $description = 'Send alerts for charts expiring within 30 days';

    public function handle(): int
    {
        // Charts expiring in exactly 30, 14, and 7 days
        $checkDays = [30, 14, 7];

        foreach ($checkDays as $days) {
            $targetDate = Carbon::today()->addDays($days)->toDateString();

            $charts = ArchivedChart::whereDate('retention_end_date', $targetDate)
                ->whereIn('status', ['archived', 'checked_out'])
                ->with('patient')
                ->get();

            foreach ($charts as $chart) {
                $message = "Chart for {$chart->patient->full_name} (Case: {$chart->case_number}) retention expires in {$days} day(s) on {$chart->retention_end_date->format('m/d/Y')}.";

                Notification::sendToAdmins('retention_expiring', 'Retention Expiring', $message, 'both');
            }

            if ($charts->isNotEmpty()) {
                $this->info("Sent alerts for {$charts->count()} chart(s) expiring in {$days} days.");
            }
        }

        // Also alert for already-expired charts
        $expired = ArchivedChart::whereNotNull('retention_end_date')
            ->where('retention_end_date', '<', Carbon::today())
            ->whereIn('status', ['archived', 'checked_out'])
            ->count();

        if ($expired > 0) {
            Notification::sendToAdmins(
                'retention_expired',
                'Charts Past Retention Date',
                "{$expired} chart(s) have passed their retention end date and may be eligible for destruction.",
                'dashboard'
            );
            $this->info("Alerted for {$expired} already-expired chart(s).");
        }

        return Command::SUCCESS;
    }
}
