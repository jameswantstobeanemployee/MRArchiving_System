<?php

namespace App\Console\Commands;

use App\Models\CheckoutHistory;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkOverdueCheckouts extends Command
{
    protected $signature   = 'checkouts:mark-overdue';
    protected $description = 'Mark active checkouts past their expected return date as overdue and notify users';

    public function handle(): int
    {
        $overdue = CheckoutHistory::where('status', 'active')
            ->where('expected_return_date', '<', Carbon::today())
            ->with(['archivedChart.patient', 'checkedOutBy'])
            ->get();

        $count = 0;
        foreach ($overdue as $checkout) {
            $checkout->update(['status' => 'overdue']);

            $patient  = $checkout->archivedChart->patient;
            $days     = (int) Carbon::today()->diffInDays($checkout->expected_return_date);
            $message  = "Chart for {$patient->full_name} (Case: {$checkout->archivedChart->case_number}) is {$days} day(s) overdue. Checked out to {$checkout->department} - {$checkout->person}.";

            // Notify all users
            Notification::sendToAll('overdue_checkout', 'Chart Overdue', $message, 'both');
            $count++;
        }

        if ($count > 0) {
            $this->info("Marked {$count} checkout(s) as overdue.");
        } else {
            $this->info('No overdue checkouts found.');
        }

        return Command::SUCCESS;
    }
}
