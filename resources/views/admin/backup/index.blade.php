@extends('layouts.app')
@section('title', 'Backup Management')

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="page-header-left">
        <h1>Backup Management</h1>
        <p style="font-size:13px; color:var(--text-muted); margin-top:3px;">
            Configure automated backup schedules and monitor backup health
        </p>
    </div>
    <a href="{{ route('admin.backup.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> New Schedule
    </a>
</div>

{{-- Backup Schedules --}}
<div class="card">
    <div class="card-header">
        <span><i class="fas fa-calendar-alt"></i>&ensp;Backup Schedules</span>
    </div>
    @if($configs->isEmpty())
        <div class="empty-state">
            <i class="fas fa-calendar-plus empty-state-icon"></i>
            <h3>No backup schedules configured</h3>
            <p>Create your first automated backup schedule to protect your data.</p>
            <a href="{{ route('admin.backup.create') }}" class="btn btn-primary btn-sm mt-2">
                <i class="fas fa-plus"></i> Create Schedule
            </a>
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Frequency</th>
                    <th>Time</th>
                    <th>Destination</th>
                    <th>Last Run</th>
                    <th>Next Run</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($configs as $cfg)
                @php $last = $cfg->latestLog; @endphp
                <tr>
                    <td>
                        <div style="font-weight:600; font-size:13.5px; color:var(--text-primary);">
                            {{ $cfg->name }}
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-info">
                            <i class="fas fa-{{ $cfg->backup_type === 'database' ? 'database' : 'folder' }}"></i>
                            {{ $cfg->backup_type === 'database' ? 'DB Only' : 'DB + Files' }}
                        </span>
                    </td>
                    <td>
                        <div style="font-size:13px; color:var(--text-secondary);">
                            {{ ucfirst($cfg->frequency) }}
                        </div>
                    </td>
                    <td>
                        <code style="font-size:12px;">{{ substr($cfg->time_of_day, 0, 5) }}</code>
                    </td>
                    <td>
                        <div style="font-size:12.5px; color:var(--text-secondary);">
                            {{ $cfg->destinationDrive?->name ?? '—' }}
                        </div>
                    </td>
                    <td>
                        @if($last)
                            <div style="font-size:12.5px; color:var(--text-secondary);">
                                {{ $last->start_time->format('M d, Y H:i') }}
                            </div>
                            <span class="badge badge-{{ $last->status === 'success' ? 'success' : 'danger' }}" style="margin-top:2px;">
                                <i class="fas fa-{{ $last->status === 'success' ? 'check-circle' : 'exclamation-circle' }}"></i>
                                {{ ucfirst($last->status) }}
                            </span>
                        @else
                            <span class="text-muted" style="font-size:12px;">Never run</span>
                        @endif
                    </td>
                    <td>
                        @if($cfg->next_run_at)
                            <div style="font-size:12.5px; color:var(--text-primary);">
                                {{ $cfg->next_run_at->format('M d, Y') }}
                            </div>
                            <div class="text-muted" style="font-size:11.5px;">
                                {{ $cfg->next_run_at->format('H:i') }}
                            </div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-{{ $cfg->is_active ? 'success' : 'warning' }}">
                            <i class="fas fa-{{ $cfg->is_active ? 'check-circle' : 'pause-circle' }}"></i>
                            {{ $cfg->is_active ? 'Active' : 'Paused' }}
                        </span>
                    </td>
                    <td>
                        <div class="row-actions">
                            <a href="{{ route('admin.backup.edit', $cfg) }}" 
                               class="action-btn" 
                               title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="{{ route('admin.backup.logs', $cfg) }}" 
                               class="action-btn" 
                               title="View Logs">
                                <i class="fas fa-history"></i>
                            </a>
                            <form action="{{ route('admin.backup.run', $cfg) }}" 
                                  method="POST" 
                                  style="display:inline;">
                                @csrf
                                <button type="button" 
                                        class="action-btn" 
                                        onclick="confirmRunNow(this.closest('form'), '{{ $cfg->name }}')"
                                        title="Run Now">
                                    <i class="fas fa-play"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.backup.destroy', $cfg) }}" 
                                  method="POST" 
                                  style="display:inline;">
                                @csrf 
                                @method('DELETE')
                                <button type="button" 
                                        class="action-btn danger" 
                                        onclick="confirmDelete(this.closest('form'), 'This backup schedule will be permanently deleted.')"
                                        title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- Recent Backup Runs --}}
<div class="card">
    <div class="card-header">
        <span><i class="fas fa-history"></i>&ensp;Recent Backup Runs</span>
    </div>
    @if($recentLogs->isEmpty())
        <div class="empty-state">
            <i class="fas fa-clock-rotate-left empty-state-icon"></i>
            <h3>No backup runs yet</h3>
            <p>Backup activity will appear here once schedules start running.</p>
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Started</th>
                    <th>Schedule</th>
                    <th>Status</th>
                    <th>Files</th>
                    <th>Size</th>
                    <th>Duration</th>
                    <th>Error</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentLogs as $log)
                <tr class="{{ $log->status === 'failed' ? 'backup-failed-row' : '' }}">
                    <td>
                        <div style="font-size:13px; color:var(--text-primary);">
                            {{ $log->start_time->format('M d, Y') }}
                        </div>
                        <div class="text-muted" style="font-size:11.5px;">
                            {{ $log->start_time->format('H:i:s') }}
                        </div>
                    </td>
                    <td>
                        <div style="font-size:13px; color:var(--text-secondary);">
                            {{ $log->configuration?->name ?? '—' }}
                        </div>
                    </td>
                    <td>
                        @php
                            $statusClass = match($log->status) {
                                'success' => 'success',
                                'running' => 'warning',
                                'failed' => 'danger',
                                default => 'info'
                            };
                            $statusIcon = match($log->status) {
                                'success' => 'check-circle',
                                'running' => 'spinner',
                                'failed' => 'exclamation-circle',
                                default => 'circle'
                            };
                        @endphp
                        <span class="badge badge-{{ $statusClass }}">
                            <i class="fas fa-{{ $statusIcon }}"></i>
                            {{ ucfirst($log->status) }}
                        </span>
                    </td>
                    <td>
                        <div style="font-size:13px; color:var(--text-secondary);">
                            {{ number_format($log->files_count) }}
                        </div>
                    </td>
                    <td>
                        <div style="font-size:13px; color:var(--text-secondary);">
                            @php
                                $mb = $log->total_size / 1048576;
                                echo $mb >= 1024 ? number_format($mb/1024, 2).' GB' : number_format($mb, 2).' MB';
                            @endphp
                        </div>
                    </td>
                    <td>
                        <div style="font-size:13px; color:var(--text-secondary);">
                            {{ $log->duration ?? '—' }}
                        </div>
                    </td>
                    <td>
                        @if($log->error_message)
                            <div style="color:var(--danger); font-size:12px;" 
                                 title="{{ $log->error_message }}">
                                {{ Str::limit($log->error_message, 60) }}
                            </div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@endsection

@push('styles')
<style>
    /* Backup failed row tint */
    .backup-failed-row {
        background: color-mix(in srgb, var(--danger-light) 30%, transparent);
    }

    .backup-failed-row:hover {
        background: color-mix(in srgb, var(--danger-light) 50%, transparent) !important;
    }
</style>
@endpush