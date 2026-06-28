@extends('layouts.app')
@section('title', 'Scan Details')

@push('styles')
<style>
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
    .issue-card-body { padding: 14px 18px; }
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
</style>
@endpush

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            <a href="{{ route('admin.ai-health.index') }}">AI Health Monitor</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            Scan Detail
        </div>
        <h1>Scan Detail</h1>
        <p style="font-size:13px; color:var(--text-muted); margin-top:3px;">
            <code style="font-size:12px;">{{ $scanId }}</code>
            &mdash; {{ $logs->first()->created_at->format('l, F j, Y \a\t g:i A') }}
        </p>
    </div>
    <a href="{{ route('admin.ai-health.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

@php
    $fixed   = $logs->where('fix_status', 'success')->count();
    $skipped = $logs->where('fix_status', 'skipped')->count();
    $failed  = $logs->where('fix_status', 'failed')->count();
@endphp

<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:20px;">
    <div class="card" style="text-align:center; padding:16px; margin:0;">
        <div style="font-size:24px; font-weight:700; color:var(--text-primary);">{{ $logs->count() }}</div>
        <div style="font-size:12px; color:var(--text-muted);">Total Issues</div>
    </div>
    <div class="card" style="text-align:center; padding:16px; margin:0;">
        <div style="font-size:24px; font-weight:700; color:var(--success);">{{ $fixed }}</div>
        <div style="font-size:12px; color:var(--text-muted);">Fixed</div>
    </div>
    <div class="card" style="text-align:center; padding:16px; margin:0;">
        <div style="font-size:24px; font-weight:700; color:var(--text-muted);">{{ $skipped }}</div>
        <div style="font-size:12px; color:var(--text-muted);">Flagged</div>
    </div>
    <div class="card" style="text-align:center; padding:16px; margin:0;">
        <div style="font-size:24px; font-weight:700; color:var(--danger);">{{ $failed }}</div>
        <div style="font-size:12px; color:var(--text-muted);">Failed</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span><i class="fas fa-list-check"></i>&ensp;Issues</span>
    </div>
    <div style="padding:16px;">
        @foreach($logs->sortByDesc('severity') as $log)
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
            </div>
            <div class="issue-card-body">
                <div style="font-size:13.5px; color:var(--text-primary); margin-bottom:8px;">
                    {{ $log->issue_description }}
                </div>
                <div class="ai-reasoning">
                    <i class="fas fa-robot" style="font-size:11px; margin-right:4px; opacity:0.7;"></i>
                    {{ $log->ai_reasoning }}
                </div>
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

@endsection