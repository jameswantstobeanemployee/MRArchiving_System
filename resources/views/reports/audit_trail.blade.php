@extends('layouts.app')
@section('title', 'Audit Trail')

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
    .action-code {
        font-family: 'DM Mono', monospace;
        font-size: 12px;
        background: var(--divider);
        border: 1px solid var(--border-color);
        padding: 3px 8px;
        border-radius: 4px;
        color: var(--text-secondary);
        font-weight: 500;
        white-space: nowrap;
    }
    .details-toggle {
        background: none;
        border: 1px solid var(--border-color);
        padding: 4px 10px;
        border-radius: var(--radius-sm);
        font-size: 11.5px;
        font-weight: 600;
        color: var(--text-muted);
        cursor: pointer;
        transition: all var(--transition);
    }
    .details-toggle:hover {
        background: var(--border-color);
        color: var(--text-primary);
    }
    .details-content {
        margin-top: 8px;
        font-size: 11.5px;
        background: var(--table-header-bg);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-sm);
        padding: 10px;
    }
    .details-content strong {
        color: var(--text-primary);
        font-weight: 600;
        display: block;
        margin-bottom: 4px;
    }
    .details-content pre {
        background: var(--card-bg);
        padding: 8px;
        border-radius: 4px;
        max-height: 120px;
        overflow: auto;
        margin: 4px 0 0 0;
        font-size: 11px;
        font-family: 'DM Mono', monospace;
        border: 1px solid var(--border-color);
    }
    .pagination-wrap {
        padding: 14px 20px;
        border-top: 1px solid var(--divider);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .pagination-info {
        font-size: 12.5px;
        color: var(--text-muted);
        font-weight: 500;
    }
    .pagination-links {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .page-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        padding: 0 10px;
        border-radius: var(--radius-sm);
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid var(--border-color);
        background: var(--card-bg);
        color: var(--text-secondary);
        cursor: pointer;
        transition: all var(--transition);
        line-height: 1;
    }
    .page-btn:hover:not(.disabled):not(.active) {
        background: var(--table-row-hover);
        border-color: var(--accent);
        color: var(--accent);
    }
    .page-btn.active {
        background: var(--accent);
        border-color: var(--accent);
        color: #fff;
        cursor: default;
    }
    .page-btn.disabled {
        opacity: 0.4;
        cursor: not-allowed;
        pointer-events: none;
    }
    @media (max-width: 768px) {
        .pagination-info { display: none; }
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
            Audit Trail
        </div>
        <h1>Audit Trail</h1>
    </div>
    <div class="d-flex gap-1">
        <a href="{{ route('reports.audit-trail', request()->all() + ['export' => 'csv']) }}" class="btn btn-secondary">
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
                <select name="user_id" class="form-control" style="max-width:200px">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
                <input 
                    type="text" 
                    name="action" 
                    class="form-control" 
                    placeholder="Action keyword…" 
                    value="{{ request('action') }}" 
                    style="max-width:180px"
                >
                <input 
                    type="date" 
                    name="date_from" 
                    class="form-control" 
                    style="max-width:150px" 
                    value="{{ request('date_from') }}"
                    placeholder="From date"
                >
                <input 
                    type="date" 
                    name="date_to" 
                    class="form-control" 
                    style="max-width:150px" 
                    value="{{ request('date_to') }}"
                    placeholder="To date"
                >
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('reports.audit-trail') }}" class="btn btn-secondary">
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
                <i class="fas fa-history" style="color:var(--text-muted)"></i>
                &nbsp;Audit Log
                <span class="results-count">&nbsp;— <strong>{{ $logs->total() }}</strong> records found</span>
            </span>
        </div>
    </div>

    @if($logs->isEmpty())
        <div class="empty-state">
            <i class="fas fa-clipboard-list empty-state-icon"></i>
            <h3>No audit records found</h3>
            <p>Try adjusting your filters or search criteria.</p>
        </div>
    @else
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Table</th>
                        <th>Record ID</th>
                        <th>IP Address</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        <td style="white-space:nowrap; font-family:'DM Mono',monospace; font-size:12px">
                            {{ $log->created_at->format('m/d/Y H:i:s') }}
                        </td>
                        <td style="font-weight:500">{{ $log->user?->name ?? '<system>' }}</td>
                        <td>
                            <span class="action-code">{{ $log->action }}</span>
                        </td>
                        <td>{{ $log->table_name ?? '—' }}</td>
                        <td>{{ $log->record_id ?? '—' }}</td>
                        <td style="font-family:'DM Mono',monospace; font-size:12px">{{ $log->ip_address ?? '—' }}</td>
                        <td>
                            @if($log->old_values || $log->new_values)
                            <button 
                                type="button" 
                                class="details-toggle"
                                onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none'"
                            >
                                <i class="fas fa-info-circle"></i> View Details
                            </button>
                            <div class="details-content" style="display:none">
                                @if($log->old_values)
                                <div>
                                    <strong>Before:</strong>
                                    <pre>{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                                @endif
                                @if($log->new_values)
                                <div style="margin-top:8px">
                                    <strong>After:</strong>
                                    <pre>{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                                @endif
                            </div>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="pagination-wrap">
            <div class="pagination-info">
                Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ number_format($logs->total()) }} results
            </div>
            <div class="pagination-links">
                {{-- Previous --}}
                @if($logs->onFirstPage())
                    <span class="page-btn disabled"><i class="fas fa-chevron-left"></i></span>
                @else
                    <a href="{{ $logs->previousPageUrl() }}" class="page-btn"><i class="fas fa-chevron-left"></i></a>
                @endif

                {{-- First page + ellipsis --}}
                @if($logs->currentPage() > 3)
                    <a href="{{ $logs->url(1) }}" class="page-btn">1</a>
                    @if($logs->currentPage() > 4)
                        <span class="page-btn disabled">&hellip;</span>
                    @endif
                @endif

                {{-- Window of pages around current --}}
                @foreach($logs->getUrlRange(max(1, $logs->currentPage() - 2), min($logs->lastPage(), $logs->currentPage() + 2)) as $page => $url)
                    @if($page == $logs->currentPage())
                        <span class="page-btn active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                    @endif
                @endforeach

                {{-- Ellipsis + last page --}}
                @if($logs->currentPage() < $logs->lastPage() - 2)
                    @if($logs->currentPage() < $logs->lastPage() - 3)
                        <span class="page-btn disabled">&hellip;</span>
                    @endif
                    <a href="{{ $logs->url($logs->lastPage()) }}" class="page-btn">{{ $logs->lastPage() }}</a>
                @endif

                {{-- Next --}}
                @if($logs->hasMorePages())
                    <a href="{{ $logs->nextPageUrl() }}" class="page-btn"><i class="fas fa-chevron-right"></i></a>
                @else
                    <span class="page-btn disabled"><i class="fas fa-chevron-right"></i></span>
                @endif
            </div>
        </div>
        @endif
    @endif
</div>

@endsection