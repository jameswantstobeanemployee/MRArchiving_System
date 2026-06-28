@extends('layouts.app')
@section('title', 'Patient: ' . $patient->full_name)

@push('styles')
<style>
    /* ── Patient Show extras ─────────────────────────────────────── */

    .detail-grid {
        display: grid;
        grid-template-columns: 320px 1fr;
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

    .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .table-scroll .data-table thead { position: static; }

    .pagination-wrap {
        padding: 14px 20px;
        border-top: 1px solid var(--divider);
        display: flex;
        justify-content: flex-end;
    }

    .mono-cell {
        font-family: 'DM Mono', monospace;
        font-size: 12.5px;
        color: var(--text-secondary);
    }

    .case-link {
        font-family: 'DM Mono', monospace;
        font-size: 12.5px;
        color: var(--accent);
        text-decoration: none;
        font-weight: 600;
    }
    .case-link:hover { text-decoration: underline; }

    @media (max-width: 960px) {
        .detail-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 560px) {
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
            <a href="{{ route('patients.index') }}">Patients</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            {{ $patient->full_name }}
        </div>
        <h1>{{ $patient->full_name }}</h1>
    </div>
    <div class="d-flex gap-1" style="flex-wrap:wrap">
        <a href="{{ route('charts.create') }}?patient_id={{ $patient->id }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Archive New Chart
        </a>
        <a href="{{ route('patients.edit', $patient) }}" class="btn btn-secondary">
            <i class="fas fa-pencil"></i> Edit
        </a>
        <a href="{{ route('patients.index') }}" class="btn btn-ghost">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

{{-- Two-column detail grid --}}
<div class="detail-grid">

    {{-- Patient Details --}}
    <div class="card" style="margin-bottom:0; align-self:start">
        <div class="card-header">
            <span><i class="fas fa-user"></i> &nbsp;Patient Details</span>
        </div>
        <div class="card-body">
            <table class="detail-table">
                <tr>
                    <td>MR Number</td>
                    <td><code>{{ $patient->medical_record_number }}</code></td>
                </tr>
                <tr>
                    <td>Last Name</td>
                    <td>{{ $patient->last_name }}</td>
                </tr>
                <tr>
                    <td>First Name</td>
                    <td>{{ $patient->first_name }}</td>
                </tr>
                <tr>
                    <td>Date of Birth</td>
                    <td>{{ $patient->date_of_birth?->format('m/d/Y') ?? '—' }}</td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>
                        <span class="badge {{ $patient->is_active ? 'badge-success' : 'badge-danger' }}">
                            {{ $patient->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Archived Charts --}}
    <div class="card" style="margin-bottom:0">
        <div class="card-header">
            <span><i class="fas fa-folder-medical"></i> &nbsp;Archived Charts</span>
            <div class="d-flex gap-1 align-center">
                <span class="badge badge-info">{{ $charts->total() }}</span>
                <a href="{{ route('charts.create') }}?patient_id={{ $patient->id }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Archive New
                </a>
            </div>
        </div>

        @if($charts->isEmpty())
            <div class="empty-state">
                <i class="fas fa-folder-open empty-state-icon"></i>
                <h3>No charts archived</h3>
                <p>This patient has no archived charts yet.</p>
            </div>
        @else
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Case #</th>
                            <th>Admission</th>
                            <th>Discharge</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Retention</th>
                            <th style="text-align:right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($charts as $chart)
                        <tr>
                            <td>
                                <a href="{{ route('charts.show', $chart) }}" class="case-link">
                                    {{ $chart->case_number }}
                                </a>
                            </td>
                            <td>{{ $chart->admission_date->format('m/d/Y') }}</td>
                            <td>{{ $chart->discharge_date?->format('m/d/Y') ?? '—' }}</td>
                            <td>
                                @if($chart->physicalLocation?->box_code)
                                    <code>{{ $chart->physicalLocation->box_code }}</code>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
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
                            <td>
                                @if($chart->retention_end_date)
                                    <span style="font-size:13px">{{ $chart->retention_end_date->format('m/d/Y') }}</span>
                                @else
                                    <span class="badge badge-success"><i class="fas fa-infinity"></i> Permanent</span>
                                @endif
                            </td>
                            <td>
                                <div class="row-actions" style="visibility:visible">
                                    <a href="{{ route('charts.show', $chart) }}" class="action-btn">
                                        <i class="fas fa-eye"></i> View
                                    </a>
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

</div>

@endsection