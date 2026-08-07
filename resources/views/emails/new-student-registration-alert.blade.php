<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Student Registration - Thinker HUB</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#0f172a;-webkit-font-smoothing:antialiased;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">
        Student registration summary for {{ $student->name }} ({{ $course->code }}).
    </div>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f1f5f9;padding:32px 12px;">
        <tr>
            <td align="center">
                <!-- Card Container -->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:620px;background-color:#ffffff;border-radius:18px;overflow:hidden;border:1px solid #e2e8f0;box-shadow:0 10px 25px -5px rgba(15,23,42,0.06),0 8px 10px -6px rgba(15,23,42,0.04);">
                    
                    <!-- Header Banner -->
                    <tr>
                        <td style="padding:28px 32px;background:linear-gradient(135deg,#0a2d27 0%,#0f766e 100%);color:#ffffff;">
                            <div style="display:inline-block;padding:4px 12px;background:rgba(255,255,255,0.12);border-radius:999px;font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#ccfbf1;">
                                think.er HUB &bull; Admin Notification
                            </div>
                            <h1 style="margin:12px 0 4px;font-size:24px;line-height:1.25;color:#ffffff;font-weight:800;letter-spacing:-0.02em;">
                                New Student Enrollment
                            </h1>
                            <p style="margin:0;font-size:13px;line-height:1.5;color:#e6fffa;">
                                A new learner has registered and joined a course offering.
                            </p>
                        </td>
                    </tr>

                    <!-- Details Table -->
                    <tr>
                        <td style="padding:24px 32px 12px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;border-collapse:separate;">
                                <tr>
                                    <td style="padding:14px 20px;border-bottom:1px solid #edf2f7;width:130px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;">
                                        Student Name
                                    </td>
                                    <td style="padding:14px 20px;border-bottom:1px solid #edf2f7;font-size:14px;font-weight:700;color:#0f172a;">
                                        {{ $student->name }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 20px;border-bottom:1px solid #edf2f7;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;">
                                        Email
                                    </td>
                                    <td style="padding:14px 20px;border-bottom:1px solid #edf2f7;font-size:14px;color:#0e7490;font-weight:600;">
                                        <a href="mailto:{{ $student->email }}" style="color:#0e7490;text-decoration:none;">{{ $student->email }}</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 20px;border-bottom:1px solid #edf2f7;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;">
                                        Course
                                    </td>
                                    <td style="padding:14px 20px;border-bottom:1px solid #edf2f7;font-size:14px;color:#0f766e;font-weight:700;">
                                        <span style="display:inline-block;padding:2px 8px;background:#ccfbf1;color:#115e59;border-radius:6px;font-size:12px;font-weight:700;margin-right:4px;">
                                            {{ $course->code }}
                                        </span>
                                        {{ $course->title }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 20px;border-bottom:1px solid #edf2f7;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;">
                                        Track / Level
                                    </td>
                                    <td style="padding:14px 20px;border-bottom:1px solid #edf2f7;font-size:14px;color:#0f172a;font-weight:600;">
                                        {{ $student->track ?: 'Standard' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 20px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;">
                                        Registered At
                                    </td>
                                    <td style="padding:14px 20px;font-size:13px;color:#475569;">
                                        {{ optional($student->created_at)->toDayDateTimeString() ?: now()->toDayDateTimeString() }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Payment / Status Banner -->
                    <tr>
                        <td style="padding:12px 32px;">
                            @if($requiresPaymentApproval)
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#fffbeb;border:1px solid #fde68a;border-radius:12px;">
                                    <tr>
                                        <td style="padding:16px 20px;">
                                            <div style="font-size:13px;line-height:1.45;color:#92400e;font-weight:700;">
                                                ⏳ Status: Pending Verification & Payment Approval
                                            </div>
                                            <div style="margin-top:4px;font-size:12px;line-height:1.5;color:#78350f;">
                                                Please confirm proof of payment on the admin panel before activating this student.
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            @else
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;">
                                    <tr>
                                        <td style="padding:16px 20px;">
                                            <div style="font-size:13px;line-height:1.45;color:#166534;font-weight:700;">
                                                ✓ Status: Active & Ready
                                            </div>
                                            <div style="margin-top:4px;font-size:12px;line-height:1.5;color:#14532d;">
                                                No manual payment verification is required for this registration.
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            @endif
                        </td>
                    </tr>

                    <!-- Instructors Section (if present) -->
                    @if(!empty($instructorContacts))
                        <tr>
                            <td style="padding:12px 32px 18px;">
                                <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;margin-bottom:6px;">
                                    Assigned Instructors
                                </div>
                                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:12px 16px;font-size:13px;color:#0f172a;line-height:1.5;">
                                    {{ implode(', ', $instructorContacts) }}
                                </div>
                            </td>
                        </tr>
                    @endif

                    <!-- Action Button -->
                    <tr>
                        <td style="padding:12px 32px 28px;text-align:center;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 auto;">
                                <tr>
                                    <td style="border-radius:999px;background:#0a2d27;">
                                        <a href="{{ url('/admin/students') }}" style="display:inline-block;background-color:#0a2d27;color:#ffffff;text-decoration:none;font-size:13px;font-weight:700;padding:12px 28px;border-radius:999px;letter-spacing:0.02em;">
                                            Open Admin Dashboard &rarr;
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer Info -->
                    <tr>
                        <td style="padding:18px 32px 24px;background-color:#f8fafc;border-top:1px solid #e2e8f0;">
                            <p style="margin:0;font-size:11px;line-height:1.6;color:#94a3b8;">
                                This is an automated operational notification generated by think.er HUB for platform administrators and instructors.
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
