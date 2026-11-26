@extends('emails.layouts.thinker', [
    'title' => ($updateTitle ?? 'Platform Update') . ' — think.er HUB',
    'preheader' => $updateSubtitle ?? 'New platform improvements are now live on think.er HUB.',
    'eyebrow' => 'System Update · ' . ($updateDate ?? now()->format('M Y')),
    'heading' => $updateTitle ?? 'What’s New on think.er HUB',
    'subtitle' => $updateSubtitle ?? 'We’ve rolled out improvements to make your learning experience faster and smoother.',
    'ctaUrl' => $actionUrl ?? url('/learn'),
    'ctaLabel' => $actionLabel ?? 'Open Student Portal',
    'footerNote' => 'You’re receiving this platform update as a registered think.er HUB member.',
])

@section('content')
  <p style="margin: 0 0 18px 0; font-size: 15px; line-height: 24px; color: #334155;">
    Hi {{ $firstName ?? 'Learner' }},
  </p>
  <p style="margin: 0 0 22px 0; font-size: 15px; line-height: 24px; color: #475569;">
    {{ $intro ?? 'Here is a summary of the latest updates now available on think.er HUB:' }}
  </p>

  @if (! empty($highlights))
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-left: 4px solid #0D9488; border-radius: 4px; margin: 0 0 20px 0;">
      <tr>
        <td style="padding: 18px 22px;">
          <p style="margin: 0 0 10px 0; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #0A2D27;">
            Key Highlights
          </p>
          <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
            @foreach ($highlights as $item)
              <tr>
                <td style="padding: 0 0 8px 0; font-size: 14px; line-height: 22px; color: #475569;">
                  <strong style="color: #0D9488;">&bull;</strong>&nbsp;
                  @if (is_array($item))
                    <strong style="color: #1E293B;">{{ $item['title'] ?? '' }}</strong>@if(! empty($item['description'])) &mdash; {{ $item['description'] }}@endif
                  @else
                    {{ $item }}
                  @endif
                </td>
              </tr>
            @endforeach
          </table>
        </td>
      </tr>
    </table>
  @endif

  @if (! empty($closingNote))
    <p style="margin: 0; font-size: 14px; line-height: 22px; color: #64748B;">
      {{ $closingNote }}
    </p>
  @endif
@endsection
