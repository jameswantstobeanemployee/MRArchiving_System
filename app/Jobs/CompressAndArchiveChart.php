<?php

namespace App\Jobs;

use App\Models\ArchivedChart;
use App\Services\ArchiveService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CompressAndArchiveChart implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum seconds Ghostscript may run before the worker kills the job.
     * Set slightly above the longest PDF you expect to compress.
     */
    public int $timeout = 600;

    /**
     * Safe to retry now — the chart record already exists on disk and in the DB
     * before this job ever runs. Ghostscript writes to a .gs_tmp sidecar file
     * and only renames on success, so retries are idempotent.
     */
    public int $tries = 3;

    /**
     * Wait 30 s between retries so transient filesystem hiccups can settle.
     */
    public int $backoff = 30;

    // -------------------------------------------------------------------------

    public function __construct(
        public readonly int $chartId,
    ) {}

    // -------------------------------------------------------------------------
    // Middleware
    // -------------------------------------------------------------------------

    /**
     * Per-chart lock — prevents the same chart from being compressed twice
     * simultaneously if the job is somehow dispatched more than once.
     * expireAfter() must be > $timeout so the lock never outlives the job.
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('chart_compress_' . $this->chartId))
                ->releaseAfter(60)
                ->expireAfter(700),
        ];
    }

    // -------------------------------------------------------------------------
    // Handle
    // -------------------------------------------------------------------------

    public function handle(ArchiveService $archiveService): void
    {
        $chart = ArchivedChart::find($this->chartId);

        if (!$chart) {
            // Chart was deleted between dispatch and execution — nothing to do
            Log::warning('CompressAndArchiveChart: chart not found, skipping.', [
                'chart_id' => $this->chartId,
            ]);
            return;
        }

        // Guard against duplicate dispatches — if already compressed, stop here
        if ($chart->compression_status === 'done') {
            return;
        }

        $archiveService->compressChartFile($chart);
    }

    // -------------------------------------------------------------------------
    // Called by Laravel when all retries are exhausted
    // -------------------------------------------------------------------------

    public function failed(\Throwable $exception): void
    {
        $chart = ArchivedChart::find($this->chartId);

        if ($chart) {
            $chart->update(['compression_status' => 'failed']);
        }

        Log::error('CompressAndArchiveChart permanently failed.', [
            'chart_id' => $this->chartId,
            'error'    => $exception->getMessage(),
        ]);
    }
}