@extends('layouts.app')
@section('title', 'Checkout Status Report')

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
    .filter-bar label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: var(--text-secondary);
        font-weight: 500;
        padding: 8px 12px;
        border: 1px solid var(--input-border);
        border-radius: var(--radius-sm);
        background: var(--input-bg);
        cursor: pointer;
        transition: all var(--transition);
        white-space: nowrap;
    }
    .filter-bar label:hover {
        border-color: var(--input-focus);
        background: var(--table-row-hover);
    }
    .filter-bar input[type="checkbox"] {
        cursor: pointer;
        width: 16px;
        height: 16px;
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
    .status-active {
        background: var(--info-light);
        color: var(--info-text);
    }
    .status-overdue {
        background: var(--danger-light);
        color: var(--danger-text);
    }
    .status-returned {
        background: var(--success-light);
        color: var(--success-text);
    }
    .overdue-warning {
        color: var(--danger);
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .overdue-badge {
        display: inline-block;
        margin-top: 4px;
        font-size: 11px;
        background: var(--danger-light);
        color: var(--danger-text);
        padding: 2px 7px;
        border-radius: var(--radius-full);
        font-weight: 600;
    }
    .pagination-wrap {
        padding: 14px 20px;
        border-top: 1px solid var(--divider);
        display: flex;
        justify-content: flex-end;
    }
    @media (max-width: 768px) {
        .filter-bar .form-control { min-width: 100%; }
        .filter-bar label { width: 100%; justify-content: center; }
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
            Checkout Status
        </div>
        <h1>Checkout Status Report</h1>
    </div>
    <div class="d-flex gap-1">
        <a href="{{ route('reports.checkout-status', request()->all() + ['export' => 'csv']) }}" class="btn btn-secondary">
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
                <select name="status" class="form-control" style="max-width:140px">
                    <option value="">All Status</option>
                    <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="overdue"  {{ request('status') === 'overdue'  ? 'selected' : '' }}>Overdue</option>
                    <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Returned</option>
                </select>
                <input 
                    type="text" 
                    name="department" 
                    class="form-control" 
                    placeholder="Department…" 
                    value="{{ request('department') }}"
                >
                <label>
                    <input type="checkbox" name="overdue" value="1" {{ request('overdue') ? 'checked' : '' }}>
                    <span>Overdue only</span>
                </label>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('reports.checkout-status') }}" class="btn btn-secondary">
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
                <i class="fas fa-exchange-alt" style="color:var(--text-muted)"></i>
                &nbsp;Checkouts
                <span class="results-count">&nbsp;— <strong>{{ $checkouts->total() }}</strong> records found</span>
            </span>
        </div>
    </div>

    @if($checkouts->isEmpty())
        <div class="empty-state">
            <i class="fas fa-inbox empty-state-icon"></i>
            <h3>No checkouts found</h3>
            <p>Try adjusting your filters or search criteria.</p>
        </div>
    @else
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Case #</th>
                        <th>Department</th>
                        <th>Person</th>
                        <th>Checked Out</th>
                        <th>Due Date</th>
                        <th>Returned</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($checkouts as $co)
                    @php $overdue = $co->status !== 'returned' && $co->expected_return_date->isPast(); @endphp
                    <tr>
                        <td>
                            <a href="{{ route('charts.show', $co->archivedChart) }}" style="font-weight:600; color:var(--accent); text-decoration:none">
                                {{ $co->archivedChart->patient->full_name }}
                            </a>
                        </td>
                        <td>{{ $co->archivedChart->case_number }}</td>
                        <td>{{ $co->department }}</td>
                        <td>{{ $co->person }}</td>
                        <td style="white-space:nowrap">{{ $co->checked_out_at->format('m/d/Y') }}</td>
                        <td style="white-space:nowrap">
                            <div class="{{ $overdue ? 'overdue-warning' : '' }}">
                                {{ $co->expected_return_date->format('m/d/Y') }}
                                @if($overdue)
                                    <i class="fas fa-exclamation-triangle"></i>
                                @endif
                            </div>
                            @if($overdue)
                                <span class="overdue-badge">
                                    {{ $co->days_overdue }} days overdue
                                </span>
                            @endif
                        </td>
                        <td style="white-space:nowrap">{{ $co->returned_at ? $co->returned_at->format('m/d/Y') : '—' }}</td>
                        <td>
                            <span class="status-badge status-{{ $co->status }}">
                                <i class="fas fa-circle" style="font-size:7px"></i>
                                {{ $co->status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($checkouts->hasPages())
        <div class="pagination-wrap">
            {{ $checkouts->withQueryString()->links() }}
        </div>
        @endif
    @endif
</div>

@endsection