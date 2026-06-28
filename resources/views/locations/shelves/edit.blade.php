@extends('layouts.app')
@section('title', 'Edit Shelf')

@push('styles')
<style>
    .delete-modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
    .delete-modal-backdrop.active { display: flex; }
    .delete-modal {
        background: var(--card-bg);
        border-radius: 8px;
        padding: 24px;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    }
    .delete-modal h3 {
        margin: 0 0 12px 0;
        color: var(--danger);
        font-size: 18px;
    }
    .delete-modal p {
        margin: 0 0 16px 0;
        line-height: 1.6;
    }
    .delete-modal-actions {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }
</style>
@endpush

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
            Edit Shelf
        </div>
        <h1>Edit Shelf: {{ $shelf->name }}</h1>
    </div>
    <div class="d-flex gap-1">
        <button onclick="showDeleteModal()" class="btn btn-danger">
            <i class="fas fa-trash"></i> Delete Shelf
        </button>
        <a href="{{ route('locations.rooms.show', $shelf->room_id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="card" style="max-width:640px">
    <div class="card-header">
        <span><i class="fas fa-layer-group" style="color:var(--text-muted)"></i> &nbsp;Shelf Details</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('locations.shelves.update', [$shelf->room, $shelf]) }}">
            @csrf @method('PUT')
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group">
                    <label>Shelf Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $shelf->name) }}" required>
                    @error('name')<div class="form-help" style="color:var(--danger)">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Code <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="code"
                        class="form-control @error('code') is-invalid @enderror"
                        value="{{ old('code', $shelf->code) }}" required>
                    @error('code')<div class="form-help" style="color:var(--danger)">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-group">
                <label>Section</label>
                <input type="text" name="section" class="form-control"
                    value="{{ old('section', $shelf->section) }}">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $shelf->description) }}</textarea>
            </div>
            <div class="form-group">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:500">
                    <input type="checkbox" name="is_active" value="1"
                        {{ old('is_active', $shelf->is_active) ? 'checked' : '' }}
                        style="width:16px;height:16px;cursor:pointer">
                    Active
                </label>
                <div class="form-help">Inactive shelves will not appear when assigning box locations.</div>
            </div>

            <div class="divider"></div>

            <div style="display:flex;justify-content:flex-end;gap:8px">
                <a href="{{ route('locations.rooms.show', $shelf->room_id) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div id="deleteModal" class="delete-modal-backdrop" onclick="if(event.target === this) hideDeleteModal()">
    <div class="delete-modal">
        <h3><i class="fas fa-exclamation-triangle"></i> Delete Shelf?</h3>
        <p>Are you sure you want to delete <strong>{{ $shelf->name }}</strong>?</p>
        @php
            $totalCharts = \App\Models\ArchivedChart::whereHas('physicalLocation', function($q) use ($shelf) {
                $q->where('shelf_id', $shelf->id);
            })->count();
        @endphp
        @if($totalCharts > 0)
        <p style="color:var(--danger);font-weight:600">
            ⚠️ This will orphan <strong>{{ $totalCharts }}</strong> chart(s) currently stored on this shelf. They will need to be reassigned to a new location.
        </p>
        @endif
        <p style="font-size:13px;color:var(--text-muted)">
            This action will delete all boxes on this shelf. Charts will NOT be deleted but will lose their physical location.
        </p>
        <div class="delete-modal-actions">
            <button onclick="hideDeleteModal()" class="btn btn-secondary">Cancel</button>
            <form method="POST" action="{{ route('locations.shelves.destroy', [$shelf->room, $shelf]) }}" style="display:inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Yes, Delete Shelf
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showDeleteModal() {
    document.getElementById('deleteModal').classList.add('active');
}
function hideDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
}
</script>
@endpush

@endsection