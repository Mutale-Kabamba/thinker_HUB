@php
    $rawName = trim((string) ($recipientName ?? $notifiable->name ?? 'Learner'));
    $firstName = $rawName !== '' ? (explode(' ', $rawName)[0] ?? $rawName) : 'Learner';
    $resolvedCourse = $courseName ?? $material->course?->title ?? $material->course?->name ?? 'your course';
    $resourceType = ucfirst((string) ($material->category ?? $material->type ?? 'Learning Resource'));
@endphp

@extends('emails.layouts.thinker', [
    'title' => 'New Learning Material: ' . $material->title . ' — think.er HUB',
    'preheader' => 'New learning material added to ' . $resolvedCourse . ': ' . $material->title,
    'eyebrow' => 'New Learning Material',
    'heading' => $material->title,
    'subtitle' => 'Course: ' . $resolvedCourse,
    'ctaUrl' => route('dashboard'),
    'ctaLabel' => 'Access Learning Material',
    'footerNote' => 'This is a course activity notification from your think.er HUB enrollment.',
])

@section('content')
  <p style="margin: 0 0 18px 0; font-size: 15px; line-height: 24px; color: #334155;">
    Hi {{ $firstName }},
  </p>
  <p style="margin: 0 0 22px 0; font-size: 15px; line-height: 24px; color: #475569;">
    New study resources have been uploaded for <strong style="color: #0A2D27;">{{ $resolvedCourse }}</strong>. Review or download them anytime from your Student Portal.
  </p>

  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; margin: 0 0 18px 0;">
    <tr>
      <td style="padding: 18px 22px;">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
          <tr>
            <td width="35%" style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600;">Resource</td>
            <td style="padding: 6px 0; font-size: 14px; color: #0F172A; font-weight: 700;">{{ $material->title }}</td>
          </tr>
          <tr>
            <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Course</td>
            <td style="padding: 6px 0; font-size: 14px; color: #334155; border-top: 1px solid #E2E8F0;">{{ $resolvedCourse }}</td>
          </tr>
          <tr>
            <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Category</td>
            <td style="padding: 6px 0; font-size: 14px; color: #006A67; font-weight: 600; border-top: 1px solid #E2E8F0;">{{ $resourceType }}</td>
          </tr>
          @if (! empty($material->description))
            <tr>
              <td valign="top" style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Overview</td>
              <td style="padding: 6px 0; font-size: 13px; line-height: 20px; color: #475569; border-top: 1px solid #E2E8F0;">{{ \Illuminate\Support\Str::limit(strip_tags($material->description), 200) }}</td>
            </tr>
          @endif
        </table>
      </td>
    </tr>
  </table>
@endsection
