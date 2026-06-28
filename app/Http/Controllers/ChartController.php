<?php

namespace App\Http\Controllers;

use App\Jobs\CompressAndArchiveChart;
use App\Models\ArchivedChart;
use App\Models\Patient;
use App\Models\Room;
use App\Models\FolderBox;
use App\Models\SystemSetting;
use App\Services\ArchiveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ChartController extends Controller
{
    public function __construct(private ArchiveService $archiveService) {}

    // =========================================================================
    // Charts index / listing
    // =========================================================================

    public function index(Request $request)
    {
        $query = ArchivedChart::with(['patient', 'physicalLocation.shelf.room']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($roomId = $request->get('room_id')) {
            $query->whereHas('physicalLocation.shelf', fn($q) => $q->where('room_id', $roomId));
        }
        if ($dateFrom = $request->get('date_from')) {
            $query->where('archived_date', '>=', $dateFrom);
        }
        if ($dateTo = $request->get('date_to')) {
            $query->where('archived_date', '<=', $dateTo);
        }
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('case_number', 'like', "%{$searchTerm}%")
                    ->orWhereHas('patient', function ($pq) use ($searchTerm) {
                        $pq->where('first_name', 'like', "%{$searchTerm}%")
                            ->orWhere('last_name', 'like', "%{$searchTerm}%")
                            ->orWhere('medical_record_number', 'like', "%{$searchTerm}%");
                    });
            });

            session()->flash('info', "Showing results for: {$searchTerm}");
        }

        $perPage = in_array((int) $request->get('per_page'), [ 15, 25, 50, 100])
        ? (int) $request->get('per_page')
        : 15;

        $charts = $query->orderByDesc('archived_date')->paginate($perPage)->withQueryString();
        $rooms  = Room::where('is_active', true)->orderBy('name')->get();

        $orphanedCount = ArchivedChart::whereNull('physical_location_id')
            ->where('status', '!=', 'destroyed')
            ->count();

        return view('charts.index', compact('charts', 'rooms', 'orphanedCount'));
    }

    // =========================================================================
    // Orphaned charts
    // =========================================================================

    public function orphanedCharts(Request $request)
    {
        $query = ArchivedChart::with(['patient'])
            ->whereNull('physical_location_id')
            ->where('status', '!=', 'destroyed');

        if ($search = $request->get('search')) {
            $query->search($search);
        }

        $charts        = $query->orderByDesc('archived_date')->paginate(25)->withQueryString();
        $rooms         = Room::where('is_active', true)->with('activeShelves.activeFolderBoxes')->orderBy('name')->get();
        $orphanedCount = $charts->total();

        return view('charts.orphaned', compact('charts', 'rooms', 'orphanedCount'));
    }

    public function bulkAssignOrphaned(Request $request)
    {
        $data = $request->validate([
            'box_id'      => 'required|exists:folder_boxes,id',
            'chart_ids'   => 'required|array|min:1',
            'chart_ids.*' => 'exists:archived_charts,id',
        ]);

        $box       = FolderBox::findOrFail($data['box_id']);
        $available = $box->capacity - $box->current_count;

        if (count($data['chart_ids']) > $available) {
            return back()->with('error',
                "Not enough space. Box has {$available} slot(s) remaining, " .
                "but you selected " . count($data['chart_ids']) . " chart(s)."
            );
        }

        $updated = ArchivedChart::whereIn('id', $data['chart_ids'])
            ->whereNull('physical_location_id')
            ->update(['physical_location_id' => $box->id]);

        return redirect()->route('charts.orphaned')
            ->with('success', "{$updated} chart(s) successfully assigned to {$box->location_label}.");
    }

    // =========================================================================
    // Create form
    // =========================================================================

    public function create()
    {
        $rooms            = Room::where('is_active', true)->with('activeShelves.activeFolderBoxes')->orderBy('name')->get();
        $defaultRetention = SystemSetting::getValue('retention_default_period', 10);
        $retentionOptions = SystemSetting::getValue('retention_available_periods', '5,10,15,20,permanent,custom');
        if (is_string($retentionOptions)) {
            $retentionOptions = explode(',', $retentionOptions);
        }
        $maxFileSizeMb = SystemSetting::getValue('max_file_size_mb', 100);

        return view('charts.create', compact('rooms', 'defaultRetention', 'retentionOptions', 'maxFileSizeMb'));
    }

    // =========================================================================
    // Store
    //
    // 1. Validate
    // 2. Move the already-uploaded temp file to the archive drive (fast)
    // 3. Save the DB record immediately (fast)
    // 4. Dispatch CompressAndArchiveChart as a fire-and-forget background job
    // 5. Return { redirect } — user is sent to the chart page right away
    //
    // The user never waits for Ghostscript.
    // =========================================================================

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id'           => 'required|exists:patients,id',
            'case_number'          => 'required|string|unique:archived_charts,case_number',
            'admission_date'       => 'required|date',
            'discharge_date'       => 'nullable|date|after_or_equal:admission_date',
            'physical_location_id' => 'nullable|exists:folder_boxes,id',
            'retention_period'     => 'required|string',
            'total_pages'          => 'nullable|integer|min:0',
            'notes'                => 'nullable|string',
            'upload_id'            => 'required|string',
            'assembled_file_path'  => 'nullable|string',
        ]);

        // Locate the already-assembled temp file
        $tempPath = $this->resolveTempPath($request);

        // Save record + move file to drive synchronously (no Ghostscript here)
        $jobData = array_merge(
            Arr::except($data, ['upload_id', 'assembled_file_path']),
            ['archived_by' => Auth::id()]
        );

        $chart = $this->archiveService->archiveChartSync($jobData, $tempPath);

        // Dispatch background compression — fire and forget
        // Each worker picks up one job at a time; WithoutOverlapping on the job
        // guarantees the same chart is never compressed twice simultaneously.
        if ($chart->digital_copy_path) {
            CompressAndArchiveChart::dispatch($chart->id)
                ->onQueue('compression');
        }

        return response()->json([
            'redirect' => route('charts.show', $chart),
        ]);
    }

    // =========================================================================
    // Chunked upload — unchanged
    // =========================================================================

    public function uploadChunk(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'upload_id'    => 'required|string|max:64',
            'chunk_index'  => 'required|integer|min:0',
            'total_chunks' => 'required|integer|min:1',
            'chunk'        => 'required|file',
        ]);

        $uploadId    = preg_replace('/[^a-zA-Z0-9_-]/', '', $request->upload_id);
        $chunkIndex  = (int) $request->chunk_index;
        $totalChunks = (int) $request->total_chunks;

        $chunkDir = storage_path("app/archive_chunks/{$uploadId}");
        if (!is_dir($chunkDir)) {
            mkdir($chunkDir, 0755, true);
        }

        $request->file('chunk')->move($chunkDir, "chunk_{$chunkIndex}");

        $receivedChunks = count(glob("{$chunkDir}/chunk_*"));
        if ($receivedChunks < $totalChunks) {
            return response()->json(['received' => $receivedChunks, 'total' => $totalChunks]);
        }

        $ext       = pathinfo($request->file('chunk')->getClientOriginalName(), PATHINFO_EXTENSION) ?: 'pdf';
        $finalPath = storage_path('app/archive_temp/' . Str::uuid() . '.' . strtolower($ext));

        if (!is_dir(dirname($finalPath))) {
            mkdir(dirname($finalPath), 0755, true);
        }

        $out = fopen($finalPath, 'wb');
        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkPath = "{$chunkDir}/chunk_{$i}";
            fwrite($out, file_get_contents($chunkPath));
            unlink($chunkPath);
        }
        fclose($out);
        rmdir($chunkDir);

        Cache::put('archive_job_' . $uploadId, [
            'temp_file_path' => $finalPath,
            'user_id'        => Auth::id(),
            'created_at'     => now()->toIso8601String(),
        ], now()->addMinutes(30));

        return response()->json(['done' => true, 'path' => basename($finalPath)]);
    }

    // =========================================================================
    // Progress endpoint — kept so existing routes don't 404.
    // No longer used by the archive form, but may still be useful elsewhere.
    // =========================================================================

    public function progress(string $jobId): \Illuminate\Http\JsonResponse
    {
        $jobId    = preg_replace('/[^a-zA-Z0-9_-]/', '', $jobId);
        $progress = Cache::get("archive_progress_{$jobId}");

        if (!$progress) {
            return response()->json([
                'status'  => 'not_found',
                'percent' => 0,
                'message' => 'Job not found or already expired.',
            ], 404);
        }

        return response()->json($progress);
    }

    // =========================================================================
    // Show / Move / Destroy / Download / Box info
    // =========================================================================

    public function show(ArchivedChart $chart)
    {
        $chart->load([
            'patient',
            'physicalLocation.shelf.room',
            'archivedBy',
            'checkoutHistory.checkedOutBy',
            'checkoutHistory.returnedBy',
            'locationHistory.fromBox.shelf.room',
            'locationHistory.toBox.shelf.room',
            'locationHistory.movedBy',
        ]);

        return view('charts.show', compact('chart'));
    }

    public function move(Request $request, ArchivedChart $chart)
    {
        $rooms = Room::where('is_active', true)
            ->with('activeShelves.activeFolderBoxes')
            ->orderBy('name')->get();

        if ($request->isMethod('get')) {
            return view('charts.move', compact('chart', 'rooms'));
        }

        $data = $request->validate([
            'new_box_id' => 'required|exists:folder_boxes,id',
            'reason'     => 'required|string',
            'notes'      => 'nullable|string',
        ]);

        try {
            $this->archiveService->moveChart($chart, $data['new_box_id'], $data['reason'], $data['notes'] ?? null);
            return redirect()->route('charts.show', $chart)->with('success', 'Chart moved successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(Request $request, ArchivedChart $chart)
    {
        $data = $request->validate([
            'reason' => 'required|string|min:10',
        ]);

        try {
            $this->archiveService->destroyChart($chart, $data['reason']);
            return redirect()->route('charts.index')->with('success', 'Chart marked as destroyed.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function download(ArchivedChart $chart)
    {
        if (!$chart->digital_copy_path) {
            abort(404, 'No digital copy available.');
        }

        if (!file_exists($chart->digital_copy_path)) {
            abort(404, 'File not found on the archive drive. The drive may be disconnected.');
        }

        return response()->download(
            $chart->digital_copy_path,
            basename($chart->digital_copy_path)
        );
    }

    public function getBoxInfo(FolderBox $box)
    {
        $box->load('shelf.room');
        return response()->json([
            'id'              => $box->id,
            'box_number'      => $box->box_number,
            'box_code'        => $box->box_code,
            'capacity'        => $box->capacity,
            'current_count'   => $box->current_count,
            'fill_percentage' => $box->fill_percentage,
            'status'          => $box->status,
            'can_accept'      => $box->canAcceptChart(),
            'location_label'  => $box->location_label,
        ]);
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    private function resolveTempPath(Request $request): ?string
    {
        $uploadId = preg_replace('/[^a-zA-Z0-9_-]/', '', $request->upload_id);
        $cacheKey = 'archive_job_' . $uploadId;

        // 1. Cache entry written by uploadChunk() on last chunk
        $cached = Cache::pull($cacheKey);
        if ($cached && !empty($cached['temp_file_path']) && file_exists($cached['temp_file_path'])) {
            return $cached['temp_file_path'];
        }

        // 2. Frontend sent the basename directly (edge-case fallback)
        if ($request->filled('assembled_file_path')) {
            $candidate = storage_path('app/archive_temp/' . basename($request->assembled_file_path));
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        // 3. Non-chunked direct upload (small files or legacy clients)
        if ($request->hasFile('digital_copy')) {
            $file     = $request->file('digital_copy');
            $ext      = strtolower($file->getClientOriginalExtension()) ?: 'pdf';
            $destPath = storage_path('app/archive_temp/' . Str::uuid() . '.' . $ext);

            if (!is_dir(dirname($destPath))) {
                mkdir(dirname($destPath), 0755, true);
            }

            $file->move(dirname($destPath), basename($destPath));
            return $destPath;
        }

        return null;
    }

    public function compressionQueue(): \Illuminate\Http\JsonResponse
    {
        $query = ArchivedChart::whereIn('compression_status', ['pending', 'processing', 'failed'])
            ->with('patient')
            ->orderByRaw("FIELD(compression_status, 'processing', 'pending', 'failed')");

        // Admins see all jobs; regular users only see their own
        if (!auth()->user()->isAdmin()) {
            $query->where('archived_by', auth()->id());
        }

        $jobs = $query->limit(20)->get()
            ->map(fn($chart) => [
                'id'                 => $chart->id,
                'case_number'        => $chart->case_number,
                'patient_name'       => $chart->patient
                                            ? $chart->patient->last_name . ', ' . $chart->patient->first_name
                                            : '—',
                'compression_status' => $chart->compression_status,
                'file_size'          => $chart->file_size_formatted,
                'url'                => route('charts.show', $chart),
                'retry_url'          => route('charts.retry-compression', $chart),
            ]);

        return response()->json([
            'jobs'  => $jobs,
            'total' => $jobs->count(),
        ]);
    }

    public function failedCompressions(Request $request)
    {
        $query = ArchivedChart::with(['patient', 'physicalLocation.shelf.room', 'archivedBy'])
            ->where('compression_status', 'failed')
            ->whereNotNull('digital_copy_path');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('case_number', 'like', "%{$request->search}%")
                ->orWhereHas('patient', fn($pq) => $pq
                    ->where('first_name', 'like', "%{$request->search}%")
                    ->orWhere('last_name',  'like', "%{$request->search}%")
                    ->orWhere('medical_record_number', 'like', "%{$request->search}%")
                );
            });
        }

        $charts = $query->orderByDesc('archived_date')->paginate(25)->withQueryString();
        $total  = ArchivedChart::where('compression_status', 'failed')->count();

        return view('charts.failed-compressions', compact('charts', 'total'));
    }

    public function retryCompressionAll(): \Illuminate\Http\RedirectResponse
    {
        $charts = ArchivedChart::where('compression_status', 'failed')
            ->whereNotNull('digital_copy_path')
            ->get();

        $count = 0;
        foreach ($charts as $chart) {
            if (file_exists($chart->digital_copy_path)) {
                $chart->update(['compression_status' => 'pending']);
                CompressAndArchiveChart::dispatch($chart->id)->onQueue('compression');
                $count++;
            }
        }

        return redirect()->route('charts.failed-compressions')
            ->with('success', "{$count} compression job(s) re-queued successfully.");
    }

    public function retryCompressionBulk(Request $request): \Illuminate\Http\RedirectResponse
    {
        $ids = array_filter(explode(',', $request->input('chart_ids', '')));

        if (empty($ids)) {
            return back()->with('error', 'No charts selected.');
        }

        $charts = ArchivedChart::whereIn('id', $ids)
            ->where('compression_status', 'failed')
            ->whereNotNull('digital_copy_path')
            ->get();

        $count = 0;
        foreach ($charts as $chart) {
            if (file_exists($chart->digital_copy_path)) {
                $chart->update(['compression_status' => 'pending']);
                CompressAndArchiveChart::dispatch($chart->id)->onQueue('compression');
                $count++;
            }
        }

        return redirect()->route('charts.failed-compressions')
            ->with('success', "{$count} job(s) re-queued successfully.");
    }

    public function retryCompression(ArchivedChart $chart): \Illuminate\Http\JsonResponse
    {
        if ($chart->compression_status === 'done') {
            return response()->json(['message' => 'Already compressed.'], 422);
        }

        if (!$chart->digital_copy_path || !file_exists($chart->digital_copy_path)) {
            return response()->json(['message' => 'File not found on drive.'], 422);
        }

        $chart->update(['compression_status' => 'pending']);

        CompressAndArchiveChart::dispatch($chart->id)
            ->onQueue('compression');

        return response()->json(['message' => 'Compression job re-queued.']);
    }
}