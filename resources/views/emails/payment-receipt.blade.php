@php
    $rawStudentName = trim((string) ($student->name ?? ''));
    $studentFirstName = $rawStudentName !== '' ? (explode(' ', $rawStudentName)[0] ?? $rawStudentName) : 'Learner';
@endphp

@extends('emails.layouts.thinker', [
    'title' => 'Payment Receipt (' . $payment->reference . ') — think.er HUB',
    'preheader' => 'Payment confirmed for ' . $course->title . '. Reference: ' . $payment->reference,
    'eyebrow' => 'Official Payment Receipt',
    'eyebrowColor' => '#5EEAD4',
    'heading' => 'Payment Confirmed!',
    'subtitle' => 'Thank you for your payment. Your course access is active.',
    'ctaUrl' => route('dashboard'),
    'ctaLabel' => 'Start Course on Dashboard',
    'footerNote' => 'For billing questions or a tax invoice, reply to this email or contact ' . config('mail.contact_to', 'thinker.learn@gmail.com') . '.',
])

@section('content')
  <p style="margin: 0 0 18px 0; font-size: 15px; line-height: 24px; color: #334155;">
    Hi {{ $studentFirstName }},
  </p>
  <p style="margin: 0 0 22px 0; font-size: 15px; line-height: 24px; color: #475569;">
    We have successfully processed your enrollment payment for <strong style="color: #0A2D27;">{{ $course->title }}</strong>. Below is your official transaction receipt.
  </p>

  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-top: 4px solid #0D9488; border-radius: 6px;">
    <tr>
      <td style="padding: 18px 22px;">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
          <tr>
            <td width="38%" style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600;">Reference #</td>
            <td style="padding: 6px 0; font-size: 13px; color: #0F172A; font-weight: 700; font-family: monospace;">{{ $payment->reference }}</td>
          </tr>
          <tr>
            <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Course</td>
            <td style="padding: 6px 0; font-size: 14px; color: #0A2D27; font-weight: 700; border-top: 1px solid #E2E8F0;">{{ $course->code }} &bull; {{ $course->title }}</td>
          </tr>
          @if ($payment->hasDiscount())
            <tr>
              <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Original Price</td>
              <td style="padding: 6px 0; font-size: 13px; color: #94A3B8; text-decoration: line-through; border-top: 1px solid #E2E8F0;">{{ $payment->formattedOriginalAmount() }}</td>
            </tr>
            <tr>
              <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Promo Discount</td>
              <td style="padding: 6px 0; font-size: 13px; color: #059669; font-weight: 700; border-top: 1px solid #E2E8F0;">-{{ $payment->formattedDiscountAmount() }} ({{ $payment->promoCodeText() }})</td>
            </tr>
          @endif
          <tr>
            <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Amount Paid</td>
            <td style="padding: 6px 0; font-size: 17px; color: #006A67; font-weight: 800; border-top: 1px solid #E2E8F0;">{{ $payment->formattedAmount() }}</td>
          </tr>
          <tr>
            <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Payment Method</td>
            <td style="padding: 6px 0; font-size: 13px; color: #334155; font-weight: 600; text-transform: capitalize; border-top: 1px solid #E2E8F0;">{{ str_replace('_', ' ', $payment->payment_method) }} ({{ strtoupper($payment->provider ?? 'Gateway') }})</td>
          </tr>
          <tr>
            <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Status</td>
            <td style="padding: 6px 0; font-size: 13px; color: #15803D; font-weight: 700; border-top: 1px solid #E2E8F0;">PAID &amp; VERIFIED</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
@endsection
