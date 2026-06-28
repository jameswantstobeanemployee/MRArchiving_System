<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\ArchivedChart;
use App\Models\CheckoutHistory;
use App\Models\FolderBox;
use App\Mail\DailyDigestMail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDailyDigest extends Command
{
    protected $signature   = 'notifications:daily-digest';
    protected $description = 'Send daily digest email to all active users';

    public function handle(): int
    {
        $stats = [
            'overdue_checkouts'  => CheckoutHistory::overdue()->count(),
            'expiring_30_days'   => ArchivedChart::expiringWithin(30)->count(),
            'near_full_boxes'    => FolderBox::where('is_active', true)->get()->filter(fn($b) => $b->fill_percentage >= 80)->count(),
            'archived_today'     => ArchivedChart::whereDate('archived_date', Carbon::today())->count(),
            'date'               => Carbon::today()->format('F j, Y'),
        ];

        $users = User::where('is_active', true)->get();
        $sent  = 0;

        foreach ($users as $user) {
            try {
                Mail::to($user->email)->send(new DailyDigestMail($user, $stats));
                $sent++;
            } catch (\Throwable $e) {
                $this->warn("Failed to send digest to {$user->email}: " . $e->getMessage());
            }
        }

        $this->info("Daily digest sent to {$sent} user(s).");
        return Command::SUCCESS;
    }
}
