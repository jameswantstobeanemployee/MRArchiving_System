@extends('layouts.app')
@section('title', 'Add Shelf')

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
            Add Shelf
        </div>
        <h1>Add Shelf to {{ $room->name }}</h1>
    </div>
    <a href="{{ route('locations.rooms.show', $room) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="card" style="max-width:640px">
    <div class="card-header">
        <span><i class="fas fa-layer-group" style="color:var(--text-muted)"></i> &nbsp;Shelf Details</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('locations.shelves.store', $room) }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group">
                    <label>Shelf Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}" required
                        placeholder="e.g. Shelf 1">
                    @error('name')<div class="form-help" style="color:var(--danger)">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Code <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="code"
                        class="form-control @error('code') is-invalid @enderror"
                        value="{{ old('code') }}" required
                        placeholder="e.g. AR-001-S1">
                    @error('code')<div class="form-help" style="color:var(--danger)">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-group">
                <label>Section</label>
                <input type="text" name="section" class="form-control"
                    value="{{ old('section') }}" placeholder="e.g. Section A">
                <div class="form-help">Optional grouping label for this shelf within the room.</div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3"
                    placeholder="Optional notes about this shelf…">{{ old('description') }}</textarea>
            </div>

            <div class="divider"></div>

            <div style="display:flex;justify-content:flex-end;gap:8px">
                <a href="{{ route('locations.rooms.show', $room) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Shelf
                </button>
            </div>
        </form>
    </div>
</div>

@endsection