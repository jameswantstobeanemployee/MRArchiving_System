{{-- archive_inventory.blade.php --}}
@extends('layouts.app')
@section('title', 'Archive Inventory Report')

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
        text-transform: capitalize;
    }
    .status-archived {
        background: var(--info-light);
        color: var(--info-text);
    }
    .status-checked_out {
        background: var(--warning-light);
        color: var(--warning-text);
    }
    .status-destroyed {
        background: var(--danger-light);
        color: var(--danger-text);
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
            Archive Inventory
        </div>
        <h1>Archive Inventory</h1>
    </div>
    <div class="d-flex gap-1">
        <a href="{{ route('reports.archive-inventory', request()->all() + ['export' => 'csv']) }}" class="btn btn-secondary">
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
                    type="text" 
                    name="patient" 
                    class="form-control" 
                    placeholder="Patient name or MR#…" 
                    value="{{ request('patient') }}"
                >
                <select name="status" class="form-control" style="max-width:140px">
                    <option value="">All Status</option>
                    <option value="archived" {{ request('status')=='archived'?'selected':'' }}>Archived</option>
                    <option value="checked_out" {{ request('status')=='checked_out'?'selected':'' }}>Checked Out</option>
                    <option value="destroyed" {{ request('status')=='destroyed'?'selected':'' }}>Destroyed</option>
                </select>
                <select name="room_id" class="form-control" style="max-width:180px">
                    <option value="">All Rooms</option>
                    @foreach($rooms as $room)
                    <option value="{{ $room->id }}" {{ request('room_id')==$room->id?'selected':'' }}>{{ $room->name }}</option>
                    @endforeach
                </select>
                <input 
                    type="date" 
                    name="date_from" 
                    class="form-control" 
                    style="max-width:140px" 
                    value="{{ request('date_from') }}"
                    placeholder="From date"
                >
                <input 
                    type="date" 
                    name="date_to" 
                    class="form-control" 
                    style="max-width:140px" 
                    value="{{ request('date_to') }}"
                    placeholder="To date"
                >
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('reports.archive-inventory') }}" class="btn btn-secondary">
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
                &nbsp;Archive Inventory
                <span class="results-count">&nbsp;— <strong>{{ $charts->total() }}</strong> records found</span>
            </span>
        </div>
    </div>

    @if($charts->isEmpty())
        <div class="empty-state">
            <i class="fas fa-inbox empty-state-icon"></i>
            <h3>No records found</h3>
            <p>Try adjusting your filters or search criteria.</p>
        </div>
    @else
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>MR#</th>
                        <th>Case #</th>
                        <th>Archived</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Retention</th>
                        <th>Archived By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($charts as $chart)
                    <tr>
                        <td>
                            <a href="{{ route('charts.show', $chart) }}" style="font-weight:600; color:var(--accent); text-decoration:none">
                                {{ $chart->patient->full_name }}
                            </a>
                        </td>
                        <td>{{ $chart->patient->medical_record_number }}</td>
                        <td>{{ $chart->case_number }}</td>
                        <td style="white-space:nowrap">{{ $chart->archived_date->format('m/d/Y') }}</td>
                        <td>{{ $chart->physicalLocation?->location_label ?? '—' }}</td>
                        <td>
                            <span class="status-badge status-{{ $chart->status }}">
                                <i class="fas fa-circle" style="font-size:7px"></i>
                                {{ str_replace('_',' ',$chart->status) }}
                            </span>
                        </td>
                        <td style="white-space:nowrap">{{ $chart->retention_end_date?->format('m/d/Y') ?? 'Permanent' }}</td>
                        <td>{{ $chart->archivedBy->name }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($charts->hasPages())
        <div class="pagination-wrap">
            {{ $charts->withQueryString()->links() }}
        </div>
        @endif
    @endif
</div>

@endsection