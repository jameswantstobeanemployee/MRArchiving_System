@extends('layouts.app')
@section('title', 'Backup Logs')

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="page-header-left">
        <h1>Backup Logs</h1>
        <p style="font-size:13px; color:var(--text-muted); margin-top:3px;">
            {{ $backup->name }} — {{ $logs->total() }} run{{ $logs->total() !== 1 ? 's' : '' }} recorded
        </p>
    </div>
    <div class="d-flex gap-1">
        <form action="{{ route('admin.backup.run', $backup) }}" method="POST">
            @csrf
            <button type="button" 
                    class="btn btn-success" 
                    onclick="confirmRunNow(this.closest('form'), '{{ $backup->name }}')">
                <i class="fas fa-play"></i> Run Now
            </button>
        </form>
        <a href="{{ route('admin.backup.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

{{-- Schedule Overview --}}
<div class="card">
    <div class="card-header">
        <span><i class="fas fa-info-circle"></i>&ensp;Schedule Overview</span>
    </div>
    <div class="card-body">
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:20px;">
            <div>
                <div class="text-muted" style="font-size:11.5px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:6px;">
                    Type
                </div>
                <div style="font-size:13.5px; color:var(--text-primary);">
                    <span class="badge badge-info">
                        <i class="fas fa-{{ $backup->backup_type === 'database' ? 'database' : 'folder' }}"></i>
                        {{ $backup->backup_type === 'database' ? 'Database Only' : 'Database + Files' }}
                    </span>
                </div>
            </div>

            <div>
                <div class="text-muted" style="font-size:11.5px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:6px;">
                    Frequency
                </div>
                <div style="font-size:13.5px; color:var(--text-primary);">
                    {{ ucfirst($backup->frequency) }}
                    @if($backup->frequency === 'weekly')
                        — {{ ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'][$backup->day_of_week] }}
                    @elseif($backup->frequency === 'monthly')
                        — Day {{ $backup->day_of_month }}
                    @endif
                    at {{ substr($backup->time_of_day, 0, 5) }}
                </div>
            </div>

            <div>
                <div class="text-muted" style="font-size:11.5px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:6px;">
                    Destination
                </div>
                <div style="font-size:13.5px; color:var(--text-primary);">
                    {{ $backup->destinationDrive?->name ?? 'Not configured' }}
                </div>
            </div>

            <div>
                <div class="text-muted" style="font-size:11.5px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:6px;">
                    Status
                </div>
                <div style="font-size:13.5px; color:var(--text-primary);">
                    <span class="badge badge-{{ $backup->is_active ? 'success' : 'warning' }}">
                        <i class="fas fa-{{ $backup->is_active ? 'check-circle' : 'pause-circle' }}"></i>
                        {{ $backup->is_active ? 'Active' : 'Paused' }}
                    </span>
                </div>
            </div>

            @if($backup->next_run_at)
            <div>
                <div class="text-muted" style="font-size:11.5px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:6px;">
                    Next Run
                </div>
                <div style="font-size:13.5px; color:var(--text-primary);">
                    {{ $backup->next_run_at->format('M d, Y H:i') }}
                </div>
                <div class="text-muted" style="font-size:11.5px;">
                    {{ $backup->next_run_at->diffForHumans() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Run History --}}
<div class="card">
    <div class="card-header">
        <span><i class="fas fa-history"></i>&ensp;Run History</span>
    </div>
    @if($logs->isEmpty())
        <div class="empty-state">
            <i class="fas fa-clock-rotate-left empty-state-icon"></i>
            <h3>No backup runs yet</h3>
            <p>This schedule hasn't been executed yet. Click "Run Now" to start the first backup.</p>
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Started</th>
                    <th>Ended</th>
                    <th>Status</th>
                    <th>Files</th>
                    <th>Size</th>
                    <th>Duration</th>
                    <th>Error</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
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
                        @if($log->end_time)
                            <div style="font-size:13px; color:var(--text-secondary);">
                                {{ $log->end_time->format('M d, Y') }}
                            </div>
                            <div class="text-muted" style="font-size:11.5px;">
                                {{ $log->end_time->format('H:i:s') }}
                            </div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
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
                            <div style="color:var(--danger); font-size:12px; max-width:300px;" 
                                 title="{{ $log->error_message }}">
                                {{ $log->error_message }}
                            </div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Pagination --}}
        @if($logs->hasPages())
        <div class="card-body">
            {{ $logs->links() }}
        </div>
        @endif
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