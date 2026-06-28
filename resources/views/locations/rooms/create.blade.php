@extends('layouts.app')
@section('title', 'Add Room')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            <a href="{{ route('locations.rooms.index') }}">Locations</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            Add Room
        </div>
        <h1>Add Room</h1>
    </div>
    <a href="{{ route('locations.rooms.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="card" style="max-width:640px">
    <div class="card-header">
        <span><i class="fas fa-door-open" style="color:var(--text-muted)"></i> &nbsp;Room Details</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('locations.rooms.store') }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group">
                    <label>Room Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}" required
                        placeholder="e.g. Archive Room A">
                    @error('name')<div class="form-help" style="color:var(--danger)">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Code <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="code"
                        class="form-control @error('code') is-invalid @enderror"
                        value="{{ old('code') }}" required
                        placeholder="e.g. AR-001">
                    @error('code')<div class="form-help" style="color:var(--danger)">{{ $message }}</div>@enderror
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group">
                    <label>Building</label>
                    <input type="text" name="building" class="form-control"
                        value="{{ old('building') }}" placeholder="e.g. Main Building">
                </div>
                <div class="form-group">
                    <label>Floor</label>
                    <input type="text" name="floor" class="form-control"
                        value="{{ old('floor') }}" placeholder="e.g. Ground Floor">
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3"
                    placeholder="Optional notes about this room…">{{ old('description') }}</textarea>
            </div>

            <div class="divider"></div>

            <div style="display:flex;justify-content:flex-end;gap:8px">
                <a href="{{ route('locations.rooms.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Room
                </button>
            </div>
        </form>
    </div>
</div>

@endsection