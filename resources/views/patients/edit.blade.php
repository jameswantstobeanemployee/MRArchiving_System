@extends('layouts.app')
@section('title', 'Edit Patient')

@push('styles')
<style>
    /* ── Patient Edit extras ─────────────────────────────────────── */

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
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

    .section-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--text-muted);
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--divider);
    }

    .section-label i {
        width: 22px;
        height: 22px;
        border-radius: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        background: var(--info-light);
        color: var(--info-text);
    }

    @media (max-width: 640px) {
        .form-row { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="page-header-left">
        <div class="breadcrumb">
            <a href="{{ route('patients.index') }}">Patients</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            <a href="{{ route('patients.show', $patient) }}">{{ $patient->full_name }}</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            Edit
        </div>
        <h1>Edit Patient: {{ $patient->full_name }}</h1>
    </div>
    <a href="{{ route('patients.show', $patient) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="card" style="max-width:640px">
    <div class="card-header">
        <span><i class="fas fa-pencil"></i> &nbsp;Patient Information</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('patients.update', $patient) }}">
            @csrf @method('PUT')

            <div class="section-label">
                <i class="fas fa-id-card"></i>
                Identification
            </div>

            <div class="form-group">
                <label for="medical_record_number">Medical Record Number <span style="color:var(--danger)">*</span></label>
                <input type="text"
                    name="medical_record_number"
                    id="medical_record_number"
                    class="form-control @error('medical_record_number') is-invalid @enderror"
                    value="{{ old('medical_record_number', $patient->medical_record_number) }}"
                    required>
                @error('medical_record_number')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="section-label" style="margin-top:20px">
                <i class="fas fa-user"></i>
                Name
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="last_name">Last Name <span style="color:var(--danger)">*</span></label>
                    <input type="text"
                        name="last_name"
                        id="last_name"
                        class="form-control @error('last_name') is-invalid @enderror"
                        value="{{ old('last_name', $patient->last_name) }}"
                        required>
                    @error('last_name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="first_name">First Name <span style="color:var(--danger)">*</span></label>
                    <input type="text"
                        name="first_name"
                        id="first_name"
                        class="form-control @error('first_name') is-invalid @enderror"
                        value="{{ old('first_name', $patient->first_name) }}"
                        required>
                    @error('first_name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="section-label" style="margin-top:6px">
                <i class="fas fa-calendar"></i>
                Other
            </div>

            <div class="form-group">
                <label for="date_of_birth">Date of Birth</label>
                <input type="date"
                    name="date_of_birth"
                    id="date_of_birth"
                    class="form-control"
                    value="{{ old('date_of_birth', $patient->date_of_birth?->format('Y-m-d')) }}">
            </div>

            <div class="divider"></div>

            <div class="form-actions">
                <a href="{{ route('patients.show', $patient) }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

@endsection