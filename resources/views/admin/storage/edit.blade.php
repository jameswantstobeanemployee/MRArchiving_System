@extends('layouts.app')
@section('title', 'Edit Drive')
@section('content')

<div class="page-header">
    <div class="page-header-left">
        <div class="breadcrumb">
            <a href="{{ route('admin.storage.index') }}">Storage</a>
            <i class="fas fa-chevron-right" style="font-size:9px;"></i>
            <span>Edit Drive</span>
        </div>
        <h1>
            <i class="fas fa-hdd" style="color:var(--accent);margin-right:10px;font-size:20px;"></i>
            Edit Drive — {{ $drive->name }}
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
            <div style="display:flex;gap:6px;">
                @if($drive->is_primary)
                    <span class="badge badge-success"><i class="fas fa-star"></i> Primary</span>
                @endif
                <span class="badge badge-{{ $drive->status === 'active' ? 'success' : 'danger' }}">
                    {{ ucfirst($drive->status) }}
                </span>
            </div>
        </div>
        <div class="card-body" style="padding:0;">

            <form method="POST" action="{{ route('admin.storage.update', $drive) }}" id="editDriveForm">
                @csrf @method('PUT')

                {{-- Drive Name --}}
                <div style="padding:18px 20px;border-bottom:1px solid var(--divider);">
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Drive Name <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $drive->name) }}"
                               required>
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
                               value="{{ old('drive_path', $drive->drive_path) }}"
                               required>
                        @error('drive_path')
                            <div class="invalid-feedback" style="display:block;color:var(--danger);font-size:12px;margin-top:4px;">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                {{-- Status + Primary --}}
                <div style="padding:18px 20px;border-bottom:1px solid var(--divider);display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="active"   {{ old('status', $drive->status) === 'active'   ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $drive->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Primary Drive</label>
                        <label style="display:flex;align-items:center;gap:8px;margin-top:10px;cursor:pointer;">
                            <input type="checkbox" name="is_primary" value="1"
                                   {{ old('is_primary', $drive->is_primary) ? 'checked' : '' }}
                                   style="width:15px;height:15px;accent-color:var(--accent);">
                            <span style="font-size:13.5px;color:var(--text-primary);">Set as primary</span>
                        </label>
                    </div>
                </div>

                {{-- Read-only info --}}
                <div style="padding:14px 20px;background:var(--table-header-bg);display:flex;gap:24px;flex-wrap:wrap;">
                    @if($drive->last_scanned_at)
                    <div style="font-size:12.5px;color:var(--text-muted);">
                        <i class="fas fa-clock" style="margin-right:4px;"></i>
                        Last scanned: <strong>{{ $drive->last_scanned_at->format('m/d/Y H:i') }}</strong>
                    </div>
                    @endif
                    <div style="font-size:12.5px;color:var(--text-muted);">
                        <i class="fas fa-database" style="margin-right:4px;"></i>
                        Added: <strong>{{ $drive->created_at->format('m/d/Y') }}</strong>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:20px;">

        {{-- Left: Delete (hidden for primary drives) --}}
        @if(!$drive->is_primary)
            <form action="{{ route('admin.storage.destroy', $drive) }}" method="POST"
                  onsubmit="return confirm('Delete \'{{ addslashes($drive->name) }}\'? This cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete Drive
                </button>
            </form>
        @else
            <div style="font-size:12.5px;color:var(--text-muted);">
                <i class="fas fa-lock" style="margin-right:4px;"></i>
                Primary drives cannot be deleted
            </div>
        @endif

        {{-- Right: Cancel / Save --}}
        <div style="display:flex;gap:8px;">
            <a href="{{ route('admin.storage.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" form="editDriveForm" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>

    </div>
</div>

@endsection