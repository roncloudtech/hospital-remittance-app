<!DOCTYPE html>
<html>
<head>
    <title>Remittance Reminder</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f8f9fa; padding: 20px;">
    <div style="max-width: 600px; margin: auto; background: #ffffff; padding: 30px; border-radius: 8px;">
        <h2 style="color: #2d3748;">📌 Outstanding Remittance Alert</h2>

        <p>Hello {{ $name }},</p>

        <p>This is a reminder that the hospital <strong>{{ $hospital->hospital_name }}</strong> has an outstanding remittance balance.</p>

        <ul style="line-height: 1.8;">
            <li><strong>📅 Month:</strong> {{ $monthName }} {{ $year }}</li>
            <li><strong>📊 Previous Unpaid Balance:</strong> ₦{{ number_format($previousBalance, 2) }}</li>
            <li><strong>➕ Current Month Target:</strong> ₦{{ number_format($currentTarget, 2) }}</li>
            <li><strong>💰 Total Paid So Far:</strong> ₦{{ number_format($totalPaid, 2) }}</li>
            <li><strong>❗ Total Outstanding:</strong> <span style="color: red;">₦{{ number_format($totalDue, 2) }}</span></li>
        </ul>

        <p>Please ensure remittance is made promptly to avoid penalties or escalations.</p>

        <p>Thank you,<br>{{ config('app.name') }} Team</p>
    </div>
</body>
</html>
