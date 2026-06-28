@extends('layouts.app')
@section('title', 'Reports')

@push('styles')
<style>
    .section-label {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 12px;
    }
    .reports-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 28px;
    }
    .report-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: var(--radius-lg);
        padding: 18px;
        cursor: pointer;
        transition: border-color var(--transition-md), transform var(--transition-md);
        position: relative;
        overflow: hidden;
    }
    .report-card:hover {
        border-color: var(--accent);
        transform: translateY(-2px);
    }
    .report-card-accent {
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
    }
    .report-icon-wrap {
        width: 36px;
        height: 36px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        font-size: 18px;
    }
    .report-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 4px;
    }
    .report-description {
        font-size: 12px;
        color: var(--text-muted);
        line-height: 1.4;
    }
    .report-link {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 12px;
        font-weight: 600;
        margin-top: 12px;
        color: var(--accent);
        text-decoration: none;
    }
    .report-link:hover { text-decoration: underline; }
    @media (max-width: 1024px) {
        .reports-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 600px) {
        .reports-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            Reports
        </div>
        <h1>Reports & Analytics</h1>
    </div>
</div>

@php
$sections = [
    'Inventory & Storage' => [
        ['name' => 'Archive Inventory',  'description' => 'All archived charts with filters',  'url' => route('reports.archive-inventory'), 'icon' => '📋', 'accent' => '#378ADD', 'icon-bg' => '#E6F1FB'],
        ['name' => 'Box Status',         'description' => 'Box utilization and fill levels',    'url' => route('reports.box-status'),        'icon' => '📦', 'accent' => '#639922', 'icon-bg' => '#EAF3DE'],
        ['name' => 'Storage Usage',      'description' => 'Drive space and capacity',           'url' => route('reports.storage-usage'),     'icon' => '💾', 'accent' => '#1D9E75', 'icon-bg' => '#E1F5EE'],
        ['name' => 'Retention Report',   'description' => 'Expiring and expired charts',        'url' => route('reports.retention'),         'icon' => '⏳', 'accent' => '#E24B4A', 'icon-bg' => '#FCEBEB'],
    ],
    'Activity & Audit' => [
        ['name' => 'Checkout Status',  'description' => 'Current and historical checkouts', 'url' => route('reports.checkout-status'),  'icon' => '🔄', 'accent' => '#7F77DD', 'icon-bg' => '#EEEDFE'],
        ['name' => 'Location History', 'description' => 'Chart movement log',               'url' => route('reports.location-history'), 'icon' => '📍', 'accent' => '#EF9F27', 'icon-bg' => '#FAEEDA'],
        ['name' => 'Activity Report',  'description' => 'Archiving activity by user',       'url' => route('reports.activity'),         'icon' => '📊', 'accent' => '#D4537E', 'icon-bg' => '#FBEAF0'],
        ['name' => 'Audit Trail',      'description' => 'All system changes',               'url' => route('reports.audit-trail'),      'icon' => '🔍', 'accent' => '#888780', 'icon-bg' => '#F1EFE8'],
    ],
];
@endphp

@foreach($sections as $label => $reports)
    <p class="section-label">{{ $label }}</p>
    <div class="reports-grid">
        @foreach($reports as $report)
        <div class="report-card" onclick="window.location.href='{{ $report['url'] }}'">
            <div class="report-card-accent" style="background: {{ $report['accent'] }}"></div>
            <div class="report-icon-wrap" style="background: {{ $report['icon-bg'] }}">
                {{ $report['icon'] }}
            </div>
            <h3 class="report-title">{{ $report['name'] }}</h3>
            <p class="report-description">{{ $report['description'] }}</p>
            <a href="{{ $report['url'] }}" class="report-link">
                View report <i class="fas fa-arrow-right" style="font-size:10px"></i>
            </a>
        </div>
        @endforeach
    </div>
@endforeach

@endsection