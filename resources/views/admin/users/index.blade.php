@extends('layouts.app')
@section('title', 'User Management')
@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>
            <i class="fas fa-user-shield" style="color:var(--accent);margin-right:10px;font-size:20px;"></i>
            User Management
        </h1>
        <p style="font-size:13px;color:var(--text-muted);margin-top:3px;">
            Manage system accounts and access levels
        </p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> New User
    </a>
</div>

<div class="card">
    <div class="card-header">
        <span>
            <i class="fas fa-users" style="color:var(--text-muted);margin-right:6px;"></i>
            Users
            <span class="badge badge-info" style="margin-left:6px;">{{ $users->total() }}</span>
        </span>
    </div>
    @if($users->isEmpty())
        <div class="empty-state">
            <i class="fas fa-users empty-state-icon"></i>
            <h3>No users found</h3>
            <p>Create the first user account to get started.</p>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm mt-2">
                <i class="fas fa-plus"></i> New User
            </a>
        </div>
    @else
    <table class="data-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Role</th>
                <th>Charts Archived</th>
                <th>Last Login</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:34px;height:34px;border-radius:8px;background:linear-gradient(135deg,#2563eb,#7c3aed);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:white;flex-shrink:0;letter-spacing:0.02em;">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <div>
                            <div style="font-weight:600;font-size:13.5px;color:var(--text-primary);">{{ $user->name }}</div>
                            <div style="font-size:12px;color:var(--text-muted);">{{ $user->email }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge badge-{{ $user->role === 'admin' ? 'warning' : 'info' }}">
                        <i class="fas fa-{{ $user->role === 'admin' ? 'user-shield' : 'user' }}"></i>
                        {{ ucfirst($user->role) }}
                    </span>
                </td>
                <td>
                    <span style="font-weight:600;">{{ number_format($user->archived_charts_count) }}</span>
                    <span style="font-size:12px;color:var(--text-muted);margin-left:2px;">charts</span>
                </td>
                <td>
                    @if($user->last_login_at)
                        <div style="font-size:13px;">{{ $user->last_login_at->format('m/d/Y') }}</div>
                        <div style="font-size:12px;color:var(--text-muted);">{{ $user->last_login_at->format('H:i') }}</div>
                    @else
                        <span style="color:var(--text-muted);font-size:13px;">Never</span>
                    @endif
                </td>
                <td>
                    <span class="badge badge-{{ $user->is_active ? 'success' : 'danger' }}">
                        <i class="fas fa-{{ $user->is_active ? 'check-circle' : 'times-circle' }}"></i>
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td>
                    <div class="row-actions">
                        <a href="{{ route('admin.users.edit', $user) }}" class="action-btn">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('admin.users.login-history', $user) }}" class="action-btn">
                            <i class="fas fa-history"></i> Login History
                        </a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($users->hasPages())
    <div class="card-body" style="border-top:1px solid var(--divider);">
        {{ $users->links() }}
    </div>
    @endif
</div>

@endsection