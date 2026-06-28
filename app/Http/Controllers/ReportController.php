<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Http\Request;


class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function index()
    {
        return view('reports.index');
    }

    public function archiveInventory(Request $request)
    {
        $filters = $request->only(['status', 'date_from', 'date_to', 'patient', 'room_id']);
        $query   = $this->reportService->archiveInventory($filters);

        if ($request->get('export') === 'csv') {
            return $this->exportCsv($query->get(), 'archive_inventory');
        }

        $charts = $query->paginate(50)->withQueryString();
        $rooms  = Room::where('is_active', true)->orderBy('name')->get();

        return view('reports.archive_inventory', compact('charts', 'rooms', 'filters'));
    }

    public function boxStatus(Request $request)
    {
        $filters    = $request->only(['room_id']);
        $collection = $this->reportService->boxStatus($filters);
        $rooms      = Room::where('is_active', true)->orderBy('name')->get();

        if ($request->get('export') === 'csv') {
            return $this->exportCsv($collection, 'box_status');
        }

        $perPage     = 50;
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $boxes       = new \Illuminate\Pagination\LengthAwarePaginator(
            $collection->forPage($currentPage, $perPage),
            $collection->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('reports.box_status', compact('boxes', 'rooms', 'filters'));
    }

    public function checkoutStatus(Request $request)
    {
        $filters = $request->only(['status', 'department', 'overdue']);
        $query   = $this->reportService->checkoutStatus($filters);

        if ($request->get('export') === 'csv') {
            return $this->exportCsv($query->get(), 'checkout_status');
        }

        $checkouts = $query->paginate(50)->withQueryString();

        return view('reports.checkout_status', compact('checkouts', 'filters'));
    }

    public function locationHistory(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'chart_id', 'user_id']);
        $query   = $this->reportService->locationHistory($filters);
        $users   = User::where('is_active', true)->orderBy('name')->get();

        if ($request->get('export') === 'csv') {
            return $this->exportCsv($query->get(), 'location_history');
        }

        $records = $query->paginate(50)->withQueryString();

        return view('reports.location_history', compact('records', 'users', 'filters'));
    }

    public function retentionReport(Request $request)
    {
        $filters = $request->only(['expiring_within_days', 'status']);
        if (!isset($filters['expiring_within_days'])) {
            $filters['expiring_within_days'] = 30;
        }
        $query  = $this->reportService->retentionReport($filters);
        $charts = $query->paginate(50)->withQueryString();

        if ($request->get('export') === 'csv') {
            return $this->exportCsv($query->get(), 'retention_report');
        }

        return view('reports.retention', compact('charts', 'filters'));
    }

    public function storageUsage(Request $request)
    {
        $data = $this->reportService->storageUsage();
        return view('reports.storage_usage', $data);
    }

    public function activityReport(Request $request)
    {
        $filters = $request->only(['user_id', 'date_from', 'date_to']);
        $query   = $this->reportService->activityReport($filters);
        $charts  = $query->paginate(50)->withQueryString();
        $users   = User::where('is_active', true)->orderBy('name')->get();

        if ($request->get('export') === 'csv') {
            return $this->exportCsv($query->get(), 'activity_report');
        }

        return view('reports.activity', compact('charts', 'users', 'filters'));
    }

    public function auditTrail(Request $request)
    {
        $filters = $request->only(['user_id', 'action', 'date_from', 'date_to']);
        $query   = $this->reportService->auditTrail($filters);
        $users   = User::orderBy('name')->get();

        if ($request->get('export') === 'csv') {
            return $this->exportCsv($query->get(), 'audit_trail');
        }

        $logs = $query->paginate(50)->withQueryString();

        return view('reports.audit_trail', compact('logs', 'users', 'filters'));
    }

    private function exportCsv($records, string $filename)
    {
        // Build CSV content in memory - avoids stream/buffer issues
        $output  = fopen('php://temp', 'r+');
        $first   = true;

        foreach ($records as $row) {
            // Flatten the model to a simple key=>value array
            $arr = $this->flattenForCsv($row);

            if ($first) {
                fputcsv($output, array_keys($arr));
                $first = false;
            }
            fputcsv($output, array_values($arr));
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        $exportFilename = $filename . '_' . now()->format('Ymd_His') . '.csv';

        return response($csvContent, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $exportFilename . '"',
            'Content-Length'      => strlen($csvContent),
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }

    private function flattenForCsv($row): array
    {
        if (is_array($row)) {
            return $row;
        }

        // Get base attributes only (no relations, no hidden)
        $arr = $row->getAttributes();

        // Append common readable relation fields if loaded
        if ($row->relationLoaded('patient') && $row->patient) {
            $arr['patient_name'] = $row->patient->full_name;
            $arr['mr_number']    = $row->patient->medical_record_number;
        }
        if ($row->relationLoaded('archivedChart') && $row->archivedChart) {
            $arr['patient_name'] = $row->archivedChart->patient->full_name ?? '';
            $arr['case_number']  = $row->archivedChart->case_number ?? '';
        }
        if ($row->relationLoaded('physicalLocation') && $row->physicalLocation) {
            $arr['location'] = $row->physicalLocation->location_label ?? '';
        }
        if ($row->relationLoaded('archivedBy') && $row->archivedBy) {
            $arr['archived_by_name'] = $row->archivedBy->name;
        }
        if ($row->relationLoaded('checkedOutBy') && $row->checkedOutBy) {
            $arr['checked_out_by_name'] = $row->checkedOutBy->name;
        }
        if ($row->relationLoaded('movedBy') && $row->movedBy) {
            $arr['moved_by_name'] = $row->movedBy->name;
        }
        if ($row->relationLoaded('user') && $row->user) {
            $arr['user_name'] = $row->user->name;
        }

        // Remove json/array columns that break CSV
        foreach ($arr as $k => $v) {
            if (is_array($v) || is_object($v)) {
                $arr[$k] = json_encode($v);
            }
        }

        return $arr;
    }
}