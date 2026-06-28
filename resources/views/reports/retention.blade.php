@extends('layouts.app')
@section('title', 'Retention Report')

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
    .filter-group {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 1;
        min-width: 200px;
    }
    .filter-group label {
        white-space: nowrap;
        font-size: 13px;
        font-weight: 500;
        color: var(--text-secondary);
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
    .row-expired {
        background: var(--danger-light) !important;
    }
    .row-warning {
        background: var(--warning-light) !important;
    }
    .days-remaining {
        font-weight: 600;
    }
    .days-remaining.critical {
        color: var(--danger);
    }
    .days-remaining.warning {
        color: var(--warning);
    }
    .expired-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        color: var(--danger);
        font-weight: 700;
        font-size: 13px;
    }
    .eligible-note {
        font-size: 11px;
        color: var(--text-muted);
        font-style: italic;
        margin-top: 4px;
    }
    .pagination-wrap {
        padding: 14px 20px;
        border-top: 1px solid var(--divider);
        display: flex;
        justify-content: flex-end;
    }
    @media (max-width: 768px) {
        .filter-bar .form-control { min-width: 100%; }
        .filter-group { min-width: 100%; }
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
            Retention Report
        </div>
        <h1>Retention Report</h1>
    </div>
    <div class="d-flex gap-1">
        <a href="{{ route('reports.retention', request()->all() + ['export' => 'csv']) }}" class="btn btn-secondary">
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
                <select name="status" class="form-control" style="max-width:180px">
                    <option value="">Expiring Charts</option>
                    <option value="expired"   {{ request('status') === 'expired'   ? 'selected' : '' }}>Already Expired</option>
                    <option value="permanent" {{ request('status') === 'permanent' ? 'selected' : '' }}>Permanent</option>
                </select>
                <div class="filter-group">
                    <label>Expiring within</label>
                    <input 
                        type="number" 
                        name="expiring_within_days" 
                        class="form-control" 
                        style="max-width:80px"
                        value="{{ request('expiring_within_days', 30) }}" 
                        min="1" 
                        max="3650"
                    >
                    <span style="font-size:13px; color:var(--text-secondary)">days</span>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('reports.retention') }}" class="btn btn-secondary">
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
                <i class="fas fa-hourglass-half" style="color:var(--text-muted)"></i>
                &nbsp;Retention Status
                <span class="results-count">&nbsp;— <strong>{{ $charts->total() }}</strong> charts found</span>
            </span>
        </div>
    </div>

    @if($charts->isEmpty())
        <div class="empty-state">
            <i class="fas fa-calendar-check empty-state-icon"></i>
            <h3>No charts match the criteria</h3>
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
                        <th>Location</th>
                        <th>Retention Period</th>
                        <th>Retention End</th>
                        <th>Days Remaining</th>
                        <th>Status</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($charts as $chart)
                    @php
                        $daysLeft = $chart->days_until_retention;
                        $rowClass = $chart->is_expired ? 'row-expired' : ($daysLeft !== null && $daysLeft <= 7 ? 'row-warning' : '');
                    @endphp
                    <tr class="{{ $rowClass }}">
                        <td>
                            <a href="{{ route('charts.show', $chart) }}" style="font-weight:600; color:var(--accent); text-decoration:none">
                                {{ $chart->patient->full_name }}
                            </a>
                        </td>
                        <td>{{ $chart->patient->medical_record_number }}</td>
                        <td>{{ $chart->case_number }}</td>
                        <td>{{ $chart->physicalLocation?->box_code ?? '—' }}</td>
                        <td>
                            <span class="badge badge-info">{{ $chart->retention_label }}</span>
                        </td>
                        <td style="white-space:nowrap">{{ $chart->retention_end_date?->format('m/d/Y') ?? 'N/A' }}</td>
                        <td>
                            @if($chart->retention_end_date === null)
                                <span class="text-muted">Permanent</span>
                            @elseif($chart->is_expired)
                                <span class="expired-badge">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Expired {{ abs($daysLeft) }} days ago
                                </span>
                            @else
                                <span class="days-remaining {{ $daysLeft <= 30 ? 'critical' : ($daysLeft <= 90 ? 'warning' : '') }}">
                                    {{ $daysLeft }} days
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="status-badge status-{{ $chart->status }}">
                                <i class="fas fa-circle" style="font-size:7px"></i>
                                {{ str_replace('_',' ',$chart->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="row-actions" style="visibility:visible; justify-content:flex-end; flex-direction:column; align-items:flex-end">
                                <a href="{{ route('charts.show', $chart) }}" class="action-btn">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                @if($chart->is_expired && auth()->user()->isAdmin())
                                    <span class="eligible-note">Eligible for destruction</span>
                                @endif
                            </div>
                        </td>
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