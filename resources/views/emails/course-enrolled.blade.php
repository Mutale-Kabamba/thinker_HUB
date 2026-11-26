@php
    $rawName = trim((string) ($notifiable->name ?? 'Learner'));
    $firstName = $rawName !== '' ? (explode(' ', $rawName)[0] ?? $rawName) : 'Learner';
    $classroomUrl = $course->slug ? url('/classroom/' . $course->slug) : url('/learn/courses');
@endphp

@extends('emails.layouts.thinker', [
    'title' => 'Enrolled in ' . $course->title . ' — think.er HUB',
    'preheader' => 'You are now enrolled in ' . $course->title . ' on think.er HUB. Start learning anytime!',
    'eyebrow' => 'Enrollment Confirmed',
    'heading' => 'You’re In! Welcome to ' . $course->title,
    'subtitle' => 'Your seat is confirmed and your classroom is ready inside the Student Portal.',
    'ctaUrl' => $classroomUrl,
    'ctaLabel' => 'Start Learning Now',
    'footerNote' => 'You received this confirmation because you were enrolled in a course on think.er HUB.',
])

@section('content')
  <p style="margin: 0 0 18px 0; font-size: 15px; line-height: 24px; color: #334155;">
    Hi {{ $firstName }},
  </p>
  <p style="margin: 0 0 22px 0; font-size: 15px; line-height: 24px; color: #475569;">
    You have been successfully enrolled in <strong style="color: #0A2D27;">{{ $course->title }}</strong>. All lessons, interactive modules, and learning materials are now unlocked for you.
  </p>

  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px;">
    <tr>
      <td style="padding: 18px 22px;">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
          <tr>
            <td width="35%" style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600;">Course</td>
            <td style="padding: 6px 0; font-size: 14px; color: #0F172A; font-weight: 700;">{{ $course->title }}</td>
          </tr>
          @if (! empty($course->code))
            <tr>
              <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Course Code</td>
              <td style="padding: 6px 0; font-size: 14px; color: #006A67; font-weight: 700; border-top: 1px solid #E2E8F0;">{{ $course->code }}</td>
            </tr>
          @endif
          <tr>
            <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Access</td>
            <td style="padding: 6px 0; font-size: 14px; color: #334155; border-top: 1px solid #E2E8F0;">Immediate (Online &amp; Offline Mode)</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
@endsection

@section('after_cta')
  <p style="margin: 20px 0 0 0; font-size: 13px; line-height: 20px; color: #64748B; text-align: center;">
    Tip: Save your lessons for offline study anytime from your Student Portal.
  </p>
@endsection
