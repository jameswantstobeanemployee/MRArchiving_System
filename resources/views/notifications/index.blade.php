@extends('layouts.app')
@section('title', 'Notifications')

@push('styles')
<style>
    /* ── Notifications extras ────────────────────────────────────── */

    /* Notification row */
    .notif-row {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 14px 20px;
        border-bottom: 1px solid var(--divider);
        transition: background var(--transition);
        position: relative;
    }

    .notif-row:last-child { border-bottom: none; }

    .notif-row:hover { background: var(--table-row-hover); }

    /* Unread tint */
    .notif-row.unread {
        background: var(--info-light);
    }

    .notif-row.unread:hover {
        background: color-mix(in srgb, var(--info-light) 80%, var(--table-row-hover));
    }

    /* Unread dot indicator */
    .notif-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--accent);
        flex-shrink: 0;
        margin-top: 6px;
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 20%, transparent);
    }

    .notif-dot.read {
        background: var(--border-color);
        box-shadow: none;
    }

    /* Icon bubble */
    .notif-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: var(--info-light);
        color: var(--info-text);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }

    .notif-row.unread .notif-icon {
        background: var(--accent);
        color: white;
    }

    /* Content */
    .notif-body { flex: 1; min-width: 0; }

    .notif-title {
        font-size: 13.5px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 3px;
        line-height: 1.4;
    }

    .notif-row.read .notif-title {
        font-weight: 500;
        color: var(--text-secondary);
    }

    .notif-message {
        font-size: 12.5px;
        color: var(--text-muted);
        line-height: 1.5;
    }

    .notif-time {
        font-size: 11.5px;
        color: var(--text-muted);
        margin-top: 5px;
        display: flex;
        align-items: center;
        gap: 5px;
        font-family: 'DM Mono', monospace;
    }

    /* Actions */
    .notif-actions {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
        opacity: 0;
        transition: opacity var(--transition);
    }

    .notif-row:hover .notif-actions { opacity: 1; }

    /* Always visible on mobile */
    @media (max-width: 768px) {
        .notif-actions { opacity: 1; }
    }

    /* Delete btn compact */
    .notif-delete-btn {
        width: 28px;
        height: 28px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        border-radius: var(--radius-sm);
        background: none;
        border: 1px solid var(--border-color);
        color: var(--text-muted);
        cursor: pointer;
        transition: all var(--transition);
        font-family: inherit;
    }

    .notif-delete-btn:hover {
        background: var(--danger-light);
        border-color: var(--danger-border);
        color: var(--danger-text);
    }

    /* Empty state */
    .notif-empty { padding: 56px 24px; }

    /* Pagination */
    .pagination-wrap {
        padding: 14px 20px;
        border-top: 1px solid var(--divider);
        display: flex;
        justify-content: flex-end;
    }

    /* Unread count badge in header */
    .unread-count {
        font-size: 12px;
        font-weight: 500;
        color: var(--text-muted);
    }

    .unread-count strong { color: var(--accent); }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="page-header-left">
        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            Notifications
        </div>
        <h1>Notifications</h1>
    </div>
    <form action="{{ route('notifications.read-all') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-secondary">
            <i class="fas fa-check-double"></i> Mark All Read
        </button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <span>
            <i class="fas fa-bell"></i> &nbsp;All Notifications
            <span class="unread-count">
                &nbsp;— <strong>{{ $notifications->total() }}</strong> total
            </span>
        </span>
    </div>

    @if($notifications->isEmpty())
        <div class="notif-empty">
            <div class="empty-state">
                <i class="fas fa-bell-slash empty-state-icon"></i>
                <h3>You're all caught up</h3>
                <p>No notifications to show right now.</p>
            </div>
        </div>
    @else
        <div>
            @foreach($notifications as $n)
            <div class="notif-row {{ $n->is_read ? 'read' : 'unread' }}">

                {{-- Dot --}}
                <div class="notif-dot {{ $n->is_read ? 'read' : '' }}"></div>

                {{-- Icon --}}
                <div class="notif-icon">
                    <i class="fas fa-bell"></i>
                </div>

                {{-- Content --}}
                <div class="notif-body">
                    <div class="notif-title">{{ $n->title }}</div>
                    <div class="notif-message">{{ $n->message }}</div>
                    <div class="notif-time">
                        <i class="fas fa-clock" style="font-size:10px"></i>
                        {{ $n->sent_at->diffForHumans() }}
                    </div>
                </div>

                {{-- Actions --}}
                <div class="notif-actions">
                    @if(!$n->is_read)
                    <form action="{{ route('notifications.read', $n) }}" method="POST" style="display:inline">
                        @csrf
                        <button type="submit" class="btn btn-xs btn-secondary" title="Mark as read">
                            <i class="fas fa-check"></i> Read
                        </button>
                    </form>
                    @endif
                    <form action="{{ route('notifications.destroy', $n) }}" method="POST" style="display:inline"
                          onsubmit="return confirmDelete(this, 'Delete this notification?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="notif-delete-btn" title="Delete">
                            <i class="fas fa-times"></i>
                        </button>
                    </form>
                </div>

            </div>
            @endforeach
        </div>

        @if($notifications->hasPages())
        <div class="pagination-wrap">
            {{ $notifications->links() }}
        </div>
        @endif
    @endif
</div>

@endsection