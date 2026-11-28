@php
    $rawName = trim((string) ($recipientName ?? $notifiable->name ?? 'Learner'));
    $firstName = $rawName !== '' ? (explode(' ', $rawName)[0] ?? $rawName) : 'Learner';
    $resolvedCourse = $courseName ?? $quiz->course?->title ?? 'your course';
@endphp

@extends('emails.layouts.thinker', [
    'title' => 'New Quiz Published: ' . $quiz->title . ' — think.er HUB',
    'preheader' => 'A new quiz is ready in ' . $resolvedCourse . ': ' . $quiz->title,
    'eyebrow' => 'New Quiz Available',
    'eyebrowColor' => '#FCD34D',
    'heading' => $quiz->title,
    'subtitle' => 'Course: ' . $resolvedCourse,
    'ctaUrl' => url('/learn/quizzes'),
    'ctaLabel' => 'Take Quiz Now',
    'footerNote' => 'This is a course activity notification from your think.er HUB enrollment.',
])

@section('content')
  <p style="margin: 0 0 18px 0; font-size: 15px; line-height: 24px; color: #334155;">
    Hi {{ $firstName }},
  </p>
  <p style="margin: 0 0 22px 0; font-size: 15px; line-height: 24px; color: #475569;">
    A new quiz has been published for <strong style="color: #0A2D27;">{{ $resolvedCourse }}</strong>. Test your knowledge and track your progress in the Student Portal.
  </p>

  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px;">
    <tr>
      <td style="padding: 18px 22px;">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
          <tr>
            <td width="35%" style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600;">Quiz</td>
            <td style="padding: 6px 0; font-size: 14px; color: #0F172A; font-weight: 700;">{{ $quiz->title }}</td>
          </tr>
          <tr>
            <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Course</td>
            <td style="padding: 6px 0; font-size: 14px; color: #334155; border-top: 1px solid #E2E8F0;">{{ $resolvedCourse }}</td>
          </tr>
          @if (! empty($quiz->time_limit_minutes))
            <tr>
              <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Time Limit</td>
              <td style="padding: 6px 0; font-size: 14px; color: #334155; border-top: 1px solid #E2E8F0;">{{ $quiz->time_limit_minutes }} minutes</td>
            </tr>
          @endif
          @if (! empty($quiz->pass_percentage))
            <tr>
              <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Pass Score</td>
              <td style="padding: 6px 0; font-size: 14px; color: #006A67; font-weight: 700; border-top: 1px solid #E2E8F0;">{{ $quiz->pass_percentage }}%</td>
            </tr>
          @endif
          @if (! empty($quiz->description))
            <tr>
              <td valign="top" style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Overview</td>
              <td style="padding: 6px 0; font-size: 13px; line-height: 20px; color: #475569; border-top: 1px solid #E2E8F0;">{{ \Illuminate\Support\Str::limit(strip_tags($quiz->description), 200) }}</td>
            </tr>
          @endif
        </table>
      </td>
    </tr>
  </table>
@endsection
