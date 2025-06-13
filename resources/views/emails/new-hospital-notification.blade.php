<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Hospital Notification</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f8f9fa; padding: 30px; color: #212529;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: auto; background-color: #ffffff; border: 1px solid #dee2e6; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <tr>
            <td style="padding: 30px;">
                <h2 style="color: #343a40; border-bottom: 1px solid #dee2e6; padding-bottom: 10px;">New Hospital Assigned</h2>

                <p style="font-size: 16px;">Dear <strong>{{ $name }}</strong>,</p>

                <p style="font-size: 15px;">A new hospital has just been registered and assigned to you. Please find the details below:</p>

                <table style="width: 100%; font-size: 15px; border-collapse: collapse; margin-top: 20px;">
                    <tr>
                        <td style="padding: 8px; border-bottom: 1px solid #f1f1f1;"><strong>Hospital ID:</strong></td>
                        <td style="padding: 8px; border-bottom: 1px solid #f1f1f1;">{{ $hospital->hospital_id }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; border-bottom: 1px solid #f1f1f1;"><strong>Name:</strong></td>
                        <td style="padding: 8px; border-bottom: 1px solid #f1f1f1;">{{ $hospital->hospital_name }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; border-bottom: 1px solid #f1f1f1;"><strong>Division:</strong></td>
                        <td style="padding: 8px; border-bottom: 1px solid #f1f1f1;">{{ $hospital->military_division }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; border-bottom: 1px solid #f1f1f1;"><strong>Address:</strong></td>
                        <td style="padding: 8px; border-bottom: 1px solid #f1f1f1;">{{ $hospital->address }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; border-bottom: 1px solid #f1f1f1;"><strong>Phone:</strong></td>
                        <td style="padding: 8px; border-bottom: 1px solid #f1f1f1;">{{ $hospital->phone_number }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px;"><strong>Monthly Remittance Target:</strong></td>
                        <td style="padding: 8px;">₦{{ number_format($hospital->monthly_remittance_target, 2) }}</td>
                    </tr>
                </table>

                <p style="margin-top: 30px; font-size: 15px;">Please follow up accordingly.</p>

                <p style="margin-top: 20px; font-size: 15px;">Best regards,<br><strong>Capitation Remittance Team</strong></p>
            </td>
        </tr>
    </table>
</body>
</html>
