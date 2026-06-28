@extends('layouts.app')
@section('title', 'Box Status Report')

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
        min-width: 160px;
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
    .box-code {
        font-family: 'DM Mono', monospace;
        font-size: 13px;
        font-weight: 700;
        color: var(--text-primary);
    }
    .fill-progress {
        background: var(--border-color);
        border-radius: var(--radius-full);
        height: 8px;
        overflow: hidden;
        min-width: 140px;
        position: relative;
    }
    .fill-progress-bar {
        height: 100%;
        border-radius: var(--radius-full);
        transition: width 0.4s cubic-bezier(0.4,0,0.2,1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: 700;
        color: white;
        text-shadow: 0 1px 2px rgba(0,0,0,0.2);
    }
    .fill-progress-bar.ok {
        background: linear-gradient(90deg, var(--success) 0%, #10b981 100%);
    }
    .fill-progress-bar.warning {
        background: linear-gradient(90deg, var(--warning) 0%, #f59e0b 100%);
    }
    .fill-progress-bar.danger {
        background: linear-gradient(90deg, var(--danger) 0%, #ef4444 100%);
    }
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 9px;
        border-radius: var(--radius-full);
        font-size: 11.5px;
        font-weight: 600;
        line-height: 1;
        white-space: nowrap;
    }
    .status-badge.status-ok {
        background: var(--success-light);
        color: var(--success-text);
    }
    .status-badge.status-warning {
        background: var(--warning-light);
        color: var(--warning-text);
    }
    .status-badge.status-full {
        background: var(--danger-light);
        color: var(--danger-text);
    }
    @media (max-width: 768px) {
        .filter-bar .form-control { min-width: 100%; }
        .filter-actions { width: 100%; }
        .filter-actions .btn { flex: 1; justify-content: center; }
    }
    .pagination-wrap {
        padding: 14px 20px;
        border-top: 1px solid var(--divider);
        display: flex;
        justify-content: flex-end;
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
            Box Status
        </div>
        <h1>Box Status Report</h1>
    </div>
    <div class="d-flex gap-1">
        <a href="{{ route('reports.box-status', request()->all() + ['export' => 'csv']) }}" class="btn btn-secondary">
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
                <select name="room_id" class="form-control" style="max-width:220px">
                    <option value="">All Rooms</option>
                    @foreach($rooms as $room)
                    <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
                    @endforeach
                </select>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('reports.box-status') }}" class="btn btn-secondary">
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
                <i class="fas fa-box" style="color:var(--text-muted)"></i>
                &nbsp;Storage Boxes
                <span class="results-count">&nbsp;— <strong>{{ $boxes->total() }}</strong> boxes found</span>
            </span>
        </div>
    </div>

    @if($boxes->isEmpty())
        <div class="empty-state">
            <i class="fas fa-box-open empty-state-icon"></i>
            <h3>No boxes found</h3>
            <p>Try adjusting your filters or add boxes to your storage locations.</p>
        </div>
    @else
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Box Code</th>
                        <th>Box #</th>
                        <th>Room</th>
                        <th>Shelf</th>
                        <th>Capacity</th>
                        <th>Charts</th>
                        <th>Fill Level</th>
                        <th>Status</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($boxes as $box)
                    @php 
                        $fillClass = $box->fill_pct >= 95 ? 'danger' : ($box->fill_pct >= 80 ? 'warning' : 'ok');
                        $statusClass = $box->fill_pct >= 95 ? 'full' : ($box->fill_pct >= 80 ? 'warning' : 'ok');
                        $statusText = $box->fill_pct >= 95 ? 'Full' : ($box->fill_pct >= 80 ? 'Warning' : 'OK');
                    @endphp
                    <tr>
                        <td><span class="box-code">{{ $box->box_code }}</span></td>
                        <td style="font-weight:500">{{ $box->box_number }}</td>
                        <td>{{ $box->shelf->room->name }}</td>
                        <td>{{ $box->shelf->name }}</td>
                        <td>
                            <span class="badge badge-info">
                                <i class="fas fa-layer-group"></i>
                                {{ $box->capacity }}
                            </span>
                        </td>
                        <td style="font-weight:600">{{ $box->current_count }}</td>
                        <td>
                            <div class="fill-progress">
                                <div class="fill-progress-bar {{ $fillClass }}" style="width:{{ $box->fill_pct }}%">
                                    {{ $box->fill_pct }}%
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-{{ $statusClass }}">
                                <i class="fas fa-circle" style="font-size:7px"></i>
                                {{ $statusText }}
                            </span>
                        </td>
                        <td>
                            <div class="row-actions" style="visibility:visible; justify-content:flex-end">
                                <a href="{{ route('locations.boxes.show', $box->id) }}" class="action-btn">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($boxes->hasPages())
        <div class="pagination-wrap">
            {{ $boxes->withQueryString()->links() }}
        </div>
        @endif
    @endif
</div>

@endsection