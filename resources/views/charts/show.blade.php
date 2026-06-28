@extends('layouts.app')
@section('title', 'Chart — ' . $chart->case_number)

@push('styles')
<style>
    /* ── Chart Show extras ───────────────────────────────────────── */

    /* Two-column detail layout */
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    /* Key-value detail rows inside cards */
    .detail-table {
        width: 100%;
        border-collapse: collapse;
    }

    .detail-table tr + tr td {
        border-top: 1px solid var(--divider);
    }

    .detail-table td {
        padding: 9px 0;
        font-size: 13.5px;
        vertical-align: top;
    }

    .detail-table td:first-child {
        width: 42%;
        color: var(--text-muted);
        font-weight: 500;
        padding-right: 12px;
        white-space: nowrap;
    }

    .detail-table td:last-child {
        color: var(--text-primary);
        font-weight: 500;
    }

    /* Destroy card — danger tint */
    .destroy-card {
        border-color: var(--danger-border) !important;
    }

    .destroy-card .card-header {
        background: var(--danger-light);
        color: var(--danger-text);
        border-bottom-color: var(--danger-border);
    }

    /* Active checkout highlight card */
    .checkout-alert-card {
        border-color: var(--warning-border) !important;
    }

    .checkout-alert-card .card-header {
        background: var(--warning-light);
        color: var(--warning-text);
        border-bottom-color: var(--warning-border);
    }

    /* Checkout info grid */
    .checkout-info-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 16px;
    }

    .checkout-info-item strong {
        display: block;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        margin-bottom: 4px;
    }

    .checkout-info-item span {
        font-size: 13.5px;
        color: var(--text-primary);
        font-weight: 500;
    }

    /* Overdue highlight */
    .overdue-text {
        color: var(--danger) !important;
        font-weight: 700 !important;
    }

    /* Scrollable table wrapper — overflow-x breaks sticky thead, so reset it */
    .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .table-scroll .data-table thead { position: static; }

    /* Responsive */
    @media (max-width: 900px) {
        .detail-grid { grid-template-columns: 1fr; }
        .checkout-info-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 560px) {
        .checkout-info-grid { grid-template-columns: 1fr; }

        .page-header .d-flex { flex-wrap: wrap; gap: 6px; }
        .page-header .btn { font-size: 12.5px; padding: 6px 12px; }
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
            <a href="{{ route('charts.index') }}">Chart Archive</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            {{ $chart->case_number }}
        </div>
        <h1>
            Chart
            <code style="font-size:18px; font-weight:700; letter-spacing:-0.01em">{{ $chart->case_number }}</code>
        </h1>
    </div>
    <div class="d-flex gap-1" style="flex-wrap:wrap">
        @if($chart->status === 'archived')
            <a href="{{ route('checkout.create', $chart) }}" class="btn btn-warning">
                <i class="fas fa-exchange-alt"></i> Check Out
            </a>
            <a href="{{ route('charts.move', $chart) }}" class="btn btn-info">
                <i class="fas fa-arrows-alt"></i> Move
            </a>
        @elseif($chart->status === 'checked_out')
            <form action="{{ route('checkout.checkin', $chart) }}" method="POST" style="display:inline">
                @csrf
                <button type="submit" class="btn btn-success"
                        onclick="return confirmReturn(this.closest('form'))">
                    <i class="fas fa-check"></i> Return Chart
                </button>
            </form>
        @endif
        @if($chart->digital_copy_path)
            <a href="{{ route('charts.download', $chart) }}" class="btn btn-secondary">
                <i class="fas fa-download"></i> Download
            </a>
        @endif
        @if(auth()->user()->isAdmin() && $chart->status !== 'destroyed')
            <button class="btn btn-danger" id="showDestroyBtn">
                <i class="fas fa-trash"></i> Destroy
            </button>
        @endif
        <a href="{{ route('charts.index') }}" class="btn btn-ghost">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

{{-- Destroy Form (hidden) --}}
@if(auth()->user()->isAdmin() && $chart->status !== 'destroyed')
<div id="destroyFormCard" class="card destroy-card" style="display:none; margin-bottom:20px">
    <div class="card-header">
        <i class="fas fa-exclamation-triangle"></i> &nbsp;Mark Chart as Destroyed
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('charts.destroy', $chart) }}">
            @csrf @method('DELETE')
            <div class="form-group">
                <label>Reason for Destruction <span style="color:var(--danger)">*</span></label>
                <textarea name="reason" class="form-control" rows="3" required minlength="10"
                    placeholder="e.g. Retention period expired, authorized destruction per policy…"></textarea>
                <p class="form-help">Minimum 10 characters. This action cannot be undone.</p>
            </div>
            <div class="d-flex gap-1">
                <button type="submit" class="btn btn-danger"
                    onclick="return confirm('This cannot be undone. Continue?')">
                    <i class="fas fa-trash"></i> Confirm Destruction
                </button>
                <button type="button" class="btn btn-ghost" id="hideDestroyBtn">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- ── Two-column detail grid ── --}}
<div class="detail-grid">

    {{-- Chart Information --}}
    <div class="card" style="margin-bottom:0">
        <div class="card-header">
            <span><i class="fas fa-folder-medical"></i> &nbsp;Chart Information</span>
        </div>
        <div class="card-body">
            <table class="detail-table">
                <tr>
                    <td>Status</td>
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
                </tr>
                <tr>
                    <td>Case Number</td>
                    <td><code>{{ $chart->case_number }}</code></td>
                </tr>
                <tr>
                    <td>Admission Date</td>
                    <td>{{ $chart->admission_date->format('m/d/Y') }}</td>
                </tr>
                <tr>
                    <td>Discharge Date</td>
                    <td>{{ $chart->discharge_date?->format('m/d/Y') ?? '—' }}</td>
                </tr>
                <tr>
                    <td>Archived Date</td>
                    <td>{{ $chart->archived_date->format('m/d/Y') }}</td>
                </tr>
                <tr>
                    <td>Archived By</td>
                    <td>{{ $chart->archivedBy->name }}</td>
                </tr>
                <tr>
                    <td>Total Pages</td>
                    <td>{{ $chart->total_pages }}</td>
                </tr>
                <tr>
                    <td>Digital Copy</td>
                    <td>
                        @if($chart->digital_copy_path)
                            <a href="{{ route('charts.download', $chart) }}" style="color:var(--accent); text-decoration:none; font-weight:600">
                                <i class="fas fa-file-pdf" style="font-size:12px"></i>
                                {{ basename($chart->digital_copy_path) }}
                            </a>
                            <span class="text-muted" style="font-size:12px"> — {{ $chart->file_size_formatted }}</span>
                        @else
                            <span class="text-muted">None</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Notes</td>
                    <td>{{ $chart->notes ?? '—' }}</td>
                </tr>
                @if($chart->status === 'destroyed')
                <tr>
                    <td>Destroyed Date</td>
                    <td style="color:var(--danger); font-weight:600">
                        <i class="fas fa-calendar-times" style="font-size:12px"></i>
                        {{ $chart->destroyed_date?->format('m/d/Y') }}
                    </td>
                </tr>
                <tr>
                    <td>Destruction Reason</td>
                    <td style="color:var(--danger-text)">{{ $chart->destroyed_reason }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    {{-- Right column: Patient + Location + Retention --}}
    <div style="display:flex; flex-direction:column; gap:20px">

        {{-- Patient --}}
        <div class="card" style="margin-bottom:0">
            <div class="card-header">
                <span><i class="fas fa-user"></i> &nbsp;Patient</span>
                <a href="{{ route('patients.show', $chart->patient) }}" class="btn btn-xs btn-secondary">
                    <i class="fas fa-external-link-alt"></i> View Patient
                </a>
            </div>
            <div class="card-body">
                <table class="detail-table">
                    <tr>
                        <td>Full Name</td>
                        <td>
                            <a href="{{ route('patients.show', $chart->patient) }}"
                               style="color:var(--accent); text-decoration:none; font-weight:700">
                                {{ $chart->patient->full_name }}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>MR Number</td>
                        <td><code>{{ $chart->patient->medical_record_number }}</code></td>
                    </tr>
                    <tr>
                        <td>Date of Birth</td>
                        <td>{{ $chart->patient->date_of_birth?->format('m/d/Y') ?? '—' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Physical Location --}}
        <div class="card" style="margin-bottom:0">
            <div class="card-header">
                <span><i class="fas fa-map-marker-alt"></i> &nbsp;Physical Location</span>
            </div>
            <div class="card-body">
                @if($chart->physicalLocation)
                    @php $box = $chart->physicalLocation; $shelf = $box->shelf; $room = $shelf->room; @endphp
                    <table class="detail-table">
                        <tr>
                            <td>Room</td>
                            <td>
                                <span style="font-weight:600">{{ $room->name }}</span>
                                <span class="text-muted" style="font-size:12px"> — {{ $room->building }}, {{ $room->floor }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>Shelf</td>
                            <td>{{ $shelf->name }} <code>{{ $shelf->code }}</code></td>
                        </tr>
                        <tr>
                            <td>Box</td>
                            <td>
                                <span style="font-weight:700">Box {{ $box->box_number }}</span>
                                <code style="margin-left:4px">{{ $box->box_code }}</code>
                                <span class="text-muted" style="font-size:12px"> — {{ $box->current_count }}/{{ $box->capacity }} charts</span>
                            </td>
                        </tr>
                    </table>
                @else
                    <div class="empty-state" style="padding:24px">
                        <i class="fas fa-map-marker-slash empty-state-icon" style="font-size:28px"></i>
                        <p>No physical location assigned.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Retention --}}
        <div class="card" style="margin-bottom:0">
            <div class="card-header">
                <span><i class="fas fa-clock"></i> &nbsp;Retention</span>
            </div>
            <div class="card-body">
                <table class="detail-table">
                    <tr>
                        <td>Period</td>
                        <td>{{ $chart->retention_label }}</td>
                    </tr>
                    <tr>
                        <td>End Date</td>
                        <td>
                            @if($chart->retention_end_date)
                                {{ $chart->retention_end_date->format('m/d/Y') }}
                                @if($chart->is_expired)
                                    <span class="badge badge-danger" style="margin-left:6px">
                                        <i class="fas fa-exclamation-triangle"></i> Expired
                                    </span>
                                @else
                                    <span class="text-muted" style="font-size:12px; margin-left:6px">
                                        ({{ $chart->days_until_retention }} days remaining)
                                    </span>
                                @endif
                            @else
                                <span class="badge badge-success">
                                    <i class="fas fa-infinity"></i> Permanent
                                </span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

    </div>
</div>

{{-- ── Active Checkout Alert ── --}}
@if($chart->isCheckedOut() && $chart->currentCheckout)
@php $co = $chart->currentCheckout; @endphp
<div class="card checkout-alert-card">
    <div class="card-header">
        <span><i class="fas fa-exchange-alt"></i> &nbsp;Currently Checked Out</span>
    </div>
    <div class="card-body">
        <div class="checkout-info-grid">
            <div class="checkout-info-item">
                <strong>Department</strong>
                <span>{{ $co->department }}</span>
            </div>
            <div class="checkout-info-item">
                <strong>Person</strong>
                <span>{{ $co->person }}</span>
            </div>
            <div class="checkout-info-item">
                <strong>Purpose</strong>
                <span>{{ $co->purpose }}</span>
            </div>
            <div class="checkout-info-item">
                <strong>Due Date</strong>
                <span class="{{ $co->is_overdue ? 'overdue-text' : '' }}">
                    {{ $co->expected_return_date->format('m/d/Y') }}
                    @if($co->is_overdue)
                        &nbsp;<i class="fas fa-exclamation-triangle"></i>
                        {{ $co->days_overdue }}d overdue
                    @endif
                </span>
            </div>
        </div>
        <form action="{{ route('checkout.checkin', $chart) }}" method="POST" style="display:inline">
            @csrf
            <input type="hidden" name="notes" value="">
            <button type="submit" class="btn btn-success"
                    onclick="return confirmReturn(this.closest('form'))">
                <i class="fas fa-check"></i> Mark as Returned
            </button>
        </form>
    </div>
</div>
@endif

{{-- ── Checkout History ── --}}
<div class="card">
    <div class="card-header">
        <span><i class="fas fa-history"></i> &nbsp;Checkout History</span>
        <span class="badge badge-info">{{ $chart->checkoutHistory->count() }}</span>
    </div>
    @if($chart->checkoutHistory->isEmpty())
        <div class="empty-state">
            <i class="fas fa-inbox empty-state-icon"></i>
            <h3>No checkout history</h3>
            <p>This chart has never been checked out.</p>
        </div>
    @else
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Checked Out</th>
                        <th>Department</th>
                        <th>Person</th>
                        <th>Purpose</th>
                        <th>Due Date</th>
                        <th>Returned</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($chart->checkoutHistory->sortByDesc('checked_out_at') as $co)
                    <tr>
                        <td>
                            <div>{{ $co->checked_out_at->format('m/d/Y') }}</div>
                            <div class="text-muted" style="font-size:12px">
                                {{ $co->checked_out_at->format('H:i') }} &middot; {{ $co->checkedOutBy->name }}
                            </div>
                        </td>
                        <td>{{ $co->department }}</td>
                        <td>{{ $co->person }}</td>
                        <td>{{ $co->purpose }}</td>
                        <td>{{ $co->expected_return_date->format('m/d/Y') }}</td>
                        <td>
                            @if($co->returned_at)
                                <div>{{ $co->returned_at->format('m/d/Y') }}</div>
                                <div class="text-muted" style="font-size:12px">{{ $co->returned_at->format('H:i') }}</div>
                            @else
                                <span class="text-muted">—</span>
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
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- ── Location History ── --}}
<div class="card">
    <div class="card-header">
        <span><i class="fas fa-route"></i> &nbsp;Location History</span>
        <span class="badge badge-info">{{ $chart->locationHistory->count() }}</span>
    </div>
    @if($chart->locationHistory->isEmpty())
        <div class="empty-state">
            <i class="fas fa-map empty-state-icon"></i>
            <h3>No location history</h3>
            <p>This chart has not been moved since being archived.</p>
        </div>
    @else
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Reason</th>
                        <th>Moved By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($chart->locationHistory->sortByDesc('moved_at') as $lh)
                    <tr>
                        <td>
                            <div>{{ $lh->moved_at->format('m/d/Y') }}</div>
                            <div class="text-muted" style="font-size:12px">{{ $lh->moved_at->format('H:i') }}</div>
                        </td>
                        <td>
                            @if($lh->fromBox)
                                <code>{{ $lh->fromBox->location_label }}</code>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($lh->toBox)
                                <code>{{ $lh->toBox->location_label }}</code>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $lh->reason }}</td>
                        <td>{{ $lh->movedBy->name }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
    // Destroy form toggle
    const showBtn = document.getElementById('showDestroyBtn');
    const hideBtn = document.getElementById('hideDestroyBtn');
    const form    = document.getElementById('destroyFormCard');

    showBtn?.addEventListener('click', () => {
        form.style.display = 'block';
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
    hideBtn?.addEventListener('click', () => {
        form.style.display = 'none';
    });
</script>
@endpush