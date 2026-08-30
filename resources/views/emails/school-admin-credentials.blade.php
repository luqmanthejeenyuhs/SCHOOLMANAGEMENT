<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="margin:0;padding:0;background:#f2f8f4;font-family:Arial,Helvetica,sans-serif;color:#222;">
    <table width="100%" cellpadding="0" cellspacing="0" style="padding:32px 16px;">
        <tr>
            <td align="center">
                <table width="100%" style="max-width:520px;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e6f0ea;">
                    <tr>
                        <td style="background:#012622;padding:20px 28px;">
                            <span style="color:#FBF1DC;font-size:18px;font-weight:700;">Taaluma SMS</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <h2 style="margin:0 0 16px;color:#01201D;">Welcome to Taaluma SMS</h2>
                            <p style="margin:0 0 16px;line-height:1.5;">Hi {{ $admin->name }},</p>
                            <p style="margin:0 0 16px;line-height:1.5;">
                                An account has been created for you as the administrator of
                                <strong>{{ $school->name }}</strong> on Taaluma SMS.
                            </p>

                            <table width="100%" cellpadding="8" style="background:#EBEEED;border-radius:8px;margin:0 0 16px;">
                                <tr><td style="font-size:13px;color:#4D6764;">Login URL</td></tr>
                                <tr><td style="font-weight:600;word-break:break-all;">{{ $loginUrl }}</td></tr>
                                <tr><td style="font-size:13px;color:#4D6764;padding-top:10px;">Email</td></tr>
                                <tr><td style="font-weight:600;">{{ $admin->email }}</td></tr>
                                <tr><td style="font-size:13px;color:#4D6764;padding-top:10px;">Temporary Password</td></tr>
                                <tr><td style="font-weight:700;font-size:16px;letter-spacing:0.5px;">{{ $temporaryPassword }}</td></tr>
                            </table>

                            <p style="margin:0 0 16px;line-height:1.5;">
                                This password is shown only in this email — nobody else, including whoever set up
                                your school, has access to it. For security, you'll be asked to set your own
                                password the first time you log in.
                            </p>

                            <p style="text-align:center;margin:24px 0;">
                                <a href="{{ $loginUrl }}" style="background:#012622;color:#FBF1DC;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600;display:inline-block;">Log In Now</a>
                            </p>

                            <p style="margin:0;line-height:1.5;color:#4D6764;font-size:14px;">
                                Once you're in, you can add your own teachers, students, classes, and fee
                                structures — everything from here is in your hands.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
