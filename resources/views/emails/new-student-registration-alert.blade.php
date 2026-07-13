<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Student Registration</title>
</head>
<body style="margin:0;padding:0;background:#edf2f7;font-family:Arial,'Helvetica Neue',Helvetica,sans-serif;color:#0f172a;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">
        Student registration summary for {{ $student->name }} ({{ $course->code }}).
    </div>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#edf2f7;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:680px;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #dbe4ee;">
                    <tr>
                        <td style="padding:20px 28px;background:#0f766e;">
                            <p style="margin:0;font-size:11px;letter-spacing:0.14em;text-transform:uppercase;font-weight:700;color:#ccfbf1;">think.er HUB</p>
                            <h1 style="margin:8px 0 0;font-size:24px;line-height:1.25;color:#ffffff;font-weight:800;">Student Registration Notification</h1>
                            <p style="margin:8px 0 0;font-size:14px;line-height:1.5;color:#e6fffa;">A student account has been registered and attached to a course.</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px 28px 8px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;">
                                <tr>
                                    <td style="width:150px;padding:8px 0;font-size:13px;color:#64748b;font-weight:700;vertical-align:top;">Student</td>
                                    <td style="padding:8px 0;font-size:15px;color:#0f172a;font-weight:700;">{{ $student->name }}</td>
                                </tr>
                                <tr>
                                    <td style="width:150px;padding:8px 0;font-size:13px;color:#64748b;font-weight:700;vertical-align:top;">Email</td>
                                    <td style="padding:8px 0;font-size:15px;color:#0f172a;"><a href="mailto:{{ $student->email }}" style="color:#0f766e;text-decoration:none;">{{ $student->email }}</a></td>
                                </tr>
                                <tr>
                                    <td style="width:150px;padding:8px 0;font-size:13px;color:#64748b;font-weight:700;vertical-align:top;">Course</td>
                                    <td style="padding:8px 0;font-size:15px;color:#0f172a;">{{ $course->code }} - {{ $course->title }}</td>
                                </tr>
                                <tr>
                                    <td style="width:150px;padding:8px 0;font-size:13px;color:#64748b;font-weight:700;vertical-align:top;">Track / Level</td>
                                    <td style="padding:8px 0;font-size:15px;color:#0f172a;">{{ $student->track ?: 'Beginner' }}</td>
                                </tr>
                                <tr>
                                    <td style="width:150px;padding:8px 0;font-size:13px;color:#64748b;font-weight:700;vertical-align:top;">Registered At</td>
                                    <td style="padding:8px 0;font-size:15px;color:#0f172a;">{{ optional($student->created_at)->toDayDateTimeString() ?: now()->toDayDateTimeString() }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:8px 28px 6px;">
                            @if($requiresPaymentApproval)
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#fffbeb;border:1px solid #f6d165;border-radius:10px;">
                                    <tr>
                                        <td style="padding:14px 16px;">
                                            <p style="margin:0;font-size:14px;line-height:1.45;color:#854d0e;font-weight:700;">Status: Pending manual approval</p>
                                            <p style="margin:8px 0 0;font-size:13px;line-height:1.5;color:#713f12;">Please verify payment and activate the student account after confirmation.</p>
                                        </td>
                                    </tr>
                                </table>
                            @else
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f0fdf4;border:1px solid #8dddb2;border-radius:10px;">
                                    <tr>
                                        <td style="padding:14px 16px;">
                                            <p style="margin:0;font-size:14px;line-height:1.45;color:#166534;font-weight:700;">Status: Active</p>
                                            <p style="margin:8px 0 0;font-size:13px;line-height:1.5;color:#14532d;">No payment verification is required for this registration.</p>
                                        </td>
                                    </tr>
                                </table>
                            @endif
                        </td>
                    </tr>

                    @if(!empty($instructorContacts))
                        <tr>
                            <td style="padding:12px 28px 6px;">
                                <p style="margin:0;font-size:13px;color:#64748b;font-weight:700;">Assigned Instructors</p>
                                <p style="margin:6px 0 0;font-size:14px;line-height:1.55;color:#0f172a;">{{ implode(', ', $instructorContacts) }}</p>
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td style="padding:14px 28px 24px;border-top:1px solid #e8eef4;">
                            <p style="margin:0;font-size:12px;color:#64748b;">This is an operational notification from think.er HUB.</p>
                        </td>
                    </tr>
                </table>

                <p style="margin:14px 0 0;font-size:11px;color:#8a99ab;">Generated automatically by think.er HUB.</p>
            </td>
        </tr>
    </table>
</body>
</html>
