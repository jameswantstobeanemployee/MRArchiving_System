@extends('layouts.app')
@section('title', 'Search Files — {{ $drive->name }}')

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="page-header-left">
        <div class="breadcrumb">
            <a href="{{ route('admin.scanner.index') }}">Scanner</a>
            <i class="fas fa-chevron-right" style="font-size:9px;"></i>
            <span>Search Files</span>
        </div>
        <h1>
            <i class="fas fa-search-plus" style="color:var(--accent);margin-right:10px;font-size:20px;"></i>
            Search Files — {{ $drive->name }}
        </h1>
    </div>
    <div style="display:flex;gap:8px;">
        <form action="{{ route('admin.scanner.scan', $drive) }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-search"></i> Full Scan
            </button>
        </form>
        <a href="{{ route('admin.scanner.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

{{-- Drive Info Card --}}
<div class="card" style="margin-bottom:20px;">
    <div class="card-body" style="display:flex;gap:24px;align-items:center;flex-wrap:wrap;padding:14px 20px;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:34px;height:34px;border-radius:8px;background:var(--info-light);display:flex;align-items:center;justify-content:center;color:var(--info-text);font-size:15px;flex-shrink:0;">
                <i class="fas fa-hdd"></i>
            </div>
            <div>
                <div style="font-weight:700;font-size:13.5px;color:var(--text-primary);">{{ $drive->name }}</div>
                <code style="font-size:11px;">{{ $drive->drive_path }}</code>
            </div>
        </div>
        <div style="height:30px;width:1px;background:var(--divider);"></div>
        <div style="display:flex;align-items:center;gap:6px;font-size:13px;">
            <span style="color:var(--text-muted);">Status:</span>
            <span class="badge badge-{{ $drive->status === 'active' ? 'success' : 'danger' }}">
                <i class="fas fa-{{ $drive->status === 'active' ? 'check-circle' : 'times-circle' }}"></i>
                {{ ucfirst($drive->status) }}
            </span>
        </div>
        <div style="display:flex;align-items:center;gap:6px;font-size:13px;">
            @if(!$archiveAccessible)
                <span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Archive folder not found</span>
            @else
                <span class="badge badge-success"><i class="fas fa-check-circle"></i> Drive accessible</span>
            @endif
        </div>
    </div>
</div>

{{-- Search Form --}}
<div class="card">
    <div class="card-header">
        <span><i class="fas fa-filter"></i>&ensp;Search Files on Drive</span>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.scanner.search', $drive) }}" id="searchForm">
            <div style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
                <div class="form-group" style="flex:3;min-width:250px;margin-bottom:0;">
                    <label>Search Query</label>
                    <div style="position:relative;">
                        <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:13px;pointer-events:none;"></i>
                        <input type="text" name="q" id="searchInput" class="form-control"
                            style="padding-left:36px;"
                            value="{{ $query }}"
                            placeholder="Filename, patient name, MR#, or case number…"
                            autofocus autocomplete="off">
                    </div>
                    <div class="form-help">Minimum 2 characters. Searches both filenames on disk and database records.</div>
                </div>
                <div class="form-group" style="flex:1;min-width:160px;margin-bottom:0;">
                    <label>Search In</label>
                    <select name="type" class="form-control">
                        <option value="all"      {{ $type === 'all'      ? 'selected' : '' }}>All Fields</option>
                        <option value="filename" {{ $type === 'filename' ? 'selected' : '' }}>Filename Only</option>
                        <option value="patient"  {{ $type === 'patient'  ? 'selected' : '' }}>Patient / MR#</option>
                        <option value="case"     {{ $type === 'case'     ? 'selected' : '' }}>Case Number</option>
                    </select>
                </div>
                <div class="form-group" style="flex:0;margin-bottom:0;">
                    <label>&nbsp;</label>
                    <div style="display:flex;gap:6px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                        @if($query)
                        <a href="{{ route('admin.scanner.search', $drive) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@if($query && strlen($query) < 2)
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    <div>Please enter at least 2 characters to search.</div>
</div>
@endif

@if($query && strlen($query) >= 2)

{{-- Summary Bar --}}
<div style="background:var(--table-header-bg);border:1px solid var(--border-color);border-radius:var(--radius-md);padding:10px 16px;margin-bottom:16px;display:flex;gap:20px;flex-wrap:wrap;align-items:center;font-size:13px;">
    <span style="color:var(--text-muted);">Query:</span>
    <span style="font-weight:700;color:var(--text-primary);">"{{ $query }}"</span>
    <div style="height:16px;width:1px;background:var(--divider);"></div>
    <span>
        <i class="fas fa-folder" style="color:var(--warning);margin-right:4px;"></i>
        Files on disk: <strong>{{ $filesOnDisk->count() }}</strong>
    </span>
    <span>
        <i class="fas fa-database" style="color:var(--info);margin-right:4px;"></i>
        DB records: <strong>{{ $dbResults->count() }}</strong>
    </span>
    @if($filesOnDisk->isEmpty() && $dbResults->isEmpty())
        <span class="badge badge-danger"><i class="fas fa-times-circle"></i> No results found</span>
    @endif
</div>

{{-- Files on Disk --}}
@if($filesOnDisk->isNotEmpty())
<div class="card">
    <div class="card-header">
        <span>
            <i class="fas fa-folder-open" style="color:var(--warning);"></i>&ensp;
            Files on Disk
            <span class="badge badge-warning" style="margin-left:6px;">{{ $filesOnDisk->count() }}</span>
        </span>
        <span style="font-size:12px;font-weight:400;color:var(--text-muted);">{{ $archiveDir }}</span>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Filename</th>
                <th>Size</th>
                <th>Modified</th>
                <th>In Database</th>
                <th>Patient</th>
                <th>Case #</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($filesOnDisk as $file)
            <tr>
                <td>
                    <div style="font-weight:600;font-size:13.5px;">{{ $file['filename'] }}</div>
                    @if($query)
                    <div style="font-size:11px;color:var(--text-muted);font-family:'DM Mono',monospace;margin-top:2px;">{{ $file['full_path'] }}</div>
                    @endif
                </td>
                <td>
                    <span class="badge badge-info">{{ $file['size_formatted'] }}</span>
                </td>
                <td>
                    <span style="font-size:12.5px;color:var(--text-muted);white-space:nowrap;">{{ $file['modified'] }}</span>
                </td>
                <td>
                    @if($file['chart'])
                        <span class="badge badge-success"><i class="fas fa-check-circle"></i> Yes</span>
                    @else
                        <span class="badge badge-danger"><i class="fas fa-times-circle"></i> Orphaned</span>
                    @endif
                </td>
                <td>
                    <span style="font-weight:500;">{{ $file['chart'] ? $file['chart']->patient->full_name : '—' }}</span>
                </td>
                <td>
                    @if($file['chart'])
                    <a href="{{ route('charts.show', $file['chart']->id) }}"
                       style="color:var(--accent);font-weight:600;text-decoration:none;">
                        {{ $file['chart']->case_number }}
                    </a>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    <div class="row-actions" style="visibility:visible;">
                        <a href="{{ route('admin.scanner.download', ['drive' => $drive->id, 'path' => $file['full_path']]) }}"
                           class="action-btn">
                            <i class="fas fa-download"></i> Download
                        </a>
                        @if($file['chart'])
                        <a href="{{ route('charts.show', $file['chart']->id) }}" class="action-btn">
                            <i class="fas fa-eye"></i> View
                        </a>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Database Records --}}
@if($dbResults->isNotEmpty())
<div class="card">
    <div class="card-header">
        <span>
            <i class="fas fa-database" style="color:var(--info);"></i>&ensp;
            Matching Database Records
            <span class="badge badge-info" style="margin-left:6px;">{{ $dbResults->count() }}</span>
        </span>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Patient</th>
                <th>MR#</th>
                <th>Case #</th>
                <th>Filename</th>
                <th>Archived</th>
                <th>Status</th>
                <th>File on Drive</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dbResults as $chart)
            @php $exists = $chart->digital_copy_path && file_exists($chart->digital_copy_path); @endphp
            <tr>
                <td>
                    <span style="font-weight:600;">{{ $chart->patient->full_name }}</span>
                </td>
                <td>
                    <span style="font-family:'DM Mono',monospace;font-size:12px;color:var(--text-muted);">
                        {{ $chart->patient->medical_record_number }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('charts.show', $chart->id) }}"
                       style="color:var(--accent);font-weight:600;text-decoration:none;">
                        {{ $chart->case_number }}
                    </a>
                </td>
                <td>
                    <span style="font-family:'DM Mono',monospace;font-size:11.5px;color:var(--text-secondary);display:block;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                          title="{{ $chart->digital_copy_path }}">
                        {{ $chart->digital_copy_path ? basename($chart->digital_copy_path) : '—' }}
                    </span>
                </td>
                <td>
                    <span style="font-size:12.5px;color:var(--text-muted);">
                        {{ $chart->archived_date->format('m/d/Y') }}
                    </span>
                </td>
                <td>
                    <span class="badge badge-{{ $chart->status === 'active' ? 'success' : ($chart->status === 'archived' ? 'info' : 'warning') }}">
                        {{ ucfirst(str_replace('_', ' ', $chart->status)) }}
                    </span>
                </td>
                <td>
                    @if(!$chart->digital_copy_path)
                        <span style="color:var(--text-muted);font-size:12.5px;">No file</span>
                    @elseif($exists)
                        <span class="badge badge-success"><i class="fas fa-check-circle"></i> Found</span>
                    @else
                        <span class="badge badge-danger"><i class="fas fa-times-circle"></i> Missing</span>
                    @endif
                </td>
                <td>
                    <div class="row-actions" style="visibility:visible;">
                        <a href="{{ route('charts.show', $chart->id) }}" class="action-btn">
                            <i class="fas fa-eye"></i> View
                        </a>
                        @if($exists)
                        <a href="{{ route('admin.scanner.download', ['drive' => $drive->id, 'path' => $chart->digital_copy_path]) }}"
                           class="action-btn">
                            <i class="fas fa-download"></i> Download
                        </a>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- No Results --}}
@if($filesOnDisk->isEmpty() && $dbResults->isEmpty())
<div class="card">
    <div class="empty-state">
        <i class="fas fa-search empty-state-icon"></i>
        <h3>No results found for "{{ $query }}"</h3>
        <p>Try a different search term or change the search type.</p>
    </div>
</div>
@endif

@endif {{-- query check --}}

@endsection

@push('scripts')
<script>
document.getElementById('searchInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('searchForm').submit();
    }
});
</script>
@endpush