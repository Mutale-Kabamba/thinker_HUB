<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Child Account Credentials - think.er HUB</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#0f172a;-webkit-font-smoothing:antialiased;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f1f5f9;padding:32px 12px;">
        <tr>
            <td align="center">
                <!-- Top Logo -->
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 0 auto 20px;">
                    <tr>
                        <td align="center">
                            <a href="http://thinkerhub.online" style="display: inline-block; text-decoration: none; font-size: 24px; font-weight: 800; color: #0a2d27; letter-spacing: -0.03em;">
                                think.er <span style="color: #4f46e5;">HUB</span>
                            </a>
                        </td>
                    </tr>
                </table>

                <!-- Main Card Container -->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:620px;background-color:#ffffff;border-radius:18px;overflow:hidden;border:1px solid #e2e8f0;box-shadow:0 10px 25px -5px rgba(15,23,42,0.06),0 8px 10px -6px rgba(15,23,42,0.04);">
                    
                    <!-- Header Banner -->
                    <tr>
                        <td style="padding:32px 32px 28px;background:linear-gradient(135deg,#312e81 0%,#4338ca 50%,#4f46e5 100%);color:#ffffff;">
                            <div style="display:inline-block;padding:4px 12px;background:rgba(255,255,255,0.18);border-radius:999px;font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#e0e7ff;">
                                Kids Learning Program
                            </div>
                            <h1 style="margin:14px 0 6px;font-size:24px;line-height:1.25;color:#ffffff;font-weight:800;letter-spacing:-0.02em;">
                                Welcome to think.er HUB!
                            </h1>
                            <p style="margin:0;font-size:14px;line-height:1.5;color:#c7d2fe;">
                                Enrollment confirmed for <strong>{{ $kid->name }}</strong> in {{ $course->title }}.
                            </p>
                        </td>
                    </tr>

                    <!-- Greeting & Intro -->
                    <tr>
                        <td style="padding:28px 32px 16px;">
                            <p style="margin:0;font-size:16px;line-height:1.5;color:#0f172a;font-weight:700;">
                                Hello {{ $parent->name }} ({{ $relationship }}),
                            </p>
                            <p style="margin:8px 0 0;font-size:14px;line-height:1.65;color:#475569;">
                                We are thrilled to welcome your child to think.er HUB's Kids Track! Two accounts have been prepared for your family so you can supervise, track progress, and coordinate with course teachers.
                            </p>
                        </td>
                    </tr>

                    <!-- Kids Account Box -->
                    <tr>
                        <td style="padding:0 32px 20px;">
                            <div style="background-color:#f5f3ff;border:2px dashed #a78bfa;border-radius:14px;padding:24px;text-align:center;">
                                <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#6d28d9;margin-bottom:6px;">
                                    Child Login Credentials
                                </div>
                                <div style="font-size:18px;font-weight:800;color:#1e1b4b;margin-bottom:12px;">
                                    {{ $kid->name }}
                                </div>

                                <div style="background-color:#ffffff;border:1px solid #ddd6fe;border-radius:10px;padding:14px 20px;display:inline-block;margin-bottom:12px;">
                                    <div style="font-size:11px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:2px;">
                                        Unique Login Code
                                    </div>
                                    <div style="font-size:26px;font-weight:900;letter-spacing:0.08em;color:#4f46e5;font-family:monospace;">
                                        {{ $uniqueCode }}
                                    </div>
                                </div>

                                <p style="margin:0;font-size:13px;line-height:1.5;color:#5b21b6;">
                                    Your child can log in at <strong>{{ route('login') }}</strong> by entering this <strong>Unique Login Code</strong> along with the password set during enrollment.
                                </p>
                            </div>
                        </td>
                    </tr>

                    <!-- Parent Account Details -->
                    <tr>
                        <td style="padding:0 32px 24px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px;">
                                <tr>
                                    <td>
                                        <div style="font-size:13px;font-weight:700;color:#0f172a;margin-bottom:8px;">
                                            Parent Portal Management Details
                                        </div>
                                        <div style="font-size:13px;color:#475569;line-height:1.6;">
                                            <strong>Parent Login Email:</strong> {{ $parent->email }}<br>
                                            <strong>Course Enrolled:</strong> {{ $course->title }} ({{ $course->code }})<br>
                                            <strong>Parents Portal URL:</strong> <a href="{{ route('filament.parent.pages.overview') }}" style="color:#4f46e5;font-weight:600;">{{ route('filament.parent.pages.overview') }}</a>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Action Buttons -->
                    <tr>
                        <td style="padding:0 32px 32px;text-align:center;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 auto;">
                                <tr>
                                    <td style="border-radius:12px;background-color:#4f46e5;padding:12px 24px;text-align:center;">
                                        <a href="{{ route('filament.parent.pages.overview') }}" style="color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;display:inline-block;">
                                            Open Parents Portal
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer Notice -->
                    <tr>
                        <td style="padding:20px 32px;background-color:#f8fafc;border-top:1px solid #e2e8f0;font-size:12px;color:#64748b;line-height:1.6;">
                            <strong>Child Safety Guarantee:</strong> Kid accounts on think.er HUB are isolated from public forums and friend directories. As a parent, you can track learning milestones, view quiz scores, and switch into your child's account anytime from the Parents Portal.
                        </td>
                    </tr>
                </table>

                <!-- Bottom Postal Footer -->
                <p style="margin:16px 0 0;font-size:11px;color:#94a3b8;text-align:center;line-height:1.5;">
                    &copy; {{ date('Y') }} <a href="http://thinkerhub.online" style="color:#4f46e5;text-decoration:underline;font-weight:600;">think.er HUB</a> &bull; 10A Off Natwange Street, Airport, Livingstone, Zambia<br>
                    Official learning platform &bull; <a href="http://thinkerhub.online" style="color:#4f46e5;text-decoration:underline;">thinkerhub.online</a>
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
