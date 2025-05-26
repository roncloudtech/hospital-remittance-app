<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">

    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <tr>
            <td>
                <h2 style="color: #333333; margin-bottom: 20px;">Hello {{ $user->firstname }},</h2>

                <!-- <p style="font-size: 16px; color: #555555; margin-bottom: 10px;">
                    You have been Thank you for registering at our <strong>Tutorial Center</strong>.
                </p> -->

                <p style="font-size: 16px; color: #555555; margin-bottom: 10px;">
                    Please click on this link to change you password:
                </p>

                <p style="font-size: 16px; color: #555555; margin-bottom: 10px;">
                    and you can click on this link to go the verification page:
                    <a href="http://localhost:3000/reset-password/?email={{ $user->email }}&id={{ $user->id }}">Verify Link</a>
                </p>

                <p style="font-size: 16px; color: #555555; margin-bottom: 10px;">
                    Or use this default password:
                </p>

                <p style="font-size: 24px; color: #007bff; font-weight: bold; text-align: center; margin: 20px 0;">
                    {{ $user->password }}
                </p>

                <p style="font-size: 14px; color: #999999; margin-top: 20px;">
                    If you did not initiate this request, you can safely ignore this message.
                </p>

                <p style="font-size: 16px; color: #555555; margin-top: 30px;">
                    Regards,<br>
                    <strong>NAFC Team</strong>
                </p>
            </td>
        </tr>
    </table>

</body>
</html>
