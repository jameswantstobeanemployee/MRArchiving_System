{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="page-header-left">
        <h1>Dashboard</h1>
        <p style="font-size:13px; color:var(--text-muted); margin-top:3px;">
            Good {{ now()->format('H') < 12 ? 'morning' : (now()->format('H') < 17 ? 'afternoon' : 'evening') }},
            <strong style="color:var(--text-secondary);">{{ auth()->user()->name }}</strong>
            &mdash; {{ now()->format('l, F j, Y') }}
        </p>
    </div>
    <div class="d-flex gap-1">
        <a href="{{ route('charts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Archive Chart
        </a>
        <a href="{{ route('checkout.select') }}" class="btn btn-secondary">
            <i class="fas fa-exchange-alt"></i> Check Out
        </a>
    </div>
</div>

{{-- Alert Banners --}}
@if($overdue_checkouts > 0)
<div class="alert alert-danger">
    <i class="fas fa-exclamation-circle"></i>
    <div>
        <strong>{{ $overdue_checkouts }} chart{{ $overdue_checkouts !== 1 ? 's' : '' }} overdue</strong>
        &mdash; please follow up on these checkouts immediately.
        <a href="{{ route('checkout.index') }}?filter=overdue"
           style="margin-left:8px; color:inherit; font-weight:600; text-decoration:underline;">
            View all &rarr;
        </a>
    </div>
</div>
@endif

@if($near_full_boxes > 0)
<div class="alert alert-warning">
    <i class="fas fa-box"></i>
    <div>
        <strong>{{ $near_full_boxes }} box{{ $near_full_boxes !== 1 ? 'es' : '' }} near full capacity</strong>
        ({{ $near_full_threshold ?? 80 }}%+ full) &mdash; consider redistributing charts or adding new storage.
    </div>
</div>
@endif

@if($expiring_charts > 0)
<div class="alert alert-info">
    <i class="fas fa-calendar-alt"></i>
    <div>
        <strong>{{ $expiring_charts }} chart{{ $expiring_charts !== 1 ? 's' : '' }} expiring within 30 days</strong>
        &mdash; review retention policies and prepare for disposition.
    </div>
</div>
@endif

{{-- Stats Grid --}}
<div class="stats-grid">
    <div class="stat-card info">
        <div class="stat-icon"><i class="fas fa-archive"></i></div>
        <div class="stat-title">Total Charts</div>
        <div class="stat-value">{{ number_format($total_charts) }}</div>
        <div class="stat-trend"><i class="fas fa-circle-dot"></i> Archived records</div>
    </div>

    <div class="stat-card {{ $total_checked_out > 0 ? 'warning' : 'success' }}">
        <div class="stat-icon"><i class="fas fa-exchange-alt"></i></div>
        <div class="stat-title">Checked Out</div>
        <div class="stat-value">{{ number_format($total_checked_out) }}</div>
        <div class="stat-trend"><i class="fas fa-circle-dot"></i> In circulation</div>
    </div>

    <div class="stat-card {{ $overdue_checkouts > 0 ? 'danger' : 'success' }}">
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
        <div class="stat-title">Overdue</div>
        <div class="stat-value">{{ number_format($overdue_checkouts) }}</div>
        <div class="stat-trend">
            <i class="fas fa-circle-dot"></i>
            {{ $overdue_checkouts > 0 ? 'Needs attention' : 'All on time' }}
        </div>
    </div>

    <div class="stat-card {{ $near_full_boxes > 0 ? 'warning' : 'success' }}">
        <div class="stat-icon"><i class="fas fa-boxes-stacked"></i></div>
        <div class="stat-title">Boxes Near Full</div>
        <div class="stat-value">{{ number_format($near_full_boxes) }}</div>
        <div class="stat-trend"><i class="fas fa-circle-dot"></i> &gt;{{ $near_full_threshold ?? 80 }}% capacity</div>
    </div>

    <div class="stat-card {{ $expiring_charts > 0 ? 'warning' : 'info' }}">
        <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
        <div class="stat-title">Expiring Soon</div>
        <div class="stat-value">{{ number_format($expiring_charts) }}</div>
        <div class="stat-trend"><i class="fas fa-circle-dot"></i> Next 30 days</div>
    </div>
</div>

{{-- Main Content Grid --}}
<div class="dashboard-grid">

    {{-- Recent Archives --}}
    <div class="card dashboard-card">
        <div class="card-header">
            <span><i class="fas fa-history"></i>&ensp;Recent Archives</span>
            <a href="{{ route('charts.index') }}" class="btn btn-secondary btn-sm">
                View all <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        @if($recent_archives->isEmpty())
            <div class="empty-state">
                <i class="fas fa-inbox empty-state-icon"></i>
                <h3>No recent archives</h3>
                <p>Charts you archive will appear here.</p>
            </div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Case #</th>
                        <th>Box</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recent_archives as $chart)
                    <tr>
                        <td>
                            <a href="{{ route('charts.show', $chart) }}" class="table-link">
                                {{ $chart->patient->full_name }}
                            </a>
                        </td>
                        <td><code>{{ $chart->case_number }}</code></td>
                        <td>
                            @if($chart->physicalLocation)
                                <span class="badge badge-info">
                                    <i class="fas fa-box"></i>
                                    {{ $chart->physicalLocation->box_code }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-muted" style="font-size:12.5px;">
                                {{ $chart->archived_date->format('M d, Y') }}
                            </span>
                        </td>
                        <td>
                            <div class="row-actions">
                                <a href="{{ route('charts.show', $chart) }}" class="action-btn" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Storage Utilization --}}
    <div class="card dashboard-card">
        <div class="card-header">
            <span><i class="fas fa-chart-simple"></i>&ensp;Storage Utilization</span>
            <a href="{{ route('reports.box-status') }}" class="btn btn-secondary btn-sm">
                Full report <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        @if($top_boxes->isEmpty())
            <div class="empty-state">
                <i class="fas fa-box-open empty-state-icon"></i>
                <h3>No boxes configured</h3>
                <p>Add your first storage box to get started.</p>
                <a href="{{ route('locations.boxes.create') }}" class="btn btn-primary btn-sm mt-2">
                    <i class="fas fa-plus"></i> Add Box
                </a>
            </div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Box</th>
                        <th>Location</th>
                        <th>Used</th>
                        <th style="min-width:130px;">Fill Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($top_boxes as $box)
                    @php
                        $fp = $box->fill_percentage;
                        $pc = $fp >= 95 ? 'danger' : ($fp >= 80 ? 'warning' : 'success');
                    @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-center gap-1">
                                <strong>{{ $box->box_code }}</strong>
                                @if($fp >= 95)
                                    <span class="badge badge-danger">Full</span>
                                @elseif($fp >= 80)
                                    <span class="badge badge-warning">Near Full</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="text-muted truncate"
                                  style="font-size:12px; max-width:120px; display:block;">
                                {{ $box->shelf->room->name }} / {{ $box->shelf->name }}
                            </span>
                        </td>
                        <td style="font-size:12.5px;">
                            <span class="{{ $pc === 'danger' ? 'text-danger' : ($pc === 'warning' ? 'text-warning' : 'text-success') }}">
                                {{ $box->current_count }}
                            </span>
                            <span class="text-muted">/ {{ $box->capacity }}</span>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <div class="progress" style="flex:1;">
                                    <div class="progress-bar {{ $pc }}" style="width:{{ $fp }}%;"></div>
                                </div>
                                <span style="font-size:11.5px; font-weight:600; color:var(--text-muted);
                                             min-width:32px; text-align:right;">
                                    {{ $fp }}%
                                </span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Active Checkouts (full width) --}}
    <div class="card dashboard-card-full" data-checkouts-table>
        <div class="card-header">
            <div class="d-flex align-center gap-1">
                <i class="fas fa-exchange-alt"></i>&ensp;Active Checkouts
                @if($active_checkouts->where('is_overdue', true)->count() > 0)
                    <span class="badge badge-danger">
                        {{ $active_checkouts->where('is_overdue', true)->count() }} overdue
                    </span>
                @endif
            </div>
            <a href="{{ route('checkout.index') }}" class="btn btn-secondary btn-sm">
                Manage all <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        @if($active_checkouts->isEmpty())
            <div class="empty-state">
                <i class="fas fa-check-circle empty-state-icon" style="color:var(--success); opacity:0.5;"></i>
                <h3>No active checkouts</h3>
                <p>All charts are currently in the archive.</p>
            </div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Case #</th>
                        <th>Department</th>
                        <th>Checked Out To</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Overdue</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($active_checkouts as $checkout)
                    @php
                        $isOverdue   = $checkout->is_overdue;
                        $daysOverdue = $checkout->days_overdue;
                    @endphp
                    <tr class="{{ $isOverdue ? 'checkout-overdue-row' : '' }}">
                        <td>
                            <a href="{{ route('charts.show', $checkout->archivedChart) }}" class="table-link">
                                {{ $checkout->archivedChart->patient->full_name }}
                            </a>
                        </td>
                        <td><code>{{ $checkout->archivedChart->case_number }}</code></td>
                        <td>
                            @if($checkout->department)
                                <span class="badge badge-info">{{ $checkout->department }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight:600; font-size:13.5px;">{{ $checkout->person }}</div>
                            @if($checkout->checkedOutBy)
                                <div class="text-muted" style="font-size:11.5px;">
                                    by {{ $checkout->checkedOutBy->name }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <div style="font-size:13.5px;
                                        {{ $isOverdue ? 'color:var(--danger); font-weight:600;' : '' }}">
                                @if($isOverdue)<i class="fas fa-exclamation-circle"></i> @endif
                                {{ $checkout->expected_return_date->format('M d, Y') }}
                            </div>
                            @if(!$isOverdue)
                                <div class="text-muted" style="font-size:11.5px;">
                                    {{ $checkout->expected_return_date->diffForHumans() }}
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($isOverdue)
                                <span class="badge badge-danger">
                                    <i class="fas fa-clock"></i> Overdue
                                </span>
                            @else
                                <span class="badge badge-warning">
                                    <i class="fas fa-book-open"></i> Active
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($isOverdue)
                                <span style="color:var(--danger); font-weight:700; font-size:13px;">
                                    {{ $daysOverdue }}d
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="row-actions">
                                <form action="{{ route('checkout.checkin', $checkout->archivedChart) }}"
                                      method="POST" style="display:inline;">
                                    @csrf
                                    <button type="button"
                                            class="action-btn"
                                            onclick="confirmReturn(this.closest('form'))">
                                        <i class="fas fa-undo-alt"></i> Return
                                    </button>
                                </form>
                                <a href="{{ route('checkout.show', $checkout) }}" class="action-btn">
                                    <i class="fas fa-info-circle"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

{{-- Quick Actions --}}
{{-- <div class="card">
    <div class="card-header">
        <span><i class="fas fa-bolt"></i>&ensp;Quick Actions</span>
    </div>
    <div class="card-body">
        <div class="quick-actions-grid">
            <a href="{{ route('charts.create') }}" class="quick-action-btn quick-action-primary">
                <div class="quick-action-icon">
                    <i class="fas fa-plus"></i>
                </div>
                <div class="quick-action-label">Archive New Chart</div>
            </a>
            <a href="{{ route('checkout.select') }}" class="quick-action-btn quick-action-success">
                <div class="quick-action-icon">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div class="quick-action-label">Check Out Chart</div>
            </a>
            <a href="{{ route('admin.scanner.index') }}" class="quick-action-btn quick-action-info">
                <div class="quick-action-icon">
                    <i class="fas fa-qrcode"></i>
                </div>
                <div class="quick-action-label">Scan Barcode</div>
            </a>
            <a href="{{ route('reports.index') }}" class="quick-action-btn quick-action-secondary">
                <div class="quick-action-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="quick-action-label">Generate Report</div>
            </a>
        </div>
    </div>
</div> --}}

@endsection

@push('styles')
<style>
    /* ── Dashboard Grid ─────────────────────────────────────────────────── */
    .dashboard-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 0;
    }

    .dashboard-card {
        margin-bottom: 0;
    }

    .dashboard-card-full {
        grid-column: span 2;
        margin-bottom: 20px;
    }

    /* ── Table link ─────────────────────────────────────────────────────── */
    .table-link {
        font-weight: 600;
        color: var(--accent);
        text-decoration: none;
        transition: color var(--transition);
    }

    .table-link:hover {
        color: var(--accent-hover);
        text-decoration: underline;
    }

    /* ── Overdue row tint ───────────────────────────────────────────────── */
    .checkout-overdue-row {
        background: color-mix(in srgb, var(--danger-light) 40%, transparent);
    }

    .checkout-overdue-row:hover {
        background: color-mix(in srgb, var(--danger-light) 65%, transparent) !important;
    }

    /* ── Quick Actions Grid ─────────────────────────────────────────────── */
    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }

    .quick-action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 20px 16px;
        border-radius: var(--radius-md);
        text-decoration: none;
        transition: all var(--transition-md);
        border: 1px solid transparent;
        cursor: pointer;
        text-align: center;
    }

    .quick-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--card-shadow-hover);
    }

    .quick-action-btn:active {
        transform: translateY(0) scale(0.98);
    }

    .quick-action-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        transition: all var(--transition-md);
    }

    .quick-action-label {
        font-size: 12.5px;
        font-weight: 600;
        line-height: 1.3;
    }

    /* Primary */
    .quick-action-primary {
        background: var(--info-light);
        border-color: var(--info-border);
        color: var(--info-text);
    }

    .quick-action-primary .quick-action-icon {
        background: var(--accent);
        color: white;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    .quick-action-primary:hover {
        background: var(--accent);
        border-color: var(--accent);
        color: white;
    }

    .quick-action-primary:hover .quick-action-icon {
        background: rgba(255,255,255,0.2);
        box-shadow: none;
    }

    /* Success */
    .quick-action-success {
        background: var(--success-light);
        border-color: var(--success-border);
        color: var(--success-text);
    }

    .quick-action-success .quick-action-icon {
        background: var(--success);
        color: white;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
    }

    .quick-action-success:hover {
        background: var(--success);
        border-color: var(--success);
        color: white;
    }

    .quick-action-success:hover .quick-action-icon {
        background: rgba(255,255,255,0.2);
        box-shadow: none;
    }

    /* Info */
    .quick-action-info {
        background: color-mix(in srgb, var(--info-light) 60%, transparent);
        border-color: var(--info-border);
        color: var(--info-text);
    }

    .quick-action-info .quick-action-icon {
        background: #0891b2;
        color: white;
        box-shadow: 0 4px 12px rgba(8, 145, 178, 0.3);
    }

    .quick-action-info:hover {
        background: #0891b2;
        border-color: #0891b2;
        color: white;
    }

    .quick-action-info:hover .quick-action-icon {
        background: rgba(255,255,255,0.2);
        box-shadow: none;
    }

    /* Secondary */
    .quick-action-secondary {
        background: var(--table-header-bg);
        border-color: var(--border-color);
        color: var(--text-secondary);
    }

    .quick-action-secondary .quick-action-icon {
        background: var(--text-muted);
        color: white;
        box-shadow: 0 4px 12px rgba(107, 114, 128, 0.25);
    }

    .quick-action-secondary:hover {
        background: var(--border-color);
        border-color: var(--text-muted);
        color: var(--text-primary);
    }

    .quick-action-secondary:hover .quick-action-icon {
        background: var(--text-secondary);
        box-shadow: none;
    }

    /* ── Responsive ─────────────────────────────────────────────────────── */
    @media (max-width: 1200px) {
        .quick-actions-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 1024px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-card-full {
            grid-column: span 1;
        }
    }

    @media (max-width: 640px) {
        .quick-actions-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Auto-refresh active checkouts every 60s
    let autoRefreshInterval;

    function startAutoRefresh() {
        autoRefreshInterval = setInterval(() => {
            fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.text())
                .then(html => {
                    const doc = parser.parseFromString(html, 'text/html');
                    const newTbody = doc.querySelector('[data-checkouts-table] tbody');
                    const curTbody = document.querySelector('[data-checkouts-table] tbody');
                    if (newTbody && curTbody) curTbody.innerHTML = newTbody.innerHTML;
                })
                .catch(() => {});
        }, 60000);
    }

    @if(!$active_checkouts->isEmpty())
        const parser = new DOMParser();
        startAutoRefresh();
    @endif

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearInterval(autoRefreshInterval);
        } else if (@json(!$active_checkouts->isEmpty())) {
            startAutoRefresh();
        }
    });
</script>
@endpush