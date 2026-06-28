@extends('layouts.app')
@section('title', 'Archive New Chart')

@push('styles')
<style>
    /* ── Page Layout ────────────────────────────────────────────────────── */
    .create-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    /* ── Section Labels ─────────────────────────────────────────────────── */
    .section-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--text-muted);
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--divider);
    }

    .section-label i {
        width: 22px;
        height: 22px;
        border-radius: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        background: var(--info-light);
        color: var(--info-text);
    }

    /* ── Form Row ───────────────────────────────────────────────────────── */
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    /* ── Form text helper ───────────────────────────────────────────────── */
    .form-text {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 5px;
    }

    /* ── Invalid feedback ───────────────────────────────────────────────── */
    .invalid-feedback {
        display: block;
        font-size: 12px;
        color: var(--danger-text);
        margin-top: 4px;
        font-weight: 500;
    }

    .is-invalid {
        border-color: var(--danger) !important;
        box-shadow: 0 0 0 3px rgba(220,38,38,0.1) !important;
    }

    /* ── Patient Search ─────────────────────────────────────────────────── */
    .patient-search-wrap {
        position: relative;
    }

    .patient-search-icon {
        position: absolute;
        left: 11px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 13px;
        pointer-events: none;
    }

    #patient_search {
        padding-left: 34px;
    }

    .patient-dropdown {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        box-shadow: var(--card-shadow-hover);
        z-index: 200;
        max-height: 220px;
        overflow-y: auto;
        animation: dropdownIn 0.12s cubic-bezier(0.4,0,0.2,1);
    }

    @keyframes dropdownIn {
        from { opacity: 0; transform: translateY(-4px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .patient-dropdown-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 14px;
        cursor: pointer;
        border-bottom: 1px solid var(--divider);
        transition: background var(--transition);
    }

    .patient-dropdown-item:last-child { border-bottom: none; }
    .patient-dropdown-item:hover      { background: var(--table-row-hover); }

    .patient-dropdown-avatar {
        width: 30px;
        height: 30px;
        border-radius: 7px;
        background: linear-gradient(135deg, var(--accent), #7c3aed);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        color: white;
        flex-shrink: 0;
    }

    .patient-dropdown-name { font-weight: 600; font-size: 13.5px; color: var(--text-primary); }
    .patient-dropdown-mr   { font-size: 11.5px; color: var(--text-muted); }

    .patient-dropdown-empty {
        padding: 20px;
        text-align: center;
        color: var(--text-muted);
        font-size: 13px;
    }

    .patient-selected-card {
        display: none;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        background: var(--info-light);
        border: 1px solid var(--info-border);
        border-radius: var(--radius-sm);
        margin-top: 8px;
    }

    .patient-selected-card.visible { display: flex; }

    .patient-selected-card .clear-btn {
        margin-left: auto;
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 13px;
        transition: all var(--transition);
    }

    .patient-selected-card .clear-btn:hover {
        background: var(--danger-light);
        color: var(--danger-text);
    }

    /* ── Box Status Banner ──────────────────────────────────────────────── */
    .box-status-banner {
        display: none;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border-radius: var(--radius-sm);
        margin-top: 8px;
        font-size: 13px;
        border: 1px solid;
        animation: alertIn 0.2s ease;
    }

    .box-status-banner.visible { display: flex; }
    .box-status-banner.ok      { background: var(--success-light); border-color: var(--success-border); color: var(--success-text); }
    .box-status-banner.warning { background: var(--warning-light); border-color: var(--warning-border); color: var(--warning-text); }
    .box-status-banner.full    { background: var(--danger-light);  border-color: var(--danger-border);  color: var(--danger-text); }

    .box-fill-mini {
        flex: 1;
        height: 5px;
        background: rgba(0,0,0,0.1);
        border-radius: 99px;
        overflow: hidden;
        max-width: 80px;
    }

    .box-fill-mini-bar {
        height: 100%;
        border-radius: 99px;
        transition: width 0.4s ease;
    }

    .box-status-banner.ok .box-fill-mini-bar      { background: var(--success); }
    .box-status-banner.warning .box-fill-mini-bar { background: var(--warning); }
    .box-status-banner.full .box-fill-mini-bar    { background: var(--danger); }

    /* ── File Upload Zone ───────────────────────────────────────────────── */
    .file-upload-zone {
        border: 2px dashed var(--border-color);
        border-radius: var(--radius-md);
        padding: 28px 20px;
        text-align: center;
        cursor: pointer;
        transition: all var(--transition-md);
        background: var(--table-header-bg);
        position: relative;
    }

    .file-upload-zone:hover,
    .file-upload-zone.dragover {
        border-color: var(--accent);
        background: var(--info-light);
    }

    .file-upload-zone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
    }

    .file-upload-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: var(--info-light);
        color: var(--info-text);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin: 0 auto 12px;
        transition: all var(--transition-md);
    }

    .file-upload-zone:hover .file-upload-icon,
    .file-upload-zone.dragover .file-upload-icon {
        background: var(--accent);
        color: white;
        transform: scale(1.1);
    }

    .file-upload-title    { font-weight: 600; font-size: 13.5px; color: var(--text-primary); margin-bottom: 4px; }
    .file-upload-subtitle { font-size: 12px; color: var(--text-muted); }

    /* ── File Preview ───────────────────────────────────────────────────── */
    .file-preview-container {
        display: none;
        margin-top: 12px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        overflow: hidden;
        background: var(--card-bg);
    }

    .file-preview-container.visible { display: block; }

    .file-preview-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        background: var(--table-header-bg);
        border-bottom: 1px solid var(--divider);
    }

    .file-preview-type-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }

    .file-preview-type-icon.pdf   { background: #fee2e2; color: #dc2626; }
    .file-preview-type-icon.image { background: #ede9fe; color: #7c3aed; }
    .file-preview-type-icon.other { background: var(--info-light); color: var(--info-text); }

    .file-preview-info { flex: 1; min-width: 0; }

    .file-preview-name {
        font-weight: 600;
        font-size: 13px;
        color: var(--text-primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .file-preview-size { font-size: 11.5px; color: var(--text-muted); }

    .file-preview-remove {
        background: none;
        border: none;
        cursor: pointer;
        color: var(--text-muted);
        padding: 4px 8px;
        border-radius: var(--radius-sm);
        font-size: 13px;
        transition: all var(--transition);
    }

    .file-preview-remove:hover {
        background: var(--danger-light);
        color: var(--danger-text);
    }

    .file-preview-image {
        width: 100%;
        max-height: 280px;
        object-fit: contain;
        display: block;
        background: #f8f8f8;
        padding: 12px;
    }

    .file-preview-pdf {
        width: 100%;
        height: 320px;
        border: none;
        display: block;
    }

    .file-preview-pdf-fallback {
        padding: 32px 20px;
        text-align: center;
        color: var(--text-muted);
    }

    .file-preview-pdf-fallback i {
        font-size: 40px;
        color: #dc2626;
        opacity: 0.6;
        display: block;
        margin-bottom: 10px;
    }

    /* ── Form Actions ───────────────────────────────────────────────────── */
    .form-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        padding-top: 6px;
    }

    /* ── Card step badge ────────────────────────────────────────────────── */
    .card-step {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--accent);
        color: white;
        font-size: 10px;
        font-weight: 700;
        flex-shrink: 0;
    }

    /* ── Save Overlay ───────────────────────────────────────────────────── */
    #saveOverlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.52);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }
    #saveOverlay.visible { display: flex; }

    .save-modal {
        background: var(--card-bg);
        border-radius: 16px;
        padding: 30px 32px 28px;
        width: 420px;
        max-width: calc(100vw - 32px);
        box-shadow: 0 24px 64px rgba(0,0,0,0.3);
        animation: saveModalIn 0.22s cubic-bezier(0.34,1.56,0.64,1);
    }

    @keyframes saveModalIn {
        from { opacity:0; transform: scale(0.92) translateY(8px); }
        to   { opacity:1; transform: scale(1)    translateY(0);   }
    }

    .save-modal-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 22px;
    }

    .save-modal-icon-wrap {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
        background: var(--accent);
        color: white;
        transition: background 0.3s ease;
    }

    .save-modal-title    { font-weight: 700; font-size: 15px; color: var(--text-primary); }
    .save-modal-subtitle { font-size: 12px; color: var(--text-muted); margin-top: 2px; }

    /* ── Upload progress bar ────────────────────────────────────────────── */
    .save-progress-wrap  { margin-bottom: 6px; }

    .save-progress-track {
        height: 7px;
        background: var(--divider);
        border-radius: 99px;
        overflow: hidden;
        margin-bottom: 6px;
    }

    .save-progress-bar {
        height: 100%;
        border-radius: 99px;
        background: var(--accent);
        width: 0%;
        transition: width 0.25s ease;
    }

    .save-progress-label {
        font-size: 12px;
        color: var(--text-muted);
        text-align: right;
    }

    /* ── Error block ────────────────────────────────────────────────────── */
    .save-modal-error {
        display: none;
        background: var(--danger-light);
        border: 1px solid var(--danger-border);
        color: var(--danger-text);
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 13px;
        margin-bottom: 14px;
        line-height: 1.5;
    }
    .save-modal-error.visible { display: block; }

    .save-modal-actions { display: none; justify-content: flex-end; }
    .save-modal-actions.visible { display: flex; }

    /* ── Responsive ─────────────────────────────────────────────────────── */
    @media (max-width: 1024px) {
        .create-grid { grid-template-columns: 1fr; }
        .form-row    { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .form-row { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="page-header-left">
        <div class="breadcrumb">
            <a href="{{ route('charts.index') }}"><i class="fas fa-archive"></i> Chart Archive</a>
            <i class="fas fa-chevron-right" style="font-size:9px;"></i>
            <span>Archive New Chart</span>
        </div>
        <h1>Archive New Chart</h1>
    </div>
    <a href="{{ route('charts.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<form method="POST" action="{{ route('charts.store') }}" enctype="multipart/form-data" id="archiveForm">
    @csrf

    <div class="create-grid">

        {{-- ── Card 1: Patient ─────────────────────────────────────────── --}}
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-center gap-1">
                    <span class="card-step">1</span>
                    Patient Information
                </div>
            </div>
            <div class="card-body">
                <div class="section-label">
                    <i class="fas fa-user"></i>
                    Select Patient
                </div>

                <div class="form-group">
                    <label for="patient_search">Patient Search <span style="color:var(--danger)">*</span></label>
                    <div class="patient-search-wrap">
                        <i class="fas fa-search patient-search-icon"></i>
                        <input type="text"
                               id="patient_search"
                               class="form-control"
                               placeholder="Type name or MR# to search…"
                               autocomplete="off">
                    </div>
                    <input type="hidden" name="patient_id" id="patient_id" value="{{ old('patient_id') }}" required>

                    <div class="patient-selected-card" id="patient_selected_card">
                        <div class="patient-dropdown-avatar" id="patient_selected_avatar"></div>
                        <div>
                            <div class="patient-dropdown-name" id="patient_selected_name"></div>
                            <div class="patient-dropdown-mr"   id="patient_selected_mr"></div>
                        </div>
                        <button type="button" class="clear-btn" onclick="clearPatient()" title="Remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    @error('patient_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="d-flex align-center gap-1" style="margin-top:-4px;">
                    <span style="font-size:12.5px; color:var(--text-muted);">Patient not found?</span>
                    <a href="{{ route('patients.create') }}" target="_blank" class="btn btn-secondary btn-sm">
                        <i class="fas fa-plus"></i> New Patient
                    </a>
                </div>
            </div>
        </div>

        {{-- ── Card 2: Chart Details ────────────────────────────────────── --}}
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-center gap-1">
                    <span class="card-step">2</span>
                    Chart Details
                </div>
            </div>
            <div class="card-body">
                <div class="section-label">
                    <i class="fas fa-file-medical"></i>
                    Case Information
                </div>

                <div class="form-group">
                    <label for="case_number">Case Number <span style="color:var(--danger)">*</span></label>
                    <input type="text"
                           name="case_number"
                           id="case_number"
                           class="form-control @error('case_number') is-invalid @enderror"
                           value="{{ old('case_number') }}"
                           required
                           placeholder="e.g. 2024-00123">
                    @error('case_number')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="admission_date">Admission Date <span style="color:var(--danger)">*</span></label>
                        <input type="date"
                               name="admission_date"
                               id="admission_date"
                               class="form-control @error('admission_date') is-invalid @enderror"
                               value="{{ old('admission_date') }}"
                               required>
                        @error('admission_date')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="discharge_date">Discharge Date</label>
                        <input type="date"
                               name="discharge_date"
                               id="discharge_date"
                               class="form-control @error('discharge_date') is-invalid @enderror"
                               value="{{ old('discharge_date') }}">
                        @error('discharge_date')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="total_pages">Total Pages</label>
                        <input type="number"
                               name="total_pages"
                               id="total_pages"
                               class="form-control"
                               value="{{ old('total_pages', 0) }}"
                               min="0">
                    </div>
                    <div class="form-group">
                        <label for="retention_period">Retention Period <span style="color:var(--danger)">*</span></label>
                        <select name="retention_period"
                                id="retention_period"
                                class="form-control @error('retention_period') is-invalid @enderror"
                                required>
                            @foreach($retentionOptions as $opt)
                                <option value="{{ $opt }}"
                                    {{ old('retention_period', $defaultRetention) == $opt ? 'selected' : '' }}>
                                    {{ is_numeric($opt) ? $opt . ' Years' : ucfirst($opt) }}
                                </option>
                            @endforeach
                        </select>
                        @error('retention_period')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Card 3: Physical Location ────────────────────────────────── --}}
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-center gap-1">
                    <span class="card-step">3</span>
                    Physical Location
                    <span style="font-size:11px;font-weight:600;color:var(--text-muted);background:var(--table-header-bg);border:1px solid var(--divider);border-radius:20px;padding:1px 8px;margin-left:4px;">
                        Optional
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="section-label">
                    <i class="fas fa-map-marker-alt"></i>
                    Storage Location
                </div>

                <div style="display:flex;align-items:flex-start;gap:9px;padding:10px 13px;background:var(--warning-light);border:1px solid var(--warning-border);border-radius:var(--radius-sm);margin-bottom:16px;font-size:12.5px;color:var(--warning-text);line-height:1.5;">
                    <i class="fas fa-info-circle" style="margin-top:2px;flex-shrink:0;"></i>
                    <span>Location is <strong>optional</strong>. If you skip it, the chart will be saved as <strong>orphaned</strong> and can be assigned a box later from the <em>Orphaned Charts</em> page.</span>
                </div>

                <div class="form-group">
                    <label for="room_select">Room</label>
                    <select id="room_select" class="form-control">
                        <option value="">— Select Room —</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}">{{ $room->name }} ({{ $room->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="shelf_select">Shelf</label>
                    <select id="shelf_select" class="form-control" disabled>
                        <option value="">— Select Room First —</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:4px;">
                    <label for="box_select">Box</label>
                    <select id="box_select"
                            name="physical_location_id"
                            class="form-control @error('physical_location_id') is-invalid @enderror"
                            disabled>
                        <option value="">— Select Shelf First —</option>
                    </select>
                    @error('physical_location_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="box-status-banner" id="box_status_banner">
                    <i class="fas fa-box" id="box_status_icon"></i>
                    <div style="flex:1; min-width:0;">
                        <div style="font-weight:600; font-size:12.5px;" id="box_status_label"></div>
                        <div style="font-size:11.5px; opacity:0.8;" id="box_status_sub"></div>
                    </div>
                    <div class="box-fill-mini">
                        <div class="box-fill-mini-bar" id="box_fill_bar" style="width:0%"></div>
                    </div>
                    <span style="font-size:11.5px; font-weight:700; min-width:32px; text-align:right;" id="box_fill_pct"></span>
                </div>
            </div>
        </div>

        {{-- ── Card 4: Digital Copy & Notes ────────────────────────────── --}}
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-center gap-1">
                    <span class="card-step">4</span>
                    Digital Copy &amp; Notes
                </div>
            </div>
            <div class="card-body">
                <div class="section-label">
                    <i class="fas fa-upload"></i>
                    File Upload
                </div>

                <div class="form-group">
                    <div class="file-upload-zone" id="file_drop_zone">
                        <input type="file"
                               name="digital_copy"
                               id="digital_copy"
                               accept=".pdf,.jpg,.jpeg,.png,.tiff"
                               class="@error('digital_copy') is-invalid @enderror">
                        <div class="file-upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div class="file-upload-title">Drop file here or click to browse</div>
                        <div class="file-upload-subtitle">PDF, JPG, PNG, TIFF &mdash; max {{ $maxFileSizeMb }}MB</div>
                    </div>
                    @error('digital_copy')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror

                    <div class="file-preview-container" id="file_preview_container">
                        <div class="file-preview-header">
                            <div class="file-preview-type-icon" id="preview_type_icon">
                                <i class="fas fa-file"></i>
                            </div>
                            <div class="file-preview-info">
                                <div class="file-preview-name" id="preview_file_name"></div>
                                <div class="file-preview-size" id="preview_file_size"></div>
                            </div>
                            <button type="button" class="file-preview-remove" onclick="removeFile()" title="Remove file">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div id="preview_content"></div>
                    </div>
                </div>

                <div class="section-label" style="margin-top: 4px;">
                    <i class="fas fa-sticky-note"></i>
                    Notes
                </div>

                <div class="form-group">
                    <textarea name="notes"
                              class="form-control"
                              rows="4"
                              placeholder="Optional notes about this chart…">{{ old('notes') }}</textarea>
                </div>

                <div class="form-actions">
                    <a href="{{ route('charts.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-check"></i> Complete Archive
                    </button>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Save Overlay ─────────────────────────────────────────────────── --}}
    <div id="saveOverlay" role="dialog" aria-modal="true" aria-label="Saving chart">
        <div class="save-modal">

            <div class="save-modal-header">
                <div class="save-modal-icon-wrap" id="modalIconWrap">
                    <i id="modalIcon" class="fas fa-cloud-upload-alt"></i>
                </div>
                <div>
                    <div class="save-modal-title"   id="modalTitle">Uploading File…</div>
                    <div class="save-modal-subtitle" id="modalSubtitle">Please wait — do not close this page</div>
                </div>
            </div>

            {{-- Upload progress bar --}}
            <div class="save-progress-wrap" id="uploadProgressWrap">
                <div class="save-progress-track">
                    <div class="save-progress-bar" id="uploadProgressBar"></div>
                </div>
                <div class="save-progress-label" id="uploadProgressLabel">0%</div>
            </div>

            {{-- Error --}}
            <div class="save-modal-error" id="modalError"></div>

            {{-- Error action --}}
            <div class="save-modal-actions" id="modalActions">
                <button type="button" class="btn btn-secondary" onclick="dismissSaveOverlay()">
                    <i class="fas fa-arrow-left"></i> Go Back &amp; Fix
                </button>
            </div>

        </div>
    </div>

</form>

@endsection

@push('scripts')
<script>
const SHELVES_URL        = '{{ route("locations.api.shelves", ["room"  => "__ROOM__"]) }}';
const BOXES_URL          = '{{ route("locations.api.boxes",   ["shelf" => "__SHELF__"]) }}';
const BOX_INFO_URL       = '{{ route("charts.box.info",       ["box"   => "__BOX__"]) }}';
const PATIENT_SEARCH_URL = '{{ route("patients.search") }}';
const CHUNK_SIZE         = 25 * 1024 * 1024; // 25 MB per chunk

// ── Helpers ───────────────────────────────────────────────────────────────
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

async function apiFetch(url) {
    const r = await fetch(url, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() }
    });
    return r.json();
}

// ── Patient Search ────────────────────────────────────────────────────────
let searchTimer;
const patientSearchEl = document.getElementById('patient_search');
const patientIdEl     = document.getElementById('patient_id');

patientSearchEl.addEventListener('input', function () {
    clearTimeout(searchTimer);
    const q = this.value.trim();
    removeDropdown();
    if (q.length < 2) return;
    searchTimer = setTimeout(async () => {
        const results = await apiFetch(PATIENT_SEARCH_URL + '?q=' + encodeURIComponent(q));
        renderPatientDropdown(results);
    }, 300);
});

function renderPatientDropdown(results) {
    removeDropdown();
    const wrap = patientSearchEl.closest('.patient-search-wrap');
    const el   = document.createElement('div');
    el.id        = 'patient_dropdown';
    el.className = 'patient-dropdown';

    if (!results.length) {
        el.innerHTML = `<div class="patient-dropdown-empty">
            <i class="fas fa-search" style="display:block;font-size:24px;opacity:0.3;margin-bottom:8px;"></i>
            No patients found
        </div>`;
    } else {
        el.innerHTML = results.map(p => {
            const initials = p.name.split(',').map(s => s.trim()[0] || '').join('').slice(0,2).toUpperCase();
            return `<div class="patient-dropdown-item"
                        onclick="selectPatient(${p.id},'${escJs(p.name)}','${escJs(p.mr)}')">
                <div class="patient-dropdown-avatar">${initials}</div>
                <div>
                    <div class="patient-dropdown-name">${escHtml(p.name)}</div>
                    <div class="patient-dropdown-mr">MR# ${escHtml(p.mr)}</div>
                </div>
            </div>`;
        }).join('');
    }
    wrap.appendChild(el);
}

function selectPatient(id, name, mr) {
    patientIdEl.value         = id;
    patientSearchEl.value     = name;
    removeDropdown();

    const initials = name.split(',').map(s => s.trim()[0] || '').join('').slice(0,2).toUpperCase();
    document.getElementById('patient_selected_avatar').textContent = initials;
    document.getElementById('patient_selected_name').textContent   = name;
    document.getElementById('patient_selected_mr').textContent     = 'MR# ' + mr;
    document.getElementById('patient_selected_card').classList.add('visible');
    patientSearchEl.style.display = 'none';
    document.querySelector('.patient-search-icon').style.display = 'none';
}

function clearPatient() {
    patientIdEl.value             = '';
    patientSearchEl.value         = '';
    patientSearchEl.style.display = '';
    document.querySelector('.patient-search-icon').style.display = '';
    document.getElementById('patient_selected_card').classList.remove('visible');
    patientSearchEl.focus();
}

function removeDropdown() {
    const el = document.getElementById('patient_dropdown');
    if (el) el.remove();
}

document.addEventListener('click', e => {
    if (!e.target.closest('.patient-search-wrap')) removeDropdown();
});

// ── Location Cascade ──────────────────────────────────────────────────────
document.getElementById('room_select').addEventListener('change', async function () {
    const roomId   = this.value;
    const shelfSel = document.getElementById('shelf_select');
    const boxSel   = document.getElementById('box_select');

    shelfSel.innerHTML = '<option value="">Loading…</option>';
    shelfSel.disabled  = true;
    boxSel.innerHTML   = '<option value="">— Select Shelf First —</option>';
    boxSel.disabled    = true;
    hideBoxBanner();

    if (!roomId) { shelfSel.innerHTML = '<option value="">— Select Room First —</option>'; return; }

    const shelves = await apiFetch(SHELVES_URL.replace('__ROOM__', roomId));
    shelfSel.innerHTML = '<option value="">— Select Shelf —</option>' +
        shelves.map(s => `<option value="${s.id}">${escHtml(s.name)} (${escHtml(s.code)})</option>`).join('');
    shelfSel.disabled = false;
});

document.getElementById('shelf_select').addEventListener('change', async function () {
    const shelfId = this.value;
    const boxSel  = document.getElementById('box_select');

    boxSel.innerHTML = '<option value="">Loading…</option>';
    boxSel.disabled  = true;
    hideBoxBanner();

    if (!shelfId) { boxSel.innerHTML = '<option value="">— Select Shelf First —</option>'; return; }

    const boxes = await apiFetch(BOXES_URL.replace('__SHELF__', shelfId));
    boxSel.innerHTML = '<option value="">— Select Box —</option>' +
        boxes.map(b => {
            const label    = `Box ${b.box_number} (${b.box_code}) — ${b.current_count}/${b.capacity} (${b.fill_percentage}%)`;
            const disabled = !b.can_accept ? 'disabled' : '';
            const suffix   = !b.can_accept ? ' [FULL]' : '';
            return `<option value="${b.id}" ${disabled} data-status="${b.status}">${escHtml(label)}${suffix}</option>`;
        }).join('');
    boxSel.disabled = false;
});

document.getElementById('box_select').addEventListener('change', async function () {
    if (!this.value) { hideBoxBanner(); return; }
    const info = await apiFetch(BOX_INFO_URL.replace('__BOX__', this.value));
    showBoxBanner(info);
});

function showBoxBanner(info) {
    document.getElementById('box_status_icon').className    = info.status === 'full' ? 'fas fa-box-open' : 'fas fa-box';
    document.getElementById('box_status_label').textContent = info.location_label;
    document.getElementById('box_status_sub').textContent   = `${info.current_count} of ${info.capacity} charts`;
    document.getElementById('box_fill_bar').style.width     = info.fill_percentage + '%';
    document.getElementById('box_fill_pct').textContent     = info.fill_percentage + '%';
    document.getElementById('box_status_banner').className  = 'box-status-banner visible ' + info.status;
}

function hideBoxBanner() {
    document.getElementById('box_status_banner').className = 'box-status-banner';
}

// ── File Upload & Preview ─────────────────────────────────────────────────
const fileInput        = document.getElementById('digital_copy');
const dropZone         = document.getElementById('file_drop_zone');
const previewContainer = document.getElementById('file_preview_container');

['dragover','dragenter'].forEach(evt =>
    dropZone.addEventListener(evt, e => { e.preventDefault(); dropZone.classList.add('dragover'); })
);
['dragleave','dragend','drop'].forEach(evt =>
    dropZone.addEventListener(evt, e => { e.preventDefault(); dropZone.classList.remove('dragover'); })
);
dropZone.addEventListener('drop', e => {
    const file = e.dataTransfer.files[0];
    if (file) {
        const dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;
        handleFilePreview(file);
    }
});

fileInput.addEventListener('change', function () {
    if (this.files[0]) handleFilePreview(this.files[0]);
});

function handleFilePreview(file) {
    const ext     = file.name.split('.').pop().toLowerCase();
    const isImage = ['jpg','jpeg','png','tiff','gif','webp'].includes(ext);
    const isPdf   = ext === 'pdf';

    document.getElementById('preview_file_name').textContent = file.name;
    document.getElementById('preview_file_size').textContent = formatBytes(file.size);

    const typeIcon = document.getElementById('preview_type_icon');
    if (isPdf) {
        typeIcon.className = 'file-preview-type-icon pdf';
        typeIcon.innerHTML = '<i class="fas fa-file-pdf"></i>';
    } else if (isImage) {
        typeIcon.className = 'file-preview-type-icon image';
        typeIcon.innerHTML = '<i class="fas fa-image"></i>';
    } else {
        typeIcon.className = 'file-preview-type-icon other';
        typeIcon.innerHTML = '<i class="fas fa-file"></i>';
    }

    const contentEl = document.getElementById('preview_content');
    const url       = URL.createObjectURL(file);

    if (isImage) {
        contentEl.innerHTML = `<img src="${url}" class="file-preview-image" alt="Preview" onload="URL.revokeObjectURL(this.src)">`;
    } else if (isPdf) {
        setTimeout(() => URL.revokeObjectURL(url), 10000);
        contentEl.innerHTML = `<iframe src="${url}" class="file-preview-pdf" title="PDF Preview"></iframe>`;
    } else {
        URL.revokeObjectURL(url);
        contentEl.innerHTML = `<div class="file-preview-pdf-fallback">
            <i class="fas fa-file" style="color:var(--text-muted);"></i>
            <strong>File ready to upload</strong>
            <p style="font-size:12px;margin-top:4px;">${escHtml(file.name)} — ${formatBytes(file.size)}</p>
        </div>`;
    }

    previewContainer.classList.add('visible');
    dropZone.style.display = 'none';
}

function removeFile() {
    fileInput.value = '';
    document.getElementById('preview_content').innerHTML = '';
    previewContainer.classList.remove('visible');
    dropZone.style.display = '';
}

function formatBytes(bytes) {
    if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(2) + ' GB';
    if (bytes >= 1048576)    return (bytes / 1048576).toFixed(2)    + ' MB';
    if (bytes >= 1024)       return (bytes / 1024).toFixed(1)       + ' KB';
    return bytes + ' B';
}

// ── Overlay helpers ───────────────────────────────────────────────────────
function setUploadProgress(percent) {
    document.getElementById('uploadProgressBar').style.width   = percent + '%';
    document.getElementById('uploadProgressLabel').textContent = percent + '%';
}

function showSaveOverlay() {
    document.getElementById('saveOverlay').classList.add('visible');
}

function dismissSaveOverlay() {
    document.getElementById('saveOverlay').classList.remove('visible');

    document.getElementById('modalTitle').textContent         = 'Uploading File…';
    document.getElementById('modalSubtitle').textContent      = 'Please wait — do not close this page';
    document.getElementById('modalIcon').className            = 'fas fa-cloud-upload-alt';
    document.getElementById('modalIconWrap').style.background = '';
    document.getElementById('uploadProgressWrap').style.display = '';
    setUploadProgress(0);

    const errEl = document.getElementById('modalError');
    errEl.classList.remove('visible');
    errEl.innerHTML = '';
    document.getElementById('modalActions').classList.remove('visible');

    const btn = document.getElementById('submitBtn');
    btn.disabled  = false;
    btn.innerHTML = '<i class="fas fa-check"></i> Complete Archive';
}

function showModalError(message) {
    document.getElementById('modalTitle').textContent         = 'Save Failed';
    document.getElementById('modalSubtitle').textContent      = 'See the error below';
    document.getElementById('modalIcon').className            = 'fas fa-exclamation-triangle';
    document.getElementById('modalIconWrap').style.background = 'var(--danger, #dc2626)';
    document.getElementById('uploadProgressWrap').style.display = 'none';

    const errEl = document.getElementById('modalError');
    errEl.innerHTML = '<i class="fas fa-exclamation-circle" style="margin-right:6px"></i>' + escHtml(message);
    errEl.classList.add('visible');
    document.getElementById('modalActions').classList.add('visible');
}

// ── Chunked upload ────────────────────────────────────────────────────────
async function uploadChunksSequential(file, uploadId, onProgress) {
    const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
    let finalPath = null;

    for (let i = 0; i < totalChunks; i++) {
        const start = i * CHUNK_SIZE;
        const end   = Math.min(start + CHUNK_SIZE, file.size);
        const fd    = new FormData();
        fd.append('upload_id',    uploadId);
        fd.append('chunk_index',  i);
        fd.append('total_chunks', totalChunks);
        fd.append('chunk',        file.slice(start, end), file.name);

        const resp = await fetch('{{ url("/charts/upload/chunk") }}', {
            method : 'POST',
            body   : fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': getCsrfToken() },
        });

        const json = await resp.json();
        if (!resp.ok) throw new Error(json.message || `Chunk ${i} upload failed.`);
        if (json.done && json.path) finalPath = json.path;

        onProgress(Math.round(((i + 1) / totalChunks) * 100));
    }
    return finalPath;
}

// ── Submit handler ────────────────────────────────────────────────────────
document.getElementById('archiveForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const btn       = document.getElementById('submitBtn');
    btn.disabled    = true;
    btn.innerHTML   = '<i class="fas fa-spinner fa-spin"></i> Saving…';
    showSaveOverlay();

    const fileInput = this.querySelector('input[type="file"]');
    const file      = fileInput?.files?.[0];

    if (!file) {
        showModalError('Please select a file to upload.');
        btn.disabled  = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Complete Archive';
        return;
    }

    // Generate a unique upload ID for this submission
    const uploadId = crypto.randomUUID
        ? crypto.randomUUID()
        : ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
            (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16));

    // ── Phase 1: Upload file in chunks ────────────────────────────────────
    let assembledFilePath = null;
    try {
        assembledFilePath = await uploadChunksSequential(file, uploadId, (percent) => {
            setUploadProgress(percent);
            document.getElementById('modalSubtitle').textContent = `Uploading… ${percent}%`;
        });
    } catch (err) {
        showModalError(err.message || 'File upload failed. Please try again.');
        btn.disabled  = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Complete Archive';
        return;
    }

    // ── Phase 2: Submit form data (record save) ───────────────────────────
    document.getElementById('modalIcon').className       = 'fas fa-database fa-spin';
    document.getElementById('modalTitle').textContent    = 'Saving Record…';
    document.getElementById('modalSubtitle').textContent = 'Almost done…';
    setUploadProgress(100);

    const formData = new FormData();
    for (const [name, value] of new FormData(this).entries()) {
        if (name !== fileInput.name) formData.append(name, value);
    }
    formData.append('upload_id', uploadId);
    if (assembledFilePath) formData.append('assembled_file_path', assembledFilePath);

    let redirectUrl;
    try {
        const resp = await fetch(this.action, {
            method : 'POST',
            body   : formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });

        const json = await resp.json();

        if (!resp.ok) {
            const firstError = json.errors
                ? Object.values(json.errors).flat()[0]
                : (json.message || 'Validation failed. Please check the form.');
            showModalError(firstError);
            return;
        }

        redirectUrl = json.redirect;

    } catch (err) {
        showModalError('Network error while saving. Please check your connection and try again.');
        return;
    }

    // ── Done — redirect immediately ───────────────────────────────────────
    document.getElementById('modalIcon').className            = 'fas fa-check';
    document.getElementById('modalTitle').textContent         = 'Chart Archived!';
    document.getElementById('modalSubtitle').textContent      = 'Redirecting…';
    document.getElementById('modalIconWrap').style.background = 'var(--success, #16a34a)';

    setTimeout(() => { window.location.href = redirectUrl; }, 600);
});

// ── Escape helpers ────────────────────────────────────────────────────────
function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escJs(str) {
    if (!str) return '';
    return String(str).replace(/\\/g,'\\\\').replace(/'/g,"\\'");
}
</script>
@endpush