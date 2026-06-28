@extends('layouts.app')
@section('title', 'System Settings')

@push('styles')
<style>
    .settings-layout {
        display: grid;
        grid-template-columns: 220px 1fr;
        gap: 20px;
        align-items: start;
    }

    .settings-nav {
        position: sticky;
        top: calc(var(--topbar-height) + 20px);
    }

    .settings-nav-item {
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

    .settings-nav-item:hover {
        background: var(--border-color);
        color: var(--text-primary);
    }

    .settings-nav-item.active {
        background: var(--info-light);
        color: var(--info-text);
        font-weight: 600;
    }

    .settings-nav-item .s-nav-icon {
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

    .settings-nav-item.active .s-nav-icon {
        background: rgba(37,99,235,0.12);
        color: var(--info);
    }

    .setting-row {
        display: grid;
        grid-template-columns: 1fr 260px;
        gap: 32px;
        align-items: center;
        padding: 18px 20px;
        transition: background var(--transition);
    }

    .setting-row:not(:last-child) {
        border-bottom: 1px solid var(--divider);
    }

    .setting-row:hover { background: var(--table-row-hover); }

    .settings-panel { display: none; }
    .settings-panel.active { display: block; }

    @media (max-width: 900px) {
        .settings-layout { grid-template-columns: 1fr; }
        .settings-nav { position: static; }
        .setting-row { grid-template-columns: 1fr; gap: 10px; }
    }
</style>
@endpush

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>
            <i class="fas fa-cog" style="color:var(--accent);margin-right:10px;font-size:20px;"></i>
            System Settings
        </h1>
        <p style="font-size:13px;color:var(--text-muted);margin-top:3px;">
            Configure system-wide behaviour and defaults
        </p>
    </div>
    <button type="submit" form="settingsForm" class="btn btn-primary">
        <i class="fas fa-save"></i> Save Changes
    </button>
</div>

<form method="POST" action="{{ route('admin.settings.update') }}" id="settingsForm">
    @csrf @method('POST')

    <div class="settings-layout">

        {{-- Sidebar Nav --}}
        <div class="settings-nav">
            <div class="card" style="margin-bottom:0;padding:8px;">
                @php $first = true; @endphp
                @foreach($settings as $category => $categorySettings)
                @php
                    $icon = match(strtolower($category)) {
                        'general'  => 'sliders-h',
                        'archive'  => 'archive',
                        'security' => 'shield-alt',
                        'email'    => 'envelope',
                        'storage'  => 'hdd',
                        'box'      => 'box',
                        'checkout' => 'exchange-alt',
                        default    => 'cog'
                    };
                @endphp
                <button type="button"
                        class="settings-nav-item {{ $first ? 'active' : '' }}"
                        data-target="panel-{{ Str::slug($category) }}">
                    <div class="s-nav-icon"><i class="fas fa-{{ $icon }}"></i></div>
                    <span>{{ ucfirst($category) }}</span>
                    <span style="margin-left:auto;font-size:11px;font-weight:700;opacity:0.5;">
                        {{ count($categorySettings) }}
                    </span>
                </button>
                @php $first = false; @endphp
                @endforeach
            </div>
        </div>

        {{-- Panels --}}
        <div>
            @php $first = true; @endphp
            @foreach($settings as $category => $categorySettings)
            @php
                $icon = match(strtolower($category)) {
                    'general'  => 'sliders-h',
                    'archive'  => 'archive',
                    'security' => 'shield-alt',
                    'email'    => 'envelope',
                    'storage'  => 'hdd',
                    'box'      => 'box',
                    'checkout' => 'exchange-alt',
                    default    => 'cog'
                };
            @endphp
            <div class="settings-panel {{ $first ? 'active' : '' }}"
                 id="panel-{{ Str::slug($category) }}">
                <div class="card" style="margin-bottom:0;">
                    <div class="card-header">
                        <span style="display:flex;align-items:center;gap:10px;">
                            <div style="width:32px;height:32px;border-radius:8px;background:var(--info-light);display:flex;align-items:center;justify-content:center;font-size:14px;color:var(--info-text);flex-shrink:0;">
                                <i class="fas fa-{{ $icon }}"></i>
                            </div>
                            <div>
                                <div style="font-weight:700;font-size:14px;">{{ ucfirst($category) }} Settings</div>
                                <div style="font-size:11.5px;font-weight:400;color:var(--text-muted);margin-top:1px;">
                                    {{ count($categorySettings) }} {{ Str::plural('setting', count($categorySettings)) }}
                                </div>
                            </div>
                        </span>
                    </div>

                    @foreach($categorySettings as $setting)
                    <div class="setting-row">
                        {{-- Label side --}}
                        <div>
                            <div style="font-weight:600;font-size:13.5px;color:var(--text-primary);">
                                {{ ucwords(str_replace('_', ' ', str_replace(strtolower($category).'_', '', $setting->setting_key))) }}
                            </div>
                            @if($setting->description)
                            <div style="font-size:12.5px;color:var(--text-muted);margin-top:3px;line-height:1.5;">
                                {{ $setting->description }}
                            </div>
                            @endif
                            <div style="display:flex;align-items:center;gap:6px;margin-top:7px;">
                                <span class="badge badge-info" style="font-size:10px;padding:2px 7px;">{{ $setting->setting_type }}</span>
                                <code style="font-size:10.5px;opacity:0.7;">{{ $setting->setting_key }}</code>
                            </div>
                        </div>

                        {{-- Control side --}}
                        <div>
                            @if($setting->setting_type === 'boolean')
                                <select name="{{ $setting->setting_key }}" class="form-control">
                                    <option value="1" {{ $setting->setting_value == '1' ? 'selected' : '' }}>✓ Enabled</option>
                                    <option value="0" {{ $setting->setting_value == '0' ? 'selected' : '' }}>✗ Disabled</option>
                                </select>
                            @elseif($setting->setting_type === 'integer')
                                <input type="number" name="{{ $setting->setting_key }}"
                                       class="form-control" value="{{ $setting->setting_value }}">
                            @else
                                <input type="text" name="{{ $setting->setting_key }}"
                                       class="form-control" value="{{ $setting->setting_value }}">
                            @endif

                            @if($setting->setting_key === 'box_default_capacity')
                            <div style="margin-top:10px;padding:11px 13px;background:var(--danger-light);border:1px solid var(--danger-border);border-radius:var(--radius-md);">
                                <label style="display:flex;align-items:flex-start;gap:8px;cursor:pointer;margin-bottom:0;">
                                    <input type="checkbox" name="apply_capacity_to_existing" value="1"
                                           style="margin-top:2px;flex-shrink:0;accent-color:var(--danger);">
                                    <div>
                                        <div style="font-weight:600;font-size:12.5px;color:var(--danger-text);">Apply to all existing boxes</div>
                                        <div style="font-size:11.5px;color:var(--danger-text);opacity:0.85;margin-top:2px;">
                                            <i class="fas fa-exclamation-triangle" style="font-size:10px;margin-right:3px;"></i>
                                            This will overwrite every box's capacity.
                                        </div>
                                    </div>
                                </label>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach

                </div>
            </div>
            @php $first = false; @endphp
            @endforeach
        </div>

    </div>
</form>

@endsection

@push('scripts')
<script>
    document.querySelectorAll('.settings-nav-item').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.settings-nav-item').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.settings-panel').forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById(btn.dataset.target)?.classList.add('active');
        });
    });
</script>
@endpush
