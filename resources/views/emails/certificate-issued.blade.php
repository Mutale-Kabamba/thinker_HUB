@php
    $rawName = trim((string) ($notifiable->name ?? 'Graduate'));
    $firstName = $rawName !== '' ? (explode(' ', $rawName)[0] ?? $rawName) : 'Graduate';
    $courseTitle = $certificate->course?->title ?? 'your course';
    $certificateCode = $certificate->verification_code ?? ('TH-' . $certificate->id);
    $issuedDate = ($certificate->issued_at ?? $certificate->created_at)?->format('M j, Y') ?? now()->format('M j, Y');
@endphp

@extends('emails.layouts.thinker', [
    'title' => 'Certificate Awarded: ' . $courseTitle . ' — think.er HUB',
    'preheader' => 'Congratulations! Your verified think.er HUB certificate for ' . $courseTitle . ' is ready.',
    'eyebrow' => 'Certificate Awarded',
    'eyebrowColor' => '#FCD34D',
    'heading' => 'Congratulations, ' . $firstName . '!',
    'subtitle' => 'Your verified certificate of completion is ready to download and share.',
    'ctaUrl' => url('/learn/certificates'),
    'ctaLabel' => 'View & Download Certificate',
    'footerNote' => 'This credential was officially issued by think.er HUB.',
])

@section('content')
  <p style="margin: 0 0 22px 0; font-size: 15px; line-height: 24px; color: #475569;">
    You&rsquo;ve officially completed <strong style="color: #0A2D27;">{{ $courseTitle }}</strong> on think.er HUB. Your verified digital certificate is now available in your Student Portal.
  </p>

  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-top: 4px solid #F59E0B; border-radius: 6px;">
    <tr>
      <td style="padding: 18px 22px;">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
          <tr>
            <td width="38%" style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600;">Course</td>
            <td style="padding: 6px 0; font-size: 14px; color: #0F172A; font-weight: 700;">{{ $courseTitle }}</td>
          </tr>
          <tr>
            <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Credential ID</td>
            <td style="padding: 6px 0; font-size: 14px; color: #006A67; font-weight: 700; font-family: monospace; border-top: 1px solid #E2E8F0;">{{ $certificateCode }}</td>
          </tr>
          <tr>
            <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Issued On</td>
            <td style="padding: 6px 0; font-size: 14px; color: #334155; border-top: 1px solid #E2E8F0;">{{ $issuedDate }}</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
@endsection
