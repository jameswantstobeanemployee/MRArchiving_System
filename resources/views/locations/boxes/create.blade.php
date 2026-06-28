@extends('layouts.app')
@section('title', 'Add Box')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            <a href="{{ route('locations.rooms.index') }}">Locations</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            <a href="{{ route('locations.rooms.show', $shelf->room_id) }}">{{ $shelf->room->name }}</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            Add Box
        </div>
        <h1>Add Box to {{ $shelf->name }}</h1>
    </div>
    <a href="{{ route('locations.rooms.show', $shelf->room_id) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="card" style="max-width:640px">
    <div class="card-header">
        <span><i class="fas fa-box" style="color:var(--text-muted)"></i> &nbsp;Box Details</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('locations.boxes.store', $shelf) }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group">
                    <label>Box Number <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="box_number"
                        class="form-control @error('box_number') is-invalid @enderror"
                        value="{{ old('box_number') }}" required
                        placeholder="e.g. 001">
                    @error('box_number')<div class="form-help" style="color:var(--danger)">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Box Code <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="box_code"
                        class="form-control @error('box_code') is-invalid @enderror"
                        value="{{ old('box_code') }}" required
                        placeholder="e.g. AR-001-S1-B1">
                    @error('box_code')<div class="form-help" style="color:var(--danger)">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-group">
                <label>Capacity <span style="color:var(--danger)">*</span></label>
                <input type="number" name="capacity"
                    class="form-control @error('capacity') is-invalid @enderror"
                    value="{{ old('capacity', $defaultCapacity) }}" required min="1" max="1000">
                <div class="form-help">Default: {{ $defaultCapacity }} charts per box.</div>
                @error('capacity')<div class="form-help" style="color:var(--danger)">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3"
                    placeholder="Optional notes about this box…">{{ old('description') }}</textarea>
            </div>

            <div class="divider"></div>

            <div style="display:flex;justify-content:flex-end;gap:8px">
                <a href="{{ route('locations.rooms.show', $shelf->room_id) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Box
                </button>
            </div>
        </form>
    </div>
</div>

@endsection