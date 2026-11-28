@php
    $rawName = trim((string) ($recipientName ?? $notifiable->name ?? 'Learner'));
    $firstName = $rawName !== '' ? (explode(' ', $rawName)[0] ?? $rawName) : 'Learner';
@endphp

@extends('emails.layouts.thinker', [
    'title' => 'Reschedule Request Update — think.er HUB',
    'preheader' => 'Update on your reschedule request for ' . $courseName . '.',
    'eyebrow' => 'Schedule Update',
    'heading' => 'Reschedule Request Update',
    'subtitle' => 'Course: ' . $courseName,
    'ctaUrl' => url('/learn/schedule'),
    'ctaLabel' => 'View Current Schedule',
    'footerNote' => 'This is a course schedule notification from think.er HUB.',
])

@section('content')
  <p style="margin: 0 0 18px 0; font-size: 15px; line-height: 24px; color: #334155;">
    Hi {{ $firstName }},
  </p>
  <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 24px; color: #475569;">
    Your request to reschedule <strong style="color: #0A2D27;">{{ $courseName }}</strong> could not be accommodated at this time.
  </p>

  @if (! empty($reason))
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #FFFBEB; border: 1px solid #FDE68A; border-left: 4px solid #F59E0B; border-radius: 4px; margin: 0 0 20px 0;">
      <tr>
        <td style="padding: 16px 20px;">
          <p style="margin: 0 0 6px 0; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.7px; color: #92400E;">
            Instructor Note
          </p>
          <p style="margin: 0; font-size: 14px; line-height: 22px; color: #78350F;">
            {{ $reason }}
          </p>
        </td>
      </tr>
    </table>
  @endif

  <p style="margin: 0; font-size: 14px; line-height: 22px; color: #64748B;">
    Your session remains active on its original timetable. You can view your full calendar anytime in the Student Portal.
  </p>
@endsection
