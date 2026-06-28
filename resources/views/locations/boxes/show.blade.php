@extends('layouts.app')
@section('title', 'Box: ' . $box->box_code)

@push('styles')
<style>
    .box-meta-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }
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
    .fill-stat {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 6px;
    }
    .fill-stat .progress { flex: 1; }
    .fill-pct {
        font-size: 13px;
        font-weight: 700;
        min-width: 36px;
        text-align: right;
    }
    .table-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table-scroll .data-table thead { position: static; }
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
        .box-meta-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>
@endpush

@section('content')

@php
    $cnt = $box->activeCharts->count();
    $pct = $box->capacity > 0 ? round(($cnt / $box->capacity) * 100, 0) : 0;
    $barClass = $pct >= 95 ? 'danger' : ($pct >= 80 ? 'warning' : 'success');
@endphp

<div class="page-header">
    <div class="page-header-left">
        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            <a href="{{ route('locations.rooms.index') }}">Locations</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            <a href="{{ route('locations.rooms.show', $box->shelf->room_id) }}">{{ $box->shelf->room->name }}</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            {{ $box->box_code }}
        </div>
        <h1>Box: {{ $box->box_code }}</h1>
    </div>
    <div class="d-flex gap-1">
        <a href="{{ route('locations.boxes.edit', $box->id) }}" class="btn btn-secondary">
            <i class="fas fa-pen"></i> Edit Box
        </a>
        <button onclick="showDeleteModal()" class="btn btn-danger">
            <i class="fas fa-trash"></i> Delete Box
        </button>
        <a href="{{ route('locations.rooms.show', $box->shelf->room_id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

{{-- Box Details --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-header">
        <span><i class="fas fa-box" style="color:var(--text-muted)"></i> &nbsp;Box Details</span>
    </div>
    <div class="card-body">
        <div class="box-meta-grid">
            <div>
                <div class="meta-label">Box Number</div>
                <div class="meta-value">{{ $box->box_number }}</div>
            </div>
            <div>
                <div class="meta-label">Box Code</div>
                <div class="meta-value">
                    <code style="font-size:13px">{{ $box->box_code }}</code>
                </div>
            </div>
            <div>
                <div class="meta-label">Location</div>
                <div class="meta-value" style="font-size:13px">
                    {{ $box->shelf->room->name }}<br>
                    <span style="font-weight:400;color:var(--text-muted)">{{ $box->shelf->name }}</span>
                </div>
            </div>
            <div>
                <div class="meta-label">Capacity Used</div>
                <div class="meta-value">{{ $cnt }} / {{ $box->capacity }}</div>
                <div class="fill-stat">
                    <div class="progress">
                        <div class="progress-bar {{ $barClass }}" style="width:{{ $pct }}%"></div>
                    </div>
                    <span class="fill-pct" style="color:var(--{{ $barClass }})">{{ $pct }}%</span>
                </div>
            </div>
            <div>
                <div class="meta-label">Status</div>
                <div class="meta-value" style="margin-top:4px">
                    @if($box->is_active)
                        <span class="badge badge-success"><i class="fas fa-circle" style="font-size:7px"></i> Active</span>
                    @else
                        <span class="badge badge-danger"><i class="fas fa-circle" style="font-size:7px"></i> Inactive</span>
                    @endif
                </div>
            </div>
            @if($box->description)
            <div>
                <div class="meta-label">Description</div>
                <div class="meta-value" style="font-weight:400;color:var(--text-secondary);font-size:13px">
                    {{ $box->description }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Charts in this box --}}
<div class="card">
    <div class="card-header">
        <div class="card-header-row">
            <span>
                <i class="fas fa-folder-medical" style="color:var(--text-muted)"></i>
                &nbsp;Charts in This Box
                <span class="results-count">&nbsp;— <strong>{{ $cnt }}</strong> {{ Str::plural('chart', $cnt) }}</span>
            </span>
        </div>
    </div>

    @if($box->activeCharts->isEmpty())
        <div class="empty-state">
            <i class="fas fa-folder-open empty-state-icon"></i>
            <h3>No charts in this box</h3>
            <p>Charts assigned to this box will appear here.</p>
        </div>
    @else
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>MR #</th>
                        <th>Case #</th>
                        <th>Admission</th>
                        <th>Discharge</th>
                        <th>Status</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($box->activeCharts as $chart)
                    @php
                        $statusClass = match($chart->status) {
                            'archived'    => 'badge-info',
                            'checked_out' => 'badge-warning',
                            'destroyed'   => 'badge-danger',
                            default       => 'badge-info',
                        };
                        $statusIcon = match($chart->status) {
                            'archived'    => 'fa-box-archive',
                            'checked_out' => 'fa-exchange-alt',
                            'destroyed'   => 'fa-trash',
                            default       => 'fa-circle',
                        };
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('charts.show', $chart) }}" class="patient-link">
                                {{ $chart->patient->full_name }}
                            </a>
                        </td>
                        <td class="mono-cell">{{ $chart->patient->medical_record_number }}</td>
                        <td class="mono-cell">{{ $chart->case_number }}</td>
                        <td>{{ $chart->admission_date->format('m/d/Y') }}</td>
                        <td>{{ $chart->discharge_date?->format('m/d/Y') ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $statusClass }}">
                                <i class="fas {{ $statusIcon }}"></i>
                                {{ str_replace('_', ' ', $chart->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="row-actions" style="visibility:visible">
                                <a href="{{ route('charts.show', $chart) }}" class="action-btn">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('charts.move', $chart) }}" class="action-btn" style="color:var(--info-text)">
                                    <i class="fas fa-arrows-alt"></i> Move
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

{{-- Delete Confirmation Modal --}}
<div id="deleteModal" class="delete-modal-backdrop" onclick="if(event.target === this) hideDeleteModal()">
    <div class="delete-modal">
        <h3><i class="fas fa-exclamation-triangle"></i> Delete Box?</h3>
        <p>Are you sure you want to delete box <strong>{{ $box->box_code }}</strong>?</p>
        @if($cnt > 0)
        <p style="color:var(--danger);font-weight:600">
            ⚠️ This will orphan <strong>{{ $cnt }}</strong> chart(s) currently stored in this box. They will need to be reassigned to a new location.
        </p>
        @endif
        <p style="font-size:13px;color:var(--text-muted)">
            Charts will NOT be deleted but will lose their physical location and appear in the "Orphaned Charts" section.
        </p>
        <div class="delete-modal-actions">
            <button onclick="hideDeleteModal()" class="btn btn-secondary">Cancel</button>
            <form method="POST" action="{{ route('locations.boxes.destroy', $box) }}" style="display:inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Yes, Delete Box
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