<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subjectLine }} - Thinker HUB</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#0f172a;-webkit-font-smoothing:antialiased;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f1f5f9;padding:32px 12px;">
        <tr>
            <td align="center">
                <!-- Card Container -->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:620px;background-color:#ffffff;border-radius:18px;overflow:hidden;border:1px solid #e2e8f0;box-shadow:0 10px 25px -5px rgba(15,23,42,0.06),0 8px 10px -6px rgba(15,23,42,0.04);">
                    
                    <!-- Header Banner -->
                    <tr>
                        <td style="padding:28px 32px;background:linear-gradient(135deg,#0a2d27 0%,#115e59 100%);color:#ffffff;">
                            <div style="display:inline-block;padding:4px 12px;background:rgba(255,255,255,0.12);border-radius:999px;font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#99f6e4;">
                                think.er HUB &bull; Course Announcement
                            </div>
                            <h1 style="margin:12px 0 4px;font-size:24px;line-height:1.25;color:#ffffff;font-weight:800;letter-spacing:-0.02em;">
                                {{ $course->title ?? 'Course Update' }}
                            </h1>
                            <p style="margin:0;font-size:13px;line-height:1.5;color:#ccfbf1;">
                                Important message from {{ $sender->name ? 'Instructor '.$sender->name : 'your course instructor' }}
                            </p>
                        </td>
                    </tr>

                    <!-- Subject / Title Card -->
                    <tr>
                        <td style="padding:24px 32px 12px;">
                            <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#64748b;margin-bottom:6px;">
                                Subject
                            </div>
                            <div style="font-size:17px;font-weight:700;color:#0f172a;line-height:1.4;">
                                {{ $subjectLine }}
                            </div>
                        </td>
                    </tr>

                    <!-- Message Body -->
                    <tr>
                        <td style="padding:12px 32px 24px;">
                            <div style="background:#ffffff;border:1px solid #cbd5e1;border-left:4px solid #0f766e;border-radius:10px;padding:20px 22px;font-size:15px;line-height:1.75;color:#334155;word-break:break-word;">
                                {!! nl2br(e($messageBody)) !!}
                            </div>
                        </td>
                    </tr>

                    <!-- Quick Dashboard CTA -->
                    <tr>
                        <td style="padding:0 32px 28px;text-align:center;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 auto;">
                                <tr>
                                    <td style="border-radius:999px;background:#0a2d27;">
                                        <a href="{{ route('dashboard') }}" style="display:inline-block;background-color:#0a2d27;color:#ffffff;text-decoration:none;font-size:13px;font-weight:700;padding:12px 28px;border-radius:999px;letter-spacing:0.02em;">
                                            Go to Course Dashboard &rarr;
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer Details -->
                    <tr>
                        <td style="padding:18px 32px 24px;background-color:#f8fafc;border-top:1px solid #e2e8f0;">
                            <p style="margin:0;font-size:11px;line-height:1.6;color:#94a3b8;">
                                You received this notification because you are enrolled in <strong style="color:#64748b;">{{ $course->title ?? 'this course' }}</strong> on think.er HUB.
                            </p>
                        </td>
                    </tr>

                </table>

                <!-- Bottom Branding -->
                <p style="margin:16px 0 0;font-size:11px;color:#94a3b8;text-align:center;">
                    &copy; {{ date('Y') }} think.er HUB &bull; Livingstone, Zambia
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
