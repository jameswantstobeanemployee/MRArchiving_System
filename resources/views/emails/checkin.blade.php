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
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f7f7f7; font-weight: bold; width: 40%; }
        .footer { background: #f7f7f7; padding: 15px 25px; font-size: 12px; color: #666; text-align: center; border-top: 1px solid #eee; }
    </style>
</head>
<body>
<div class="email-wrapper">
    <div class="header">
        <h1>🏥 Medical Records Archive System</h1>
        <p style="margin:5px 0 0;opacity:0.8;font-size:13px">Chart Return Confirmation</p>
    </div>
    <div class="body">
        <p>Hello <strong>{{ $checkout->returnedBy?->name ?? 'Staff' }}</strong>,</p>
        <p>The following chart has been <strong>returned</strong> to the Medical Records Archive:</p>

        <table>
            <tr><th>Patient</th><td>{{ $chart->patient->full_name }}</td></tr>
            <tr><th>MR Number</th><td>{{ $chart->patient->medical_record_number }}</td></tr>
            <tr><th>Case Number</th><td>{{ $chart->case_number }}</td></tr>
            <tr><th>Department</th><td>{{ $checkout->department }}</td></tr>
            <tr><th>Returned By</th><td>{{ $checkout->returnedBy?->name ?? '—' }}</td></tr>
            <tr><th>Original Due Date</th><td>{{ $checkout->expected_return_date->format('F j, Y') }}</td></tr>
            <tr><th>Returned At</th><td>{{ $checkout->returned_at?->format('F j, Y g:i A') }}</td></tr>
            @if($checkout->notes)
            <tr><th>Notes</th><td>{{ $checkout->notes }}</td></tr>
            @endif
        </table>

        <p style="background:#d4edda;border-left:4px solid #28a745;padding:10px 14px;border-radius:3px;font-size:13px">
            ✓ Chart has been returned and is now available in the archive.
        </p>
    </div>
    <div class="footer">
        Medical Records Archive System &bull; {{ now()->format('Y') }}
    </div>
</div>
</body>
</html>
