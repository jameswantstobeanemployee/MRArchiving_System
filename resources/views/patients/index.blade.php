@extends('layouts.app')
@section('title', 'Patients')

@push('styles')
<style>
    /* ── Patients Index extras ───────────────────────────────────── */
    .search-bar {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }

    .search-bar .form-control {
        flex: 1;
        min-width: 200px;
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

    .chart-count {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 12.5px;
        font-weight: 600;
        color: var(--info-text);
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
        .search-bar .form-control { min-width: 100%; }
        .search-bar .btn { flex: 1; justify-content: center; }
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
            Patients
        </div>
        <h1>Patients</h1>
    </div>
    <a href="{{ route('patients.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> New Patient
    </a>
</div>

{{-- Search --}}
<div class="card">
    <div class="card-header">
        <span><i class="fas fa-filter"></i> &nbsp;Search</span>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('patients.index') }}">
            <div class="search-bar">
                <input type="text" name="search" class="form-control"
                    placeholder="Search by name or MR#…"
                    value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                <a href="{{ route('patients.index') }}" class="btn btn-secondary">
                    <i class="fas fa-undo"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Results --}}
<div class="card">
    <div class="card-header">
        <div class="card-header-row">
            <span>
                <i class="fas fa-users" style="color:var(--text-muted)"></i>
                &nbsp;Results
                <span class="results-count">&nbsp;— <strong>{{ $patients->total() }}</strong> patients found</span>
            </span>
        </div>
    </div>

    @if($patients->isEmpty())
        <div class="empty-state">
            <i class="fas fa-users empty-state-icon"></i>
            <h3>No patients found</h3>
            <p>
                Try adjusting your search or
                <a href="{{ route('patients.create') }}" style="color:var(--accent)">add a new patient</a>.
            </p>
        </div>
    @else
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>MR Number</th>
                        <th>Date of Birth</th>
                        <th>Charts</th>
                        <th>Status</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($patients as $patient)
                    <tr>
                        <td>
                            <a href="{{ route('patients.show', $patient) }}" class="patient-link">
                                {{ $patient->full_name }}
                            </a>
                        </td>
                        <td class="mono-cell">{{ $patient->medical_record_number }}</td>
                        <td>{{ $patient->date_of_birth?->format('m/d/Y') ?? '—' }}</td>
                        <td>
                            <span class="chart-count">
                                <i class="fas fa-folder-medical" style="font-size:11px"></i>
                                {{ $patient->archived_charts_count }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $patient->is_active ? 'badge-success' : 'badge-danger' }}">
                                {{ $patient->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="actions-cell">
                            <div class="row-actions" style="visibility:visible">
                                <a href="{{ route('patients.show', $patient) }}" class="action-btn">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('patients.edit', $patient) }}" class="action-btn">
                                    <i class="fas fa-pencil"></i> Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($patients->hasPages())
        <div class="pagination-wrap">
            {{ $patients->withQueryString()->links() }}
        </div>
        @endif
    @endif
</div>

@endsection