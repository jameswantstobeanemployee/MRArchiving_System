@extends('layouts.app')
@section('title', 'Edit User')
@section('content')

<div class="page-header">
    <div class="page-header-left">
        <div class="breadcrumb">
            <a href="{{ route('admin.users.index') }}">Users</a>
            <i class="fas fa-chevron-right" style="font-size:9px;"></i>
            <span>Edit User</span>
        </div>
        <h1>
            <i class="fas fa-user-edit" style="color:var(--accent);margin-right:10px;font-size:20px;"></i>
            Edit User — {{ $user->name }}
        </h1>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div style="max-width:600px;">
    <form method="POST" action="{{ route('admin.users.update', $user) }}" id="editUserForm">
        @csrf @method('PUT')

        {{-- Account Info --}}
        <div class="card">
            <div class="card-header">
                <span style="display:flex;align-items:center;gap:10px;">
                    <div style="width:34px;height:34px;border-radius:8px;background:linear-gradient(135deg,#2563eb,#7c3aed);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:white;flex-shrink:0;">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <div>
                        <div style="font-weight:700;font-size:13.5px;">Account Information</div>
                        <div style="font-size:11.5px;font-weight:400;color:var(--text-muted);">
                            Member since {{ $user->created_at->format('M d, Y') }}
                        </div>
                    </div>
                </span>
                <span class="badge badge-{{ $user->is_active ? 'success' : 'danger' }}">
                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <div class="card-body" style="padding:0;">

                <div style="padding:18px 20px;border-bottom:1px solid var(--divider);">
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Full Name <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $user->name) }}" required>
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
                               value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div style="color:var(--danger);font-size:12px;margin-top:4px;">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                {{-- Role --}}
                <div style="padding:18px 20px;border-bottom:1px solid var(--divider);">
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Role <span style="color:var(--danger);">*</span></label>
                        <div style="display:flex;gap:10px;margin-top:8px;">
                            @foreach(['staff' => ['label' => 'Staff', 'icon' => 'user', 'desc' => 'Can archive and manage charts'], 'admin' => ['label' => 'Admin', 'icon' => 'user-shield', 'desc' => 'Full system access']] as $value => $info)
                            <label style="flex:1;display:flex;align-items:flex-start;gap:10px;padding:12px 14px;border:1px solid var(--border-color);border-radius:var(--radius-md);cursor:pointer;background:var(--table-header-bg);transition:all var(--transition);">
                                <input type="radio" name="role" value="{{ $value }}"
                                       {{ old('role', $user->role) === $value ? 'checked' : '' }}
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
                    </div>
                </div>

                {{-- Account Status --}}
                <div style="padding:18px 20px;">
                    <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                               style="width:15px;height:15px;margin-top:2px;flex-shrink:0;accent-color:var(--accent);">
                        <div>
                            <div style="font-weight:600;font-size:13.5px;color:var(--text-primary);">Active Account</div>
                            <div style="font-size:12.5px;color:var(--text-muted);margin-top:2px;">
                                Inactive users cannot log in to the system
                            </div>
                        </div>
                    </label>
                </div>

            </div>
        </div>

        {{-- Change Password --}}
        <div class="card">
            <div class="card-header">
                <span>
                    <i class="fas fa-lock" style="color:var(--text-muted);margin-right:6px;"></i>
                    Change Password
                </span>
                <span style="font-size:12px;font-weight:400;color:var(--text-muted);">Leave blank to keep current</span>
            </div>
            <div class="card-body" style="padding:0;">

                <div style="padding:18px 20px;border-bottom:1px solid var(--divider);">
                    <div class="form-group" style="margin-bottom:0;">
                        <label>New Password</label>
                        <input type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               minlength="8"
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
                        <label>Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                </div>

            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:8px;margin-bottom:20px;">
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>

    </form>
</div>

@endsection