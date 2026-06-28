@extends('layouts.app')
@section('title', 'Move Chart — ' . $chart->case_number)

@push('styles')
<style>
    /* ── Move Chart extras ───────────────────────────────────────── */

    .move-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    /* Detail mini-table reused from show page */
    .detail-table {
        width: 100%;
        border-collapse: collapse;
    }

    .detail-table tr + tr td {
        border-top: 1px solid var(--divider);
    }

    .detail-table td {
        padding: 9px 0;
        font-size: 13.5px;
        vertical-align: top;
    }

    .detail-table td:first-child {
        width: 42%;
        color: var(--text-muted);
        font-weight: 500;
        padding-right: 12px;
        white-space: nowrap;
    }

    .detail-table td:last-child {
        color: var(--text-primary);
        font-weight: 500;
    }

    /* Location selector steps */
    .location-steps {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 20px;
    }

    .location-step {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .location-step label {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .location-step label .step-num {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--accent);
        color: white;
        font-size: 10px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .location-step select:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }

    /* Box status preview */
    .box-preview {
        display: none;
        padding: 12px 16px;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
        background: var(--card-bg);
        font-size: 13.5px;
        margin-bottom: 4px;
        transition: all var(--transition);
        align-items: center;
        gap: 10px;
    }

    .box-preview.show { display: flex; }

    .box-preview-icon {
        font-size: 20px;
        flex-shrink: 0;
    }

    .box-preview-info { flex: 1; }

    .box-preview-name {
        font-weight: 700;
        color: var(--text-primary);
        font-size: 13.5px;
    }

    .box-preview-sub {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 2px;
    }

    .box-preview.ok     { background: var(--success-light); border-color: var(--success-border); color: var(--success-text); }
    .box-preview.warn   { background: var(--warning-light); border-color: var(--warning-border); color: var(--warning-text); }
    .box-preview.full   { background: var(--danger-light);  border-color: var(--danger-border);  color: var(--danger-text); }

    .box-preview.ok   .box-preview-name,
    .box-preview.ok   .box-preview-sub  { color: var(--success-text); }
    .box-preview.warn .box-preview-name,
    .box-preview.warn .box-preview-sub  { color: var(--warning-text); }
    .box-preview.full .box-preview-name,
    .box-preview.full .box-preview-sub  { color: var(--danger-text); }

    /* Fill bar */
    .fill-bar-wrap { margin-top: 6px; }

    /* Form actions */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding-top: 8px;
        border-top: 1px solid var(--divider);
        margin-top: 8px;
    }

    @media (max-width: 900px) {
        .move-grid       { grid-template-columns: 1fr; }
        .location-steps  { grid-template-columns: 1fr; }
    }

    @media (max-width: 768px) {
        .form-actions { flex-direction: column-reverse; }
        .form-actions .btn { width: 100%; justify-content: center; }
    }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="page-header-left">
        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            <a href="{{ route('charts.index') }}">Chart Archive</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            <a href="{{ route('charts.show', $chart) }}">{{ $chart->case_number }}</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            Move
        </div>
        <h1>Move Chart <code style="font-size:18px; font-weight:700">{{ $chart->case_number }}</code></h1>
    </div>
    <a href="{{ route('charts.show', $chart) }}" class="btn btn-ghost">
        <i class="fas fa-arrow-left"></i> Back to Chart
    </a>
</div>

{{-- Current location + patient overview --}}
<div class="move-grid">
    <div class="card" style="margin-bottom:0">
        <div class="card-header">
            <span><i class="fas fa-map-marker-alt"></i> &nbsp;Current Location</span>
        </div>
        <div class="card-body">
            @if($chart->physicalLocation)
                @php $box = $chart->physicalLocation; @endphp
                <table class="detail-table">
                    <tr>
                        <td>Location</td>
                        <td><span style="font-weight:700">{{ $box->location_label }}</span></td>
                    </tr>
                    <tr>
                        <td>Box</td>
                        <td><code>Box {{ $box->box_number }}</code></td>
                    </tr>
                    <tr>
                        <td>Capacity</td>
                        <td>
                            {{ $box->current_count }}/{{ $box->capacity }} charts
                            <div class="fill-bar-wrap" style="margin-top:6px">
                                <div class="progress">
                                    <div class="progress-bar {{ $box->fill_percentage >= 100 ? 'danger' : ($box->fill_percentage >= 80 ? 'warning' : 'success') }}"
                                         style="width:{{ $box->fill_percentage }}%"></div>
                                </div>
                            </div>
                            <div style="font-size:11.5px; color:var(--text-muted); margin-top:4px">{{ $box->fill_percentage }}% full</div>
                        </td>
                    </tr>
                </table>
            @else
                <div class="empty-state" style="padding:24px">
                    <i class="fas fa-map-marker-slash empty-state-icon" style="font-size:28px"></i>
                    <p>No current location assigned.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="card" style="margin-bottom:0">
        <div class="card-header">
            <span><i class="fas fa-user"></i> &nbsp;Patient</span>
            <a href="{{ route('patients.show', $chart->patient) }}" class="btn btn-xs btn-secondary">
                <i class="fas fa-external-link-alt"></i> View
            </a>
        </div>
        <div class="card-body">
            <table class="detail-table">
                <tr>
                    <td>Full Name</td>
                    <td>
                        <a href="{{ route('patients.show', $chart->patient) }}"
                           style="color:var(--accent); text-decoration:none; font-weight:700">
                            {{ $chart->patient->full_name }}
                        </a>
                    </td>
                </tr>
                <tr>
                    <td>MR Number</td>
                    <td><code>{{ $chart->patient->medical_record_number }}</code></td>
                </tr>
                <tr>
                    <td>Case Number</td>
                    <td><code>{{ $chart->case_number }}</code></td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>
                        <span class="badge badge-info">
                            <i class="fas fa-box-archive"></i> Archived
                        </span>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

{{-- Move Form --}}
<div class="card">
    <div class="card-header">
        <span><i class="fas fa-arrows-alt"></i> &nbsp;Select New Location</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('charts.move.store', $chart) }}">
            @csrf

            {{-- Location steppers --}}
            <div class="location-steps">
                {{-- Step 1: Room --}}
                <div class="location-step">
                    <label>
                        <span class="step-num">1</span>
                        Room <span style="color:var(--danger)">*</span>
                    </label>
                    <select id="room_select" class="form-control" required>
                        <option value="">— Select a room —</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}">{{ $room->name }} ({{ $room->code }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Step 2: Shelf --}}
                <div class="location-step">
                    <label>
                        <span class="step-num">2</span>
                        Shelf <span style="color:var(--danger)">*</span>
                    </label>
                    <select id="shelf_select" class="form-control" disabled>
                        <option value="">— Select room first —</option>
                    </select>
                </div>

                {{-- Step 3: Box --}}
                <div class="location-step">
                    <label>
                        <span class="step-num">3</span>
                        Box <span style="color:var(--danger)">*</span>
                    </label>
                    <select id="box_select" name="new_box_id"
                            class="form-control @error('new_box_id') is-invalid @enderror"
                            disabled required>
                        <option value="">— Select shelf first —</option>
                    </select>
                    @error('new_box_id')
                        <span style="color:var(--danger); font-size:12px; margin-top:4px; display:block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Box status preview --}}
            <div id="box_preview" class="box-preview">
                <div class="box-preview-icon"><i class="fas fa-box"></i></div>
                <div class="box-preview-info">
                    <div class="box-preview-name" id="box_preview_name"></div>
                    <div class="box-preview-sub" id="box_preview_sub"></div>
                    <div class="fill-bar-wrap" style="margin-top:6px; max-width:220px">
                        <div class="progress">
                            <div class="progress-bar" id="box_preview_bar" style="width:0%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="divider"></div>

            {{-- Reason + Notes --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px">
                <div class="form-group" style="margin-bottom:0">
                    <label>Reason <span style="color:var(--danger)">*</span></label>
                    <select name="reason" class="form-control @error('reason') is-invalid @enderror" required>
                        <option value="">— Select a reason —</option>
                        <option value="Box full">Box full</option>
                        <option value="Reorganization">Reorganization</option>
                        <option value="Wrong location">Wrong location</option>
                        <option value="Other">Other</option>
                    </select>
                    @error('reason')
                        <span style="color:var(--danger); font-size:12px; margin-top:4px; display:block">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label>Notes <span class="text-muted" style="font-weight:400">(optional)</span></label>
                    <textarea name="notes" class="form-control" rows="1" style="resize:vertical">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('charts.show', $chart) }}" class="btn btn-ghost">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i> Confirm Move
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
const SHELVES_URL = '{{ route("locations.api.shelves", ["room" => "__ROOM__"]) }}';
const BOXES_URL   = '{{ route("locations.api.boxes",   ["shelf" => "__SHELF__"]) }}';

async function apiFetch(url) {
    const res = await fetch(url, {
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    });
    return res.json();
}

const roomSel    = document.getElementById('room_select');
const shelfSel   = document.getElementById('shelf_select');
const boxSel     = document.getElementById('box_select');
const boxPreview = document.getElementById('box_preview');
const boxBar     = document.getElementById('box_preview_bar');

function resetPreview() {
    boxPreview.className = 'box-preview';
    boxPreview.classList.remove('show');
}

roomSel.addEventListener('change', async function () {
    shelfSel.innerHTML = '<option value="">Loading…</option>';
    shelfSel.disabled = true;
    boxSel.innerHTML = '<option value="">— Select shelf first —</option>';
    boxSel.disabled = true;
    resetPreview();
    if (!this.value) {
        shelfSel.innerHTML = '<option value="">— Select room first —</option>';
        return;
    }
    const shelves = await apiFetch(SHELVES_URL.replace('__ROOM__', this.value));
    shelfSel.innerHTML = '<option value="">— Select shelf —</option>' +
        shelves.map(s => `<option value="${s.id}">${s.name} (${s.code})</option>`).join('');
    shelfSel.disabled = false;
});

shelfSel.addEventListener('change', async function () {
    boxSel.innerHTML = '<option value="">Loading…</option>';
    boxSel.disabled = true;
    resetPreview();
    if (!this.value) {
        boxSel.innerHTML = '<option value="">— Select shelf first —</option>';
        return;
    }
    const boxes = await apiFetch(BOXES_URL.replace('__SHELF__', this.value));
    boxSel.innerHTML = '<option value="">— Select box —</option>' +
        boxes.map(b => {
            const disabled = !b.can_accept ? 'disabled' : '';
            const fullTag  = !b.can_accept ? ' [FULL]' : '';
            return `<option value="${b.id}" ${disabled}
                data-pct="${b.fill_percentage}"
                data-status="${b.status}"
                data-name="Box ${b.box_number} (${b.box_code})"
                data-sub="${b.current_count}/${b.capacity} charts — ${b.fill_percentage}% full${fullTag}">
                Box ${b.box_number} (${b.box_code}) — ${b.current_count}/${b.capacity} (${b.fill_percentage}%)${fullTag}
            </option>`;
        }).join('');
    boxSel.disabled = false;
});

boxSel.addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    if (!this.value) { resetPreview(); return; }

    const pct    = parseFloat(opt.dataset.pct);
    const status = opt.dataset.status; // ok / warning / full

    // Map status to CSS class and progress-bar variant
    const stateMap = {
        'full':    { cls: 'full',   bar: 'danger'  },
        'warning': { cls: 'warn',   bar: 'warning' },
    };
    const state = stateMap[status] || { cls: 'ok', bar: 'success' };

    boxPreview.className = `box-preview show ${state.cls}`;
    document.getElementById('box_preview_name').textContent = opt.dataset.name;
    document.getElementById('box_preview_sub').textContent  = opt.dataset.sub;
    boxBar.className = `progress-bar ${state.bar}`;
    boxBar.style.width = pct + '%';
});
</script>
@endpush