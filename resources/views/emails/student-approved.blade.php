<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account Approved</title>
</head>
<body style="margin:0;padding:0;background:#f4f7fb;font-family:Arial,'Helvetica Neue',Helvetica,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f4f7fb;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:680px;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e2e8f0;">
                    <tr>
                        <td style="padding:0;background:linear-gradient(120deg,#0a2d27 0%,#115e59 100%);">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="padding:24px 28px;">
                                        <p style="margin:0;font-size:11px;letter-spacing:0.16em;text-transform:uppercase;font-weight:700;color:#99f6e4;">think.er HUB</p>
                                        <h1 style="margin:10px 0 0;font-size:28px;line-height:1.2;color:#ffffff;font-weight:800;">Your Account Is Active</h1>
                                        <p style="margin:10px 0 0;font-size:14px;line-height:1.5;color:#ccfbf1;">Your registration has been approved. You can now access your student workspace.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:22px 28px 8px;">
                            <p style="margin:0;font-size:15px;line-height:1.6;color:#0f172a;">Hi {{ $student->name }},</p>
                            <p style="margin:10px 0 0;font-size:14px;line-height:1.65;color:#334155;">Great news. Your student account has been activated successfully.</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:10px 28px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse:separate;border-spacing:0 8px;">
                                <tr>
                                    <td style="width:140px;font-size:13px;color:#64748b;font-weight:700;vertical-align:top;">Name</td>
                                    <td style="font-size:15px;color:#0f172a;font-weight:700;">{{ $student->name }}</td>
                                </tr>
                                <tr>
                                    <td style="width:140px;font-size:13px;color:#64748b;font-weight:700;vertical-align:top;">Email</td>
                                    <td style="font-size:15px;color:#0f172a;">{{ $student->email }}</td>
                                </tr>
                                @if ($course)
                                    <tr>
                                        <td style="width:140px;font-size:13px;color:#64748b;font-weight:700;vertical-align:top;">Course</td>
                                        <td style="font-size:15px;color:#0f172a;">{{ $course->code }} - {{ $course->title }}</td>
                                    </tr>
                                @endif
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:12px 28px 8px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f0fdf4;border:1px solid #86efac;border-radius:12px;">
                                <tr>
                                    <td style="padding:14px 16px;">
                                        <p style="margin:0;font-size:14px;line-height:1.45;color:#166534;font-weight:700;">Access confirmed.</p>
                                        <p style="margin:8px 0 0;font-size:13px;line-height:1.55;color:#14532d;">
                                            @if (! empty($student->firebase_uid))
                                                Click the button below to sign in directly.
                                            @else
                                                Click the button below to open sign in with your email and password prefilled.
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 28px 12px;">
                            <a href="{{ $actionUrl }}" style="display:inline-block;background:#0a2d27;color:#ffffff;text-decoration:none;font-size:13px;font-weight:700;padding:11px 16px;border-radius:999px;">{{ $actionLabel }}</a>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:10px 28px 24px;">
                            <p style="margin:0;font-size:12px;color:#64748b;">If you need help accessing your account, reply to this email and the team will assist.</p>
                        </td>
                    </tr>
                </table>

                <p style="margin:14px 0 0;font-size:11px;color:#94a3b8;">This message was generated automatically by think.er HUB.</p>
            </td>
        </tr>
    </table>
</body>
</html>
