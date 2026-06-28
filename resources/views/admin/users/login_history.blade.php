@extends('layouts.app')
@section('title', 'Login History')
@section('content')

<div class="page-header">
    <div class="page-header-left">
        <div class="breadcrumb">
            <a href="{{ route('admin.users.index') }}">Users</a>
            <i class="fas fa-chevron-right" style="font-size:9px;"></i>
            <span>Login History</span>
        </div>
        <h1>
            <i class="fas fa-history" style="color:var(--accent);margin-right:10px;font-size:20px;"></i>
            Login History — {{ $user->name }}
        </h1>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

{{-- User summary card --}}
<div class="card" style="margin-bottom:20px;">
    <div class="card-body" style="display:flex;align-items:center;gap:16px;padding:16px 20px;flex-wrap:wrap;">
        <div style="width:44px;height:44px;border-radius:10px;background:linear-gradient(135deg,#2563eb,#7c3aed);display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:700;color:white;flex-shrink:0;">
            {{ strtoupper(substr($user->name, 0, 2)) }}
        </div>
        <div style="flex:1;min-width:0;">
            <div style="font-weight:700;font-size:14px;color:var(--text-primary);">{{ $user->name }}</div>
            <div style="font-size:12.5px;color:var(--text-muted);">{{ $user->email }}</div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <span class="badge badge-{{ $user->role === 'admin' ? 'warning' : 'info' }}">
                <i class="fas fa-{{ $user->role === 'admin' ? 'user-shield' : 'user' }}"></i>
                {{ ucfirst($user->role) }}
            </span>
            <span class="badge badge-{{ $user->is_active ? 'success' : 'danger' }}">
                {{ $user->is_active ? 'Active' : 'Inactive' }}
            </span>
            <span class="badge badge-info">
                <i class="fas fa-history"></i> {{ $logs->total() }} {{ Str::plural('event', $logs->total()) }}
            </span>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span>
            <i class="fas fa-list" style="color:var(--text-muted);margin-right:6px;"></i>
            Login / Logout Events
            <span class="badge badge-info" style="margin-left:6px;">{{ $logs->total() }}</span>
        </span>
    </div>

    @if($logs->isEmpty())
        <div class="empty-state">
            <i class="fas fa-history empty-state-icon"></i>
            <h3>No login history</h3>
            <p>No login or logout events have been recorded for this user.</p>
        </div>
    @else
    <table class="data-table">
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>Action</th>
                <th>IP Address</th>
                <th>User Agent</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr>
                <td>
                    <div style="font-size:13px;font-weight:500;">{{ $log->created_at->format('m/d/Y') }}</div>
                    <div style="font-size:12px;color:var(--text-muted);">{{ $log->created_at->format('H:i:s') }}</div>
                </td>
                <td>
                    <span class="badge badge-{{ $log->action === 'login' ? 'success' : 'info' }}">
                        <i class="fas fa-{{ $log->action === 'login' ? 'sign-in-alt' : 'sign-out-alt' }}"></i>
                        {{ ucfirst($log->action) }}
                    </span>
                </td>
                <td>
                    @if($log->ip_address)
                        <code style="font-size:12px;">{{ $log->ip_address }}</code>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    <div style="font-size:12px;color:var(--text-muted);max-width:320px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                         title="{{ $log->user_agent }}">
                        {{ $log->user_agent ?? '—' }}
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($logs->hasPages())
    <div class="card-body" style="border-top:1px solid var(--divider);">
        {{ $logs->links() }}
    </div>
    @endif
</div>

@endsection