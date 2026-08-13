<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Contact Message - Thinker HUB</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#0f172a;-webkit-font-smoothing:antialiased;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f1f5f9;padding:32px 12px;">
        <tr>
            <td align="center">
                <!-- Top Logo -->
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 0 auto 20px;">
                    <tr>
                        <td align="center">
                            <a href="{{ config('app.url') }}" style="display: inline-block; text-decoration: none; font-size: 24px; font-weight: 800; color: #0a2d27; letter-spacing: -0.03em;">
                                think.er <span style="color: #0f766e;">HUB</span>
                            </a>
                        </td>
                    </tr>
                </table>

                <!-- Main Container -->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:620px;background-color:#ffffff;border-radius:18px;overflow:hidden;border:1px solid #e2e8f0;box-shadow:0 10px 25px -5px rgba(15,23,42,0.06),0 8px 10px -6px rgba(15,23,42,0.04);">
                    
                    <!-- Header Banner -->
                    <tr>
                        <td style="padding:28px 32px;background:linear-gradient(135deg,#0a2d27 0%,#115e59 100%);color:#ffffff;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td>
                                        <div style="display:inline-block;padding:4px 12px;background:rgba(255,255,255,0.12);border-radius:999px;font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#99f6e4;">
                                            think.er HUB &bull; Contact Inquiry
                                        </div>
                                        <h1 style="margin:12px 0 4px;font-size:24px;line-height:1.25;color:#ffffff;font-weight:800;letter-spacing:-0.02em;">
                                            New Contact Message
                                        </h1>
                                        <p style="margin:0;font-size:13px;line-height:1.5;color:#ccfbf1;">
                                            A new inquiry has been submitted via the public contact form.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Inquiry Metadata Card -->
                    <tr>
                        <td style="padding:24px 32px 12px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;border-collapse:separate;">
                                <tr>
                                    <td style="padding:16px 20px;border-bottom:1px solid #edf2f7;width:120px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;vertical-align:top;">
                                        Sender
                                    </td>
                                    <td style="padding:16px 20px;border-bottom:1px solid #edf2f7;font-size:15px;font-weight:700;color:#0f172a;">
                                        {{ $name }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:16px 20px;border-bottom:1px solid #edf2f7;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;vertical-align:top;">
                                        Email
                                    </td>
                                    <td style="padding:16px 20px;border-bottom:1px solid #edf2f7;font-size:15px;color:#0e7490;font-weight:600;">
                                        <a href="mailto:{{ $email }}?subject=Re:%20{{ rawurlencode($subject) }}" style="color:#0e7490;text-decoration:none;">{{ $email }}</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:16px 20px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;vertical-align:top;">
                                        Subject
                                    </td>
                                    <td style="padding:16px 20px;font-size:15px;color:#0f172a;font-weight:600;">
                                        <span style="display:inline-block;padding:2px 10px;background:#e0f2fe;color:#0369a1;border-radius:6px;font-size:13px;font-weight:700;">
                                            {{ $subject }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Message Body -->
                    <tr>
                        <td style="padding:12px 32px 24px;">
                            <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#64748b;margin-bottom:8px;">
                                Message Content
                            </div>
                            <div style="background:#ffffff;border:1px solid #cbd5e1;border-left:4px solid #0f766e;border-radius:8px;padding:18px 20px;font-size:14px;line-height:1.7;color:#334155;white-space:pre-wrap;word-break:break-word;">
{{ $bodyText }}
                            </div>
                        </td>
                    </tr>

                    <!-- Action Button -->
                    <tr>
                        <td style="padding:0 32px 28px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td>
                                        <a href="mailto:{{ $email }}?subject=Re:%20{{ rawurlencode($subject) }}" style="display:inline-block;background-color:#0a2d27;color:#ffffff;text-decoration:none;font-size:13px;font-weight:700;padding:12px 24px;border-radius:999px;letter-spacing:0.02em;box-shadow:0 2px 4px rgba(10,45,39,0.2);">
                                            &rarr; Reply directly to {{ $name }}
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Divider / Footer info -->
                    <tr>
                        <td style="padding:18px 32px 24px;background-color:#f8fafc;border-top:1px solid #e2e8f0;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="font-size:11px;line-height:1.6;color:#94a3b8;">
                                        This email was generated from the <a href="{{ config('app.url') }}" style="color:#0f766e;text-decoration:underline;">think.er HUB</a> contact form and delivered to <strong style="color:#64748b;">{{ config('mail.contact_to', config('mail.from.address')) }}</strong>.
                                    </td>
                                </tr>
                            </table>
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
