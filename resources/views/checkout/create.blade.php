@extends('layouts.app')
@section('title', 'Check Out Chart')

@push('styles')
<style>
    /* ── Checkout Create extras ──────────────────────────────────── */
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
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

    .invalid-feedback {
        display: block;
        font-size: 12px;
        color: var(--danger-text);
        margin-top: 4px;
        font-weight: 500;
    }

    .is-invalid {
        border-color: var(--danger) !important;
        box-shadow: 0 0 0 3px rgba(220,38,38,0.1) !important;
    }

    .form-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        padding-top: 6px;
    }

    @media (max-width: 900px) {
        .detail-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="page-header-left">
        <div class="breadcrumb">
            <a href="{{ route('charts.index') }}">Chart Archive</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            <a href="{{ route('charts.show', $chart) }}">{{ $chart->case_number }}</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            Check Out
        </div>
        <h1>Check Out Chart</h1>
    </div>
    <a href="{{ route('charts.show', $chart) }}" class="btn btn-ghost">
        <i class="fas fa-arrow-left"></i> Back to Chart
    </a>
</div>

<div class="detail-grid">

    {{-- Chart Details --}}
    <div class="card" style="margin-bottom:0; align-self:start">
        <div class="card-header">
            <span><i class="fas fa-folder-medical"></i> &nbsp;Chart Details</span>
        </div>
        <div class="card-body">
            <table class="detail-table">
                <tr>
                    <td>Patient</td>
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
                    <td>Case Number</td>
                    <td><code>{{ $chart->case_number }}</code></td>
                </tr>
                <tr>
                    <td>Location</td>
                    <td>{{ $chart->physicalLocation?->location_label ?? '—' }}</td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>
                        <span class="badge badge-info">
                            <i class="fas fa-box-archive"></i> {{ $chart->status }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Checkout Form --}}
    <div class="card" style="margin-bottom:0">
        <div class="card-header">
            <span><i class="fas fa-exchange-alt"></i> &nbsp;Checkout Information</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('checkout.store', $chart) }}">
                @csrf

                <div class="form-group">
                    <label for="department">Department <span style="color:var(--danger)">*</span></label>
                    <input type="text"
                        name="department"
                        id="department"
                        class="form-control @error('department') is-invalid @enderror"
                        value="{{ old('department') }}"
                        required
                        placeholder="e.g. Billing, Legal, Medical Records">
                    @error('department')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="person">Person Receiving <span style="color:var(--danger)">*</span></label>
                    <input type="text"
                        name="person"
                        id="person"
                        class="form-control @error('person') is-invalid @enderror"
                        value="{{ old('person') }}"
                        required
                        placeholder="Full name of person taking chart">
                    @error('person')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="purpose">Purpose <span style="color:var(--danger)">*</span></label>
                    <input type="text"
                        name="purpose"
                        id="purpose"
                        class="form-control @error('purpose') is-invalid @enderror"
                        value="{{ old('purpose') }}"
                        required
                        placeholder="e.g. Patient request, Insurance review">
                    @error('purpose')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="expected_return_date">Expected Return Date <span style="color:var(--danger)">*</span></label>
                    <input type="date"
                        name="expected_return_date"
                        id="expected_return_date"
                        class="form-control @error('expected_return_date') is-invalid @enderror"
                        value="{{ old('expected_return_date', now()->addDays($defaultDays)->format('Y-m-d')) }}"
                        min="{{ today()->format('Y-m-d') }}"
                        max="{{ now()->addDays($maxDays)->format('Y-m-d') }}"
                        required>
                    <p class="form-help">Default: {{ $defaultDays }} days. Maximum: {{ $maxDays }} days.</p>
                    @error('expected_return_date')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2"
                        placeholder="Optional notes…">{{ old('notes') }}</textarea>
                </div>

                <div class="divider"></div>

                <div class="form-actions">
                    <a href="{{ route('charts.show', $chart) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-exchange-alt"></i> Confirm Check Out
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection