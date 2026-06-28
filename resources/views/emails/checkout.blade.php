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
        .badge { display: inline-block; padding: 3px 10px; border-radius: 10px; font-size: 12px; font-weight: bold; background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
<div class="email-wrapper">
    <div class="header">
        <h1>🏥 Medical Records Archive System</h1>
        <p style="margin:5px 0 0;opacity:0.8;font-size:13px">Chart Check-Out Confirmation</p>
    </div>
    <div class="body">
        <p>Hello <strong>{{ $checkout->checkedOutBy->name }}</strong>,</p>
        <p>The following chart has been checked out from the Medical Records Archive:</p>

        <table>
            <tr><th>Patient</th><td>{{ $chart->patient->full_name }}</td></tr>
            <tr><th>MR Number</th><td>{{ $chart->patient->medical_record_number }}</td></tr>
            <tr><th>Case Number</th><td>{{ $chart->case_number }}</td></tr>
            <tr><th>Department</th><td>{{ $checkout->department }}</td></tr>
            <tr><th>Received By</th><td>{{ $checkout->person }}</td></tr>
            <tr><th>Purpose</th><td>{{ $checkout->purpose }}</td></tr>
            <tr><th>Checked Out</th><td>{{ $checkout->checked_out_at->format('F j, Y g:i A') }}</td></tr>
            <tr><th>Expected Return</th><td><strong>{{ $checkout->expected_return_date->format('F j, Y') }}</strong></td></tr>
        </table>

        <p style="background:#fff3cd;border-left:4px solid #ffc107;padding:10px 14px;border-radius:3px;font-size:13px">
            ⚠️ <strong>Important:</strong> Please return the chart by <strong>{{ $checkout->expected_return_date->format('F j, Y') }}</strong>.
            Failure to return on time will trigger an overdue notification.
        </p>

        <p style="margin-top:16px;font-size:13px;color:#666">
            If you have any questions, please contact the Medical Records department.
        </p>
    </div>
    <div class="footer">
        Medical Records Archive System &bull; {{ now()->format('Y') }}
    </div>
</div>
</body>
</html>
