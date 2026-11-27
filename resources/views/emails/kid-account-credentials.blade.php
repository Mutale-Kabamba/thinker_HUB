@extends('emails.layouts.thinker', [
    'title' => 'Child Account Credentials — think.er HUB',
    'preheader' => 'Welcome to think.er HUB Kids Track! Login credentials for ' . $kid->name . '.',
    'eyebrow' => 'Kids Learning Program',
    'eyebrowColor' => '#FCD34D',
    'heading' => 'Welcome to think.er HUB!',
    'subtitle' => 'Enrollment confirmed for ' . $kid->name . ' in ' . $course->title . '.',
    'ctaUrl' => route('filament.parent.pages.overview'),
    'ctaLabel' => 'Open Parents Portal',
    'footerNote' => 'Kid accounts on think.er HUB are isolated from public forums. Track progress anytime in the Parents Portal.',
])

@section('content')
  <p style="margin: 0 0 18px 0; font-size: 15px; line-height: 24px; color: #334155;">
    Hello {{ $parent->name }} ({{ $relationship }}),
  </p>
  <p style="margin: 0 0 22px 0; font-size: 15px; line-height: 24px; color: #475569;">
    We&rsquo;re excited to welcome <strong style="color: #0A2D27;">{{ $kid->name }}</strong> to think.er HUB&rsquo;s Kids Track! Both your child&rsquo;s learning account and your Parents Portal account are ready.
  </p>

  <!-- Child Login Code Box -->
  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F0FDFA; border: 2px dashed #0D9488; border-radius: 8px; margin: 0 0 22px 0;">
    <tr>
      <td align="center" style="padding: 22px 20px;">
        <p style="margin: 0 0 6px 0; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #006A67;">
          Child Login Credentials &middot; {{ $kid->name }}
        </p>
        <p style="margin: 0 0 4px 0; font-size: 12px; color: #475569;">
          Unique Login Code
        </p>
        <p style="margin: 0 0 12px 0; font-size: 28px; font-weight: 800; letter-spacing: 2px; color: #0A2D27; font-family: monospace;">
          {{ $uniqueCode }}
        </p>
        <p style="margin: 0; font-size: 13px; line-height: 20px; color: #334155;">
          Sign in at <a href="{{ route('login') }}" target="_blank" style="color: #006A67; font-weight: 600; text-decoration: underline;">{{ route('login') }}</a> using this code and the password set during enrollment.
        </p>
      </td>
    </tr>
  </table>

  <!-- Parent Account Summary -->
  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px;">
    <tr>
      <td style="padding: 18px 22px;">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
          <tr>
            <td width="38%" style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600;">Parent Login Email</td>
            <td style="padding: 6px 0; font-size: 14px; color: #0F172A; font-weight: 600;">{{ $parent->email }}</td>
          </tr>
          <tr>
            <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Course Enrolled</td>
            <td style="padding: 6px 0; font-size: 14px; color: #006A67; font-weight: 700; border-top: 1px solid #E2E8F0;">{{ $course->title }} ({{ $course->code }})</td>
          </tr>
          <tr>
            <td style="padding: 6px 0; font-size: 13px; color: #64748B; font-weight: 600; border-top: 1px solid #E2E8F0;">Parents Portal</td>
            <td style="padding: 6px 0; font-size: 13px; border-top: 1px solid #E2E8F0;">
              <a href="{{ route('filament.parent.pages.overview') }}" target="_blank" style="color: #006A67; text-decoration: underline;">{{ route('filament.parent.pages.overview') }}</a>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
@endsection
