@extends('layouts.app')
@section('title', 'Add Drive')
@section('content')

<div class="page-header">
    <div class="page-header-left">
        <div class="breadcrumb">
            <a href="{{ route('admin.storage.index') }}">Storage</a>
            <i class="fas fa-chevron-right" style="font-size:9px;"></i>
            <span>Add Drive</span>
        </div>
        <h1>
            <i class="fas fa-hdd" style="color:var(--accent);margin-right:10px;font-size:20px;"></i>
            Add External Drive
        </h1>
    </div>
    <a href="{{ route('admin.storage.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div style="max-width:600px;">
    <div class="card">
        <div class="card-header">
            <span>
                <i class="fas fa-hdd" style="color:var(--text-muted);margin-right:6px;"></i>
                Drive Details
            </span>
        </div>
        <div class="card-body" style="padding:0;">

            <form method="POST" action="{{ route('admin.storage.store') }}" id="driveForm">
                @csrf

                {{-- Drive Name --}}
                <div style="padding:18px 20px;border-bottom:1px solid var(--divider);">
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Drive Name <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}"
                               required
                               placeholder="e.g. Primary Archive Drive">
                        @error('name')
                            <div class="invalid-feedback" style="display:block;color:var(--danger);font-size:12px;margin-top:4px;">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                {{-- Drive Path --}}
                <div style="padding:18px 20px;border-bottom:1px solid var(--divider);">
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Drive Path <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="drive_path"
                               class="form-control @error('drive_path') is-invalid @enderror"
                               value="{{ old('drive_path') }}"
                               required
                               placeholder="e.g. D:\ or /mnt/archive">
                        <div class="form-help" style="margin-top:5px;">Full path to the archive drive or network share</div>
                        @error('drive_path')
                            <div class="invalid-feedback" style="display:block;color:var(--danger);font-size:12px;margin-top:4px;">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                {{-- Primary Drive --}}
                <div style="padding:18px 20px;">
                    <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;">
                        <input type="checkbox" name="is_primary" value="1"
                               {{ old('is_primary') ? 'checked' : '' }}
                               style="width:15px;height:15px;margin-top:2px;flex-shrink:0;accent-color:var(--accent);">
                        <div>
                            <div style="font-weight:600;font-size:13.5px;color:var(--text-primary);">Set as Primary Drive</div>
                            <div style="font-size:12.5px;color:var(--text-muted);margin-top:2px;">
                                New PDF uploads will be saved to the primary drive by default
                            </div>
                        </div>
                    </label>
                </div>

            </form>
        </div>
    </div>

    <div style="display:flex;justify-content:flex-end;gap:8px;margin-bottom:20px;">
        <a href="{{ route('admin.storage.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" form="driveForm" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Drive
        </button>
    </div>
</div>

@endsection