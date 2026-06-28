<?php

namespace App\Services;

use App\Models\ArchivedChart;
use App\Models\FolderBox;
use App\Models\CheckoutHistory;
use App\Models\LocationHistory;
use App\Models\ExternalDrive;
use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class ReportService
{
    public function archiveInventory(array $filters = []): Builder
    {
        $query = ArchivedChart::with(['patient', 'physicalLocation.shelf.room', 'archivedBy'])
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['date_from']), fn($q) => $q->where('archived_date', '>=', $filters['date_from']))
            ->when(isset($filters['date_to']), fn($q) => $q->where('archived_date', '<=', $filters['date_to']))
            ->when(isset($filters['patient']), fn($q) => $q->search($filters['patient']))
            ->when(isset($filters['box_id']), fn($q) => $q->where('physical_location_id', $filters['box_id']))
            ->when(isset($filters['room_id']), function ($q) use ($filters) {
                $q->whereHas('physicalLocation.shelf', fn($sq) => $sq->where('room_id', $filters['room_id']));
            })
            ->orderBy('archived_date', 'desc');

        return $query;
    }

    public function boxStatus(array $filters = []): Collection
    {
        $query = FolderBox::with(['shelf.room'])
            ->withCount(['activeCharts as current_count'])
            ->when(isset($filters['room_id']), function ($q) use ($filters) {
                $q->whereHas('shelf', fn($sq) => $sq->where('room_id', $filters['room_id']));
            })
            ->where('is_active', true)
            ->orderByDesc('current_count');

        return $query->get()->map(function ($box) {
            $box->fill_pct = $box->capacity > 0
                ? round(($box->current_count / $box->capacity) * 100, 1)
                : 0;
            return $box;
        });
    }

    public function checkoutStatus(array $filters = []): Builder
    {
        return CheckoutHistory::with(['archivedChart.patient', 'checkedOutBy'])
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['department']), fn($q) => $q->where('department', 'like', "%{$filters['department']}%"))
            ->when($filters['overdue'] ?? false, fn($q) => $q->overdue())
            ->orderByDesc('checked_out_at');
    }

    public function locationHistory(array $filters = []): Builder
    {
        return LocationHistory::with(['archivedChart.patient', 'fromBox.shelf.room', 'toBox.shelf.room', 'movedBy'])
            ->when(isset($filters['date_from']), fn($q) => $q->where('moved_at', '>=', $filters['date_from']))
            ->when(isset($filters['date_to']), fn($q) => $q->where('moved_at', '<=', Carbon::parse($filters['date_to'])->endOfDay()))
            ->when(isset($filters['chart_id']), fn($q) => $q->where('archived_chart_id', $filters['chart_id']))
            ->when(isset($filters['user_id']), fn($q) => $q->where('moved_by', $filters['user_id']))
            ->orderByDesc('moved_at');
    }

    public function retentionReport(array $filters = []): Builder
    {
        return ArchivedChart::with(['patient', 'physicalLocation.shelf.room'])
            ->whereIn('status', ['archived', 'checked_out'])
            ->when(isset($filters['expiring_within_days']), function ($q) use ($filters) {
                $q->expiringWithin((int)$filters['expiring_within_days']);
            })
            ->when(isset($filters['status']) && $filters['status'] === 'expired', function ($q) {
                $q->whereNotNull('retention_end_date')
                  ->where('retention_end_date', '<', Carbon::today());
            })
            ->when(isset($filters['status']) && $filters['status'] === 'permanent', function ($q) {
                $q->whereNull('retention_period_years');
            })
            ->orderBy('retention_end_date');
    }

    public function storageUsage(): array
    {
        $drives = ExternalDrive::all();
        $totalArchived = ArchivedChart::where('status', '!=', 'destroyed')->sum('digital_copy_size');
        $totalCharts   = ArchivedChart::where('status', '!=', 'destroyed')->count();

        return [
            'drives'       => $drives,
            'total_archived_size' => $totalArchived,
            'total_charts' => $totalCharts,
        ];
    }

    public function activityReport(array $filters = []): Builder
    {
        return ArchivedChart::with(['patient', 'archivedBy'])
            ->when(isset($filters['user_id']), fn($q) => $q->where('archived_by', $filters['user_id']))
            ->when(isset($filters['date_from']), fn($q) => $q->where('archived_date', '>=', $filters['date_from']))
            ->when(isset($filters['date_to']), fn($q) => $q->where('archived_date', '<=', $filters['date_to']))
            ->orderByDesc('archived_date');
    }

    public function auditTrail(array $filters = []): Builder
    {
        return AuditLog::with('user')
            ->when(isset($filters['user_id']), fn($q) => $q->where('user_id', $filters['user_id']))
            ->when(isset($filters['action']), fn($q) => $q->where('action', 'like', "%{$filters['action']}%"))
            ->when(isset($filters['date_from']), fn($q) => $q->where('created_at', '>=', $filters['date_from']))
            ->when(isset($filters['date_to']), fn($q) => $q->where('created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay()))
            ->orderByDesc('created_at');
    }

    public function getDashboardStats(): array
    {
        $nearFullThreshold = (int) \App\Models\SystemSetting::getValue('box_warning_threshold', 80);

        $nearFullBoxes   = FolderBox::where('is_active', true)
                            ->get()
                            ->filter(fn($b) => $b->fill_percentage >= $nearFullThreshold);

        $overdueCheckouts = CheckoutHistory::overdue()->count();
        $expiringCharts   = ArchivedChart::expiringWithin(30)->count();
        $recentArchives   = ArchivedChart::with(['patient', 'physicalLocation'])->latest()->take(5)->get();
        $topBoxes         = FolderBox::with('shelf.room')
                            ->where('is_active', true)
                            ->get()
                            ->sortByDesc(fn($box) => $box->fill_percentage)
                            ->take(5)
                            ->values();
        $activeCheckouts  = CheckoutHistory::active()
                            ->with(['archivedChart.patient', 'checkedOutBy'])
                            ->latest()->take(10)->get();

        return [
            'near_full_threshold' => $nearFullThreshold,   // ← was missing
            'near_full_boxes'     => $nearFullBoxes->count(),
            'overdue_checkouts'   => $overdueCheckouts,
            'expiring_charts'     => $expiringCharts,
            'recent_archives'     => $recentArchives,
            'top_boxes'           => $topBoxes,
            'active_checkouts'    => $activeCheckouts,
            'total_charts'        => ArchivedChart::whereIn('status', ['archived', 'checked_out'])->count(),
            'total_checked_out'   => ArchivedChart::where('status', 'checked_out')->count(),
        ];
    }

    public function getAdminDashboardStats(): array
    {
        $drives      = ExternalDrive::all();
        $lastBackup  = \App\Models\BackupLog::where('status', 'success')->latest('start_time')->first();
        $nextBackup  = \App\Models\BackupConfiguration::where('is_active', true)->orderBy('next_run_at')->first();
        $recentLogs  = \App\Models\BackupLog::latest('start_time')->take(5)->get();

        return [
            'drives'      => $drives,
            'last_backup' => $lastBackup,
            'next_backup' => $nextBackup,
            'recent_backup_logs' => $recentLogs,
        ];
    }
}
