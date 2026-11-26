@extends('emails.layouts.thinker', [
    'title' => 'Reset Your Password — think.er HUB',
    'preheader' => 'Reset your think.er HUB password. This link expires in ' . ($expiresMinutes ?? 60) . ' minutes.',
    'eyebrow' => 'Account Security',
    'heading' => 'Reset Your Password',
    'ctaUrl' => $resetUrl,
    'ctaLabel' => 'Reset Password',
    'footerNote' => 'This is an automated security email from think.er HUB.',
])

@section('content')
  <p style="margin: 0 0 18px 0; font-size: 15px; line-height: 24px; color: #334155;">
    Hi {{ $firstName ?? 'there' }},
  </p>
  <p style="margin: 0 0 8px 0; font-size: 15px; line-height: 24px; color: #475569;">
    We received a request to reset the password for your <strong style="color: #0A2D27;">think.er HUB</strong> account. Click the button below to choose a new password.
  </p>
@endsection

@section('after_cta')
  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #FFFBEB; border: 1px solid #FDE68A; border-radius: 4px; margin: 26px 0 20px 0;">
    <tr>
      <td style="padding: 14px 18px; font-size: 13px; line-height: 20px; color: #92400E;">
        <strong>Security note:</strong> This password reset link expires in <strong>{{ $expiresMinutes ?? 60 }} minutes</strong>. If you did not request a password reset, you can safely ignore this email.
      </td>
    </tr>
  </table>
  <p style="margin: 0; font-size: 12px; line-height: 19px; color: #64748B; word-break: break-all;">
    If the button above doesn&rsquo;t work, copy and paste this link into your browser:<br>
    <a href="{{ $resetUrl }}" target="_blank" style="color: #006A67; text-decoration: underline;">{{ $resetUrl }}</a>
  </p>
@endsection
