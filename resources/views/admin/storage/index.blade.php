@extends('layouts.app')
@section('title', 'Storage Management')
@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>
            <i class="fas fa-hdd" style="color:var(--accent);margin-right:10px;font-size:20px;"></i>
            Storage Management
        </h1>
        <p style="font-size:13px;color:var(--text-muted);margin-top:3px;">
            Manage external archive drives and monitor storage capacity
        </p>
    </div>
    <a href="{{ route('admin.storage.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Drive
    </a>
</div>

@forelse($drives as $drive)
@php
    $pct      = $drive->used_percentage;
    $barClass = $pct >= 90 ? 'danger' : ($pct >= 80 ? 'warning' : 'info');
    $barColor = $pct >= 90 ? 'var(--danger)' : ($pct >= 80 ? 'var(--warning)' : 'var(--info)');
@endphp
<div class="card">
    <div class="card-header">
        <span style="display:flex;align-items:center;gap:10px;">
            <div style="width:34px;height:34px;border-radius:8px;background:var(--info-light);display:flex;align-items:center;justify-content:center;color:var(--info-text);font-size:16px;flex-shrink:0;">
                <i class="fas fa-hdd"></i>
            </div>
            <div>
                <div style="font-weight:700;font-size:14px;color:var(--text-primary);">{{ $drive->name }}</div>
                <code style="font-size:11px;">{{ $drive->drive_path }}</code>
            </div>
        </span>
        <div style="display:flex;align-items:center;gap:6px;">
            @if($drive->is_primary)
                <span class="badge badge-success"><i class="fas fa-star"></i> Primary</span>
            @endif
            <span class="badge badge-{{ $drive->status === 'active' ? 'success' : 'danger' }}">
                <i class="fas fa-{{ $drive->status === 'active' ? 'check-circle' : 'times-circle' }}"></i>
                {{ ucfirst($drive->status) }}
            </span>
        </div>
    </div>
    <div class="card-body">

        {{-- Storage bar --}}
        <div style="margin-bottom:16px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                <span style="font-size:12.5px;font-weight:600;color:var(--text-secondary);">Storage Usage</span>
                <span style="font-size:12.5px;font-weight:700;color:{{ $barColor }};">{{ $pct }}%</span>
            </div>
            <div class="progress" style="height:8px;">
                <div class="progress-bar {{ $barClass }}" style="width:{{ $pct }}%;"></div>
            </div>
            <div style="display:flex;justify-content:space-between;margin-top:6px;font-size:12px;color:var(--text-muted);">
                <span>{{ $drive->used_space_formatted }} used</span>
                <span>{{ $drive->available_space_formatted }} free of {{ $drive->total_space_formatted }}</span>
            </div>
        </div>

        {{-- Meta row --}}
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div style="font-size:12.5px;color:var(--text-muted);">
                @if($drive->last_scanned_at)
                    <i class="fas fa-clock" style="margin-right:4px;"></i>
                    Last scanned: {{ $drive->last_scanned_at->format('m/d/Y H:i') }}
                @else
                    <i class="fas fa-clock" style="margin-right:4px;"></i>
                    Never scanned
                @endif
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <a href="{{ route('admin.storage.edit', $drive) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route('admin.storage.scan', $drive) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm">
                        <i class="fas fa-sync-alt"></i> Scan
                    </button>
                </form>
                @if(!$drive->is_primary)
                <form action="{{ route('admin.storage.set-primary', $drive) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-info btn-sm"
                            onclick="return confirm('Set {{ $drive->name }} as the primary drive?')">
                        <i class="fas fa-star"></i> Set Primary
                    </button>
                </form>
                <form action="{{ route('admin.storage.destroy', $drive) }}" method="POST"
                    style="display:inline;" class="delete-form">
                    @csrf @method('DELETE')
                    <button type="button" class="btn btn-danger btn-sm delete-btn"
                            data-name="{{ $drive->name }}">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
                @endif 
            </div>
        </div>

    </div>
</div>
@empty
<div class="card">
    <div class="empty-state">
        <i class="fas fa-hdd empty-state-icon"></i>
        <h3>No drives configured</h3>
        <p>Add an external drive to start managing archive storage.</p>
        <a href="{{ route('admin.storage.create') }}" class="btn btn-primary btn-sm mt-2">
            <i class="fas fa-plus"></i> Add a Drive
        </a>
    </div>
</div>
@endforelse

@push('scripts')
<script>
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const name = this.dataset.name;
        const form = this.closest('form');

        Swal.fire({
            title: 'Delete Drive?',
            html: `Are you sure you want to delete <strong>${name}</strong>?<br>
                   <span style="font-size:13px;color:#888;">This action cannot be undone.</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash"></i> Yes, Delete',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            focusCancel: true,
        }).then(result => {
            if (result.isConfirmed) form.submit();
        });
    });
});
</script>
@endpush

@endsection