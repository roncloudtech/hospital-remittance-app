<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Monthly Remittance Balance Summary</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            background-color: #f8f9fa;
            margin: 0;
            padding: 2rem;
        }
        .container {
            background-color: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            max-width: 700px;
            margin: auto;
        }
        h1 {
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #e9ecef;
        }
        .summary {
            margin-top: 1.5rem;
            padding: 1rem;
            background-color: #f1f3f5;
            border-left: 4px solid #28a745;
            font-size: 1rem;
        }
        .footer {
            margin-top: 2rem;
            font-size: 0.9rem;
            color: #888;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📌 Monthly Remittance Summary</h1>

        <p>Dear {{ $name ?? 'Remitter' }},</p>

        <p>This is a summary of your expected payments for <strong>{{ $monthWord }} {{ $year }}</strong>. Below is the breakdown of remittance balances across your assigned hospitals:</p>

        <table>
            <thead>
                <tr>
                    <th>Hospital</th>
                    <th>Total Balance (₦)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($balances as $hospitalName => $amount)
                    <tr>
                        <td>{{ $hospitalName }}</td>
                        <td>₦{{ number_format($amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            Please ensure all pending balances are remitted promptly to maintain your account in good standing.
        </div>

        <p>Thank you for your continued cooperation.</p>

        <p>Warm regards,<br><strong>{{ config('app.name') }}</strong></p>

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
