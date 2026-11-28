@php
    $rawName = trim((string) ($recipientName ?? $notifiable->name ?? 'Learner'));
    $firstName = $rawName !== '' ? (explode(' ', $rawName)[0] ?? $rawName) : 'Learner';
    $dateObj = $session->getEffectiveDate();
    $formattedDate = $dateObj->format('l, M j, Y');
    $startTimeStr = $session->getEffectiveStartTime();
    $formattedTime = filled($startTimeStr) ? \Illuminate\Support\Carbon::parse($startTimeStr)->format('g:i A') : 'TBA';
@endphp

@extends('emails.layouts.thinker', [
    'title' => 'Live Session Rescheduled — think.er HUB',
    'preheader' => 'Your live session for ' . $courseName . ' has moved to ' . $formattedDate . ' at ' . $formattedTime . '.',
    'eyebrow' => 'Schedule Update',
    'eyebrowColor' => '#FCD34D',
    'heading' => 'Live Session Rescheduled',
    'subtitle' => 'Course: ' . $courseName,
    'ctaUrl' => url('/learn/schedule'),
    'ctaLabel' => 'View Updated Schedule',
    'footerNote' => 'This is a course schedule notification from think.er HUB.',
])

@section('content')
  <p style="margin: 0 0 18px 0; font-size: 15px; line-height: 24px; color: #334155;">
    Hi {{ $firstName }},
  </p>
  <p style="margin: 0 0 22px 0; font-size: 15px; line-height: 24px; color: #475569;">
    Please note that your upcoming live session for <strong style="color: #0A2D27;">{{ $courseName }}</strong> has been moved to a new date and time.
  </p>

  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-left: 4px solid #0D9488; border-radius: 4px; margin: 0 0 18px 0;">
    <tr>
      <td style="padding: 18px 22px;">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
          <tr>
            <td width="36%" style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600;">Course</td>
            <td style="padding: 6px 0; font-size: 14px; color: #0F172A; font-weight: 700;">{{ $courseName }}</td>
          </tr>
          <tr>
            <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">New Date &amp; Time</td>
            <td style="padding: 6px 0; font-size: 14px; color: #006A67; font-weight: 700; border-top: 1px solid #E2E8F0;">{{ $formattedDate }} at {{ $formattedTime }}</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <p style="margin: 0; font-size: 14px; line-height: 22px; color: #64748B;">
    Please update your personal calendar accordingly.
  </p>
@endsection
