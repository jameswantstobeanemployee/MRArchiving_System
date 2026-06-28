{{-- resources/views/profile/index.blade.php --}}
@extends('layouts.app')

@section('title', 'My Profile')

@push('styles')
<style>
    /* ============================================
       Profile Layout
       ============================================ */
    .profile-grid {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 24px;
        align-items: start;
    }

    /* ============================================
       Profile Card (Left Column)
       ============================================ */
    .profile-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: var(--radius-lg);
        box-shadow: var(--card-shadow);
        overflow: hidden;
        position: sticky;
        top: calc(var(--topbar-height) + 28px);
    }

    .profile-card-banner {
        height: 80px;
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #7c3aed 100%);
        position: relative;
        overflow: hidden;
    }

    .profile-card-banner::after {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 20% 50%, rgba(255,255,255,0.08) 0%, transparent 60%),
            radial-gradient(circle at 80% 20%, rgba(255,255,255,0.06) 0%, transparent 50%);
    }

    .profile-card-banner::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
        background-size: 20px 20px;
        z-index: 1;
    }

    .profile-card-body {
        padding: 0 20px 20px;
    }

    .profile-avatar-wrap {
        position: relative;
        display: inline-block;
        margin-top: -28px;
        margin-bottom: 12px;
    }

    .profile-avatar {
        width: 64px;
        height: 64px;
        border-radius: 14px;
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 22px;
        color: white;
        letter-spacing: -0.02em;
        border: 3px solid var(--card-bg);
        box-shadow: 0 4px 16px rgba(37,99,235,0.35);
        position: relative;
        z-index: 2;
        transition: box-shadow var(--transition-md);
        flex-shrink: 0;
        line-height: 1;
    }

    .profile-avatar:hover {
        box-shadow: 0 6px 24px rgba(37,99,235,0.45);
    }

    .profile-status-dot {
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 12px;
        height: 12px;
        background: var(--success);
        border-radius: 50%;
        border: 2px solid var(--card-bg);
        z-index: 3;
    }

    .profile-name {
        font-size: 17px;
        font-weight: 700;
        color: var(--text-primary);
        letter-spacing: -0.02em;
        line-height: 1.3;
        margin-bottom: 3px;
    }

    .profile-role-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: var(--radius-full);
        font-size: 11.5px;
        font-weight: 600;
        margin-bottom: 16px;
    }

    .profile-role-badge.admin {
        background: linear-gradient(135deg, rgba(37,99,235,0.12), rgba(124,58,237,0.12));
        color: #6d28d9;
        border: 1px solid rgba(124,58,237,0.2);
    }

    [data-theme="dark"] .profile-role-badge.admin {
        color: #a78bfa;
        border-color: rgba(167,139,250,0.2);
        background: rgba(124,58,237,0.15);
    }

    .profile-role-badge.staff {
        background: var(--info-light);
        color: var(--info-text);
        border: 1px solid var(--info-border);
    }

    .profile-meta {
        display: flex;
        flex-direction: column;
        gap: 9px;
        padding: 14px 0;
        border-top: 1px solid var(--divider);
        border-bottom: 1px solid var(--divider);
        margin-bottom: 16px;
    }

    .profile-meta-item {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 12.5px;
        color: var(--text-secondary);
    }

    .profile-meta-icon {
        width: 28px;
        height: 28px;
        border-radius: 7px;
        background: var(--divider);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        color: var(--text-muted);
        flex-shrink: 0;
    }

    .profile-meta-label {
        font-size: 11px;
        color: var(--text-muted);
        display: block;
        line-height: 1.2;
    }

    .profile-meta-value {
        font-size: 12.5px;
        font-weight: 500;
        color: var(--text-secondary);
        line-height: 1.2;
    }

    .profile-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .profile-stat-item {
        background: var(--divider);
        border-radius: var(--radius-md);
        padding: 12px;
        text-align: center;
        transition: background var(--transition);
    }

    .profile-stat-item:hover { background: var(--border-color); }

    .profile-stat-value {
        font-size: 22px;
        font-weight: 700;
        color: var(--text-primary);
        letter-spacing: -0.03em;
        line-height: 1;
        margin-bottom: 3px;
    }

    .profile-stat-label {
        font-size: 11px;
        color: var(--text-muted);
        font-weight: 500;
        line-height: 1.3;
    }

    /* ============================================
       Right Column
       ============================================ */
    .profile-right {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* ============================================
       Section Cards
       ============================================ */
    .section-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: var(--radius-lg);
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }

    .section-card-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--divider);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .section-card-title {
        font-size: 14px;
        font-weight: 700;
        color: var(--text-primary);
        letter-spacing: -0.01em;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-card-title i {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: var(--info-light);
        color: var(--info-text);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
    }

    .section-card-body { padding: 20px; }

    /* ============================================
       Form Grid
       ============================================ */
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .form-grid .form-group.full { grid-column: 1 / -1; }

    /* ============================================
       Password Strength
       ============================================ */
    .password-strength { margin-top: 8px; display: none; }
    .password-strength.visible { display: block; }

    .strength-bar-track {
        height: 4px;
        background: var(--border-color);
        border-radius: var(--radius-full);
        overflow: hidden;
        margin-bottom: 5px;
    }

    .strength-bar-fill {
        height: 100%;
        border-radius: var(--radius-full);
        transition: width 0.3s ease, background 0.3s ease;
        width: 0%;
    }

    .strength-text { font-size: 11.5px; font-weight: 600; }
    .strength-0, .strength-1 { color: var(--danger); }
    .strength-2 { color: var(--warning); }
    .strength-3 { color: #f59e0b; }
    .strength-4 { color: var(--success); }

    .password-reqs {
        margin-top: 10px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 5px;
    }

    .req-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: var(--text-muted);
        transition: color var(--transition);
    }

    .req-item i { font-size: 10px; width: 14px; text-align: center; transition: all var(--transition); }
    .req-item.met { color: var(--success-text); }
    .req-item.met i { color: var(--success); }

    /* ============================================
       Input with icon
       ============================================ */
    .input-icon-wrap { position: relative; }
    .input-icon-wrap .form-control { padding-left: 38px; }

    .input-icon-wrap .input-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 13px;
        pointer-events: none;
    }

    .input-icon-wrap .input-toggle-pw {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        background: none;
        border: none;
        cursor: pointer;
        padding: 4px;
        font-size: 13px;
        transition: color var(--transition);
    }

    .input-icon-wrap .input-toggle-pw:hover { color: var(--text-primary); }

    /* ============================================
       Activity Feed
       ============================================ */
    .activity-feed { display: flex; flex-direction: column; gap: 0; }

    .activity-item {
        display: flex;
        gap: 14px;
        padding: 12px 0;
        border-bottom: 1px solid var(--divider);
        position: relative;
    }

    .activity-item:last-child { border-bottom: none; padding-bottom: 0; }
    .activity-item:first-child { padding-top: 0; }

    .activity-icon {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .activity-icon.checkout { background: var(--info-light);    color: var(--info-text); }
    .activity-icon.return   { background: var(--success-light); color: var(--success-text); }
    .activity-icon.login    { background: var(--warning-light); color: var(--warning-text); }
    .activity-icon.system   { background: var(--divider);       color: var(--text-muted); }

    .activity-body { flex: 1; min-width: 0; }

    .activity-title {
        font-size: 13px;
        font-weight: 500;
        color: var(--text-primary);
        line-height: 1.4;
        margin-bottom: 2px;
    }

    .activity-meta {
        font-size: 11.5px;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .activity-time {
        font-size: 11.5px;
        color: var(--text-muted);
        white-space: nowrap;
        flex-shrink: 0;
        margin-top: 2px;
        font-family: 'DM Mono', monospace;
    }

    /* ============================================
       Sessions Panel
       ============================================ */
    .sessions-list { display: flex; flex-direction: column; }

    .session-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 20px;
        border-bottom: 1px solid var(--divider);
        transition: background var(--transition);
    }

    .session-item:last-child { border-bottom: none; }
    .session-item:hover { background: var(--hover-bg, rgba(0,0,0,0.02)); }

    .session-item.session-current {
        background: linear-gradient(90deg, rgba(5,150,105,0.05) 0%, transparent 100%);
        border-left: 3px solid var(--success);
        padding-left: 17px;
    }

    .session-device-icon {
        width: 42px;
        height: 42px;
        border-radius: 11px;
        background: var(--divider);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        color: var(--text-secondary);
        flex-shrink: 0;
    }

    .session-current .session-device-icon {
        background: var(--success-light);
        color: var(--success-text);
    }

    .session-info { flex: 1; min-width: 0; }

    .session-top {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 5px;
    }

    .session-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 5px;
        font-size: 11.5px;
        color: var(--text-muted);
        font-family: 'DM Mono', monospace;
    }

    .session-dot { color: var(--border-color); }
    .session-action { flex-shrink: 0; }

    .sessions-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 20px;
        background: var(--danger-light);
        border-top: 1px solid var(--danger-border);
        flex-wrap: wrap;
    }

    .sessions-footer-text {
        font-size: 12px;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* ============================================
       Modals
       ============================================ */
    .modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 20px;
        backdrop-filter: blur(3px);
        -webkit-backdrop-filter: blur(3px);
        animation: fadeInBd 0.15s ease;
    }

    /* JS toggles display:flex / display:none */
    .modal-backdrop.is-open { display: flex !important; }

    @keyframes fadeInBd { from { opacity: 0; } to { opacity: 1; } }

    .modal-box {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: var(--radius-lg);
        box-shadow: 0 24px 64px rgba(0,0,0,0.2);
        width: 100%;
        max-width: 430px;
        animation: slideUpBox 0.2s cubic-bezier(0.34,1.56,0.64,1);
    }

    @keyframes slideUpBox {
        from { transform: translateY(18px) scale(0.97); opacity: 0; }
        to   { transform: translateY(0) scale(1); opacity: 1; }
    }

    .modal-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 18px 20px 14px;
        border-bottom: 1px solid var(--divider);
    }

    .modal-icon-wrap {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .modal-icon-wrap.danger { background: rgba(220,38,38,0.12); color: var(--danger); }

    .modal-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0 0 2px;
        letter-spacing: -0.01em;
    }

    .modal-subtitle { font-size: 12.5px; color: var(--text-muted); margin: 0; }
    .modal-body { padding: 16px 20px; }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 12px 20px;
        border-top: 1px solid var(--divider);
    }

    .modal-session-info {
        background: var(--divider);
        border-radius: var(--radius-md);
        padding: 10px 14px;
        font-size: 12.5px;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .modal-session-info i { color: var(--text-muted); font-size: 14px; }

    .revoke-all-sessions-preview {
        background: var(--divider);
        border-radius: var(--radius-md);
        padding: 10px 14px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .session-preview-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        color: var(--text-secondary);
    }

    .session-preview-item i { color: var(--text-muted); width: 14px; text-align: center; }

    .session-time-small {
        margin-left: auto;
        font-size: 11px;
        color: var(--text-muted);
        font-family: 'DM Mono', monospace;
    }

    /* ============================================
       Responsive
       ============================================ */
    @media (max-width: 1024px) {
        .profile-grid { grid-template-columns: 1fr; }
        .profile-card { position: static; }
    }

    @media (max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; }
        .form-grid .form-group.full { grid-column: 1; }
        .password-reqs { grid-template-columns: 1fr; }
        .profile-stats { grid-template-columns: repeat(4, 1fr); }
        .sessions-footer { flex-direction: column; align-items: flex-start; }
    }

    @media (max-width: 480px) {
        .profile-stats { grid-template-columns: 1fr 1fr; }
        .session-meta { gap: 4px; }
    }
</style>
@endpush

@section('content')

{{-- Breadcrumb + Header --}}
<div class="page-header">
    <div class="page-header-left">
        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:9px;"></i>
            <span>My Profile</span>
        </div>
        <h1>My Profile</h1>
    </div>
</div>

{{-- Alert Messages --}}
@if(session('profile_success'))
    <div class="alert alert-success mb-2">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('profile_success') }}</span>
    </div>
@endif

@if(session('profile_error'))
    <div class="alert alert-danger mb-2">
        <i class="fas fa-exclamation-circle"></i>
        <span>{{ session('profile_error') }}</span>
    </div>
@endif

@if(session('password_success'))
    <div class="alert alert-success mb-2">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('password_success') }}</span>
    </div>
@endif

<div class="profile-grid">

    {{-- ── LEFT: Profile Summary Card ──────────────────────────────────────── --}}
    <div>
        <div class="profile-card">
            <div class="profile-card-banner"></div>
            <div class="profile-card-body">

                <div class="profile-avatar-wrap">
                    <div class="profile-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <span class="profile-status-dot" title="Active"></span>
                </div>

                <div class="profile-name">{{ auth()->user()->name }}</div>

                <div class="profile-role-badge {{ auth()->user()->isAdmin() ? 'admin' : 'staff' }}">
                    <i class="fas {{ auth()->user()->isAdmin() ? 'fa-user-shield' : 'fa-user' }}"></i>
                    {{ ucfirst(auth()->user()->role) }}
                </div>

                <div class="profile-meta">
                    <div class="profile-meta-item">
                        <div class="profile-meta-icon"><i class="fas fa-envelope"></i></div>
                        <div>
                            <span class="profile-meta-label">Email</span>
                            <span class="profile-meta-value">{{ auth()->user()->email }}</span>
                        </div>
                    </div>
                    <div class="profile-meta-item">
                        <div class="profile-meta-icon"><i class="fas fa-calendar-alt"></i></div>
                        <div>
                            <span class="profile-meta-label">Member since</span>
                            <span class="profile-meta-value">{{ auth()->user()->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                    <div class="profile-meta-item">
                        <div class="profile-meta-icon"><i class="fas fa-clock"></i></div>
                        <div>
                            <span class="profile-meta-label">Last login</span>
                            <span class="profile-meta-value">
                                {{ auth()->user()->last_login_at
                                    ? \Carbon\Carbon::parse(auth()->user()->last_login_at)->diffForHumans()
                                    : 'N/A' }}
                            </span>
                        </div>
                    </div>
                    <div class="profile-meta-item">
                        <div class="profile-meta-icon">
                            <i class="fas fa-circle" style="font-size:8px; color: var(--success);"></i>
                        </div>
                        <div>
                            <span class="profile-meta-label">Status</span>
                            <span class="profile-meta-value text-success">Active</span>
                        </div>
                    </div>
                </div>

                <div class="profile-stats">
                    <div class="profile-stat-item">
                        <div class="profile-stat-value">{{ $stats['checkouts_total'] ?? 0 }}</div>
                        <div class="profile-stat-label">Checkouts</div>
                    </div>
                    <div class="profile-stat-item">
                        <div class="profile-stat-value">{{ $stats['checkouts_active'] ?? 0 }}</div>
                        <div class="profile-stat-label">Active</div>
                    </div>
                    <div class="profile-stat-item">
                        <div class="profile-stat-value">{{ $stats['charts_accessed'] ?? 0 }}</div>
                        <div class="profile-stat-label">Charts</div>
                    </div>
                    <div class="profile-stat-item">
                        <div class="profile-stat-value">{{ $stats['days_active'] ?? 0 }}</div>
                        <div class="profile-stat-label">Days Active</div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ── RIGHT: Content ───────────────────────────────────────────────────── --}}
    <div class="profile-right">

        {{-- ── Personal Information ─────────────────────────────────────── --}}
        <div class="section-card">
            <div class="section-card-header">
                <div class="section-card-title">
                    <i class="fas fa-id-card"></i>
                    Personal Information
                </div>
            </div>
            <div class="section-card-body">
                <form action="{{ route('profile.update') }}" method="POST" id="profileForm">
                    @csrf
                    @method('PATCH')

                    <div class="form-grid">
                        <div class="form-group full">
                            <label for="name">Full Name</label>
                            <div class="input-icon-wrap">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" name="name" id="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', auth()->user()->name) }}"
                                    placeholder="Your full name" required autocomplete="name">
                            </div>
                            @error('name')
                                <div class="form-help text-danger"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group full">
                            <label for="email">Email Address</label>
                            <div class="input-icon-wrap">
                                <i class="fas fa-envelope input-icon"></i>
                                <input type="email" name="email" id="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', auth()->user()->email) }}"
                                    placeholder="your@email.com" required autocomplete="email">
                            </div>
                            @error('email')
                                <div class="form-help text-danger"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-end gap-1 mt-2">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="resetProfileForm()">
                            <i class="fas fa-undo"></i> Discard
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm" id="saveProfileBtn">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── Change Password ──────────────────────────────────────────── --}}
        <div class="section-card">
            <div class="section-card-header">
                <div class="section-card-title">
                    <i class="fas fa-lock"></i>
                    Change Password
                </div>
            </div>
            <div class="section-card-body">
                <form action="{{ route('profile.password') }}" method="POST" id="passwordForm">
                    @csrf
                    @method('PATCH')

                    <div class="form-grid">
                        <div class="form-group full">
                            <label for="current_password">Current Password</label>
                            <div class="input-icon-wrap">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" name="current_password" id="current_password"
                                    class="form-control @error('current_password') is-invalid @enderror"
                                    placeholder="Enter your current password" autocomplete="current-password">
                                <button type="button" class="input-toggle-pw" onclick="togglePw('current_password', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <div class="form-help text-danger"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">New Password</label>
                            <div class="input-icon-wrap">
                                <i class="fas fa-key input-icon"></i>
                                <input type="password" name="password" id="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="New password" autocomplete="new-password"
                                    oninput="checkPasswordStrength(this.value)">
                                <button type="button" class="input-toggle-pw" onclick="togglePw('password', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="form-help text-danger"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                            @enderror

                            <div class="password-strength" id="strengthIndicator">
                                <div class="strength-bar-track">
                                    <div class="strength-bar-fill" id="strengthBar"></div>
                                </div>
                                <div class="d-flex justify-between align-center">
                                    <span class="strength-text" id="strengthLabel"></span>
                                    <span style="font-size:11px; color: var(--text-muted);" id="strengthHint"></span>
                                </div>
                            </div>

                            <div class="password-reqs" id="passwordReqs" style="display:none;">
                                <div class="req-item" id="req-length"><i class="fas fa-circle"></i> At least 8 characters</div>
                                <div class="req-item" id="req-upper"><i class="fas fa-circle"></i> Uppercase letter</div>
                                <div class="req-item" id="req-lower"><i class="fas fa-circle"></i> Lowercase letter</div>
                                <div class="req-item" id="req-number"><i class="fas fa-circle"></i> Number</div>
                                <div class="req-item" id="req-special"><i class="fas fa-circle"></i> Special character</div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Confirm New Password</label>
                            <div class="input-icon-wrap">
                                <i class="fas fa-key input-icon"></i>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="form-control" placeholder="Confirm new password"
                                    autocomplete="new-password" oninput="checkPasswordMatch()">
                                <button type="button" class="input-toggle-pw" onclick="togglePw('password_confirmation', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-help" id="matchHint" style="display:none;"></div>
                        </div>
                    </div>

                    <div class="d-flex justify-end gap-1 mt-2">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="resetPasswordForm()">
                            <i class="fas fa-undo"></i> Clear
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-shield-alt"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── Recent Activity ──────────────────────────────────────────── --}}
        <div class="section-card">
            <div class="section-card-header">
                <div class="section-card-title">
                    <i class="fas fa-history"></i>
                    Recent Activity
                </div>
                <a href="{{ route('checkout.index') }}" class="btn btn-secondary btn-xs">
                    View all <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="section-card-body" style="padding: 0 20px;">
                @if(isset($recentActivity) && $recentActivity->count())
                    <div class="activity-feed">
                        @foreach($recentActivity as $activity)
                            <div class="activity-item">
                                <div class="activity-icon {{ $activity->type ?? 'system' }}">
                                    <i class="fas {{ $activity->icon ?? 'fa-circle' }}"></i>
                                </div>
                                <div class="activity-body">
                                    <div class="activity-title">{{ $activity->description }}</div>
                                    <div class="activity-meta">
                                        @if($activity->subject ?? null)
                                            <span>{{ $activity->subject }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="activity-time">
                                    {{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans(null, true, true) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @elseif(isset($recentCheckouts) && $recentCheckouts->count())
                    <div class="activity-feed">
                        @foreach($recentCheckouts as $checkout)
                            <div class="activity-item">
                                <div class="activity-icon {{ $checkout->status === 'returned' ? 'return' : 'checkout' }}">
                                    <i class="fas {{ $checkout->status === 'returned' ? 'fa-undo' : 'fa-exchange-alt' }}"></i>
                                </div>
                                <div class="activity-body">
                                    <div class="activity-title">
                                        {{ $checkout->status === 'returned' ? 'Returned' : 'Checked out' }}
                                        chart <strong>{{ $checkout->archivedChart->case_number ?? 'N/A' }}</strong>
                                    </div>
                                    <div class="activity-meta">
                                        <span>{{ $checkout->archivedChart->patient->full_name ?? 'Unknown patient' }}</span>
                                        &middot;
                                        <span class="badge {{ $checkout->status === 'returned' ? 'badge-success' : ($checkout->is_overdue ? 'badge-danger' : 'badge-info') }}"
                                              style="font-size:10.5px; padding:2px 7px;">
                                            {{ ucfirst($checkout->status) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="activity-time">
                                    {{ $checkout->checked_out_at->diffForHumans(null, true, true) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state" style="padding: 32px 16px;">
                        <i class="fas fa-history empty-state-icon"></i>
                        <h3>No recent activity</h3>
                        <p>Your actions will appear here.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Active Sessions ──────────────────────────────────────────── --}}
        <div class="section-card" id="sessions-card">
            <div class="section-card-header">
                <div class="section-card-title">
                    <i class="fas fa-shield-alt" style="background: var(--warning-light); color: var(--warning-text);"></i>
                    Active Sessions
                </div>
                <span class="badge badge-secondary" style="font-size:11px;">
                    {{ $sessions->count() }} {{ Str::plural('device', $sessions->count()) }}
                </span>
            </div>
            <div class="section-card-body" style="padding: 0;">

                <div class="sessions-list">
                    @forelse($sessions as $session)
                    <div class="session-item {{ $session->is_current ? 'session-current' : '' }}">

                        <div class="session-device-icon">
                            <i class="fas {{ $session->device_icon }}"></i>
                        </div>

                        <div class="session-info">
                            <div class="session-top">
                                <span>{{ $session->browser }} on {{ $session->platform }}</span>
                                @if($session->is_current)
                                    <span class="badge badge-success" style="font-size:10px; padding:2px 8px;">
                                        <i class="fas fa-circle" style="font-size:7px;"></i> This device
                                    </span>
                                @endif
                            </div>
                            <div class="session-meta">
                                <i class="fas fa-map-marker-alt" style="font-size:9px;"></i>
                                <span>{{ $session->ip_address }}</span>
                                <span class="session-dot">·</span>
                                <i class="fas fa-clock" style="font-size:9px;"></i>
                                <span>{{ $session->last_active }}</span>
                                <span class="session-dot">·</span>
                                <i class="fas {{ $session->device_icon }}" style="font-size:9px;"></i>
                                <span>{{ $session->device_type }}</span>
                            </div>
                        </div>

                        <div class="session-action">
                            @if(!$session->is_current)
                                <button type="button"
                                    class="btn btn-danger btn-xs"
                                    onclick="openRevokeModal('{{ $session->id }}', '{{ addslashes($session->browser) }} on {{ addslashes($session->platform) }}', '{{ $session->ip_address }}')"
                                    title="Sign out this session">
                                    <i class="fas fa-times"></i>
                                </button>
                            @else
                                <span style="font-size:11px; color: var(--success-text); display:flex; align-items:center; gap:4px;">
                                    <i class="fas fa-check-circle"></i> Current
                                </span>
                            @endif
                        </div>

                    </div>
                    @empty
                    <div class="empty-state" style="padding: 32px;">
                        <i class="fas fa-shield-alt empty-state-icon"></i>
                        <h3>No active sessions found</h3>
                        <p>Make sure SESSION_DRIVER=database in your .env</p>
                    </div>
                    @endforelse
                </div>

                @if($sessions->where('is_current', false)->count() > 0)
                <div class="sessions-footer">
                    <div class="sessions-footer-text">
                        <i class="fas fa-info-circle"></i>
                        Signing out others won't affect your current session.
                    </div>
                    <button type="button" class="btn btn-danger btn-sm" onclick="openRevokeAllModal()">
                        <i class="fas fa-sign-out-alt"></i>
                        Sign Out All Others ({{ $sessions->where('is_current', false)->count() }})
                    </button>
                </div>
                @endif

            </div>
        </div>
        {{-- ── End Active Sessions ──────────────────────────────────────── --}}

    </div>{{-- .profile-right --}}
</div>{{-- .profile-grid --}}


{{-- ═══════════════════════════════════════════
     MODAL: Revoke single session
     ═══════════════════════════════════════════ --}}
<div id="revokeModal" class="modal-backdrop" style="display:none;" onclick="closeSingleModal(event)">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div class="modal-icon-wrap danger">
                <i class="fas fa-sign-out-alt"></i>
            </div>
            <div>
                <h3 class="modal-title">Sign Out Session</h3>
                <p class="modal-subtitle" id="revokeModalSubtitle">—</p>
            </div>
        </div>
        <form id="revokeSessionForm" method="POST" action="">
            @csrf
            @method('DELETE')
            <div class="modal-body">
                <div class="modal-session-info" id="revokeSessionInfo">
                    <i class="fas fa-desktop"></i>
                    <span>Loading…</span>
                </div>
                <div class="form-group" style="margin-top:14px; margin-bottom:0;">
                    <label for="revokePassword" style="font-size:13px; font-weight:600;">Confirm your password</label>
                    <div class="input-icon-wrap" style="margin-top:6px;">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="revokePassword" name="password"
                            class="form-control" placeholder="Enter your password"
                            autocomplete="current-password">
                        <button type="button" class="input-toggle-pw" onclick="togglePw('revokePassword', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div id="revokePasswordError" class="form-help text-danger" style="display:none; margin-top:5px;">
                        <i class="fas fa-exclamation-circle"></i> Password is required.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('revokeModal').style.display='none'">
                    Cancel
                </button>
                <button type="submit" class="btn btn-danger btn-sm" onclick="return validateRevokePw('revokePassword','revokePasswordError')">
                    <i class="fas fa-sign-out-alt"></i> Sign Out Session
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════
     MODAL: Revoke all other sessions
     ═══════════════════════════════════════════ --}}
<div id="revokeAllModal" class="modal-backdrop" style="display:none;" onclick="closeAllModal(event)">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div class="modal-icon-wrap danger">
                <i class="fas fa-user-slash"></i>
            </div>
            <div>
                <h3 class="modal-title">Sign Out All Other Sessions</h3>
                <p class="modal-subtitle">
                    {{ $sessions->where('is_current', false)->count() }}
                    other {{ Str::plural('session', $sessions->where('is_current', false)->count()) }} will be terminated.
                </p>
            </div>
        </div>
        <form method="POST" action="{{ route('profile.logout-other-sessions') }}">
            @csrf
            @method('DELETE')
            <div class="modal-body">
                <div class="revoke-all-sessions-preview">
                    @foreach($sessions->where('is_current', false)->take(3) as $s)
                    <div class="session-preview-item">
                        <i class="fas {{ $s->device_icon }}"></i>
                        <span>{{ $s->browser }} · {{ $s->ip_address }}</span>
                        <span class="session-time-small">{{ $s->last_active }}</span>
                    </div>
                    @endforeach
                    @if($sessions->where('is_current', false)->count() > 3)
                    <div style="font-size:12px; color:var(--text-muted); padding:4px 0 0 22px;">
                        + {{ $sessions->where('is_current', false)->count() - 3 }} more
                    </div>
                    @endif
                </div>

                <div class="form-group" style="margin-top:16px; margin-bottom:0;">
                    <label for="revokeAllPassword" style="font-size:13px; font-weight:600;">Confirm your password</label>
                    <div class="input-icon-wrap" style="margin-top:6px;">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="revokeAllPassword" name="password"
                            class="form-control" placeholder="Enter your password"
                            autocomplete="current-password">
                        <button type="button" class="input-toggle-pw" onclick="togglePw('revokeAllPassword', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div id="revokeAllPasswordError" class="form-help text-danger" style="display:none; margin-top:5px;">
                        <i class="fas fa-exclamation-circle"></i> Password is required.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm"
                    onclick="document.getElementById('revokeAllModal').style.display='none'">
                    Cancel
                </button>
                <button type="submit" class="btn btn-danger btn-sm"
                    onclick="return validateRevokePw('revokeAllPassword','revokeAllPasswordError')">
                    <i class="fas fa-sign-out-alt"></i> Sign Out All Others
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ─────────────────────────────────────────────────────────────────────────────
//  Password show/hide
// ─────────────────────────────────────────────────────────────────────────────
function togglePw(fieldId, btn) {
    const input = document.getElementById(fieldId);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// ─────────────────────────────────────────────────────────────────────────────
//  Password strength
// ─────────────────────────────────────────────────────────────────────────────
const strengthColors = ['#dc2626','#dc2626','#d97706','#f59e0b','#059669'];
const strengthLabels = ['Very Weak','Weak','Fair','Good','Strong'];
const strengthHints  = ['Add more characters','Add uppercase & numbers','Try adding special chars','Almost there!','Great password!'];

function checkPasswordStrength(val) {
    const indicator = document.getElementById('strengthIndicator');
    const bar       = document.getElementById('strengthBar');
    const label     = document.getElementById('strengthLabel');
    const hint      = document.getElementById('strengthHint');
    const reqs      = document.getElementById('passwordReqs');

    if (!val) { indicator.classList.remove('visible'); reqs.style.display = 'none'; return; }

    indicator.classList.add('visible');
    reqs.style.display = 'grid';

    const checks = {
        'req-length':  val.length >= 8,
        'req-upper':   /[A-Z]/.test(val),
        'req-lower':   /[a-z]/.test(val),
        'req-number':  /[0-9]/.test(val),
        'req-special': /[^A-Za-z0-9]/.test(val),
    };

    let score = 0;
    for (const [id, met] of Object.entries(checks)) {
        const el = document.getElementById(id);
        const ic = el.querySelector('i');
        el.classList.toggle('met', met);
        ic.className = met ? 'fas fa-check-circle' : 'fas fa-circle';
        if (met) score++;
    }

    bar.style.width      = `${(score / 5) * 100}%`;
    bar.style.background = strengthColors[score] ?? strengthColors[4];
    label.textContent    = strengthLabels[score]  ?? 'Strong';
    label.className      = `strength-text strength-${score}`;
    hint.textContent     = strengthHints[score]   ?? '';

    checkPasswordMatch();
}

// ─────────────────────────────────────────────────────────────────────────────
//  Password match
// ─────────────────────────────────────────────────────────────────────────────
function checkPasswordMatch() {
    const pw   = document.getElementById('password').value;
    const conf = document.getElementById('password_confirmation').value;
    const hint = document.getElementById('matchHint');

    if (!conf) { hint.style.display = 'none'; return; }

    hint.style.display = 'block';
    hint.innerHTML = pw === conf
        ? '<i class="fas fa-check-circle text-success"></i> <span class="text-success">Passwords match</span>'
        : '<i class="fas fa-times-circle text-danger"></i> <span class="text-danger">Passwords do not match</span>';
}

// ─────────────────────────────────────────────────────────────────────────────
//  Form resets
// ─────────────────────────────────────────────────────────────────────────────
function resetProfileForm() { document.getElementById('profileForm').reset(); }

function resetPasswordForm() {
    document.getElementById('passwordForm').reset();
    document.getElementById('strengthIndicator').classList.remove('visible');
    document.getElementById('passwordReqs').style.display = 'none';
    document.getElementById('matchHint').style.display = 'none';
}

// ─────────────────────────────────────────────────────────────────────────────
//  Session modals
// ─────────────────────────────────────────────────────────────────────────────
function openRevokeModal(sessionId, description, ip) {
    document.getElementById('revokeSessionForm').action = `/profile/sessions/${sessionId}`;
    document.getElementById('revokeModalSubtitle').textContent = description;
    document.getElementById('revokeSessionInfo').innerHTML =
        `<i class="fas fa-map-marker-alt"></i> ${ip} &nbsp;·&nbsp; ${description}`;
    document.getElementById('revokePassword').value = '';
    document.getElementById('revokePasswordError').style.display = 'none';
    document.getElementById('revokeModal').style.display = 'flex';
    setTimeout(() => document.getElementById('revokePassword').focus(), 150);
}

function closeSingleModal(e) {
    if (!e || e.target === document.getElementById('revokeModal'))
        document.getElementById('revokeModal').style.display = 'none';
}

function openRevokeAllModal() {
    document.getElementById('revokeAllPassword').value = '';
    document.getElementById('revokeAllPasswordError').style.display = 'none';
    document.getElementById('revokeAllModal').style.display = 'flex';
    setTimeout(() => document.getElementById('revokeAllPassword').focus(), 150);
}

function closeAllModal(e) {
    if (!e || e.target === document.getElementById('revokeAllModal'))
        document.getElementById('revokeAllModal').style.display = 'none';
}

// Shared password validator for both modals
function validateRevokePw(inputId, errorId) {
    if (!document.getElementById(inputId).value) {
        document.getElementById(errorId).style.display = 'block';
        return false;
    }
    return true;
}

// Escape key closes any open modal
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.getElementById('revokeModal').style.display    = 'none';
        document.getElementById('revokeAllModal').style.display = 'none';
    }
});

// ─────────────────────────────────────────────────────────────────────────────
//  Dirty-state indicator on profile form
// ─────────────────────────────────────────────────────────────────────────────
(function () {
    const original = {
        name:  document.getElementById('name').value,
        email: document.getElementById('email').value,
    };

    ['name', 'email'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', () => {
            const dirty = document.getElementById('name').value  !== original.name ||
                          document.getElementById('email').value !== original.email;
            const btn = document.getElementById('saveProfileBtn');
            btn.classList.toggle('btn-primary',   dirty);
            btn.classList.toggle('btn-secondary', !dirty);
        });
    });
})();
</script>
@endpush