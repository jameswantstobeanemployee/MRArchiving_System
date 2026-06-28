@extends('layouts.app')
@section('title', 'AI System Health')

@push('styles')
<style>
    .health-hero {
        background: var(--info-light);
        border: 1px solid var(--info-border);
        border-radius: var(--radius-lg);
        padding: 28px 32px;
        display: flex;
        align-items: center;
        gap: 24px;
        margin-bottom: 24px;
    }
    .health-hero-icon {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        background: var(--accent);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        color: white;
        flex-shrink: 0;
    }
    .health-hero-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 4px;
    }
    .health-hero-sub {
        font-size: 13px;
        color: var(--text-muted);
        line-height: 1.5;
    }
    .scan-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 24px;
    }
    .scan-stat {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: var(--radius-md);
        padding: 16px 20px;
        text-align: center;
    }
    .scan-stat-num {
        font-size: 28px;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 4px;
    }
    .scan-stat-label {
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 500;
    }
    .issue-card {
        border: 1px solid var(--card-border);
        border-radius: var(--radius-md);
        overflow: hidden;
        margin-bottom: 12px;
        background: var(--card-bg);
    }
    .issue-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        border-bottom: 1px solid var(--divider);
        background: var(--table-header-bg);
    }
    .issue-card-body {
        padding: 14px 18px;
    }
    .issue-source-badge {
        font-size: 10px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 4px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .source-log      { background: #f1f0fe; color: #534AB7; }
    .source-database { background: #e1f5ee; color: #0F6E56; }
    .ai-reasoning {
        font-size: 13px;
        color: var(--text-secondary);
        line-height: 1.6;
        border-left: 3px solid var(--accent);
        padding-left: 12px;
        margin: 10px 0;
        font-style: italic;
    }
    .fix-result {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 20px;
        margin-top: 8px;
    }
    .fix-success { background: var(--success-light); color: var(--success-text); }
    .fix-skipped { background: var(--table-header-bg); color: var(--text-muted); }
    .fix-failed  { background: var(--danger-light); color: var(--danger-text); }
    .payload-detail {
        font-size: 11.5px;
        color: var(--text-muted);
        margin-top: 6px;
        font-family: var(--font-mono, monospace);
    }
    .past-scan-row {
        display: grid;
        grid-template-columns: 1fr auto auto auto auto;
        align-items: center;
        gap: 16px;
        padding: 12px 18px;
        border-bottom: 1px solid var(--divider);
        font-size: 13px;
    }
    .past-scan-row:last-child { border-bottom: none; }
    .healthy-state {
        text-align: center;
        padding: 60px 20px;
    }
    .healthy-state i {
        font-size: 48px;
        color: var(--success);
        opacity: 0.6;
        margin-bottom: 16px;
        display: block;
    }
    @media (max-width: 768px) {
        .scan-stats { grid-template-columns: repeat(2, 1fr); }
        .health-hero { flex-direction: column; text-align: center; }
    }
</style>
@endpush

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            AI Health Monitor
        </div>
        <h1>AI Health Monitor</h1>
    </div>
    <form action="{{ route('admin.ai-health.scan') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-primary" id="scanBtn">
            <i class="fas fa-robot"></i> Run AI Scan
        </button>
    </form>
</div>

{{-- Hero --}}
<div class="health-hero">
    <div class="health-hero-icon">
        <i class="fas fa-robot"></i>
    </div>
    <div>
        <div class="health-hero-title">AI System Health Monitor</div>
        <div class="health-hero-sub">
            Scans Laravel error logs and database records for inconsistencies, then uses AI to diagnose each issue and automatically apply fixes where possible.
            Log-level errors are flagged for your review; data integrity issues are fixed automatically.
        </div>
    </div>
    @if($latestScanLogs->isNotEmpty())
    <div style="margin-left:auto; flex-shrink:0; text-align:right;">
        <div style="font-size:11px; color:var(--text-muted);">Last scan</div>
        <div style="font-size:13px; font-weight:600; color:var(--text-primary);">
            {{ $latestScanLogs->first()->created_at->diffForHumans() }}
        </div>
    </div>
    @endif
</div>

@if($latestScanLogs->isEmpty())

    {{-- No scans yet --}}
    <div class="card">
        <div class="healthy-state">
            <i class="fas fa-shield-alt"></i>
            <h3 style="margin-bottom:8px;">No scans run yet</h3>
            <p style="color:var(--text-muted); margin-bottom:20px;">Click "Run AI Scan" to analyse your system for errors and data issues.</p>
        </div>
    </div>

@else

    {{-- Latest scan stats --}}
    @php
        $total   = $latestScanLogs->count();
        $fixed   = $latestScanLogs->where('fix_status', 'success')->count();
        $skipped = $latestScanLogs->where('fix_status', 'skipped')->count();
        $failed  = $latestScanLogs->where('fix_status', 'failed')->count();
        $critical = $latestScanLogs->whereIn('severity', ['critical', 'error'])->count();
    @endphp

    <div class="scan-stats">
        <div class="scan-stat">
            <div class="scan-stat-num" style="color:var(--text-primary);">{{ $total }}</div>
            <div class="scan-stat-label">Issues Found</div>
        </div>
        <div class="scan-stat">
            <div class="scan-stat-num" style="color:var(--success);">{{ $fixed }}</div>
            <div class="scan-stat-label">Auto-Fixed</div>
        </div>
        <div class="scan-stat">
            <div class="scan-stat-num" style="color:var(--text-muted);">{{ $skipped }}</div>
            <div class="scan-stat-label">Flagged Only</div>
        </div>
        <div class="scan-stat">
            <div class="scan-stat-num" style="color:var(--danger);">{{ $failed }}</div>
            <div class="scan-stat-label">Fix Failed</div>
        </div>
    </div>

    {{-- Latest scan issues --}}
    <div class="card" style="margin-bottom:24px;">
        <div class="card-header">
            <span><i class="fas fa-list-check"></i>&ensp;Latest Scan Results</span>
            <span style="font-size:12px; color:var(--text-muted);">
                Scan ID: <code style="font-size:11px;">{{ substr($latestScanId, 0, 8) }}...</code>
            </span>
        </div>
        <div style="padding:16px;">
            @foreach($latestScanLogs->sortByDesc('severity') as $log)
            <div class="issue-card">
                <div class="issue-card-header">
                    <span class="badge badge-{{ $log->severity_color }}">
                        <i class="fas fa-{{ $log->severity === 'critical' ? 'exclamation-circle' : ($log->severity === 'error' ? 'times-circle' : 'exclamation-triangle') }}"></i>
                        {{ strtoupper($log->severity) }}
                    </span>
                    <span class="issue-source-badge source-{{ $log->source }}">
                        {{ $log->source === 'log' ? 'Laravel Log' : 'Database' }}
                    </span>
                    <span style="font-size:13px; font-weight:600; color:var(--text-primary); flex:1;">
                        {{ ucwords(str_replace('_', ' ', $log->issue_type)) }}
                    </span>
                    <span style="font-size:11.5px; color:var(--text-muted);">
                        {{ $log->created_at->format('H:i:s') }}
                    </span>
                </div>
                <div class="issue-card-body">
                    <div style="font-size:13.5px; color:var(--text-primary); margin-bottom:8px;">
                        {{ $log->issue_description }}
                    </div>
                    <div class="ai-reasoning">
                        <i class="fas fa-robot" style="font-size:11px; margin-right:4px; opacity:0.7;"></i>
                        {{ $log->ai_reasoning }}
                    </div>

                    {{-- Fix result --}}
                    @if($log->fix_status === 'success')
                        <span class="fix-result fix-success">
                            <i class="fas fa-check-circle"></i> Fixed automatically
                        </span>
                        @if(!empty($log->fix_payload))
                            <div class="payload-detail">
                                @foreach($log->fix_payload as $k => $v)
                                    {{ ucwords(str_replace('_', ' ', $k)) }}: {{ is_array($v) ? implode(', ', $v) : $v }} &nbsp;
                                @endforeach
                            </div>
                        @endif
                    @elseif($log->fix_status === 'skipped')
                        <span class="fix-result fix-skipped">
                            <i class="fas fa-flag"></i> Flagged for review
                        </span>
                        @if(!empty($log->fix_payload['reason']))
                            <div class="payload-detail">{{ $log->fix_payload['reason'] }}</div>
                        @endif
                    @elseif($log->fix_status === 'failed')
                        <span class="fix-result fix-failed">
                            <i class="fas fa-times-circle"></i> Fix failed
                        </span>
                        @if($log->fix_error)
                            <div class="payload-detail" style="color:var(--danger);">{{ $log->fix_error }}</div>
                        @endif
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

@endif

{{-- Past Scans --}}
@if($pastScans->count() > 1)
<div class="card">
    <div class="card-header">
        <span><i class="fas fa-history"></i>&ensp;Scan History</span>
    </div>
    <div class="past-scan-row" style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); letter-spacing:0.04em; background:var(--table-header-bg);">
        <span>Scan ID</span>
        <span>Time</span>
        <span>Issues</span>
        <span>Fixed</span>
        <span></span>
    </div>
    @foreach($pastScans as $scan)
    <div class="past-scan-row">
        <code style="font-size:11px; color:var(--text-muted);">{{ substr($scan->scan_id, 0, 8) }}...</code>
        <span style="color:var(--text-secondary);">{{ \Carbon\Carbon::parse($scan->scanned_at)->diffForHumans() }}</span>
        <span class="badge badge-info">{{ $scan->total_issues }}</span>
        <span class="badge badge-success">{{ $scan->fixed_count }} fixed</span>
        <a href="{{ route('admin.ai-health.show', $scan->scan_id) }}" class="btn btn-secondary btn-sm">
            View <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    @endforeach
</div>
@endif

@endsection

@push('scripts')
<script>
document.querySelector('form').addEventListener('submit', function() {
    const btn = document.getElementById('scanBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Scanning...';
});
</script>
@endpush