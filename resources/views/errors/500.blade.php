@extends('layouts.app')
@section('title', 'Server Error')
@section('content')

<div style="display:flex;align-items:center;justify-content:center;min-height:60vh;">
    <div style="text-align:center;max-width:480px;">
        <div style="font-size:72px;font-weight:800;color:var(--danger);opacity:0.15;line-height:1;margin-bottom:8px;">
            500
        </div>
        <div style="font-size:48px;color:var(--danger);margin-bottom:20px;">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h1 style="font-size:22px;font-weight:700;color:var(--text-primary);margin-bottom:10px;">
            Something went wrong
        </h1>
        <p style="font-size:14px;color:var(--text-muted);margin-bottom:28px;line-height:1.7;">
            An unexpected server error occurred. This has been logged and our team will look into it.
            Please try again or contact your administrator if the problem persists.
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