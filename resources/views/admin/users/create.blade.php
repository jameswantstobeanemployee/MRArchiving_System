@extends('layouts.app')
@section('title', 'Create User')
@section('content')

<div class="page-header">
    <div class="page-header-left">
        <div class="breadcrumb">
            <a href="{{ route('admin.users.index') }}">Users</a>
            <i class="fas fa-chevron-right" style="font-size:9px;"></i>
            <span>Create User</span>
        </div>
        <h1>
            <i class="fas fa-user-plus" style="color:var(--accent);margin-right:10px;font-size:20px;"></i>
            Create User
        </h1>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div style="max-width:600px;">
    <form method="POST" action="{{ route('admin.users.store') }}" id="createUserForm">
        @csrf

        {{-- Account Info --}}
        <div class="card">
            <div class="card-header">
                <span>
                    <i class="fas fa-user" style="color:var(--text-muted);margin-right:6px;"></i>
                    Account Information
                </span>
            </div>
            <div class="card-body" style="padding:0;">

                <div style="padding:18px 20px;border-bottom:1px solid var(--divider);">
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Full Name <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required
                               placeholder="e.g. Juan dela Cruz">
                        @error('name')
                            <div style="color:var(--danger);font-size:12px;margin-top:4px;">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div style="padding:18px 20px;border-bottom:1px solid var(--divider);">
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Email Address <span style="color:var(--danger);">*</span></label>
                        <input type="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required
                               placeholder="e.g. juan@hospital.org">
                        @error('email')
                            <div style="color:var(--danger);font-size:12px;margin-top:4px;">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div style="padding:18px 20px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Role <span style="color:var(--danger);">*</span></label>
                        <div style="display:flex;gap:10px;margin-top:8px;">
                            @foreach(['staff' => ['label' => 'Staff', 'icon' => 'user', 'desc' => 'Can archive and manage charts'], 'admin' => ['label' => 'Admin', 'icon' => 'user-shield', 'desc' => 'Full system access']] as $value => $info)
                            <label style="flex:1;display:flex;align-items:flex-start;gap:10px;padding:12px 14px;border:1px solid {{ old('role') === $value ? 'var(--accent)' : 'var(--border-color)' }};border-radius:var(--radius-md);cursor:pointer;background:{{ old('role') === $value ? 'var(--info-light)' : 'var(--table-header-bg)' }};transition:all var(--transition);">
                                <input type="radio" name="role" value="{{ $value }}"
                                       {{ old('role', 'staff') === $value ? 'checked' : '' }}
                                       style="margin-top:2px;accent-color:var(--accent);">
                                <div>
                                    <div style="font-weight:600;font-size:13px;color:var(--text-primary);">
                                        <i class="fas fa-{{ $info['icon'] }}" style="margin-right:4px;"></i>
                                        {{ $info['label'] }}
                                    </div>
                                    <div style="font-size:11.5px;color:var(--text-muted);margin-top:2px;">{{ $info['desc'] }}</div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                        @error('role')
                            <div style="color:var(--danger);font-size:12px;margin-top:4px;">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

            </div>
        </div>

        {{-- Password --}}
        <div class="card">
            <div class="card-header">
                <span>
                    <i class="fas fa-lock" style="color:var(--text-muted);margin-right:6px;"></i>
                    Password
                </span>
            </div>
            <div class="card-body" style="padding:0;">

                <div style="padding:18px 20px;border-bottom:1px solid var(--divider);">
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Password <span style="color:var(--danger);">*</span></label>
                        <input type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               required minlength="8"
                               placeholder="Minimum 8 characters">
                        @error('password')
                            <div style="color:var(--danger);font-size:12px;margin-top:4px;">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div style="padding:18px 20px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Confirm Password <span style="color:var(--danger);">*</span></label>
                        <input type="password" name="password_confirmation"
                               class="form-control" required>
                    </div>
                </div>

            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:8px;margin-bottom:20px;">
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Create User
            </button>
        </div>

    </form>
</div>

@endsection