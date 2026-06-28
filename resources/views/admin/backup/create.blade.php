@extends('layouts.app')
@section('title', 'New Backup Schedule')

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="page-header-left">
        <h1>New Backup Schedule</h1>
        <p style="font-size:13px; color:var(--text-muted); margin-top:3px;">
            Create an automated backup schedule to protect your data
        </p>
    </div>
    <a href="{{ route('admin.backup.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="card" style="max-width:800px;">
    <div class="card-header">
        <span><i class="fas fa-calendar-plus"></i>&ensp;Schedule Configuration</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.backup.store') }}">
            @csrf

            {{-- Schedule Name --}}
            <div class="form-group">
                <label for="name">
                    Schedule Name <span style="color:var(--danger);">*</span>
                </label>
                <input type="text"
                       id="name"
                       name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}"
                       required
                       placeholder="e.g., Weekly Full Backup"
                       autofocus>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-help">
                    A descriptive name to identify this backup schedule
                </div>
            </div>

            {{-- Backup Type & Frequency --}}
            <div class="form-row" style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label for="backup_type">
                        Backup Type <span style="color:var(--danger);">*</span>
                    </label>
                    <select id="backup_type" name="backup_type" class="form-control">
                        <option value="database" {{ old('backup_type') === 'database' ? 'selected' : '' }}>
                            <i class="fas fa-database"></i> Database Only
                        </option>
                        <option value="database_files" {{ old('backup_type') === 'database_files' ? 'selected' : '' }}>
                            <i class="fas fa-folder"></i> Database + Files
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="frequency">
                        Frequency <span style="color:var(--danger);">*</span>
                    </label>
                    <select id="frequency" name="frequency" class="form-control" onchange="toggleDayFields()">
                        <option value="daily" {{ old('frequency') === 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="weekly" {{ old('frequency') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ old('frequency') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    </select>
                </div>

                <div class="form-group" id="source_drives_group" style="display:none;">
                    <label>
                        Source Drives <span style="color:var(--danger);">*</span>
                    </label>
                    <div class="form-help" style="margin-bottom:10px;">
                        Select which drives' <code>archives/</code> folders will be included in the files backup.
                    </div>

                    @if($drives->isEmpty())
                        <div style="color:var(--text-muted); font-size:13px;">No active drives available.</div>
                    @else
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            @foreach($drives as $drive)
                            <label style="display:flex; align-items:center; gap:10px; cursor:pointer;
                                        padding:10px 14px; border:1px solid var(--border);
                                        border-radius:var(--radius-md); font-weight:normal;
                                        transition: background 0.15s;"
                                onmouseover="this.style.background='var(--surface-hover)'"
                                onmouseout="this.style.background=''">
                                <input type="checkbox"
                                    name="source_drive_ids[]"
                                    value="{{ $drive->id }}"
                                    {{-- For create.blade.php: --}}
                                    {{ in_array($drive->id, old('source_drive_ids', [])) ? 'checked' : '' }}
                                    {{-- For edit.blade.php, replace the line above with: --}}
                                    {{-- {{ in_array($drive->id, old('source_drive_ids', $selectedSourceIds ?? [])) ? 'checked' : '' }} --}}
                                    style="cursor:pointer;">
                                <div>
                                    <div style="font-size:13.5px; font-weight:600; color:var(--text-primary);">
                                        {{ $drive->name }}
                                        @if($drive->is_primary)
                                            <span class="badge badge-info" style="font-size:10px; margin-left:4px;">Primary</span>
                                        @endif
                                    </div>
                                    <div style="font-size:12px; color:var(--text-muted);">
                                        {{ $drive->drive_path }}
                                    </div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Day Selection & Time --}}
            <div class="form-row" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">
                <div class="form-group" id="day_of_week_group" style="display:none;">
                    <label for="day_of_week">Day of Week</label>
                    <select id="day_of_week" name="day_of_week" class="form-control">
                        @foreach(['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $i => $day)
                        <option value="{{ $i }}" {{ old('day_of_week') == $i ? 'selected' : '' }}>
                            {{ $day }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" id="day_of_month_group" style="display:none;">
                    <label for="day_of_month">Day of Month</label>
                    <select id="day_of_month" name="day_of_month" class="form-control">
                        @for($d = 1; $d <= 28; $d++)
                        <option value="{{ $d }}" {{ old('day_of_month') == $d ? 'selected' : '' }}>
                            {{ $d }}
                        </option>
                        @endfor
                    </select>
                    <div class="form-help">Max day is 28 to ensure monthly runs</div>
                </div>

                <div class="form-group">
                    <label for="time_of_day">
                        Time <span style="color:var(--danger);">*</span>
                    </label>
                    <input type="time"
                           id="time_of_day"
                           name="time_of_day"
                           class="form-control"
                           value="{{ old('time_of_day', '02:00') }}"
                           required>
                    <div class="form-help">24-hour format (HH:MM)</div>
                </div>
            </div>

            <div class="divider"></div>

            {{-- Destination & Retention --}}
            <div class="form-row" style="display:grid; grid-template-columns:2fr 1fr; gap:16px;">
                <div class="form-group">
                    <label for="destination_drive_id">
                        Destination Drive <span style="color:var(--danger);">*</span>
                    </label>
                    <select id="destination_drive_id"
                            name="destination_drive_id"
                            class="form-control @error('destination_drive_id') is-invalid @enderror"
                            required>
                        <option value="">— Select Drive —</option>
                        @foreach($drives as $drive)
                        <option value="{{ $drive->id }}" {{ old('destination_drive_id') == $drive->id ? 'selected' : '' }}>
                            {{ $drive->name }}
                            @if($drive->is_primary) (Primary) @endif
                        </option>
                        @endforeach
                    </select>
                    @error('destination_drive_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-help">Where backup files will be stored</div>
                </div>

                <div class="form-group">
                    <label for="retention_count">Retention Count</label>
                    <input type="number"
                           id="retention_count"
                           name="retention_count"
                           class="form-control"
                           value="{{ old('retention_count', 10) }}"
                           min="1"
                           max="100">
                    <div class="form-help">Keep last N backups</div>
                </div>
            </div>

            {{-- Active Status --}}
            <div class="form-group">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           {{ old('is_active', true) ? 'checked' : '' }}
                           style="cursor:pointer;">
                    <span>
                        <strong>Active</strong> — Schedule will run automatically
                    </span>
                </label>
                <div class="form-help" style="margin-left:28px;">
                    Uncheck to create the schedule without enabling it
                </div>
            </div>

            {{-- Actions --}}
            <div class="divider"></div>
            <div class="d-flex justify-end gap-1">
                <a href="{{ route('admin.backup.index') }}" class="btn btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i> Create Schedule
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function toggleDayFields() {
    const freq = document.getElementById('frequency').value;
    const weekGroup = document.getElementById('day_of_week_group');
    const monthGroup = document.getElementById('day_of_month_group');

    weekGroup.style.display = freq === 'weekly' ? 'block' : 'none';
    monthGroup.style.display = freq === 'monthly' ? 'block' : 'none';
}
function toggleBackupType() {
    const type = document.getElementById('backup_type').value;
    const sourceGroup = document.getElementById('source_drives_group');
    sourceGroup.style.display = type === 'database_files' ? 'block' : 'none';
}

// Call on both change events and page load
document.getElementById('backup_type').addEventListener('change', toggleBackupType);
toggleBackupType();
// Run on page load
toggleDayFields();
</script>
@endpush
