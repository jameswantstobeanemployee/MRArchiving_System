@extends('layouts.app')
@section('title', 'Access Denied')
@section('content')

<div style="display:flex;align-items:center;justify-content:center;min-height:60vh;">
    <div style="text-align:center;max-width:480px;">
        <div style="font-size:72px;font-weight:800;color:var(--warning);opacity:0.15;line-height:1;margin-bottom:8px;">
            403
        </div>
        <div style="font-size:48px;color:var(--warning);margin-bottom:20px;">
            <i class="fas fa-lock"></i>
        </div>
        <h1 style="font-size:22px;font-weight:700;color:var(--text-primary);margin-bottom:10px;">
            Access Denied
        </h1>
        <p style="font-size:14px;color:var(--text-muted);margin-bottom:28px;line-height:1.7;">
            You don't have permission to access this page.
            If you believe this is a mistake, please contact your administrator.
        </p>
        <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;">
            <a href="javascript:history.back()" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Go Back
            </a>
            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </div>
    </div>
</div>

@endsection