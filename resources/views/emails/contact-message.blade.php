@php
    $resolvedSubject = $contactSubject ?? $subject ?? 'General Inquiry';
    $replyHref = 'mailto:' . $email . '?subject=' . rawurlencode('Re: ' . $resolvedSubject);
@endphp

@extends('emails.layouts.thinker', [
    'title' => 'New Contact Message: ' . $resolvedSubject . ' — think.er HUB',
    'preheader' => 'New contact inquiry from ' . $name . ' (' . $email . '): ' . $resolvedSubject,
    'eyebrow' => 'Contact Inquiry',
    'eyebrowColor' => '#FCD34D',
    'heading' => $resolvedSubject,
    'subtitle' => 'Submitted by ' . $name . ' via thinkerhub.online/contact',
    'ctaUrl' => $replyHref,
    'ctaLabel' => 'Reply to ' . $name,
    'footerNote' => 'Delivered from the think.er HUB contact form to ' . config('mail.contact_to', config('mail.from.address')) . '. You can reply directly to this email.',
])

@section('content')
  <p style="margin: 0 0 18px 0; font-size: 15px; line-height: 24px; color: #334155;">
    A new inquiry was submitted through the <strong style="color: #0A2D27;">think.er HUB</strong> contact form.
  </p>

  <!-- Sender Details Card -->
  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; margin: 0 0 22px 0;">
    <tr>
      <td style="padding: 18px 22px;">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
          <tr>
            <td width="30%" style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600;">Sender</td>
            <td style="padding: 6px 0; font-size: 14px; color: #0F172A; font-weight: 700;">{{ $name }}</td>
          </tr>
          <tr>
            <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Email</td>
            <td style="padding: 6px 0; font-size: 14px; font-weight: 600; border-top: 1px solid #E2E8F0;">
              <a href="{{ $replyHref }}" style="color: #006A67; text-decoration: underline;">{{ $email }}</a>
            </td>
          </tr>
          <tr>
            <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Subject</td>
            <td style="padding: 6px 0; font-size: 14px; color: #0F172A; font-weight: 600; border-top: 1px solid #E2E8F0;">{{ $resolvedSubject }}</td>
          </tr>
          <tr>
            <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Received</td>
            <td style="padding: 6px 0; font-size: 13px; color: #475569; border-top: 1px solid #E2E8F0;">{{ now()->format('D, M j, Y \a\t g:i A') }}</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <!-- Message Body -->
  <p style="margin: 0 0 8px 0; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #64748B;">
    Message
  </p>
  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-left: 4px solid #0D9488; border-radius: 4px;">
    <tr>
      <td style="padding: 18px 22px; font-size: 15px; line-height: 24px; color: #334155; word-break: break-word;">
        {!! nl2br(e($bodyText)) !!}
      </td>
    </tr>
  </table>
@endsection

@section('after_cta')
  <p style="margin: 18px 0 0 0; font-size: 12px; line-height: 18px; color: #64748B; text-align: center;">
    Reply-To is automatically set to <strong style="color: #334155;">{{ $email }}</strong>, so hitting Reply in Gmail responds directly to {{ $name }}.
  </p>
@endsection
