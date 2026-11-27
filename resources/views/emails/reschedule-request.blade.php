@php
    $rawName = trim((string) ($recipientName ?? $notifiable->name ?? 'Instructor'));
    $firstName = $rawName !== '' ? (explode(' ', $rawName)[0] ?? $rawName) : 'Instructor';
    $resolvedCourse = $session->course->title ?? $session->course->name ?? 'Course Session';
@endphp

@extends('emails.layouts.thinker', [
    'title' => 'Session Reschedule Request — think.er HUB',
    'preheader' => $studentName . ' requested to reschedule a session in ' . $resolvedCourse . '.',
    'eyebrow' => 'Schedule Request',
    'eyebrowColor' => '#FCD34D',
    'heading' => 'Session Reschedule Request',
    'subtitle' => 'Requested by ' . $studentName . ' · ' . $resolvedCourse,
    'ctaUrl' => url('/teach/schedule'),
    'ctaLabel' => 'Review & Manage Schedule',
    'footerNote' => 'This is an instructor schedule notification from think.er HUB.',
])

@section('content')
  <p style="margin: 0 0 18px 0; font-size: 15px; line-height: 24px; color: #334155;">
    Hi {{ $firstName }},
  </p>
  <p style="margin: 0 0 22px 0; font-size: 15px; line-height: 24px; color: #475569;">
    <strong style="color: #0A2D27;">{{ $studentName }}</strong> has requested to reschedule an upcoming live session. Please review the details below and approve or decline in your schedule dashboard.
  </p>

  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; margin: 0 0 18px 0;">
    <tr>
      <td style="padding: 18px 22px;">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
          <tr>
            <td width="38%" style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600;">Course</td>
            <td style="padding: 6px 0; font-size: 14px; color: #0F172A; font-weight: 700;">{{ $resolvedCourse }}</td>
          </tr>
          <tr>
            <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Original Session</td>
            <td style="padding: 6px 0; font-size: 14px; color: #334155; border-top: 1px solid #E2E8F0;">{{ $session->session_date->format('l, M j, Y') }} at {{ $session->start_time }}</td>
          </tr>
          @if (! empty($preferredDate))
            <tr>
              <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Requested Date</td>
              <td style="padding: 6px 0; font-size: 14px; color: #006A67; font-weight: 700; border-top: 1px solid #E2E8F0;">{{ $preferredDate }}</td>
            </tr>
          @endif
          @if (! empty($preferredTime))
            <tr>
              <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Requested Time</td>
              <td style="padding: 6px 0; font-size: 14px; color: #006A67; font-weight: 700; border-top: 1px solid #E2E8F0;">{{ $preferredTime }}</td>
            </tr>
          @endif
          <tr>
            <td valign="top" style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Reason</td>
            <td style="padding: 6px 0; font-size: 14px; line-height: 21px; color: #475569; border-top: 1px solid #E2E8F0;">{{ $reason }}</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
@endsection
