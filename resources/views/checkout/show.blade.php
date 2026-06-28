@extends('layouts.app')
@section('title', 'Checkout Record #' . $checkout->id)

@push('styles')
<style>
    /* ── Checkout Show extras ────────────────────────────────────── */
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

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

    .overdue-text {
        color: var(--danger);
        font-weight: 700;
    }

    /* Return form card — success tint */
    .return-card {
        border-color: var(--success-border) !important;
    }

    .return-card .card-header {
        background: var(--success-light);
        color: var(--success-text);
        border-bottom-color: var(--success-border);
    }

    .return-form-row {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .return-form-row .form-control {
        flex: 1;
        min-width: 200px;
    }

    @media (max-width: 768px) {
        .detail-grid { grid-template-columns: 1fr; }
        .return-form-row { flex-direction: column; align-items: stretch; }
        .return-form-row .btn { justify-content: center; }
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
            <a href="{{ route('checkout.index') }}">Checkouts</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            Record #{{ $checkout->id }}
        </div>
        <h1>
            Checkout Record
            <code style="font-size:18px; font-weight:700">#{{ $checkout->id }}</code>
        </h1>
    </div>
    <a href="{{ route('checkout.index') }}" class="btn btn-ghost">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

{{-- Two-column detail grid --}}
<div class="detail-grid">

    {{-- Chart & Patient --}}
    <div class="card" style="margin-bottom:0">
        <div class="card-header">
            <span><i class="fas fa-folder-medical"></i> &nbsp;Chart &amp; Patient</span>
            <a href="{{ route('charts.show', $checkout->archivedChart) }}" class="btn btn-xs btn-secondary">
                <i class="fas fa-external-link-alt"></i> View Chart
            </a>
        </div>
        <div class="card-body">
            <table class="detail-table">
                <tr>
                    <td>Patient</td>
                    <td>
                        <a href="{{ route('charts.show', $checkout->archivedChart) }}"
                           style="color:var(--accent); text-decoration:none; font-weight:700">
                            {{ $checkout->archivedChart->patient->full_name }}
                        </a>
                    </td>
                </tr>
                <tr>
                    <td>Case Number</td>
                    <td><code>{{ $checkout->archivedChart->case_number }}</code></td>
                </tr>
                <tr>
                    <td>Department</td>
                    <td>{{ $checkout->department }}</td>
                </tr>
                <tr>
                    <td>Person</td>
                    <td>{{ $checkout->person }}</td>
                </tr>
                <tr>
                    <td>Purpose</td>
                    <td>{{ $checkout->purpose }}</td>
                </tr>
                @if($checkout->notes)
                <tr>
                    <td>Notes</td>
                    <td>{{ $checkout->notes }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    {{-- Dates & Status --}}
    <div class="card" style="margin-bottom:0">
        <div class="card-header">
            <span><i class="fas fa-clock"></i> &nbsp;Dates &amp; Status</span>
            @php
                $coClass = match($checkout->status) {
                    'active'   => 'badge-warning',
                    'returned' => 'badge-success',
                    'overdue'  => 'badge-danger',
                    default    => 'badge-info',
                };
            @endphp
            <span class="badge {{ $coClass }}">{{ $checkout->status }}</span>
        </div>
        <div class="card-body">
            <table class="detail-table">
                <tr>
                    <td>Checked Out</td>
                    <td>{{ $checkout->checked_out_at->format('m/d/Y H:i') }}</td>
                </tr>
                <tr>
                    <td>Checked Out By</td>
                    <td>{{ $checkout->checkedOutBy->name }}</td>
                </tr>
                <tr>
                    <td>Expected Return</td>
                    <td class="{{ $checkout->is_overdue ? 'overdue-text' : '' }}">
                        {{ $checkout->expected_return_date->format('m/d/Y') }}
                        @if($checkout->is_overdue)
                            &nbsp;<span class="badge badge-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                {{ $checkout->days_overdue }}d overdue
                            </span>
                        @endif
                    </td>
                </tr>
                @if($checkout->returned_at)
                <tr>
                    <td>Returned At</td>
                    <td>{{ $checkout->returned_at->format('m/d/Y H:i') }}</td>
                </tr>
                <tr>
                    <td>Returned By</td>
                    <td>{{ $checkout->returnedBy?->name }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

</div>

{{-- Return Form --}}
@if($checkout->status !== 'returned')
<div class="card return-card">
    <div class="card-header">
        <span><i class="fas fa-check"></i> &nbsp;Mark as Returned</span>
    </div>
    <div class="card-body">
        <form action="{{ route('checkout.checkin', $checkout->archivedChart) }}" method="POST">
            @csrf
            <div class="return-form-row">
                <input type="text" name="notes" class="form-control"
                    placeholder="Return notes (optional)">
                <button type="submit" class="btn btn-success"
                    onclick="return confirmReturn(this.closest('form'))">
                    <i class="fas fa-check"></i> Mark as Returned
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection