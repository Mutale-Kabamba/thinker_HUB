think.er HUB - Account Activation

@php
    $rawStudentName = trim((string) ($student->name ?? ''));
    $studentFirstName = $rawStudentName !== '' ? (explode(' ', $rawStudentName)[0] ?? $rawStudentName) : 'Learner';
@endphp
Hello {{ $studentFirstName }}!

Great news! Your student registration has been approved and your account is now active.

STUDENT DETAILS:
- Name: {{ $student->name }}
- Email: {{ $student->email }}
@if ($course)
- Enrolled Course: {{ $course->code }} - {{ $course->title }}
@endif
- Status: Active & Verified

HOW TO SIGN IN:
@if (!empty($student->firebase_uid))
Visit {{ $actionUrl }} and sign in using your Google Account.
@else
Visit {{ $actionUrl }} to sign in with your registered email and password.
@endif

If you have any questions or need assistance, reply to this email or reach us at thinkerhub@oristudiozm.com.

Best regards,
The think.er HUB Team
Livingstone, Zambia
https://oristudiozm.com
