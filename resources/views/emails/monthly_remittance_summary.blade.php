<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Monthly Remittance Summary</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div style="max-width: 600px; margin: auto; background: #ffffff; padding: 30px; border-radius: 8px;">
        <h2 style="color: #2f855a;">Hello {{ $name }},</h2>

        <p>A new remittance target has been generated for:</p>

        <ul>
            <li><strong>Hospital:</strong> {{ $hospital->hospital_name }}</li>
            <li><strong>Target Month:</strong> {{ $monthName }} {{ $year }}</li>
            <li><strong>New Month Target:</strong> ₦{{ number_format($newTarget, 2) }}</li>
            <li><strong>Previous Unpaid Balance:</strong> ₦{{ number_format($previousBalance, 2) }}</li>
            <li><strong><span style="color: #c53030;">Total Expected:</span></strong> ₦{{ number_format($totalDue, 2) }}</li>
        </ul>

        <p>Please ensure remittance is made promptly.</p>

        <p>Thank you,<br>{{ config('app.name') }}</p>
    </div>
</body>
</html>
