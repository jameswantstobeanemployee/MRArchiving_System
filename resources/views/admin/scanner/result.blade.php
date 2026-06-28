@extends('layouts.app')
@section('title', 'Scan Results')
@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="page-header-left">
        <div class="breadcrumb">
            <a href="{{ route('admin.scanner.index') }}">Scanner</a>
            <i class="fas fa-chevron-right" style="font-size:9px;"></i>
            <span>Scan Results</span>
        </div>
        <h1>
            <i class="fas fa-search" style="color:var(--accent);margin-right:10px;font-size:20px;"></i>
            Scan Results — {{ $result['drive']->name }}
        </h1>
    </div>
    <div style="display:flex;gap:8px;">
        <form action="{{ route('admin.scanner.scan', $result['drive']) }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-sync-alt"></i> Re-scan
            </button>
        </form>
        <a href="{{ route('admin.scanner.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

@if(!$result['drive_accessible'])

<div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle"></i>
    <div>{{ $result['summary']['error'] }}</div>
</div>

@else

{{-- Summary Stats --}}
<div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr));margin-bottom:24px;">
    <div class="stat-card info">
        <div class="stat-icon"><i class="fas fa-hdd"></i></div>
        <div class="stat-title">Files on Drive</div>
        <div class="stat-value">{{ number_format($result['summary']['total_files_on_drive']) }}</div>
    </div>
    <div class="stat-card info">
        <div class="stat-icon"><i class="fas fa-database"></i></div>
        <div class="stat-title">DB Records</div>
        <div class="stat-value">{{ number_format($result['summary']['total_db_records']) }}</div>
    </div>
    <div class="stat-card success">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-title">Matched</div>
        <div class="stat-value">{{ number_format($result['summary']['matched']) }}</div>
    </div>
    <div class="stat-card {{ $result['summary']['orphaned_files'] > 0 ? 'warning' : 'success' }}">
        <div class="stat-icon"><i class="fas fa-question-circle"></i></div>
        <div class="stat-title">Orphaned Files</div>
        <div class="stat-value">{{ number_format($result['summary']['orphaned_files']) }}</div>
    </div>
    <div class="stat-card {{ $result['summary']['missing_files'] > 0 ? 'danger' : 'success' }}">
        <div class="stat-icon"><i class="fas fa-exclamation-circle"></i></div>
        <div class="stat-title">Missing Files</div>
        <div class="stat-value">{{ number_format($result['summary']['missing_files']) }}</div>
    </div>
    <div class="stat-card info">
        <div class="stat-icon"><i class="fas fa-trash"></i></div>
        <div class="stat-title">In Deleted Folder</div>
        <div class="stat-value">{{ number_format($result['summary']['deleted_files']) }}</div>
    </div>
</div>

{{-- Archive Dir Notice --}}
<div style="font-size:12.5px;color:var(--text-muted);margin-bottom:20px;display:flex;align-items:center;gap:8px;">
    <i class="fas fa-folder-open" style="color:var(--warning);"></i>
    Archive directory: <code>{{ $result['archive_dir'] }}</code>
    @if(!$result['archive_accessible'])
        <span style="color:var(--danger);font-weight:600;">— directory does not exist yet</span>
    @else
        <span style="color:var(--success);">✓ accessible</span>
    @endif
</div>

{{-- All Clear --}}
@if($result['summary']['orphaned_files'] === 0 && $result['summary']['missing_files'] === 0 && $result['summary']['matched'] > 0)
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i>
    <div>All <strong>{{ $result['summary']['matched'] }}</strong> files are accounted for. Drive and database are in sync.</div>
</div>
@endif

{{-- Missing Files --}}
@if(!empty($result['missing_files']))
<div class="card" style="border-color:var(--danger-border);">
    <div class="card-header" style="background:var(--danger-light);color:var(--danger-text);">
        <span>
            <i class="fas fa-exclamation-triangle"></i>&ensp;
            Missing Files
            <span class="badge badge-danger" style="margin-left:6px;">{{ count($result['missing_files']) }}</span>
        </span>
        <span style="font-size:12px;font-weight:400;color:var(--danger-text);opacity:0.8;">In database but NOT found on drive</span>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Chart ID</th>
                <th>Case #</th>
                <th>Patient</th>
                <th>Status</th>
                <th>Expected Path</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($result['missing_files'] as $item)
            <tr>
                <td>
                    <span style="font-family:'DM Mono',monospace;font-size:12px;color:var(--text-muted);">
                        #{{ $item['chart_id'] }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('charts.show', $item['chart_id']) }}"
                       style="color:var(--accent);font-weight:600;text-decoration:none;">
                        {{ $item['case_number'] }}
                    </a>
                </td>
                <td>
                    <span style="font-weight:500;">{{ $item['patient'] }}</span>
                </td>
                <td>
                    <span class="badge badge-{{ $item['status'] === 'active' ? 'success' : ($item['status'] === 'archived' ? 'info' : 'warning') }}">
                        {{ ucfirst(str_replace('_', ' ', $item['status'])) }}
                    </span>
                </td>
                <td>
                    <code style="font-size:11px;word-break:break-all;">{{ $item['stored_path'] }}</code>
                </td>
                <td>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
                        {{-- Fix Path --}}
                        <button type="button" class="action-btn"
                            onclick="document.getElementById('fix-{{ $item['chart_id'] }}').style.display='block';this.style.display='none'">
                            <i class="fas fa-wrench"></i> Fix Path
                        </button>
                        {{-- Clear Path --}}
                        <form action="{{ route('admin.scanner.clear-path') }}" method="POST" style="display:inline">
                            @csrf
                            <input type="hidden" name="chart_id" value="{{ $item['chart_id'] }}">
                            <button type="submit" class="action-btn danger"
                                onclick="return confirm('Clear this broken path from the database?')">
                                <i class="fas fa-times"></i> Clear
                            </button>
                        </form>
                    </div>
                    {{-- Fix Path Inline Form --}}
                    <div id="fix-{{ $item['chart_id'] }}" style="display:none;margin-top:8px;">
                        <form action="{{ route('admin.scanner.fix-path') }}" method="POST"
                              style="display:flex;gap:6px;align-items:center;">
                            @csrf
                            <input type="hidden" name="chart_id" value="{{ $item['chart_id'] }}">
                            <input type="text" name="new_path" class="form-control"
                                style="font-size:12px;"
                                placeholder="Enter full file path..."
                                value="{{ $item['stored_path'] }}">
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fas fa-save"></i> Save
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Orphaned Files --}}
@if(!empty($result['orphaned_files']))
<div class="card" style="border-color:var(--warning-border);">
    <div class="card-header" style="background:var(--warning-light);color:var(--warning-text);">
        <span>
            <i class="fas fa-folder-open"></i>&ensp;
            Orphaned Files
            <span class="badge badge-warning" style="margin-left:6px;">{{ count($result['orphaned_files']) }}</span>
        </span>
        <span style="font-size:12px;font-weight:400;opacity:0.8;">On drive but NOT in database</span>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Filename</th>
                <th>Size</th>
                <th>Last Modified</th>
                <th>Full Path</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($result['orphaned_files'] as $file)
            @php
                $mb = $file['size'] / 1048576;
                $sizeLabel = $mb >= 1024 ? number_format($mb/1024,2).' GB' : number_format($mb,2).' MB';
            @endphp
            <tr>
                <td>
                    <div style="font-weight:600;font-size:13.5px;">{{ $file['filename'] }}</div>
                </td>
                <td>
                    <span class="badge badge-info">{{ $sizeLabel }}</span>
                </td>
                <td>
                    <span style="font-size:12.5px;color:var(--text-muted);">{{ $file['modified'] }}</span>
                </td>
                <td>
                    <code style="font-size:11px;word-break:break-all;">{{ $file['full_path'] }}</code>
                </td>
                <td>
                    <form action="{{ route('admin.scanner.delete-orphan') }}" method="POST" style="display:inline">
                        @csrf
                        <input type="hidden" name="full_path" value="{{ $file['full_path'] }}">
                        <button type="submit" class="action-btn danger"
                            onclick="return confirm('Permanently delete {{ $file['filename'] }} from the drive?')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Matched Files --}}
<div class="card">
    <div class="card-header">
        <span>
            <i class="fas fa-check-circle" style="color:var(--success);"></i>&ensp;
            Matched Records
            <span class="badge badge-success" style="margin-left:6px;">{{ count($result['matched']) }}</span>
        </span>
        <button type="button" class="btn btn-secondary btn-sm"
            onclick="var t=document.getElementById('matched-table');t.style.display=t.style.display==='none'?'block':'none';this.innerHTML=t.style.display==='none'?'<i class=\'fas fa-eye\'></i> Show':'<i class=\'fas fa-eye-slash\'></i> Hide'">
            <i class="fas fa-eye"></i> Show
        </button>
    </div>
    <div id="matched-table" style="display:none;">
        @if(empty($result['matched']))
            <div class="empty-state">
                <i class="fas fa-folder-open empty-state-icon"></i>
                <h3>No matched records</h3>
            </div>
        @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Filename</th>
                    <th>Case #</th>
                    <th>Patient</th>
                    <th>Size</th>
                    <th>Status</th>
                    <th>Path</th>
                </tr>
            </thead>
            <tbody>
                @foreach($result['matched'] as $item)
                @php
                    $mb = $item['size'] / 1048576;
                    $sizeLabel = $mb >= 1024 ? number_format($mb/1024,2).' GB' : number_format($mb,2).' MB';
                @endphp
                <tr>
                    <td>
                        <span style="font-family:'DM Mono',monospace;font-size:12px;">{{ $item['filename'] }}</span>
                    </td>
                    <td>
                        <a href="{{ route('charts.show', $item['chart_id']) }}"
                           style="color:var(--accent);font-weight:600;text-decoration:none;">
                            {{ $item['case_number'] }}
                        </a>
                    </td>
                    <td>{{ $item['patient'] }}</td>
                    <td>
                        <span class="badge badge-info">{{ $sizeLabel }}</span>
                    </td>
                    <td>
                        <span class="badge badge-{{ $item['status'] === 'active' ? 'success' : ($item['status'] === 'archived' ? 'info' : 'warning') }}">
                            {{ ucfirst(str_replace('_', ' ', $item['status'])) }}
                        </span>
                    </td>
                    <td>
                        @if($item['path_match'])
                            <span class="badge badge-success"><i class="fas fa-check"></i> OK</span>
                        @else
                            <span class="badge badge-warning" title="Path in DB differs from actual path">
                                <i class="fas fa-exclamation-triangle"></i> Mismatch
                            </span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

{{-- Deleted Folder --}}
@if(!empty($result['deleted_files']))
<div class="card">
    <div class="card-header">
        <span>
            <i class="fas fa-trash" style="color:var(--text-muted);"></i>&ensp;
            Files in Deleted Folder
            <span class="badge badge-warning" style="margin-left:6px;">{{ count($result['deleted_files']) }}</span>
        </span>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Filename</th>
                <th>Size</th>
                <th>Moved Date</th>
                <th>Full Path</th>
            </tr>
        </thead>
        <tbody>
            @foreach($result['deleted_files'] as $file)
            @php $mb=$file['size']/1048576; $sz=$mb>=1024?number_format($mb/1024,2).' GB':number_format($mb,2).' MB'; @endphp
            <tr>
                <td><span style="font-weight:500;">{{ $file['filename'] }}</span></td>
                <td><span class="badge badge-info">{{ $sz }}</span></td>
                <td><span style="font-size:12.5px;color:var(--text-muted);">{{ $file['modified'] }}</span></td>
                <td><code style="font-size:11px;">{{ $file['full_path'] }}</code></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@endif {{-- drive_accessible --}}
@endsection