<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subjectLine }}</title>
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
                                        <h1 style="margin:10px 0 0;font-size:24px;line-height:1.25;color:#ffffff;font-weight:800;">{{ $course->title ?? 'Course update' }}</h1>
                                        <p style="margin:10px 0 0;font-size:13px;line-height:1.5;color:#ccfbf1;">A message from your instructor{{ $sender->name ? ', '.$sender->name : '' }}.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px 28px;">
                            <div style="font-size:14px;line-height:1.7;color:#334155;">{!! nl2br(e($messageBody)) !!}</div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 28px 24px;">
                            <p style="margin:0;padding-top:16px;border-top:1px solid #e2e8f0;font-size:12px;line-height:1.6;color:#94a3b8;">
                                You are receiving this email because you are enrolled in {{ $course->title ?? 'this course' }} on think.er HUB.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
