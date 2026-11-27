@php
    $rawName = trim((string) ($recipientName ?? $notifiable->name ?? 'Learner'));
    $firstName = $rawName !== '' ? (explode(' ', $rawName)[0] ?? $rawName) : 'Learner';
    $typeLabel = ucfirst((string) $submissionType);
    $resultLabel = $scoreOrGrade !== null ? (string) $scoreOrGrade : 'Assessed';
@endphp

@extends('emails.layouts.thinker', [
    'title' => $typeLabel . ' Graded: ' . $itemTitle . ' — think.er HUB',
    'preheader' => 'Your ' . $submissionType . ' (' . $itemTitle . ') has been reviewed and graded: ' . $resultLabel,
    'eyebrow' => 'Submission Graded',
    'eyebrowColor' => '#5EEAD4',
    'heading' => 'Your ' . $typeLabel . ' Was Graded',
    'subtitle' => $itemTitle,
    'ctaUrl' => route('dashboard'),
    'ctaLabel' => 'View Detailed Results',
    'footerNote' => 'This is a course progress notification from think.er HUB.',
])

@section('content')
  <p style="margin: 0 0 18px 0; font-size: 15px; line-height: 24px; color: #334155;">
    Hi {{ $firstName }},
  </p>
  <p style="margin: 0 0 22px 0; font-size: 15px; line-height: 24px; color: #475569;">
    Great news! Your instructor has reviewed and graded your {{ $submissionType }}.
  </p>

  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-top: 4px solid #0D9488; border-radius: 6px; margin: 0 0 18px 0;">
    <tr>
      <td style="padding: 18px 22px;">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
          <tr>
            <td width="35%" style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600;">{{ $typeLabel }}</td>
            <td style="padding: 6px 0; font-size: 14px; color: #0F172A; font-weight: 700;">{{ $itemTitle }}</td>
          </tr>
          <tr>
            <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Result / Score</td>
            <td style="padding: 6px 0; font-size: 16px; color: #006A67; font-weight: 800; border-top: 1px solid #E2E8F0;">{{ $resultLabel }}</td>
          </tr>
          <tr>
            <td valign="top" style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Feedback</td>
            <td style="padding: 6px 0; font-size: 14px; line-height: 21px; color: #475569; border-top: 1px solid #E2E8F0;">
              @if (! empty($feedback))
                {{ $feedback }}
              @else
                <em>Your submission was reviewed successfully with no additional notes.</em>
              @endif
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <p style="margin: 0; font-size: 14px; line-height: 22px; color: #64748B;">
    Keep up the great momentum in your studies!
  </p>
@endsection
