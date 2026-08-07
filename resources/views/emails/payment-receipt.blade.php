<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Receipt - think.er HUB</title>
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

                <!-- Main Card Container -->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:620px;background-color:#ffffff;border-radius:18px;overflow:hidden;border:1px solid #e2e8f0;box-shadow:0 10px 25px -5px rgba(15,23,42,0.06),0 8px 10px -6px rgba(15,23,42,0.04);">
                    
                    <!-- Header Banner -->
                    <tr>
                        <td style="padding:32px 32px 28px;background:linear-gradient(135deg,#0a2d27 0%,#115e59 100%);color:#ffffff;">
                            <div style="display:inline-block;padding:4px 12px;background:rgba(255,255,255,0.12);border-radius:999px;font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#99f6e4;">
                                Official Payment Receipt
                            </div>
                            <h1 style="margin:14px 0 6px;font-size:24px;line-height:1.25;color:#ffffff;font-weight:800;letter-spacing:-0.02em;">
                                Payment Confirmed! 💳
                            </h1>
                            <p style="margin:0;font-size:14px;line-height:1.5;color:#ccfbf1;">
                                Thank you for your enrollment payment. Your course access is fully active.
                            </p>
                        </td>
                    </tr>

                    <!-- Greeting -->
                    <tr>
                        <td style="padding:28px 32px 12px;">
                            @php
                                $rawStudentName = trim((string) ($student->name ?? ''));
                                $studentFirstName = $rawStudentName !== '' ? (explode(' ', $rawStudentName)[0] ?? $rawStudentName) : 'Learner';
                            @endphp
                            <p style="margin:0;font-size:16px;line-height:1.5;color:#0f172a;font-weight:700;">
                                Hello {{ $studentFirstName }}!
                            </p>
                            <p style="margin:8px 0 0;font-size:14px;line-height:1.65;color:#475569;">
                                We have successfully processed your course enrollment fee for <strong>{{ $course->title }}</strong>. Below is your official transaction receipt.
                            </p>
                        </td>
                    </tr>

                    <!-- Receipt Summary Table -->
                    <tr>
                        <td style="padding:12px 32px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;border-collapse:separate;">
                                <tr>
                                    <td style="padding:14px 20px;border-bottom:1px solid #edf2f7;width:130px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;">
                                        Reference #
                                    </td>
                                    <td style="padding:14px 20px;border-bottom:1px solid #edf2f7;font-size:13px;font-weight:700;color:#0f172a;font-family:monospace;">
                                        {{ $payment->reference }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 20px;border-bottom:1px solid #edf2f7;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;">
                                        Course
                                    </td>
                                    <td style="padding:14px 20px;border-bottom:1px solid #edf2f7;font-size:14px;font-weight:700;color:#0a2d27;">
                                        {{ $course->code }} &bull; {{ $course->title }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 20px;border-bottom:1px solid #edf2f7;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;">
                                        Amount Paid
                                    </td>
                                    <td style="padding:14px 20px;border-bottom:1px solid #edf2f7;font-size:18px;font-weight:800;color:#0f766e;">
                                        {{ $payment->formattedAmount() }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 20px;border-bottom:1px solid #edf2f7;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;">
                                        Payment Method
                                    </td>
                                    <td style="padding:14px 20px;border-bottom:1px solid #edf2f7;font-size:13px;color:#334155;font-weight:600;text-transform:capitalize;">
                                        {{ str_replace('_', ' ', $payment->payment_method) }} ({{ strtoupper($payment->provider ?? 'Gateway') }})
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 20px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;">
                                        Status
                                    </td>
                                    <td style="padding:14px 20px;font-size:13px;font-weight:700;">
                                        <span style="display:inline-block;padding:3px 10px;background:#dcfce7;color:#15803d;border-radius:999px;">
                                            ✓ PAID &amp; VERIFIED
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Primary Action Button -->
                    <tr>
                        <td style="padding:20px 32px 28px;text-align:center;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 auto;">
                                <tr>
                                    <td style="border-radius:999px;background:#0a2d27;">
                                        <a href="{{ route('dashboard') }}" style="display:inline-block;background-color:#0a2d27;color:#ffffff;text-decoration:none;font-size:14px;font-weight:700;padding:14px 36px;border-radius:999px;letter-spacing:0.02em;box-shadow:0 4px 6px -1px rgba(10,45,39,0.25);">
                                            Start Course on Dashboard &rarr;
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer Details -->
                    <tr>
                        <td style="padding:20px 32px 24px;background-color:#f8fafc;border-top:1px solid #e2e8f0;">
                            <p style="margin:0;font-size:12px;line-height:1.6;color:#64748b;">
                                If you have any billing questions or need a tax invoice, reply directly to this email or write to <a href="mailto:{{ config('mail.contact_to', 'thinkerhub@oristudiozm.com') }}" style="color:#0f766e;text-decoration:underline;">{{ config('mail.contact_to', 'thinkerhub@oristudiozm.com') }}</a>.
                            </p>
                        </td>
                    </tr>

                </table>

                <!-- Bottom Postal Footer -->
                <p style="margin:16px 0 0;font-size:11px;color:#94a3b8;text-align:center;line-height:1.5;">
                    &copy; {{ date('Y') }} think.er HUB &bull; 10A Off Natwange Street, Airport, Livingstone, Zambia<br>
                    Official learning platform by <a href="{{ config('app.url') }}" style="color:#0f766e;text-decoration:underline;">oristudiozm.com</a>
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
