@extends('layouts.app')
@section('title', 'Storage Usage Report')

@push('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .stat-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: var(--radius-lg);
        padding: 20px;
        box-shadow: var(--card-shadow);
        transition: all var(--transition-md);
        position: relative;
        overflow: hidden;
    }
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--card-shadow-hover);
    }
    .stat-card.info::before { background: var(--info); }
    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        margin-bottom: 14px;
    }
    .stat-card.info .stat-icon { background: var(--info-light); color: var(--info); }
    .stat-title {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 6px;
    }
    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--text-primary);
        letter-spacing: -0.02em;
        line-height: 1;
    }
    .drive-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: var(--radius-lg);
        margin-bottom: 20px;
        box-shadow: var(--card-shadow);
        transition: all var(--transition-md);
    }
    .drive-card:hover {
        box-shadow: var(--card-shadow-hover);
    }
    .drive-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--divider);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .drive-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .drive-badges {
        display: flex;
        gap: 8px;
    }
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 9px;
        border-radius: var(--radius-full);
        font-size: 11.5px;
        font-weight: 600;
        line-height: 1;
        white-space: nowrap;
    }
    .badge-primary {
        background: var(--info-light);
        color: var(--info-text);
    }
    .badge-success {
        background: var(--success-light);
        color: var(--success-text);
    }
    .badge-danger {
        background: var(--danger-light);
        color: var(--danger-text);
    }
    .drive-body {
        padding: 20px;
    }
    .drive-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-bottom: 16px;
    }
    .drive-info p {
        margin-bottom: 10px;
        font-size: 13.5px;
        color: var(--text-secondary);
    }
    .drive-info strong {
        color: var(--text-primary);
        font-weight: 600;
    }
    .drive-progress-section {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .progress-label {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
    }
    .progress {
        background: var(--border-color);
        border-radius: var(--radius-full);
        height: 28px;
        overflow: hidden;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
    }
    .progress-bar {
        height: 100%;
        border-radius: var(--radius-full);
        transition: width 0.4s cubic-bezier(0.4,0,0.2,1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12.5px;
        font-weight: 700;
        color: white;
        text-shadow: 0 1px 2px rgba(0,0,0,0.2);
    }
    .progress-bar.success {
        background: linear-gradient(90deg, var(--success) 0%, #10b981 100%);
    }
    .progress-bar.warning {
        background: linear-gradient(90deg, var(--warning) 0%, #f59e0b 100%);
    }
    .progress-bar.danger {
        background: linear-gradient(90deg, var(--danger) 0%, #ef4444 100%);
    }
    .alert-critical {
        margin-top: 10px;
        padding: 10px 14px;
        background: var(--danger-light);
        border: 1px solid var(--danger-border);
        border-radius: var(--radius-sm);
        color: var(--danger-text);
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .alert-warning {
        margin-top: 10px;
        padding: 10px 14px;
        background: var(--warning-light);
        border: 1px solid var(--warning-border);
        border-radius: var(--radius-sm);
        color: var(--warning-text);
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .drive-actions {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid var(--divider);
        display: flex;
        gap: 8px;
    }
    @media (max-width: 768px) {
        .drive-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
@endpush

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            <a href="{{ route('reports.index') }}">Reports</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            Storage Usage
        </div>
        <h1>Storage Usage Report</h1>
    </div>
    <a href="{{ route('reports.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Reports
    </a>
</div>

{{-- Stats Grid --}}
<div class="stats-grid">
    <div class="stat-card info">
        <div class="stat-icon">
            <i class="fas fa-archive"></i>
        </div>
        <div class="stat-title">Total Charts</div>
        <div class="stat-value">{{ number_format($total_charts) }}</div>
    </div>
    <div class="stat-card info">
        <div class="stat-icon">
            <i class="fas fa-database"></i>
        </div>
        <div class="stat-title">Total Archive Size</div>
        <div class="stat-value">
            @php
                $tb = $total_archived_size / 1099511627776;
                $gb = $total_archived_size / 1073741824;
                $mb = $total_archived_size / 1048576;
                echo $tb >= 1 ? number_format($tb, 2) . ' TB' : ($gb >= 1 ? number_format($gb, 2) . ' GB' : number_format($mb, 2) . ' MB');
            @endphp
        </div>
    </div>
    <div class="stat-card info">
        <div class="stat-icon">
            <i class="fas fa-hdd"></i>
        </div>
        <div class="stat-title">Primary Drives</div>
        <div class="stat-value">{{ $drives->where('is_primary', true)->count() }}</div>
    </div>
    <div class="stat-card info">
        <div class="stat-icon">
            <i class="fas fa-server"></i>
        </div>
        <div class="stat-title">Total Drives</div>
        <div class="stat-value">{{ $drives->count() }}</div>
    </div>
</div>

{{-- Drive Cards --}}
@foreach($drives as $drive)
@php
    $pct = $drive->used_percentage;
    $barClass = $pct >= 90 ? 'danger' : ($pct >= 80 ? 'warning' : 'success');
@endphp
<div class="drive-card">
    <div class="drive-header">
        <div class="drive-title">
            <i class="fas fa-hdd" style="color:var(--info)"></i>
            {{ $drive->name }}
        </div>
        <div class="drive-badges">
            @if($drive->is_primary)
                <span class="badge badge-primary">
                    <i class="fas fa-star"></i> Primary
                </span>
            @endif
            <span class="badge badge-{{ $drive->status === 'active' ? 'success' : 'danger' }}">
                <i class="fas fa-circle" style="font-size:7px"></i>
                {{ ucfirst($drive->status) }}
            </span>
        </div>
    </div>
    <div class="drive-body">
        <div class="drive-grid">
            <div class="drive-info">
                <p><strong>Path:</strong> <code style="background:var(--divider); padding:2px 6px; border-radius:4px; font-size:12px">{{ $drive->drive_path }}</code></p>
                <p><strong>Used:</strong> {{ $drive->used_space_formatted }} / {{ $drive->total_space_formatted }}</p>
                <p><strong>Available:</strong> {{ $drive->available_space_formatted }}</p>
                @if($drive->last_scanned_at)
                <p class="text-muted" style="font-size:12.5px; margin-top:8px">
                    <i class="fas fa-clock"></i> Last scanned: {{ $drive->last_scanned_at->format('m/d/Y H:i') }}
                </p>
                @endif
            </div>
            <div class="drive-progress-section">
                <div class="progress-label">{{ $pct }}% Used</div>
                <div class="progress">
                    <div class="progress-bar {{ $barClass }}" style="width:{{ $pct }}%">
                        {{ $pct }}%
                    </div>
                </div>
                @if($pct >= 90)
                <div class="alert-critical">
                    <i class="fas fa-exclamation-triangle"></i>
                    Critical — drive almost full
                </div>
                @elseif($pct >= 80)
                <div class="alert-warning">
                    <i class="fas fa-exclamation-circle"></i>
                    Warning — drive filling up
                </div>
                @endif
            </div>
        </div>
        @if(auth()->user()->isAdmin())
        <div class="drive-actions">
            <form action="{{ route('admin.storage.scan', $drive) }}" method="POST" style="display:inline">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm">
                    <i class="fas fa-sync"></i> Re-scan Drive
                </button>
            </form>
            <a href="{{ route('admin.storage.edit', $drive) }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-pen"></i> Edit
            </a>
        </div>
        @endif
    </div>
</div>
@endforeach

@if($drives->isEmpty())
<div class="card">
    <div class="empty-state">
        <i class="fas fa-hdd empty-state-icon"></i>
        <h3>No external drives configured</h3>
        <p>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.storage.create') }}" style="color:var(--accent); font-weight:600">Add a drive</a> to start tracking storage usage.
            @else
                Contact your administrator to add storage drives.
            @endif
        </p>
    </div>
</div>
@endif

@endsection