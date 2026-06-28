<?php

namespace App\Services;

use App\Models\ArchivedChart;
use App\Models\CheckoutHistory;
use App\Models\SystemSetting;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Mail\CheckoutMail;
use App\Mail\CheckinMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CheckoutService
{
    public function checkout(ArchivedChart $chart, array $data): CheckoutHistory
    {
        if ($chart->isCheckedOut()) {
            throw new Exception('This chart is already checked out.');
        }
        if ($chart->isDestroyed()) {
            throw new Exception('This chart has been destroyed and cannot be checked out.');
        }

        return DB::transaction(function () use ($chart, $data) {
            $maxDays     = SystemSetting::getValue('checkout_max_loan_days', 30);
            $defaultDays = SystemSetting::getValue('checkout_default_loan_days', 14);

            $expectedReturn = isset($data['expected_return_date'])
                ? Carbon::parse($data['expected_return_date'])
                : Carbon::today()->addDays($defaultDays);

            if ($expectedReturn->diffInDays(Carbon::today()) > $maxDays) {
                throw new Exception("Loan period cannot exceed {$maxDays} days.");
            }

            $checkout = CheckoutHistory::create([
                'archived_chart_id'    => $chart->id,
                'checked_out_by'       => Auth::id(),
                'checked_out_at'       => now(),
                'expected_return_date' => $expectedReturn,
                'purpose'              => $data['purpose'],
                'department'           => $data['department'],
                'person'               => $data['person'],
                'notes'                => $data['notes'] ?? null,
                'status'               => 'active',
            ]);

            $chart->update(['status' => 'checked_out']);

            AuditLog::record('checkout_chart', 'archived_charts', $chart->id,
                ['status' => 'archived'],
                ['status' => 'checked_out', 'checkout_id' => $checkout->id]
            );

            Notification::sendToAll(
                'checkout',
                'Chart Checked Out',
                "Chart for {$chart->patient->full_name} (Case: {$chart->case_number}) checked out to {$data['department']} - {$data['person']}. Due: {$expectedReturn->format('m/d/Y')}",
                'dashboard'
            );

            try {
                Mail::to(Auth::user()->email)->send(new CheckoutMail($chart, $checkout));
            } catch (\Throwable $e) {
                Log::warning('Checkout email failed: ' . $e->getMessage());
            }

            return $checkout;
        });
    }

    public function checkin(ArchivedChart $chart, ?string $notes = null): CheckoutHistory
    {
        if (!$chart->isCheckedOut()) {
            throw new Exception('This chart is not currently checked out.');
        }

        return DB::transaction(function () use ($chart, $notes) {
            // Lock the chart record to prevent race conditions
            $freshChart = ArchivedChart::where('id', $chart->id)
                ->lockForUpdate()
                ->first();

            if (!$freshChart) {
                throw new Exception('Chart record no longer exists.');
            }

            $checkout = CheckoutHistory::where('archived_chart_id', $freshChart->id)
                ->whereIn('status', ['active', 'overdue'])
                ->lockForUpdate()
                ->firstOrFail();

            $checkout->update([
                'returned_by' => Auth::id(),
                'returned_at' => now(),
                'notes'       => $notes ? ($checkout->notes . "\nReturn notes: " . $notes) : $checkout->notes,
                'status'      => 'returned',
            ]);

            $freshChart->update(['status' => 'archived']);

            AuditLog::record('checkin_chart', 'archived_charts', $freshChart->id,
                ['status' => 'checked_out'],
                ['status' => 'archived', 'checkout_id' => $checkout->id]
            );

            Notification::sendToAll(
                'checkin',
                'Chart Returned',
                "Chart for {$freshChart->patient->full_name} (Case: {$freshChart->case_number}) has been returned.",
                'dashboard'
            );

            try {
                Mail::to(Auth::user()->email)->send(new CheckinMail($freshChart, $checkout));
            } catch (\Throwable $e) {
                Log::warning('Checkin email failed: ' . $e->getMessage());
            }

            return $checkout;
        });
    }

    public function markOverdueCheckouts(): int
    {
        $count = CheckoutHistory::active()
            ->where('expected_return_date', '<', Carbon::today())
            ->where('status', 'active')
            ->update(['status' => 'overdue']);

        return $count;
    }
}