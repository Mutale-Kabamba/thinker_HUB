@php
    $rawStudentName = trim((string) ($student->name ?? ''));
    $studentFirstName = $rawStudentName !== '' ? (explode(' ', $rawStudentName)[0] ?? $rawStudentName) : 'Learner';
@endphp

@extends('emails.layouts.thinker', [
    'title' => 'Your Account is Active — think.er HUB',
    'preheader' => 'Welcome to think.er HUB! Your student account is active and ready.',
    'eyebrow' => 'Student Account Active',
    'heading' => 'Welcome to think.er HUB, ' . $studentFirstName . '!',
    'subtitle' => 'Your enrollment has been verified and your Student Portal is ready.',
    'ctaUrl' => $actionUrl,
    'ctaLabel' => $actionLabel,
    'footerNote' => 'You received this email because your student account was activated on think.er HUB.',
])

@section('content')
  <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 24px; color: #475569;">
    We&rsquo;re thrilled to have you! You now have full access to your student dashboard, coursework, learning materials, AI Study Buddy, and live schedule.
  </p>

  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; margin: 0 0 20px 0;">
    <tr>
      <td style="padding: 18px 22px;">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
          <tr>
            <td width="35%" style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600;">Student Name</td>
            <td style="padding: 6px 0; font-size: 14px; color: #0F172A; font-weight: 700;">{{ $student->name }}</td>
          </tr>
          <tr>
            <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Email Login</td>
            <td style="padding: 6px 0; font-size: 14px; color: #334155; font-weight: 600; border-top: 1px solid #E2E8F0;">{{ $student->email }}</td>
          </tr>
          @if ($course)
            <tr>
              <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Enrolled Course</td>
              <td style="padding: 6px 0; font-size: 14px; color: #006A67; font-weight: 700; border-top: 1px solid #E2E8F0;">{{ $course->code }} &bull; {{ $course->title }}</td>
            </tr>
          @endif
          <tr>
            <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Status</td>
            <td style="padding: 6px 0; font-size: 13px; font-weight: 700; color: #15803D; border-top: 1px solid #E2E8F0;">Active &amp; Verified</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 6px;">
    <tr>
      <td style="padding: 14px 18px; font-size: 13px; line-height: 20px; color: #14532D;">
        <strong>How to sign in:</strong>
        @if (! empty($student->firebase_uid))
          Click the button below to open the login page, then continue using your <strong>Google Account</strong>.
        @else
          Click the button below to sign in with your email address and password.
        @endif
      </td>
    </tr>
  </table>
@endsection
