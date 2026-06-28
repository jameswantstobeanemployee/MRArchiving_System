@extends('layouts.app')
@section('title', 'Location History Report')

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
    .filter-actions {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }
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
    .table-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table-scroll .data-table thead { position: static; }
    .location-badge {
        font-family: 'DM Mono', monospace;
        font-size: 12px;
        background: var(--info-light);
        border: 1px solid var(--info-border);
        padding: 3px 8px;
        border-radius: 4px;
        color: var(--info-text);
        font-weight: 600;
        white-space: nowrap;
    }
    .location-arrow {
        color: var(--text-muted);
        font-size: 12px;
        margin: 0 8px;
    }
    .reason-badge {
        background: var(--table-header-bg);
        border: 1px solid var(--border-color);
        padding: 3px 9px;
        border-radius: var(--radius-full);
        font-size: 11.5px;
        font-weight: 500;
        color: var(--text-secondary);
        display: inline-block;
    }
    .pagination-wrap {
        padding: 14px 20px;
        border-top: 1px solid var(--divider);
        display: flex;
        justify-content: flex-end;
    }
    @media (max-width: 768px) {
        .filter-bar .form-control { min-width: 100%; }
        .filter-actions { width: 100%; }
        .filter-actions .btn { flex: 1; justify-content: center; }
    }
</style>
@endpush

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            <a href="{{ route('reports.index') }}">Reports</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            Location History
        </div>
        <h1>Location History Report</h1>
    </div>
    <div class="d-flex gap-1">
        <a href="{{ route('reports.location-history', request()->all() + ['export' => 'csv']) }}" class="btn btn-secondary">
            <i class="fas fa-download"></i> Export CSV
        </a>
        <a href="{{ route('reports.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Reports
        </a>
    </div>
</div>

{{-- Filters --}}
<div class="card">
    <div class="card-header">
        <span><i class="fas fa-filter"></i> &nbsp;Filters</span>
    </div>
    <div class="card-body">
        <form method="GET">
            <div class="filter-bar">
                <input 
                    type="date" 
                    name="date_from" 
                    class="form-control" 
                    style="max-width:160px" 
                    value="{{ request('date_from') }}" 
                    placeholder="From date"
                >
                <input 
                    type="date" 
                    name="date_to" 
                    class="form-control" 
                    style="max-width:160px" 
                    value="{{ request('date_to') }}" 
                    placeholder="To date"
                >
                <select name="user_id" class="form-control" style="max-width:200px">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('reports.location-history') }}" class="btn btn-secondary">
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
                <i class="fas fa-map-marker-alt" style="color:var(--text-muted)"></i>
                &nbsp;Location History
                <span class="results-count">&nbsp;— <strong>{{ $records->total() }}</strong> movements found</span>
            </span>
        </div>
    </div>

    @if($records->isEmpty())
        <div class="empty-state">
            <i class="fas fa-route empty-state-icon"></i>
            <h3>No location history found</h3>
            <p>Try adjusting your filters or search criteria.</p>
        </div>
    @else
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Patient</th>
                        <th>Case #</th>
                        <th>Movement</th>
                        <th>Reason</th>
                        <th>Moved By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $lh)
                    <tr>
                        <td style="white-space:nowrap; font-family:'DM Mono',monospace; font-size:12px">
                            {{ $lh->moved_at->format('m/d/Y H:i') }}
                        </td>
                        <td>
                            <a href="{{ route('charts.show', $lh->archivedChart) }}" style="font-weight:600; color:var(--accent); text-decoration:none">
                                {{ $lh->archivedChart->patient->full_name }}
                            </a>
                        </td>
                        <td>{{ $lh->archivedChart->case_number }}</td>
                        <td>
                            <div style="display:flex; align-items:center; gap:4px; flex-wrap:wrap">
                                <span class="location-badge">
                                    {{ $lh->fromBox ? $lh->fromBox->box_code : '—' }}
                                </span>
                                <i class="fas fa-arrow-right location-arrow"></i>
                                <span class="location-badge">
                                    {{ $lh->toBox ? $lh->toBox->box_code : '—' }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <span class="reason-badge">{{ $lh->reason }}</span>
                        </td>
                        <td style="font-weight:500">{{ $lh->movedBy->name }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($records->hasPages())
        <div class="pagination-wrap">
            {{ $records->withQueryString()->links() }}
        </div>
        @endif
    @endif
</div>

@endsection