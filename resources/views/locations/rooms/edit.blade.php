@extends('layouts.app')
@section('title', 'Edit Room')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            <a href="{{ route('locations.rooms.index') }}">Locations</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            <a href="{{ route('locations.rooms.show', $room) }}">{{ $room->name }}</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            Edit
        </div>
        <h1>Edit Room: {{ $room->name }}</h1>
    </div>
    <a href="{{ route('locations.rooms.show', $room) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="card" style="max-width:640px">
    <div class="card-header">
        <span><i class="fas fa-door-open" style="color:var(--text-muted)"></i> &nbsp;Room Details</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('locations.rooms.update', $room) }}">
            @csrf @method('PUT')
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group">
                    <label>Room Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $room->name) }}" required>
                    @error('name')<div class="form-help" style="color:var(--danger)">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Code <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="code"
                        class="form-control @error('code') is-invalid @enderror"
                        value="{{ old('code', $room->code) }}" required>
                    @error('code')<div class="form-help" style="color:var(--danger)">{{ $message }}</div>@enderror
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group">
                    <label>Building</label>
                    <input type="text" name="building" class="form-control"
                        value="{{ old('building', $room->building) }}">
                </div>
                <div class="form-group">
                    <label>Floor</label>
                    <input type="text" name="floor" class="form-control"
                        value="{{ old('floor', $room->floor) }}">
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $room->description) }}</textarea>
            </div>
            <div class="form-group">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:500">
                    <input type="checkbox" name="is_active" value="1"
                        {{ old('is_active', $room->is_active) ? 'checked' : '' }}
                        style="width:16px;height:16px;cursor:pointer">
                    Active
                </label>
                <div class="form-help">Inactive rooms will not appear in location selection dropdowns.</div>
            </div>

            <div class="divider"></div>

            <div style="display:flex;justify-content:flex-end;gap:8px">
                <a href="{{ route('locations.rooms.show', $room) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

@endsection