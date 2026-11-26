@extends('emails.layouts.thinker', [
    'title' => 'Verify Your Email Address — think.er HUB',
    'preheader' => 'Confirm your email address to activate your think.er HUB account.',
    'eyebrow' => 'Email Verification',
    'heading' => 'Verify Your Email Address',
    'ctaUrl' => $verificationUrl,
    'ctaLabel' => 'Verify Email Address',
    'footerNote' => 'This is an automated account verification email from think.er HUB.',
])

@section('content')
  <p style="margin: 0 0 18px 0; font-size: 15px; line-height: 24px; color: #334155;">
    Hi {{ $firstName ?? 'Learner' }},
  </p>
  <p style="margin: 0 0 22px 0; font-size: 15px; line-height: 24px; color: #475569;">
    Thanks for signing up for <strong style="color: #0A2D27;">think.er HUB</strong>! Please confirm your email address so we can activate your Student Portal, save your course progress, and issue verified certificates.
  </p>
@endsection

@section('after_cta')
  <p style="margin: 24px 0 14px 0; font-size: 13px; line-height: 20px; color: #64748B;">
    If you did not create an account on think.er HUB, no further action is required.
  </p>
  <p style="margin: 0; font-size: 12px; line-height: 19px; color: #64748B; word-break: break-all;">
    Button not working? Copy and paste this link into your browser:<br>
    <a href="{{ $verificationUrl }}" target="_blank" style="color: #006A67; text-decoration: underline;">{{ $verificationUrl }}</a>
  </p>
@endsection
