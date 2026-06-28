@extends('layouts.app')
@section('title', 'Locations')

@push('styles')
<style>
    .alert-warning-custom {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        border: 2px solid #ffb03b;
        border-radius: 8px;
        padding: 16px 20px;
        margin-bottom: 20px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .alert-warning-custom i.alert-icon {
        font-size: 24px;
        color: #d68910;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .alert-warning-content h3 {
        margin: 0 0 4px 0;
        font-size: 15px;
        font-weight: 700;
        color: #856404;
    }
    .alert-warning-content p {
        margin: 0;
        font-size: 13.5px;
        color: #856404;
        line-height: 1.5;
    }
    .alert-warning-content a {
        color: #856404;
        font-weight: 700;
        text-decoration: underline;
    }
    .alert-warning-content a:hover {
        color: #533f03;
    }
    .filter-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: flex-end;
    }
    .filter-bar .form-control {
        flex: 1;
        min-width: 160px;
    }
    .filter-actions {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }
    .card-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
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
    .room-code {
        font-family: 'DM Mono', monospace;
        font-size: 12.5px;
        background: var(--divider);
        border: 1px solid var(--border-color);
        padding: 2px 8px;
        border-radius: 4px;
        color: var(--text-secondary);
        font-weight: 500;
    }
    .pagination-wrap {
        padding: 14px 20px;
        border-top: 1px solid var(--divider);
        display: flex;
        justify-content: flex-end;
    }
    @media (max-width: 768px) {
        .filter-bar .form-control { min-width: 100%; }
        .filter-actions { width: 100%; }
        .filter-actions .btn { flex: 1; justify-content: center; }
    }
</style>
@endpush

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            Locations
        </div>
        <h1>Storage Locations</h1>
    </div>
    <a href="{{ route('locations.rooms.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Room
    </a>
</div>

{{-- Orphaned Charts Banner --}}
@if($orphanedCount > 0)
<div class="alert-warning-custom">
    <i class="fas fa-exclamation-triangle alert-icon"></i>
    <div class="alert-warning-content">
        <h3>{{ $orphanedCount }} {{ Str::plural('chart', $orphanedCount) }} need a location assigned</h3>
        <p>Some charts have no physical location, likely due to a room, shelf, or box being deleted.
            <a href="{{ route('charts.orphaned') }}">Review and assign them →</a>
        </p>
    </div>
</div>
@else
<div class="alert-warning-custom" style="background:linear-gradient(135deg,#d1fae5 0%,#a7f3d0 100%);border-color:#34d399">
    <i class="fas fa-check-circle alert-icon" style="color:#059669"></i>
    <div class="alert-warning-content" style="color:#065f46">
        <h3 style="color:#065f46">All charts have a location assigned</h3>
        <p style="color:#065f46">No orphaned charts at the moment.
            <a href="{{ route('charts.orphaned', ['from' => 'locations']) }}" style="color:#065f46">View orphaned charts page →</a>
        </p>
    </div>
</div>
@endif

{{-- Filters --}}
<div class="card">
    <div class="card-header">
        <span><i class="fas fa-filter"></i> &nbsp;Filters</span>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('locations.rooms.index') }}">
            <div class="filter-bar">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search room name, code, building…"
                    value="{{ request('search') }}"
                >
                <select name="status" class="form-control" style="max-width:160px">
                    <option value="">All Statuses</option>
                    <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <select name="building" class="form-control" style="max-width:180px">
                    <option value="">All Buildings</option>
                    @foreach($rooms->pluck('building')->filter()->unique() as $building)
                        <option value="{{ $building }}" {{ request('building') === $building ? 'selected' : '' }}>
                            {{ $building }}
                        </option>
                    @endforeach
                </select>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="{{ route('locations.rooms.index') }}" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Results --}}
<div class="card">
    <div class="card-header">
        <div class="card-header-row">
            <span>
                <i class="fas fa-map-marker-alt" style="color:var(--text-muted)"></i>
                &nbsp;Rooms
                <span class="results-count">&nbsp;— <strong>{{ $rooms->total() }}</strong> rooms found</span>
            </span>
        </div>
    </div>

    @if($rooms->isEmpty())
        <div class="empty-state">
            <i class="fas fa-door-open empty-state-icon"></i>
            <h3>No rooms configured</h3>
            <p>Get started by <a href="{{ route('locations.rooms.create') }}" style="color:var(--accent)">adding a room</a>.</p>
        </div>
    @else
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Building</th>
                        <th>Floor</th>
                        <th>Shelves</th>
                        <th>Status</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rooms as $room)
                    <tr>
                        <td><span class="room-code">{{ $room->code }}</span></td>
                        <td style="font-weight:600">{{ $room->name }}</td>
                        <td>{{ $room->building ?? '—' }}</td>
                        <td>{{ $room->floor ?? '—' }}</td>
                        <td>
                            <span class="badge badge-info">
                                <i class="fas fa-layer-group"></i>
                                {{ $room->shelves_count }}
                            </span>
                        </td>
                        <td>
                            @if($room->is_active)
                                <span class="badge badge-success"><i class="fas fa-circle" style="font-size:7px"></i> Active</span>
                            @else
                                <span class="badge badge-danger"><i class="fas fa-circle" style="font-size:7px"></i> Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="row-actions" style="visibility:visible">
                                <a href="{{ route('locations.rooms.show', $room) }}" class="action-btn">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('locations.rooms.edit', $room) }}" class="action-btn">
                                    <i class="fas fa-pen"></i> Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($rooms->hasPages())
        <div class="pagination-wrap">
            {{ $rooms->withQueryString()->links() }}
        </div>
        @endif
    @endif
</div>

@endsection