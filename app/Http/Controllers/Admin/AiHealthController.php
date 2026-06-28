<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiHealthLog;
use App\Services\AiFixService;
use Illuminate\Http\Request;

class AiHealthController extends Controller
{
    public function __construct(private AiFixService $aiFixService) {}

    public function index()
    {
        // Latest scan summary
        $latestScanId = AiHealthLog::latest()->value('scan_id');

        $latestScanLogs = $latestScanId
            ? AiHealthLog::where('scan_id', $latestScanId)->latest()->get()
            : collect();

        // Past scans (grouped by scan_id, last 10)
        $pastScans = AiHealthLog::selectRaw('scan_id, MIN(created_at) as scanned_at, COUNT(*) as total_issues, SUM(was_fixed) as fixed_count')
            ->groupBy('scan_id')
            ->orderByDesc('scanned_at')
            ->limit(10)
            ->get();

        return view('admin.ai-health.index', compact('latestScanLogs', 'latestScanId', 'pastScans'));
    }

    public function scan(Request $request)
    {
        $result = $this->aiFixService->runFullScan(auth()->id());

        if ($result['healthy']) {
            return redirect()->route('admin.ai-health.index')
                ->with('success', '✓ System scan complete — no issues found. Everything looks healthy!');
        }

        return redirect()->route('admin.ai-health.index')
            ->with('success', "Scan complete. Found {$result['total']} issue(s): {$result['fixed']} fixed, {$result['skipped']} flagged, {$result['failed']} failed.");
    }

    public function show(string $scanId)
    {
        $logs = AiHealthLog::where('scan_id', $scanId)->latest()->get();

        abort_if($logs->isEmpty(), 404);

        return view('admin.ai-health.show', compact('logs', 'scanId'));
    }
}