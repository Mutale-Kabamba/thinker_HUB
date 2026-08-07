@props(['url'])
@php
	$appUrl = rtrim((string) config('app.url', ''), '/');
	$logoPath = '/images/logos/green.png';
	$logoUrl = $appUrl !== '' ? $appUrl.$logoPath : asset(ltrim($logoPath, '/'));
@endphp
<tr>
<td class="header" style="padding: 32px 0 20px; text-align: center;">
<a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
<img src="{{ $logoUrl }}" class="logo" alt="Thinker HUB" style="height: 44px; width: auto; max-height: 44px; margin-bottom: 4px; display: inline-block; vertical-align: middle;">
<div style="font-size: 20px; font-weight: 800; color: #0a2d27; letter-spacing: -0.02em; margin-top: 4px;">
think.er <span style="color: #0f766e;">HUB</span>
</div>
</a>
</td>
</tr>
