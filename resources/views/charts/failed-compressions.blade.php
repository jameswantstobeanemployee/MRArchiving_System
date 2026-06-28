@extends('layouts.app')
@section('title', 'Failed Compressions')

@push('styles')
<style>
    .fc-stat-strip {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px;
        margin-bottom: 24px;
    }

    .fc-stat {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: var(--radius-lg);
        padding: 18px 20px;
        box-shadow: var(--card-shadow);
        position: relative;
        overflow: hidden;
    }

    .fc-stat::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    }

    .fc-stat.danger::before  { background: var(--danger); }
    .fc-stat.warning::before { background: var(--warning); }
    .fc-stat.info::before    { background: var(--info); }

    .fc-stat-icon {
        width: 36px;
        height: 36px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        margin-bottom: 12px;
    }

    .fc-stat.danger  .fc-stat-icon { background: var(--danger-light);  color: var(--danger); }
    .fc-stat.warning .fc-stat-icon { background: var(--warning-light); color: var(--warning); }
    .fc-stat.info    .fc-stat-icon { background: var(--info-light);    color: var(--info); }

    .fc-stat-value { font-size: 28px; font-weight: 700; color: var(--text-primary); letter-spacing: -0.02em; line-height: 1; margin-bottom: 4px; }
    .fc-stat-label { font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; }

    .fc-toolbar {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .fc-search-wrap {
        position: relative;
        flex: 1;
        min-width: 200px;
        max-width: 360px;
    }

    .fc-search-wrap i {
        position: absolute;
        left: 11px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 13px;
        pointer-events: none;
    }

    .fc-search-wrap input {
        padding-left: 34px;
    }

    .fc-empty {
        padding: 64px 24px;
        text-align: center;
        color: var(--text-muted);
    }

    .fc-empty-icon {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        background: var(--success-light);
        color: var(--success);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        margin: 0 auto 16px;
    }

    .fc-empty h3 { font-size: 15px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px; }
    .fc-empty p  { font-size: 13px; }

    .file-missing-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        font-weight: 600;
        padding: 2px 7px;
        border-radius: var(--radius-full);
        background: var(--warning-light);
        color: var(--warning-text);
    }

    .retry-all-form { display: inline; }

    .error-hint {
        font-size: 11.5px;
        color: var(--danger-text);
        background: var(--danger-light);
        border: 1px solid var(--danger-border);
        border-radius: var(--radius-sm);
        padding: 2px 8px;
        display: inline-block;
        margin-top: 3px;
    }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="page-header-left">
        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}"><i class="fas fa-chart-line"></i> Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:9px;"></i>
            <a href="{{ route('charts.index') }}">Chart Archive</a>
            <i class="fas fa-chevron-right" style="font-size:9px;"></i>
            <span>Failed Compressions</span>
        </div>
        <h1>Failed Compressions</h1>
    </div>
    <div class="d-flex align-center gap-1 flex-wrap">
        @if($total > 0)
        <form action="{{ route('charts.retry-compression-all') }}" method="POST" class="retry-all-form"
              onsubmit="return confirm('Re-queue all {{ $total }} failed job(s)?')">
            @csrf
            <button type="submit" class="btn btn-warning" style="background:var(--warning);color:white;border-color:var(--warning);">
                <i class="fas fa-redo"></i> Retry All ({{ $total }})
            </button>
        </form>
        @endif
        <a href="{{ route('charts.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

{{-- Stats --}}
<div class="fc-stat-strip">
    <div class="fc-stat danger">
        <div class="fc-stat-icon"><i class="fas fa-times-circle"></i></div>
        <div class="fc-stat-value">{{ $total }}</div>
        <div class="fc-stat-label">Failed jobs</div>
    </div>
    <div class="fc-stat warning">
        <div class="fc-stat-icon"><i class="fas fa-hdd"></i></div>
        @php
            $missingFiles = \App\Models\ArchivedChart::where('compression_status', 'failed')
                ->whereNotNull('digital_copy_path')
                ->get()
                ->filter(fn($c) => !file_exists($c->digital_copy_path))
                ->count();
        @endphp
        <div class="fc-stat-value">{{ $missingFiles }}</div>
        <div class="fc-stat-label">Missing files</div>
    </div>
    <div class="fc-stat info">
        <div class="fc-stat-icon"><i class="fas fa-database"></i></div>
        <div class="fc-stat-value">
            @php
                $failedSize = \App\Models\ArchivedChart::where('compression_status', 'failed')->sum('digital_copy_size');
                if ($failedSize >= 1073741824)      echo number_format($failedSize / 1073741824, 1) . ' GB';
                elseif ($failedSize >= 1048576)     echo number_format($failedSize / 1048576, 1)    . ' MB';
                else                                echo number_format($failedSize / 1024, 1)        . ' KB';
            @endphp
        </div>
        <div class="fc-stat-label">Uncompressed size</div>
    </div>
</div>

{{-- Main Card --}}
<div class="card">
    <div class="card-header">
        <div class="d-flex align-center gap-1">
            <i class="fas fa-exclamation-triangle" style="color:var(--danger);"></i>
            Failed compression jobs
            @if($total > 0)
                <span class="badge badge-danger">{{ $total }}</span>
            @endif
        </div>

        {{-- Toolbar --}}
        <div class="fc-toolbar">
            <form method="GET" action="{{ route('charts.failed-compressions') }}" class="fc-search-wrap">
                <i class="fas fa-search"></i>
                <input type="text"
                       name="search"
                       class="form-control"
                       placeholder="Search case #, patient…"
                       value="{{ request('search') }}"
                       autocomplete="off">
            </form>

            @if(request('search'))
                <a href="{{ route('charts.failed-compressions') }}" class="btn btn-ghost btn-sm">
                    <i class="fas fa-times"></i> Clear
                </a>
            @endif
        </div>
    </div>

    @if($charts->isEmpty())
        <div class="fc-empty">
            <div class="fc-empty-icon"><i class="fas fa-check"></i></div>
            @if(request('search'))
                <h3>No results for "{{ request('search') }}"</h3>
                <p>Try a different search term.</p>
            @else
                <h3>No failed compression jobs</h3>
                <p>All PDFs have been compressed successfully.</p>
            @endif
        </div>
    @else
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:40px;">
                            <input type="checkbox" id="selectAll" style="cursor:pointer;">
                        </th>
                        <th>Case / Patient</th>
                        <th>Location</th>
                        <th>File</th>
                        <th>Archived</th>
                        <th>Archived by</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($charts as $chart)
                    @php $fileExists = $chart->digital_copy_path && file_exists($chart->digital_copy_path); @endphp
                    <tr>
                        <td>
                            <input type="checkbox" class="row-check" value="{{ $chart->id }}" style="cursor:pointer;">
                        </td>

                        {{-- Case / Patient --}}
                        <td>
                            <a href="{{ route('charts.show', $chart) }}"
                               style="font-weight:600;color:var(--accent);text-decoration:none;font-size:13.5px;">
                                {{ $chart->case_number }}
                            </a>
                            @if($chart->patient)
                                <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">
                                    <i class="fas fa-user" style="font-size:10px;"></i>
                                    {{ $chart->patient->last_name }}, {{ $chart->patient->first_name }}
                                    &nbsp;·&nbsp; MR# {{ $chart->patient->medical_record_number }}
                                </div>
                            @endif
                        </td>

                        {{-- Location --}}
                        <td>
                            @if($chart->physicalLocation)
                                <span style="font-size:13px;">{{ $chart->physicalLocation->location_label }}</span>
                            @else
                                <span class="text-muted" style="font-size:12px;">Orphaned</span>
                            @endif
                        </td>

                        {{-- File --}}
                        <td>
                            @if($fileExists)
                                <div style="font-size:13px;font-weight:500;">{{ $chart->file_size_formatted }}</div>
                                <div style="font-size:11.5px;color:var(--text-muted);margin-top:2px;font-family:'DM Mono',monospace;">
                                    {{ basename($chart->digital_copy_path) }}
                                </div>
                            @else
                                <span class="file-missing-badge">
                                    <i class="fas fa-exclamation-triangle"></i> File missing
                                </span>
                                @if($chart->digital_copy_path)
                                    <div style="font-size:11px;color:var(--text-muted);margin-top:3px;font-family:'DM Mono',monospace;word-break:break-all;">
                                        {{ basename($chart->digital_copy_path) }}
                                    </div>
                                @endif
                            @endif
                        </td>

                        {{-- Archived date --}}
                        <td>
                            <span style="font-size:13px;">{{ $chart->archived_date->format('M d, Y') }}</span>
                        </td>

                        {{-- Archived by --}}
                        <td>
                            <span style="font-size:13px;">{{ $chart->archivedBy?->name ?? '—' }}</span>
                        </td>

                        {{-- Actions --}}
                        <td>
                            <div class="row-actions" style="visibility:visible;">
                                @if($fileExists)
                                    <form action="{{ route('charts.retry-compression', $chart) }}" method="POST"
                                          style="display:inline;">
                                        @csrf
                                        <button type="submit" class="action-btn"
                                                title="Retry compression"
                                                style="color:var(--info-text);">
                                            <i class="fas fa-redo"></i> Retry
                                        </button>
                                    </form>
                                @else
                                    <span style="font-size:12px;color:var(--text-muted);">
                                        <i class="fas fa-ban"></i> No file
                                    </span>
                                @endif
                                <a href="{{ route('charts.show', $chart) }}" class="action-btn" title="View chart">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Bulk retry bar --}}
        <div id="bulkBar" style="display:none;padding:12px 20px;background:var(--info-light);border-top:1px solid var(--info-border);display:none;align-items:center;gap:12px;">
            <span style="font-size:13px;color:var(--info-text);font-weight:600;">
                <span id="bulkCount">0</span> chart(s) selected
            </span>
            <form id="bulkRetryForm" action="{{ route('charts.retry-compression-bulk') }}" method="POST">
                @csrf
                <input type="hidden" name="chart_ids" id="bulkIds">
                <button type="submit" class="btn btn-sm"
                        style="background:var(--info);color:white;">
                    <i class="fas fa-redo"></i> Retry Selected
                </button>
            </form>
            <button type="button" class="btn btn-ghost btn-sm" id="clearSelection">
                <i class="fas fa-times"></i> Clear
            </button>
        </div>

        {{-- Pagination --}}
        @if($charts->hasPages())
            <div style="padding:14px 20px;border-top:1px solid var(--divider);">
                {{ $charts->links() }}
            </div>
        @endif
    @endif
</div>

@endsection

@push('scripts')
<script>
// ── Select all / bulk ─────────────────────────────────────────────────────
const selectAll   = document.getElementById('selectAll');
const bulkBar     = document.getElementById('bulkBar');
const bulkCount   = document.getElementById('bulkCount');
const bulkIds     = document.getElementById('bulkIds');
const clearBtn    = document.getElementById('clearSelection');

function getChecked() {
    return [...document.querySelectorAll('.row-check:checked')].map(c => c.value);
}

function updateBulkBar() {
    const ids = getChecked();
    if (ids.length > 0) {
        bulkBar.style.display  = 'flex';
        bulkCount.textContent  = ids.length;
        bulkIds.value          = ids.join(',');
    } else {
        bulkBar.style.display  = 'none';
    }
}

selectAll?.addEventListener('change', function () {
    document.querySelectorAll('.row-check').forEach(c => c.checked = this.checked);
    updateBulkBar();
});

document.querySelectorAll('.row-check').forEach(c =>
    c.addEventListener('change', () => {
        selectAll.checked = [...document.querySelectorAll('.row-check')].every(c => c.checked);
        updateBulkBar();
    })
);

clearBtn?.addEventListener('click', () => {
    document.querySelectorAll('.row-check').forEach(c => c.checked = false);
    selectAll.checked = false;
    updateBulkBar();
});

// ── Confirm retry all ─────────────────────────────────────────────────────
document.querySelector('.retry-all-form')?.addEventListener('submit', function (e) {
    e.preventDefault();
    Swal.fire({
        title: 'Retry all failed jobs?',
        html: `This will re-queue <strong>{{ $total }}</strong> compression job(s).<br>
               The worker will process them in the background.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#d97706',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-redo"></i> Yes, retry all',
    }).then(r => { if (r.isConfirmed) this.submit(); });
});
</script>
@endpush