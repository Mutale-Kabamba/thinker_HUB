@extends('emails.layouts.thinker', [
    'title' => $subjectLine . ' — think.er HUB',
    'preheader' => 'Course announcement from ' . ($sender->name ?: 'your instructor') . ': ' . $subjectLine,
    'eyebrow' => 'Course Announcement',
    'heading' => $course->title ?? 'Course Update',
    'subtitle' => 'Message from ' . ($sender->name ? 'Instructor ' . $sender->name : 'your course instructor'),
    'ctaUrl' => route('dashboard'),
    'ctaLabel' => 'Go to Course Dashboard',
    'footerNote' => 'You received this announcement because you are enrolled in ' . ($course->title ?? 'this course') . ' on think.er HUB.',
])

@section('content')
  <p style="margin: 0 0 8px 0; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #64748B;">
    Subject
  </p>
  <p style="margin: 0 0 18px 0; font-size: 17px; font-weight: 700; color: #0F172A; line-height: 24px;">
    {{ $subjectLine }}
  </p>

  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-left: 4px solid #0D9488; border-radius: 4px; margin: 0 0 18px 0;">
    <tr>
      <td style="padding: 18px 22px; font-size: 15px; line-height: 24px; color: #334155; word-break: break-word;">
        {!! nl2br(e($messageBody)) !!}
      </td>
    </tr>
  </table>

  @if (filled($attachmentPath))
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F0FDFA; border: 1px solid #99F6E4; border-radius: 4px;">
      <tr>
        <td style="padding: 12px 16px; font-size: 13px; color: #0F172A;">
          <strong style="color: #006A67; text-transform: uppercase; font-size: 11px; letter-spacing: 0.6px;">Attachment Included:</strong>
          &nbsp;{{ $attachmentName ?: basename($attachmentPath) }}
        </td>
      </tr>
    </table>
  @endif
@endsection
