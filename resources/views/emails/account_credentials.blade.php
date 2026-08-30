<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Your account details</title>
</head>
<body style="margin:0;padding:0;background:#f2f6f4;font-family:Arial,Helvetica,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f2f6f4;padding:32px 0;">
        <tr>
            <td align="center">
                <table width="480" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:10px;overflow:hidden;border-top:4px solid #C9972F;">
                    <tr>
                        <td style="background:#0F7A45;padding:24px 32px;">
                            <span style="color:#fff;font-size:20px;font-weight:bold;">{{ $school->name ?? 'Taaluma SMS' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="font-size:16px;color:#222;margin-top:0;">Hi {{ $user->name }},</p>
                            <p style="font-size:15px;color:#444;line-height:1.5;">
                                An account has been created for you
                                @if($school) at <strong>{{ $school->name }}</strong> @endif
                                on the school management system. Here are your sign-in details:
                            </p>

                            <table cellpadding="8" cellspacing="0" style="background:#E8F5EC;border-radius:8px;width:100%;margin:20px 0;">
                                <tr>
                                    <td style="font-size:13px;color:#0A5A33;width:120px;">Username</td>
                                    <td style="font-size:15px;color:#0A5A33;font-weight:bold;font-family:monospace;">{{ $user->username }}</td>
                                </tr>
                                <tr>
                                    <td style="font-size:13px;color:#0A5A33;">Password</td>
                                    <td style="font-size:15px;color:#0A5A33;font-weight:bold;font-family:monospace;">{{ $plainPassword }}</td>
                                </tr>
                            </table>

                            <p style="font-size:14px;color:#444;line-height:1.5;">
                                This password was generated automatically and only you have received it.
                                For your security, you'll be asked to set your own password the first time
                                you sign in — after that it's yours alone; not even your school admin can see it.
                            </p>

                            <p style="text-align:center;margin:28px 0;">
                                <a href="{{ $loginUrl }}" style="background:#0F7A45;color:#fff;text-decoration:none;padding:12px 28px;border-radius:6px;font-size:15px;display:inline-block;">Sign In</a>
                            </p>

                            <p style="font-size:12px;color:#999;margin-bottom:0;">
                                If you weren't expecting this email, please contact your school administrator.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
