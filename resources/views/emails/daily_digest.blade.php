<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #333; background: #f4f4f4; margin: 0; padding: 20px; }
        .email-wrapper { max-width: 600px; margin: 0 auto; background: #fff; border: 1px solid #ddd; border-radius: 6px; overflow: hidden; }
        .header { background: #1a3c5e; color: #fff; padding: 20px 25px; }
        .header h1 { font-size: 18px; margin: 0; }
        .body { padding: 25px; }
        .stat-row { display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; }
        .stat-box { flex: 1; min-width: 120px; border: 1px solid #ddd; border-radius: 4px; padding: 15px; text-align: center; background: #f9f9f9; }
        .stat-number { font-size: 28px; font-weight: bold; }
        .stat-label  { font-size: 12px; color: #666; margin-top: 4px; }
        .alert-item  { padding: 10px 14px; border-left: 4px solid #ffc107; background: #fff3cd; border-radius: 3px; margin-bottom: 8px; font-size: 13px; }
        .ok-item     { padding: 10px 14px; border-left: 4px solid #28a745; background: #d4edda; border-radius: 3px; margin-bottom: 8px; font-size: 13px; }
        .footer      { background: #f7f7f7; padding: 15px 25px; font-size: 12px; color: #666; text-align: center; border-top: 1px solid #eee; }
    </style>
</head>
<body>
<div class="email-wrapper">
    <div class="header">
        <h1>🏥 Medical Records Archive System</h1>
        <p style="margin:5px 0 0;opacity:0.8;font-size:13px">Daily Digest &mdash; {{ $stats['date'] }}</p>
    </div>
    <div class="body">
        <p>Hello <strong>{{ $user->name }}</strong>,</p>
        <p>Here is your daily summary for the Medical Records Archive:</p>

        <div class="stat-row">
            <div class="stat-box">
                <div class="stat-number" style="{{ $stats['overdue_checkouts'] > 0 ? 'color:#dc3545' : 'color:#28a745' }}">{{ $stats['overdue_checkouts'] }}</div>
                <div class="stat-label">Overdue Checkouts</div>
            </div>
            <div class="stat-box">
                <div class="stat-number" style="{{ $stats['expiring_30_days'] > 0 ? 'color:#856404' : 'color:#155724' }}">{{ $stats['expiring_30_days'] }}</div>
                <div class="stat-label">Expiring (30 days)</div>
            </div>
            <div class="stat-box">
                <div class="stat-number" style="{{ $stats['near_full_boxes'] > 0 ? 'color:#856404' : 'color:#155724' }}">{{ $stats['near_full_boxes'] }}</div>
                <div class="stat-label">Boxes Near Full</div>
            </div>
            <div class="stat-box">
                <div class="stat-number" style="color:#004085">{{ $stats['archived_today'] }}</div>
                <div class="stat-label">Archived Today</div>
            </div>
        </div>

        @if($stats['overdue_checkouts'] > 0)
        <div class="alert-item">⚠️ <strong>{{ $stats['overdue_checkouts'] }}</strong> chart(s) are overdue for return. Please follow up with the relevant departments.</div>
        @else
        <div class="ok-item">✓ No overdue checkouts today.</div>
        @endif

        @if($stats['expiring_30_days'] > 0)
        <div class="alert-item">📅 <strong>{{ $stats['expiring_30_days'] }}</strong> chart(s) have retention dates within the next 30 days. Please review the Retention Report.</div>
        @endif

        @if($stats['near_full_boxes'] > 0)
        <div class="alert-item">📦 <strong>{{ $stats['near_full_boxes'] }}</strong> storage box(es) are at 80%+ capacity. Consider adding new boxes.</div>
        @endif

        <p style="margin-top:20px;font-size:13px;color:#666">
            Log in to the <a href="{{ config('app.url') }}">Medical Records Archive System</a> to view full details and take action.
        </p>
    </div>
    <div class="footer">
        Medical Records Archive System &bull; {{ now()->format('Y') }}<br>
        <small>You are receiving this because you are an active system user. Contact your admin to unsubscribe.</small>
    </div>
</div>
</body>
</html>
