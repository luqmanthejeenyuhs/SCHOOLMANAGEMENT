<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reset your password</title>
</head>
<body style="margin:0;padding:0;background:#f2f6f4;font-family:Arial,Helvetica,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f2f6f4;padding:32px 0;">
        <tr>
            <td align="center">
                <table width="480" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:10px;overflow:hidden;border-top:4px solid #C9972F;">
                    <tr>
                        <td style="background:#012622;padding:24px 32px;">
                            <span style="color:#fff;font-size:20px;font-weight:bold;">{{ $school->name ?? 'Taaluma SMS' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="font-size:16px;color:#222;margin-top:0;">Hi {{ $user->name }},</p>
                            <p style="font-size:15px;color:#444;line-height:1.5;">
                                We received a request to reset the password for the account with username
                                <strong style="font-family:monospace;">{{ $user->username }}</strong>.
                            </p>

                            <p style="text-align:center;margin:28px 0;">
                                <a href="{{ $resetUrl }}" style="background:#012622;color:#fff;text-decoration:none;padding:12px 28px;border-radius:6px;font-size:15px;display:inline-block;">Reset Password</a>
                            </p>

                            <p style="font-size:14px;color:#444;line-height:1.5;">
                                This link expires in {{ $expiryMinutes }} minutes. Only you received this
                                email — not even your school admin can see or set your new password.
                            </p>

                            <p style="font-size:12px;color:#999;margin-bottom:0;">
                                If you didn't request this, you can safely ignore this email — your password
                                won't change unless you click the link above and set a new one.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
