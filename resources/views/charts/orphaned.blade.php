@extends('layouts.app')
@section('title', 'Orphaned Charts')

@push('styles')
<style>
    .bulk-bar {
        position: sticky;
        top: 0;
        z-index: 100;
        background: var(--card-bg, #fff);
        border: 2px solid var(--accent, #3b82f6);
        border-radius: 10px;
        padding: 16px 20px;
        margin-bottom: 20px;
        display: none;
        align-items: flex-end;
        gap: 16px;
        flex-wrap: wrap;
        box-shadow: 0 4px 20px rgba(59,130,246,0.15);
    }
    .bulk-bar.visible { display: flex; }

    .bulk-bar-left {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }
    .bulk-badge {
        background: var(--accent, #3b82f6);
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        border-radius: 20px;
        padding: 3px 12px;
        min-width: 28px;
        text-align: center;
    }
    .bulk-bar-label {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .bulk-location {
        display: flex;
        align-items: flex-end;
        gap: 10px;
        flex: 1;
        flex-wrap: wrap;
    }
    .bulk-location .form-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
        flex: 1;
        min-width: 150px;
    }
    .bulk-location label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: var(--text-muted);
    }
    .bulk-location .form-control { height: 38px; }

    .assign-btn {
        height: 38px;
        padding: 0 20px;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .deselect-btn {
        height: 38px;
        padding: 0 14px;
        flex-shrink: 0;
    }

    /* Table enhancements */
    .cb-col { width: 42px; text-align: center; }
    .data-table tbody tr.selected-row {
        background: color-mix(in srgb, var(--accent, #3b82f6) 8%, transparent);
    }
    .data-table tbody tr { transition: background .15s; }
    .data-table thead th {
        border-bottom: 2px solid var(--divider, rgba(255,255,255,0.1));
        padding-bottom: 10px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: var(--text-muted);
        white-space: nowrap;
    }
    .data-table thead {
    display: table-header-group !important;
}
.data-table thead tr {
    display: table-row !important;
}
.data-table thead th {
    display: table-cell !important;
    border-bottom: 2px solid var(--divider, rgba(255,255,255,0.1));
    padding-bottom: 10px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: var(--text-muted);
    white-space: nowrap;
    vertical-align: middle;
}

    .select-all-wrap {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 600;
    }

    /* Alert banner */
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
    .alert-warning-content h3 { margin: 0 0 4px 0; font-size: 15px; font-weight: 700; color: #856404; }
    .alert-warning-content p  { margin: 0; font-size: 13.5px; color: #856404; line-height: 1.5; }

    .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .pagination-wrap { padding: 14px 20px; border-top: 1px solid var(--divider); display: flex; justify-content: flex-end; }

    .spinner { display: none; }
    .spinner.active { display: inline-block; animation: spin .7s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* Disabled state while submitting */
    .bulk-bar.submitting .assign-btn { opacity: .6; pointer-events: none; }
</style>
@endpush

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            @if(request('from') === 'locations')
                <a href="{{ route('locations.rooms.index') }}">Locations</a>
            @else
                <a href="{{ route('charts.index') }}">Chart Archive</a>
            @endif
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            Orphaned Charts
        </div>
        <h1>Orphaned Charts</h1>
    </div>
    @if(request('from') === 'locations')
        <a href="{{ route('locations.rooms.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Locations
        </a>
    @else
        <a href="{{ route('charts.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Archive
        </a>
    @endif
</div>

{{-- Info banner --}}
<div class="alert-warning-custom">
    <i class="fas fa-exclamation-triangle alert-icon"></i>
    <div class="alert-warning-content">
        <h3>These charts have no physical location</h3>
        <p>Select one or more charts below, choose a new location, then click <strong>Assign Selected</strong>. You can assign multiple charts to the same box at once.</p>
    </div>
</div>

{{-- Session flash --}}
@if(session('success'))
    <div class="alert alert-success" style="margin-bottom:16px">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger" style="margin-bottom:16px">
        <i class="fas fa-times-circle"></i> {{ session('error') }}
    </div>
@endif

{{-- ── Sticky Bulk Assignment Bar ── --}}
<div class="bulk-bar" id="bulkBar">
    <div class="bulk-bar-left">
        <span class="bulk-badge" id="selectedCount">0</span>
        <span class="bulk-bar-label">charts selected</span>
    </div>

    <div class="bulk-location">
        {{-- Room --}}
        <div class="form-group">
            <label for="bulk_room">Room</label>
            <select id="bulk_room" class="form-control" onchange="loadShelves(this.value)">
                <option value="">— Select Room —</option>
                @foreach($rooms as $room)
                    <option value="{{ $room->id }}">{{ $room->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Shelf --}}
        <div class="form-group">
            <label for="bulk_shelf">Shelf</label>
            <select id="bulk_shelf" class="form-control" disabled onchange="loadBoxes(this.value)">
                <option value="">— Select Shelf —</option>
            </select>
        </div>

        {{-- Box --}}
        <div class="form-group">
            <label for="bulk_box">Box</label>
            <select id="bulk_box" class="form-control" disabled>
                <option value="">— Select Box —</option>
            </select>
        </div>
    </div>

    <button class="btn btn-primary assign-btn" id="assignBtn" onclick="submitBulkAssign()">
        <i class="fas fa-map-marker-alt"></i>
        <i class="fas fa-sync-alt spinner" id="assignSpinner"></i>
        Assign Selected
    </button>

    <button class="btn btn-secondary deselect-btn" onclick="clearSelection()">
        <i class="fas fa-times"></i> Deselect All
    </button>
</div>

{{-- Hidden form for bulk POST --}}
<form id="bulkForm" method="POST" action="{{ route('charts.orphaned.assign') }}">
    @csrf
    <input type="hidden" name="box_id" id="formBoxId">
    <div id="chartIdsContainer"></div>
</form>

{{-- Charts table --}}
<div class="card">
    <div class="card-header">
        <span>
            <i class="fas fa-folder-open" style="color:var(--text-muted)"></i>
            &nbsp;Orphaned Charts
            <span style="font-size:13px;color:var(--text-muted);font-weight:500">
                &nbsp;— <strong style="color:var(--text-primary)">{{ $charts->total() }}</strong> charts need a location
            </span>
        </span>
    </div>

    @if($charts->isEmpty())
        <div class="empty-state">
            <i class="fas fa-check-circle empty-state-icon" style="color:var(--success,#22c55e)"></i>
            <h3>All charts have locations!</h3>
            <p>There are no orphaned charts at the moment.</p>
        </div>
    @else
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="cb-col" style="vertical-align:middle">
                            <input type="checkbox" id="selectAll" onchange="toggleAll(this)" title="Select all on this page">
                        </th>
                        <th>Case #</th>
                        <th>Patient</th>
                        <th>MRN</th>
                        <th>Admission</th>
                        <th>Discharge</th>
                        <th>Archived</th>
                        <th>Status</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($charts as $chart)
                    <tr id="row-{{ $chart->id }}">
                        <td class="cb-col">
                            <input
                                type="checkbox"
                                class="chart-checkbox"
                                value="{{ $chart->id }}"
                                onchange="onCheckboxChange()"
                            >
                        </td>
                        <td style="font-family:'DM Mono',monospace;font-size:13px;font-weight:600">
                            {{ $chart->case_number }}
                        </td>
                        <td style="font-weight:600">
                            {{ $chart->patient->full_name ?? '—' }}
                        </td>
                        <td style="font-family:'DM Mono',monospace;font-size:12.5px;color:var(--text-muted)">
                            {{ $chart->patient->medical_record_number ?? '—' }}
                        </td>
                        <td>{{ $chart->admission_date?->format('M d, Y') ?? '—' }}</td>
                        <td>{{ $chart->discharge_date?->format('M d, Y') ?? '—' }}</td>
                        <td>{{ $chart->archived_date?->format('M d, Y') ?? '—' }}</td>
                        <td>
                            <span class="badge badge-warning">
                                <i class="fas fa-unlink" style="font-size:9px"></i>
                                No Location
                            </span>
                        </td>
                        <td>
                            <div class="row-actions" style="visibility:visible">
                                <a href="{{ route('charts.show', $chart) }}" class="action-btn">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('charts.move', $chart) }}" class="action-btn">
                                    <i class="fas fa-map-marker-alt"></i> Assign
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($charts->hasPages())
        <div class="pagination-wrap">
            {{ $charts->withQueryString()->links() }}
        </div>
        @endif
    @endif
</div>

@endsection

@push('scripts')
<script>
// ── Selection state ──────────────────────────────────────────
function getChecked() {
    return [...document.querySelectorAll('.chart-checkbox:checked')];
}

function onCheckboxChange() {
    const checked = getChecked();
    const count   = checked.length;
    const bar     = document.getElementById('bulkBar');
    const allCb   = document.getElementById('selectAll');

    document.getElementById('selectedCount').textContent = count;
    bar.classList.toggle('visible', count > 0);

    // Highlight rows
    document.querySelectorAll('.chart-checkbox').forEach(cb => {
        document.getElementById('row-' + cb.value)?.classList.toggle('selected-row', cb.checked);
    });

    // Sync select-all state
    const all = document.querySelectorAll('.chart-checkbox');
    allCb.indeterminate = count > 0 && count < all.length;
    allCb.checked = count === all.length && all.length > 0;
}

function toggleAll(source) {
    document.querySelectorAll('.chart-checkbox').forEach(cb => { cb.checked = source.checked; });
    onCheckboxChange();
}

function clearSelection() {
    document.querySelectorAll('.chart-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    onCheckboxChange();
}

// ── Cascade dropdowns ────────────────────────────────────────
async function loadShelves(roomId) {
    const shelfSel = document.getElementById('bulk_shelf');
    const boxSel   = document.getElementById('bulk_box');

    shelfSel.innerHTML = '<option value="">Loading…</option>';
    shelfSel.disabled  = true;
    boxSel.innerHTML   = '<option value="">— Select Box —</option>';
    boxSel.disabled    = true;

    if (!roomId) {
        shelfSel.innerHTML = '<option value="">— Select Shelf —</option>';
        return;
    }

    const res     = await fetch(`/locations/api/rooms/${roomId}/shelves`);
    const shelves = await res.json();

    shelfSel.innerHTML = '<option value="">— Select Shelf —</option>' +
        shelves.map(s => `<option value="${s.id}">${s.name} (${s.code})</option>`).join('');
    shelfSel.disabled = false;
}

async function loadBoxes(shelfId) {
    const boxSel = document.getElementById('bulk_box');

    boxSel.innerHTML = '<option value="">Loading…</option>';
    boxSel.disabled  = true;

    if (!shelfId) {
        boxSel.innerHTML = '<option value="">— Select Box —</option>';
        return;
    }

    const res   = await fetch(`/locations/api/shelves/${shelfId}/boxes`);
    const boxes = await res.json();

    boxSel.innerHTML = '<option value="">— Select Box —</option>' +
        boxes.map(b => {
            const full    = b.current_count >= b.capacity;
            const label   = `Box ${b.box_number} (${b.box_code}) — ${b.current_count}/${b.capacity}`;
            const warning = full ? ' ⚠ Full' : '';
            return `<option value="${b.id}" ${full ? 'disabled' : ''}>${label}${warning}</option>`;
        }).join('');
    boxSel.disabled = false;
}

// ── Submit ───────────────────────────────────────────────────
function submitBulkAssign() {
    const checked = getChecked();
    const boxId   = document.getElementById('bulk_box').value;

    if (checked.length === 0) {
        alert('Please select at least one chart.');
        return;
    }
    if (!boxId) {
        alert('Please select a destination box.');
        return;
    }

    // Confirm
    const boxText = document.getElementById('bulk_box').selectedOptions[0].text;
    if (!confirm(`Assign ${checked.length} chart(s) to:\n${boxText}\n\nContinue?`)) return;

    // Build form
    document.getElementById('formBoxId').value = boxId;
    const container = document.getElementById('chartIdsContainer');
    container.innerHTML = '';
    checked.forEach(cb => {
        const input = document.createElement('input');
        input.type  = 'hidden';
        input.name  = 'chart_ids[]';
        input.value = cb.value;
        container.appendChild(input);
    });

    // Show spinner
    document.getElementById('bulkBar').classList.add('submitting');
    document.getElementById('assignSpinner').classList.add('active');

    document.getElementById('bulkForm').submit();
}
</script>
@endpush