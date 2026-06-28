@extends('layouts.app')
@section('title', 'Checkout Management')

@push('styles')
<style>
    /* ── Checkout Index extras ───────────────────────────────────── */
    .filter-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    .filter-bar .form-control {
        flex: 1;
        min-width: 140px;
    }

    .filter-actions {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
        align-items: center;
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

    .overdue-cell {
        color: var(--danger);
        font-weight: 700;
    }

    .overdue-sub {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 11.5px;
        color: var(--danger);
        font-weight: 600;
        margin-top: 2px;
    }

    .by-line {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 2px;
    }

    .card-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        width: 100%;
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

    .pagination-wrap {
        padding: 14px 20px;
        border-top: 1px solid var(--divider);
        display: flex;
        justify-content: flex-end;
    }

    .actions-cell { white-space: nowrap; }

    @media (max-width: 768px) {
        .filter-bar .form-control { min-width: 100%; }
        .filter-actions { width: 100%; flex-wrap: wrap; }
        .filter-actions .btn { flex: 1; justify-content: center; }
    }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="page-header-left">
        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            Checkouts
        </div>
        <h1>Checkout Management</h1>
    </div>
</div>

{{-- Filters --}}
<div class="card">
    <div class="card-header">
        <span><i class="fas fa-filter"></i> &nbsp;Filters</span>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('checkout.index') }}">
            <div class="filter-bar">
                <select name="status" class="form-control" style="max-width:160px">
                    <option value="">All Statuses</option>
                    <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="overdue"  {{ request('status') === 'overdue'  ? 'selected' : '' }}>Overdue</option>
                    <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Returned</option>
                </select>
                <input type="text" name="department" class="form-control"
                    placeholder="Filter by department…"
                    value="{{ request('department') }}">
                <div class="filter-actions">
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--text-secondary);white-space:nowrap;cursor:pointer">
                        <input type="checkbox" name="overdue" value="1" {{ request('overdue') ? 'checked' : '' }}>
                        Overdue only
                    </label>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('checkout.index') }}" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                    <a href="{{ route('reports.checkout-status', ['export' => 'csv'] + request()->all()) }}"
                       class="btn btn-secondary">
                        <i class="fas fa-download"></i> Export CSV
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
                &nbsp;Results
                <span class="results-count">&nbsp;— <strong>{{ $checkouts->total() }}</strong> checkouts found</span>
            </span>
        </div>
    </div>

    @if($checkouts->isEmpty())
        <div class="empty-state">
            <i class="fas fa-inbox empty-state-icon"></i>
            <h3>No checkouts found</h3>
            <p>Try adjusting your filters.</p>
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
                        <th>Purpose</th>
                        <th>Checked Out</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($checkouts as $co)
                    @php $overdue = $co->status !== 'returned' && $co->expected_return_date->isPast(); @endphp
                    <tr>
                        <td>
                            <a href="{{ route('charts.show', $co->archivedChart) }}" class="patient-link">
                                {{ $co->archivedChart->patient->full_name }}
                            </a>
                        </td>
                        <td class="mono-cell">{{ $co->archivedChart->case_number }}</td>
                        <td>{{ $co->department }}</td>
                        <td>{{ $co->person }}</td>
                        <td>{{ Str::limit($co->purpose, 40) }}</td>
                        <td>
                            <div>{{ $co->checked_out_at->format('m/d/Y') }}</div>
                            <div class="by-line">{{ $co->checkedOutBy->name }}</div>
                        </td>
                        <td>
                            <div class="{{ $overdue ? 'overdue-cell' : '' }}">
                                {{ $co->expected_return_date->format('m/d/Y') }}
                            </div>
                            @if($overdue)
                                <div class="overdue-sub">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    {{ $co->days_overdue }}d overdue
                                </div>
                            @endif
                        </td>
                        <td>
                            @php
                                $coClass = match($co->status) {
                                    'active'   => 'badge-warning',
                                    'returned' => 'badge-success',
                                    'overdue'  => 'badge-danger',
                                    default    => 'badge-info',
                                };
                            @endphp
                            <span class="badge {{ $coClass }}">{{ $co->status }}</span>
                        </td>
                        <td class="actions-cell">
                            <div class="row-actions" style="visibility:visible">
                                <a href="{{ route('checkout.show', $co) }}" class="action-btn">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                @if($co->status !== 'returned')
                                    <form action="{{ route('checkout.checkin', $co->archivedChart) }}" method="POST" style="display:inline">
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

        @if($checkouts->hasPages())
        <div class="pagination-wrap">
            {{ $checkouts->withQueryString()->links() }}
        </div>
        @endif
    @endif
</div>

@endsection