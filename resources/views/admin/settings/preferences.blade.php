@extends('layouts.app')
@section('title', 'My Preferences')

@push('styles')
<style>
    .pref-layout {
        display: grid;
        grid-template-columns: 220px 1fr;
        gap: 20px;
        align-items: start;
        max-width: 960px;
    }

    .pref-nav {
        position: sticky;
        top: calc(var(--topbar-height) + 20px);
    }

    .pref-nav-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 12px;
        border-radius: var(--radius-sm);
        font-size: 13.5px;
        font-weight: 500;
        color: var(--text-muted);
        cursor: pointer;
        transition: all var(--transition);
        border: none;
        background: none;
        width: 100%;
        text-align: left;
    }

    .pref-nav-item:hover {
        background: var(--border-color);
        color: var(--text-primary);
    }

    .pref-nav-item.active {
        background: var(--info-light);
        color: var(--info-text);
        font-weight: 600;
    }

    .pref-nav-item .p-nav-icon {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        flex-shrink: 0;
        background: var(--divider);
        color: var(--text-muted);
        transition: all var(--transition);
    }

    .pref-nav-item.active .p-nav-icon {
        background: rgba(37,99,235,0.12);
        color: var(--info);
    }

    /* Consistent preference rows */
    .pref-row {
        display: grid;
        grid-template-columns: 1fr 200px;
        gap: 24px;
        align-items: center;
        padding: 16px 20px;
        transition: background var(--transition);
    }

    .pref-row:not(:last-child) {
        border-bottom: 1px solid var(--divider);
    }

    .pref-row:hover { background: var(--table-row-hover); }

    /* Toggle row — full width, no right column */
    .pref-row-toggle {
        padding: 14px 20px;
        transition: background var(--transition);
        cursor: pointer;
    }

    .pref-row-toggle:not(:last-child) {
        border-bottom: 1px solid var(--divider);
    }

    .pref-row-toggle:hover { background: var(--table-row-hover); }

    .pref-panel { display: none; }
    .pref-panel.active { display: block; }

    @media (max-width: 768px) {
        .pref-layout { grid-template-columns: 1fr; }
        .pref-nav { position: static; }
        .pref-row { grid-template-columns: 1fr; gap: 10px; }
    }
</style>
@endpush

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>
            <i class="fas fa-sliders-h" style="color:var(--accent);margin-right:10px;font-size:20px;"></i>
            My Preferences
        </h1>
        <p style="font-size:13px;color:var(--text-muted);margin-top:3px;">
            Personalise your display and workflow defaults
        </p>
    </div>
    <button type="submit" form="prefsForm" class="btn btn-primary">
        <i class="fas fa-save"></i> Save Preferences
    </button>
</div>

<form method="POST" action="{{ route('preferences.update') }}" id="prefsForm">
    @csrf

    <div class="pref-layout">

        {{-- Sidebar Nav --}}
        <div class="pref-nav">
            <div class="card" style="margin-bottom:0;padding:8px;">
                <button type="button" class="pref-nav-item active" data-target="pref-display">
                    <div class="p-nav-icon"><i class="fas fa-desktop"></i></div>
                    <span>Display</span>
                </button>
                <button type="button" class="pref-nav-item" data-target="pref-workflow">
                    <div class="p-nav-icon"><i class="fas fa-bolt"></i></div>
                    <span>Workflow</span>
                </button>
                <button type="button" class="pref-nav-item" data-target="pref-search">
                    <div class="p-nav-icon"><i class="fas fa-search"></i></div>
                    <span>Search</span>
                </button>
            </div>
        </div>

        {{-- Panels --}}
        <div>

            {{-- Display Panel --}}
            <div class="pref-panel active" id="pref-display">
                <div class="card" style="margin-bottom:0;">
                    <div class="card-header">
                        <span style="display:flex;align-items:center;gap:10px;">
                            <div style="width:32px;height:32px;border-radius:8px;background:var(--info-light);display:flex;align-items:center;justify-content:center;font-size:14px;color:var(--info-text);flex-shrink:0;">
                                <i class="fas fa-desktop"></i>
                            </div>
                            <div>
                                <div style="font-weight:700;font-size:14px;">Display</div>
                                <div style="font-size:11.5px;font-weight:400;color:var(--text-muted);margin-top:1px;">Visual and formatting preferences</div>
                            </div>
                        </span>
                    </div>

                    {{-- Page Size --}}
                    <div class="pref-row">
                        <div>
                            <div style="font-weight:600;font-size:13.5px;color:var(--text-primary);">Default Page Size</div>
                            <div style="font-size:12.5px;color:var(--text-muted);margin-top:3px;">Number of rows shown per page in all tables</div>
                        </div>
                        <div>
                            <select name="page_size" class="form-control">
                                @foreach([10, 25, 50, 100] as $size)
                                <option value="{{ $size }}" {{ $user->getPreference('page_size', 25) == $size ? 'selected' : '' }}>
                                    {{ $size }} rows
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Date Format --}}
                    <div class="pref-row">
                        <div>
                            <div style="font-weight:600;font-size:13.5px;color:var(--text-primary);">Date Format</div>
                            <div style="font-size:12.5px;color:var(--text-muted);margin-top:3px;">How dates appear throughout the app</div>
                        </div>
                        <div>
                            <select name="date_format" class="form-control">
                                @foreach(['MM/DD/YYYY', 'DD/MM/YYYY', 'YYYY-MM-DD'] as $fmt)
                                <option value="{{ $fmt }}" {{ $user->getPreference('date_format', 'MM/DD/YYYY') === $fmt ? 'selected' : '' }}>
                                    {{ $fmt }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Workflow Panel --}}
            <div class="pref-panel" id="pref-workflow">
                <div class="card" style="margin-bottom:0;">
                    <div class="card-header">
                        <span style="display:flex;align-items:center;gap:10px;">
                            <div style="width:32px;height:32px;border-radius:8px;background:var(--warning-light);display:flex;align-items:center;justify-content:center;font-size:14px;color:var(--warning);flex-shrink:0;">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <div>
                                <div style="font-weight:700;font-size:14px;">Workflow</div>
                                <div style="font-size:11.5px;font-weight:400;color:var(--text-muted);margin-top:1px;">Auto-fill and form shortcuts</div>
                            </div>
                        </span>
                    </div>

                    {{-- Auto-fill archivist --}}
                    <label class="pref-row-toggle" style="display:grid;grid-template-columns:1fr auto;gap:16px;align-items:center;">
                        <div>
                            <div style="font-weight:600;font-size:13.5px;color:var(--text-primary);">Auto-fill my name as archivist</div>
                            <div style="font-size:12.5px;color:var(--text-muted);margin-top:3px;">
                                Pre-populate the archivist field with your name when creating records
                            </div>
                        </div>
                        <div style="flex-shrink:0;">
                            <input type="checkbox" name="auto_fill_archivist" value="1"
                                   {{ $user->getPreference('auto_fill_archivist', '1') == '1' ? 'checked' : '' }}
                                   style="width:16px;height:16px;accent-color:var(--accent);cursor:pointer;">
                        </div>
                    </label>

                    {{-- Auto-fill date --}}
                    <label class="pref-row-toggle" style="display:grid;grid-template-columns:1fr auto;gap:16px;align-items:center;">
                        <div>
                            <div style="font-weight:600;font-size:13.5px;color:var(--text-primary);">Auto-fill today's date</div>
                            <div style="font-size:12.5px;color:var(--text-muted);margin-top:3px;">
                                Pre-populate date fields with the current date when creating records
                            </div>
                        </div>
                        <div style="flex-shrink:0;">
                            <input type="checkbox" name="auto_fill_date" value="1"
                                   {{ $user->getPreference('auto_fill_date', '1') == '1' ? 'checked' : '' }}
                                   style="width:16px;height:16px;accent-color:var(--accent);cursor:pointer;">
                        </div>
                    </label>

                </div>
            </div>

            {{-- Search Panel --}}
            <div class="pref-panel" id="pref-search">
                <div class="card" style="margin-bottom:0;">
                    <div class="card-header">
                        <span style="display:flex;align-items:center;gap:10px;">
                            <div style="width:32px;height:32px;border-radius:8px;background:var(--success-light);display:flex;align-items:center;justify-content:center;font-size:14px;color:var(--success);flex-shrink:0;">
                                <i class="fas fa-search"></i>
                            </div>
                            <div>
                                <div style="font-weight:700;font-size:14px;">Search</div>
                                <div style="font-size:11.5px;font-weight:400;color:var(--text-muted);margin-top:1px;">Default search behaviour</div>
                            </div>
                        </span>
                    </div>

                    {{-- Default Search Type --}}
                    <div class="pref-row">
                        <div>
                            <div style="font-weight:600;font-size:13.5px;color:var(--text-primary);">Default Search Type</div>
                            <div style="font-size:12.5px;color:var(--text-muted);margin-top:3px;">
                                The pre-selected filter when you open the search bar
                            </div>
                        </div>
                        <div>
                            <select name="default_search_type" class="form-control">
                                @foreach(['Patient Name', 'MR Number', 'Case Number', 'Location'] as $type)
                                <option value="{{ $type }}" {{ $user->getPreference('default_search_type', 'Patient Name') === $type ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                </div>
            </div>

        </div>{{-- /panels --}}
    </div>{{-- /pref-layout --}}

</form>

@endsection

@push('scripts')
<script>
    document.querySelectorAll('.pref-nav-item').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.pref-nav-item').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.pref-panel').forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById(btn.dataset.target)?.classList.add('active');
        });
    });
</script>
@endpush