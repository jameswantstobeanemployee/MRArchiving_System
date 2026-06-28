@extends('layouts.app')
@section('title', 'Room: ' . $room->name)

@push('styles')
<style>
    .room-meta-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }
    .meta-item {}
    .meta-label {
        font-size: 11.5px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        margin-bottom: 4px;
    }
    .meta-value {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
    }
    .shelf-section { margin-bottom: 20px; }
    .shelf-header-title {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
        min-width: 0;
    }
    .shelf-code {
        font-family: 'DM Mono', monospace;
        font-size: 12px;
        background: var(--divider);
        border: 1px solid var(--border-color);
        padding: 2px 8px;
        border-radius: 4px;
        color: var(--text-secondary);
    }
    .shelf-section-tag {
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 500;
    }
    .table-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table-scroll .data-table thead { position: static; }
    .box-code {
        font-family: 'DM Mono', monospace;
        font-size: 12px;
        color: var(--text-secondary);
    }
    .progress-cell { min-width: 130px; }
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
    @media (max-width: 768px) {
        .room-meta-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 480px) {
        .room-meta-grid { grid-template-columns: 1fr; }
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
            {{ $room->name }}
        </div>
        <h1>{{ $room->name }} <span style="font-weight:400;color:var(--text-muted);font-size:18px">({{ $room->code }})</span></h1>
    </div>
    <div class="d-flex gap-1">
        <a href="{{ route('locations.shelves.create', $room) }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Shelf
        </a>
        <a href="{{ route('locations.rooms.edit', $room) }}" class="btn btn-secondary">
            <i class="fas fa-pen"></i> Edit Room
        </a>
        <button onclick="showDeleteModal()" class="btn btn-danger">
            <i class="fas fa-trash"></i> Delete Room
        </button>
        <a href="{{ route('locations.rooms.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

{{-- Room Details Card --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-header">
        <span><i class="fas fa-info-circle" style="color:var(--text-muted)"></i> &nbsp;Room Details</span>
    </div>
    <div class="card-body">
        <div class="room-meta-grid">
            <div class="meta-item">
                <div class="meta-label">Building</div>
                <div class="meta-value">{{ $room->building ?? '—' }}</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Floor</div>
                <div class="meta-value">{{ $room->floor ?? '—' }}</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Status</div>
                <div class="meta-value">
                    @if($room->is_active)
                        <span class="badge badge-success"><i class="fas fa-circle" style="font-size:7px"></i> Active</span>
                    @else
                        <span class="badge badge-danger"><i class="fas fa-circle" style="font-size:7px"></i> Inactive</span>
                    @endif
                </div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Description</div>
                <div class="meta-value" style="font-weight:400;color:var(--text-secondary);font-size:13px">
                    {{ $room->description ?? '—' }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Shelves --}}
@forelse($room->shelves as $shelf)
<div class="card shelf-section">
    <div class="card-header">
        <div class="shelf-header-title">
            <i class="fas fa-layer-group" style="color:var(--text-muted)"></i>
            <span style="font-weight:700">{{ $shelf->name }}</span>
            <span class="shelf-code">{{ $shelf->code }}</span>
            @if($shelf->section)
                <span class="shelf-section-tag">— {{ $shelf->section }}</span>
            @endif
        </div>
        <div class="d-flex gap-1">
            <a href="{{ route('locations.boxes.create', $shelf) }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Add Box
            </a>
            <a href="{{ route('locations.shelves.edit', [$room, $shelf]) }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-pen"></i> Edit
            </a>
        </div>
    </div>

    @if($shelf->folderBoxes->isEmpty())
        <div class="empty-state" style="padding:32px 24px">
            <i class="fas fa-box-open empty-state-icon" style="font-size:32px"></i>
            <h3>No boxes on this shelf</h3>
            <p><a href="{{ route('locations.boxes.create', $shelf) }}" style="color:var(--accent)">Add a box</a> to get started.</p>
        </div>
    @else
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Box #</th>
                        <th>Code</th>
                        <th>Capacity</th>
                        <th>Charts</th>
                        <th>Fill Level</th>
                        <th>Status</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shelf->folderBoxes as $box)
                    @php
                        $cnt = $box->activeCharts()->count();
                        $pct = $box->capacity > 0 ? round(($cnt / $box->capacity) * 100, 0) : 0;
                        $barClass = $pct >= 95 ? 'danger' : ($pct >= 80 ? 'warning' : 'success');
                    @endphp
                    <tr>
                        <td style="font-weight:700">{{ $box->box_number }}</td>
                        <td><span class="box-code">{{ $box->box_code }}</span></td>
                        <td>{{ $box->capacity }}</td>
                        <td>
                            <span class="badge badge-info">{{ $cnt }}</span>
                        </td>
                        <td class="progress-cell">
                            <div style="display:flex;align-items:center;gap:8px">
                                <div class="progress" style="flex:1">
                                    <div class="progress-bar {{ $barClass }}" style="width:{{ $pct }}%"></div>
                                </div>
                                <span style="font-size:12px;color:var(--text-muted);min-width:32px">{{ $pct }}%</span>
                            </div>
                        </td>
                        <td>
                            @if($box->is_active)
                                <span class="badge badge-success"><i class="fas fa-circle" style="font-size:7px"></i> Active</span>
                            @else
                                <span class="badge badge-danger"><i class="fas fa-circle" style="font-size:7px"></i> Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="row-actions" style="visibility:visible">
                                <a href="{{ route('locations.boxes.show', $box->id) }}" class="action-btn">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('locations.boxes.edit', $box->id) }}" class="action-btn">
                                    <i class="fas fa-pen"></i> Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@empty
<div class="card">
    <div class="empty-state" style="padding:48px 24px">
        <i class="fas fa-layer-group empty-state-icon"></i>
        <h3>No shelves in this room yet</h3>
        <p><a href="{{ route('locations.shelves.create', $room) }}" style="color:var(--accent)">Add a shelf</a> to get started.</p>
    </div>
</div>
@endforelse

{{-- Delete Confirmation Modal --}}
<div id="deleteModal" class="delete-modal-backdrop" onclick="if(event.target === this) hideDeleteModal()">
    <div class="delete-modal">
        <h3><i class="fas fa-exclamation-triangle"></i> Delete Room?</h3>
        <p>Are you sure you want to delete <strong>{{ $room->name }}</strong>?</p>
        @php
            $totalCharts = \App\Models\ArchivedChart::whereHas('physicalLocation.shelf', function($q) use ($room) {
                $q->where('room_id', $room->id);
            })->count();
        @endphp
        @if($totalCharts > 0)
        <p style="color:var(--danger);font-weight:600">
            ⚠️ This will orphan <strong>{{ $totalCharts }}</strong> chart(s) currently stored in this room. They will need to be reassigned to a new location.
        </p>
        @endif
        <p style="font-size:13px;color:var(--text-muted)">
            This action will delete all shelves and boxes in this room. Charts will NOT be deleted but will lose their physical location.
        </p>
        <div class="delete-modal-actions">
            <button onclick="hideDeleteModal()" class="btn btn-secondary">Cancel</button>
            <form method="POST" action="{{ route('locations.rooms.destroy', $room) }}" style="display:inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Yes, Delete Room
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