@extends('layouts.app')
@section('title', 'Charts')

@push('styles')
<style>
    .filter-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: flex-end;
    }
    .filter-bar .form-control {
        flex: 1;
        min-width: 140px;
    }
    .filter-bar .form-control:first-child {
        flex: 2;
        min-width: 220px;
    }
    .filter-actions {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }
    .patient-link {
        font-weight: 600;
        color: var(--accent);
        text-decoration: none;
    }
    .patient-link:hover { text-decoration: underline; }
    .mono-cell {
        font-family: 'DM Mono', monospace;
        font-size: 12.5px;
        color: var(--text-secondary);
    }
    .retention-expired {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        color: var(--danger);
        font-weight: 600;
        font-size: 12px;
    }
    .table-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table-scroll .data-table thead { position: static; }
    .card-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .results-count {
        font-size: 13px;
        color: var(--text-muted);
        font-weight: 500;
    }
    .results-count strong {
        color: var(--text-primary);
        font-weight: 700;
    }
    .pagination-wrap {
        padding: 12px 20px;
        border-top: 1px solid var(--divider);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .pagination-info {
        font-size: 13px;
        color: var(--text-muted);
        white-space: nowrap;
    }
    .pagination-info strong {
        color: var(--text-primary);
        font-weight: 600;
    }
    .pagination-controls {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .pagination-controls .page-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        padding: 0 8px;
        border-radius: 6px;
        border: 1px solid var(--divider);
        background: var(--surface);
        color: var(--text-primary);
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        cursor: pointer;
        transition: background 0.15s, border-color 0.15s, color 0.15s;
        line-height: 1;
    }
    .pagination-controls .page-btn:hover:not(.active):not(.disabled) {
        background: var(--hover);
        border-color: var(--accent);
        color: var(--accent);
    }
    .pagination-controls .page-btn.active {
        background: var(--accent);
        border-color: var(--accent);
        color: #fff;
        cursor: default;
    }
    .pagination-controls .page-btn.disabled {
        opacity: 0.4;
        cursor: not-allowed;
        pointer-events: none;
    }
    .pagination-controls .page-ellipsis {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 32px;
        font-size: 13px;
        color: var(--text-muted);
    }
    .per-page-wrap {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: var(--text-muted);
        white-space: nowrap;
    }
    .per-page-wrap select {
        height: 32px;
        padding: 0 8px;
        border-radius: 6px;
        border: 1px solid var(--divider);
        background: var(--surface);
        color: var(--text-primary);
        font-size: 13px;
        cursor: pointer;
    }
    @media (max-width: 600px) {
        .pagination-wrap { justify-content: center; }
        .pagination-info { width: 100%; text-align: center; }
        .per-page-wrap { width: 100%; justify-content: center; }
    }
    .chart-empty .empty-state-icon { font-size: 40px; }
    .actions-cell { white-space: nowrap; }
    .page-header-actions {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }
    @media (max-width: 768px) {
        .filter-bar { gap: 6px; }
        .filter-bar .form-control { min-width: 100%; }
        .filter-actions { width: 100%; }
        .filter-actions .btn { flex: 1; justify-content: center; }
        .page-header-actions { width: 100%; }
        .page-header-actions .btn { flex: 1; justify-content: center; }
    }
</style>
@endpush

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            Chart Archive
        </div>
        <h1>Chart Archive</h1>
    </div>
    <div class="page-header-actions">
       <a href="{{ route('charts.orphaned', ['from' => 'charts']) }}" class="btn {{ $orphanedCount > 0 ? 'btn-warning' : 'btn-secondary' }}">
            <i class="fas fa-unlink"></i> Orphaned Charts
            @if($orphanedCount > 0)
                <span style="background:rgba(0,0,0,0.15);border-radius:20px;padding:1px 8px;font-size:12px;margin-left:4px;font-weight:700">
                    {{ $orphanedCount }}
                </span>
            @endif
        </a>
        <a href="{{ route('charts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Archive New Chart
        </a>
    </div>
</div>

{{-- Filters --}}
<div class="card">
    <div class="card-header">
        <span><i class="fas fa-filter"></i> &nbsp;Filters</span>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('charts.index') }}">
            <div class="filter-bar">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search patient name, case #, MR#…"
                    value="{{ request('search') }}"
                >
                <select name="status" class="form-control" style="max-width:160px">
                    <option value="">All Statuses</option>
                    <option value="archived"     {{ request('status') === 'archived'     ? 'selected' : '' }}>Archived</option>
                    <option value="checked_out"  {{ request('status') === 'checked_out'  ? 'selected' : '' }}>Checked Out</option>
                    <option value="destroyed"    {{ request('status') === 'destroyed'    ? 'selected' : '' }}>Destroyed</option>
                </select>
                <select name="room_id" class="form-control" style="max-width:200px">
                    <option value="">All Rooms</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                            {{ $room->name }}
                        </option>
                    @endforeach
                </select>
                <input type="date" name="date_from" class="form-control" style="max-width:150px" value="{{ request('date_from') }}" title="From date">
                <input type="date" name="date_to"   class="form-control" style="max-width:150px" value="{{ request('date_to') }}"   title="To date">
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="{{ route('charts.index') }}" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Results --}}
<div class="card">
    <div class="card-header">
        <div class="card-header-row">
            <span>
                <i class="fas fa-archive" style="color:var(--text-muted)"></i>
                &nbsp;Results
                <span class="results-count">&nbsp;— <strong>{{ $charts->total() }}</strong> charts found</span>
            </span>
            <a href="{{ route('reports.archive-inventory', array_merge(request()->all(), ['export' => 'csv'])) }}"
               class="btn btn-sm btn-secondary">
                <i class="fas fa-download"></i> Export CSV
            </a>
        </div>
    </div>

    @if($charts->isEmpty())
        <div class="chart-empty">
            <div class="empty-state">
                <i class="fas fa-folder-open empty-state-icon"></i>
                @if(request()->hasAny(['search', 'status', 'room_id', 'date_from', 'date_to']))
                    <h3>No charts match your filters</h3>
                    <p>Try adjusting or <a href="{{ route('charts.index') }}" style="color:var(--accent)">clearing your filters</a> to see all charts.</p>
                @else
                    <h3>No charts archived yet</h3>
                    <p>Get started by <a href="{{ route('charts.create') }}" style="color:var(--accent)">archiving a new chart</a>.</p>
                @endif
            </div>
        </div>
    @else
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>MR #</th>
                        <th>Case #</th>
                        <th>Admission</th>
                        <th>Discharge</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Retention</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($charts as $chart)
                    <tr>
                        <td>
                            <a href="{{ route('charts.show', $chart) }}" class="patient-link">
                                {{ $chart->patient->full_name }}
                            </a>
                        </td>
                        <td class="mono-cell">{{ $chart->patient->medical_record_number }}</td>
                        <td class="mono-cell">{{ $chart->case_number }}</td>
                        <td>{{ $chart->admission_date->format('m/d/Y') }}</td>
                        <td>{{ $chart->discharge_date?->format('m/d/Y') ?? '—' }}</td>
                        <td>
                            @if($chart->physicalLocation)
                                <code>{{ $chart->physicalLocation->box_code }}</code>
                            @else
                                <span style="color:var(--danger);font-size:12px;font-weight:600">
                                    <i class="fas fa-unlink"></i> No Location
                                </span>
                            @endif
                        </td>
                        <td>
                            @php
                                $statusClass = match($chart->status) {
                                    'archived'    => 'badge-info',
                                    'checked_out' => 'badge-warning',
                                    'destroyed'   => 'badge-danger',
                                    default       => 'badge-info',
                                };
                                $statusIcon = match($chart->status) {
                                    'archived'    => 'fa-box-archive',
                                    'checked_out' => 'fa-exchange-alt',
                                    'destroyed'   => 'fa-trash',
                                    default       => 'fa-circle',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">
                                <i class="fas {{ $statusIcon }}"></i>
                                {{ str_replace('_', ' ', $chart->status) }}
                            </span>
                        </td>
                        <td>
                            @if($chart->retention_end_date)
                                <span style="font-size:13px">{{ $chart->retention_end_date->format('m/d/Y') }}</span>
                                @if($chart->is_expired)
                                    <span class="retention-expired">
                                        <i class="fas fa-exclamation-triangle"></i> Expired
                                    </span>
                                @endif
                            @else
                                <span class="badge badge-success"><i class="fas fa-infinity"></i> Permanent</span>
                            @endif
                        </td>
                        <td class="actions-cell">
                            <div class="row-actions" style="visibility:visible">
                                <a href="{{ route('charts.show', $chart) }}" class="action-btn">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                @if($chart->status === 'archived')
                                    <a href="{{ route('checkout.create', $chart) }}" class="action-btn" style="color:var(--warning)">
                                        <i class="fas fa-exchange-alt"></i> Check Out
                                    </a>
                                    <a href="{{ route('charts.move', $chart) }}" class="action-btn" style="color:var(--info)">
                                        <i class="fas fa-arrows-alt"></i> Move
                                    </a>
                                @elseif($chart->status === 'checked_out')
                                    <form action="{{ route('checkout.checkin', $chart) }}" method="POST" style="display:inline">
                                        @csrf
                                        <button type="submit" class="action-btn" style="color:var(--success)"
                                            onclick="return confirmReturn(this.closest('form'))">
                                            <i class="fas fa-check"></i> Return
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Footer bar: always visible --}}
        <div class="pagination-wrap">

            {{-- Left: showing X–Y of Z --}}
            <div class="pagination-info">
                Showing
                <strong>{{ $charts->firstItem() }}</strong>–<strong>{{ $charts->lastItem() }}</strong>
                of <strong>{{ $charts->total() }}</strong> charts
            </div>

            {{-- Centre: page buttons — only when there is more than one page --}}
            @if($charts->hasPages())
            <div class="pagination-controls">

                {{-- Previous --}}
                @if($charts->onFirstPage())
                    <span class="page-btn disabled">
                        <i class="fas fa-chevron-left" style="font-size:11px"></i>
                    </span>
                @else
                    <a href="{{ $charts->previousPageUrl() }}&{{ http_build_query(request()->except('page')) }}"
                       class="page-btn" title="Previous page">
                        <i class="fas fa-chevron-left" style="font-size:11px"></i>
                    </a>
                @endif

                {{-- Page numbers with ellipsis --}}
                @php
                    $current = $charts->currentPage();
                    $last    = $charts->lastPage();
                    $window  = 2;
                    $pages   = collect();

                    $pages->push(1);
                    if ($current - $window > 2) $pages->push('...');
                    for ($i = max(2, $current - $window); $i <= min($last - 1, $current + $window); $i++) {
                        $pages->push($i);
                    }
                    if ($current + $window < $last - 1) $pages->push('...');
                    if ($last > 1) $pages->push($last);
                @endphp

                @foreach($pages as $page)
                    @if($page === '...')
                        <span class="page-ellipsis">&hellip;</span>
                    @elseif($page === $current)
                        <span class="page-btn active">{{ $page }}</span>
                    @else
                        <a href="{{ $charts->url($page) }}&{{ http_build_query(request()->except('page')) }}"
                           class="page-btn">{{ $page }}</a>
                    @endif
                @endforeach

                {{-- Next --}}
                @if($charts->hasMorePages())
                    <a href="{{ $charts->nextPageUrl() }}&{{ http_build_query(request()->except('page')) }}"
                       class="page-btn" title="Next page">
                        <i class="fas fa-chevron-right" style="font-size:11px"></i>
                    </a>
                @else
                    <span class="page-btn disabled">
                        <i class="fas fa-chevron-right" style="font-size:11px"></i>
                    </span>
                @endif

            </div>
            @endif

            {{-- Right: per-page selector — always visible so user can always change it --}}
            <div class="per-page-wrap">
                <label for="per-page-select">Rows per page:</label>
                <select id="per-page-select"
                        onchange="window.location.href='{{ route('charts.index') }}?' + new URLSearchParams({...Object.fromEntries(new URLSearchParams(location.search)), per_page: this.value, page: 1}).toString()">
                    @foreach([15, 25, 50, 100] as $size)
                        <option value="{{ $size }}" {{ request('per_page', 25) == $size ? 'selected' : '' }}>
                            {{ $size }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>
    @endif
</div>

@endsection