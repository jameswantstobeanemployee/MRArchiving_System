@extends('layouts.app')
@section('title', 'Drive File Scanner')
@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-hdd" style="color:var(--accent);margin-right:10px;font-size:20px;"></i>Drive File Scanner</h1>
        <p style="font-size:13px; color:var(--text-muted); margin-top:3px;">
            Scan physical files on each external drive and cross-reference with the database
        </p>
    </div>
    <a href="{{ route('admin.storage.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Storage
    </a>
</div>

{{-- Info Banner --}}
<div style="background:var(--info-light);border:1px solid var(--info-border);border-radius:var(--radius-md);padding:12px 16px;margin-bottom:24px;display:flex;align-items:center;gap:10px;font-size:13px;color:var(--info-text);">
    <i class="fas fa-info-circle" style="flex-shrink:0;font-size:15px;"></i>
    <span>Use <strong>Full Scan</strong> to get a complete report of matched, orphaned, and missing files. Use <strong>Search Files</strong> to find a specific file by name, patient, or case number.</span>
</div>

{{-- Drives Grid --}}
@forelse($drives as $drive)
<div class="card" style="margin-bottom:16px;">
    <div class="card-header">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:36px;height:36px;border-radius:8px;background:var(--info-light);display:flex;align-items:center;justify-content:center;color:var(--info-text);font-size:16px;flex-shrink:0;">
                <i class="fas fa-hdd"></i>
            </div>
            <div>
                <div style="font-weight:700;font-size:14px;color:var(--text-primary);">{{ $drive->name }}</div>
                <code style="font-size:11px;color:var(--text-muted);">{{ $drive->drive_path }}</code>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:6px;">
            @if($drive->is_primary)
                <span class="badge badge-info"><i class="fas fa-star"></i> Primary</span>
            @endif
            <span class="badge badge-{{ $drive->status === 'active' ? 'success' : 'danger' }}">
                <i class="fas fa-{{ $drive->status === 'active' ? 'check-circle' : 'times-circle' }}"></i>
                {{ ucfirst($drive->status) }}
            </span>
        </div>
    </div>
    <div class="card-body" style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
        <div style="font-size:12.5px;color:var(--text-muted);">
            <i class="fas fa-folder" style="margin-right:5px;color:var(--warning);"></i>
            Archives folder: <code>{{ rtrim($drive->drive_path, '/\\') }}\archives\</code>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <form action="{{ route('admin.scanner.scan', $drive) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Full Scan
                </button>
            </form>
            <a href="{{ route('admin.scanner.search', $drive) }}" class="btn btn-secondary">
                <i class="fas fa-search-plus"></i> Search Files
            </a>
        </div>
    </div>
</div>
@empty
<div class="card">
    <div class="empty-state">
        <i class="fas fa-hdd empty-state-icon"></i>
        <h3>No drives configured</h3>
        <p>Add an external drive to start scanning files.</p>
        <a href="{{ route('admin.storage.create') }}" class="btn btn-primary btn-sm mt-2">
            <i class="fas fa-plus"></i> Add a Drive
        </a>
    </div>
</div>
@endforelse

@endsection