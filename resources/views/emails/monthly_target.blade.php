<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Monthly Remittance Target</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f4f4f4;
      padding: 40px 0;
      margin: 0;
    }
    .email-container {
      max-width: 600px;
      margin: auto;
      background-color: #ffffff;
      border-radius: 10px;
      padding: 30px;
      box-shadow: 0 0 15px rgba(0,0,0,0.05);
    }
    .header {
      text-align: center;
      background-color: #1f2937;
      color: white;
      padding: 20px;
      border-radius: 10px 10px 0 0;
    }
    .header h1 {
      margin: 0;
      font-size: 22px;
    }
    .content {
      padding: 20px;
    }
    .content p {
      font-size: 16px;
      color: #333333;
      line-height: 1.6;
    }
    .details {
      background-color: #f9fafb;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      padding: 15px;
      margin-top: 20px;
    }
    .details strong {
      display: inline-block;
      width: 150px;
      color: #111827;
    }
    .footer {
      margin-top: 30px;
      text-align: center;
      font-size: 14px;
      color: #6b7280;
    }
    .button {
      display: inline-block;
      margin-top: 20px;
      padding: 10px 20px;
      background-color: #065f46;
      color: #ffffff !important;
      text-decoration: none;
      border-radius: 5px;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <div class="email-container">
    <div class="header">
      <h1>🧾 Monthly Remittance Target - {{ $monthWord }} {{ $year }}</h1>
    </div>

    <div class="content">
      <p>Dear {{ $name ?? 'Remitter' }},</p>

      <p>
        A new monthly remittance target has been generated for the hospital under your supervision.
        Kindly find the details below:
      </p>

      <div class="details">
        <p><strong>🏥 Hospital:</strong> {{ $hospital->hospital_name }}</p>
        <p><strong>📅 Month:</strong> {{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}</p>
        <p><strong>💰 Monthly Target:</strong> ₦{{ number_format($target, 2) }}</p>
      </div>

      <p>
        Please ensure prompt remittance and proper follow-up with the hospital management.
      </p>

      <!-- <a href="{{ url('/') }}" class="button">Visit Dashboard</a> -->

      <p class="footer">
        Thank you,<br>
        <strong>{{ config('app.name') }}</strong>
      </p>
    </div>
  </div>
</body>
</html>
