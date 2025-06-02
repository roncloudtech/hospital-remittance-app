<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>User Registration</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">

    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <tr>
            <td>
                <h2 style="color: #333333; margin-bottom: 20px;">Hello {{ $user->firstname }} {{ $user->lastname }},</h2>

                <p style="font-size: 16px; color: #555555; margin-bottom: 10px;">
                    You have been registered as a/an <strong>{{ $user->role }}</strong>.
                </p>

                <p style="font-size: 16px; color: #555555; margin-bottom: 10px;">
                    Please use the password below and your email address: {{ $user->email }}
                </p>

                <p style="font-size: 24px; color: #E83831; font-weight: bold; text-align: center; margin: 20px 0;">
                    {{ $tempPassword }}
                </p>
                
                <!-- <p style="font-size: 16px; color: #555555; margin-bottom: 10px;">
                    and you can click on this link to go the verification page:
                    <a href="https://tcapi.roncloud.com.ng/email-verification?identifier={{ $user->email }}">Verify Link</a>
                </p> -->

                <p style="font-size: 14px; color: #999999; margin-top: 20px;">
                    If you did not initiate this request, you can safely ignore this message.
                </p>

                <p style="font-size: 16px; color: #555555; margin-top: 30px;">
                    Best Regards,<br>
                    <strong>NAFC Services Team</strong>
                </p>
            </td>
        </tr>
    </table>

</body>
</html>
