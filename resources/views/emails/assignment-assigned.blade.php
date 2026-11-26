@php
    $rawName = trim((string) ($recipientName ?? $notifiable->name ?? 'Learner'));
    $firstName = $rawName !== '' ? (explode(' ', $rawName)[0] ?? $rawName) : 'Learner';
    $resolvedCourse = $courseName ?? $assignment->course?->title ?? 'your course';
    $dueFormatted = $assignment->due_date?->format('l, M j, Y \a\t h:i A') ?? 'Check portal for deadline';
@endphp

@extends('emails.layouts.thinker', [
    'title' => 'New Assignment: ' . $assignment->name . ' — think.er HUB',
    'preheader' => 'New assignment posted in ' . $resolvedCourse . ': ' . $assignment->name,
    'eyebrow' => 'New Assignment Posted',
    'eyebrowColor' => '#FCD34D',
    'heading' => $assignment->name,
    'subtitle' => 'Course: ' . $resolvedCourse,
    'ctaUrl' => url('/learn/assignments'),
    'ctaLabel' => 'View Assignment',
    'footerNote' => 'This is a course activity notification from your think.er HUB enrollment.',
])

@section('content')
  <p style="margin: 0 0 18px 0; font-size: 15px; line-height: 24px; color: #334155;">
    Hi {{ $firstName }},
  </p>
  <p style="margin: 0 0 22px 0; font-size: 15px; line-height: 24px; color: #475569;">
    A new assignment has been posted for <strong style="color: #0A2D27;">{{ $resolvedCourse }}</strong>. Review the details below and submit your work before the deadline.
  </p>

  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; margin: 0 0 20px 0;">
    <tr>
      <td style="padding: 18px 22px;">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
          <tr>
            <td width="35%" style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600;">Assignment</td>
            <td style="padding: 6px 0; font-size: 14px; color: #0F172A; font-weight: 700;">{{ $assignment->name }}</td>
          </tr>
          <tr>
            <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Course</td>
            <td style="padding: 6px 0; font-size: 14px; color: #334155; border-top: 1px solid #E2E8F0;">{{ $resolvedCourse }}</td>
          </tr>
          <tr>
            <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Due Date</td>
            <td style="padding: 6px 0; font-size: 14px; color: #B45309; font-weight: 700; border-top: 1px solid #E2E8F0;">{{ $dueFormatted }}</td>
          </tr>
          @if (! empty($assignment->description))
            <tr>
              <td valign="top" style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Summary</td>
              <td style="padding: 6px 0; font-size: 13px; line-height: 20px; color: #475569; border-top: 1px solid #E2E8F0;">{{ \Illuminate\Support\Str::limit(strip_tags($assignment->description), 200) }}</td>
            </tr>
          @endif
        </table>
      </td>
    </tr>
  </table>

  <p style="margin: 0; font-size: 14px; line-height: 22px; color: #64748B;">
    Need guidance while working on this task? Open the <strong style="color: #334155;">AI Study Buddy</strong> in your Student Portal or message your instructor.
  </p>
@endsection
