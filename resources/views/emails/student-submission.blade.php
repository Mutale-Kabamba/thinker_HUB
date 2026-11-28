@php
    $rawName = trim((string) ($recipientName ?? $notifiable->name ?? 'Instructor'));
    $firstName = $rawName !== '' ? (explode(' ', $rawName)[0] ?? $rawName) : 'Instructor';
    $typeLabel = ucfirst((string) $submissionType);
@endphp

@extends('emails.layouts.thinker', [
    'title' => 'New Submission from ' . $studentName . ' — think.er HUB',
    'preheader' => $studentName . ' submitted ' . $itemTitle . ' (' . $typeLabel . ') for your review.',
    'eyebrow' => 'Submission Received',
    'eyebrowColor' => '#FCD34D',
    'heading' => 'New ' . $typeLabel . ' Submission',
    'subtitle' => 'Submitted by ' . $studentName,
    'ctaUrl' => route('dashboard'),
    'ctaLabel' => 'Review & Grade Submission',
    'footerNote' => 'This is an instructor review notification from think.er HUB.',
])

@section('content')
  <p style="margin: 0 0 18px 0; font-size: 15px; line-height: 24px; color: #334155;">
    Hi {{ $firstName }},
  </p>
  <p style="margin: 0 0 22px 0; font-size: 15px; line-height: 24px; color: #475569;">
    A learner has submitted coursework awaiting your review and feedback.
  </p>

  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px;">
    <tr>
      <td style="padding: 18px 22px;">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
          <tr>
            <td width="35%" style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600;">Learner</td>
            <td style="padding: 6px 0; font-size: 14px; color: #0F172A; font-weight: 700;">{{ $studentName }}</td>
          </tr>
          <tr>
            <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Submission Item</td>
            <td style="padding: 6px 0; font-size: 14px; color: #006A67; font-weight: 700; border-top: 1px solid #E2E8F0;">{{ $itemTitle }}</td>
          </tr>
          <tr>
            <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Type</td>
            <td style="padding: 6px 0; font-size: 14px; color: #334155; border-top: 1px solid #E2E8F0;">{{ $typeLabel }}</td>
          </tr>
          <tr>
            <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Submitted On</td>
            <td style="padding: 6px 0; font-size: 13px; color: #475569; border-top: 1px solid #E2E8F0;">{{ now()->format('l, M j, Y \a\t h:i A') }}</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
@endsection
