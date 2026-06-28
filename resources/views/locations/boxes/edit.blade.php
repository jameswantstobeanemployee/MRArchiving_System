@extends('layouts.app')
@section('title', 'Edit Box')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            <a href="{{ route('locations.rooms.index') }}">Locations</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            <a href="{{ route('locations.rooms.show', $box->shelf->room_id) }}">{{ $box->shelf->room->name }}</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            <a href="{{ route('locations.boxes.show', $box->id) }}">{{ $box->box_code }}</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            Edit
        </div>
        <h1>Edit Box: {{ $box->box_code }}</h1>
    </div>
    <a href="{{ route('locations.rooms.show', $box->shelf->room_id) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="card" style="max-width:640px">
    <div class="card-header">
        <span><i class="fas fa-box" style="color:var(--text-muted)"></i> &nbsp;Box Details</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('locations.boxes.update', [$box->shelf, $box]) }}">
            @csrf @method('PUT')
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group">
                    <label>Box Number <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="box_number"
                        class="form-control @error('box_number') is-invalid @enderror"
                        value="{{ old('box_number', $box->box_number) }}" required>
                    @error('box_number')<div class="form-help" style="color:var(--danger)">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Box Code <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="box_code"
                        class="form-control @error('box_code') is-invalid @enderror"
                        value="{{ old('box_code', $box->box_code) }}" required>
                    @error('box_code')<div class="form-help" style="color:var(--danger)">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-group">
                <label>Capacity <span style="color:var(--danger)">*</span></label>
                <input type="number" name="capacity" class="form-control"
                    value="{{ old('capacity', $box->capacity) }}" required min="1" max="1000">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $box->description) }}</textarea>
            </div>
            <div class="form-group">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:500">
                    <input type="checkbox" name="is_active" value="1"
                        {{ old('is_active', $box->is_active) ? 'checked' : '' }}
                        style="width:16px;height:16px;cursor:pointer">
                    Active
                </label>
                <div class="form-help">Inactive boxes will not be available for chart assignment.</div>
            </div>

            <div class="divider"></div>

            <div style="display:flex;justify-content:flex-end;gap:8px">
                <a href="{{ route('locations.rooms.show', $box->shelf->room_id) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

@endsection