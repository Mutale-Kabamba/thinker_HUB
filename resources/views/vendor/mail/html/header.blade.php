@props(['url'])
@php
	$appUrl = rtrim((string) config('app.url', ''), '/');
	$logoPath = '/images/logos/green.png';
	$logoUrl = $appUrl !== '' ? $appUrl.$logoPath : asset(ltrim($logoPath, '/'));
@endphp
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ $logoUrl }}" class="logo" alt="Thinker HUB" style="height: 48px; width: auto; max-height: 48px; margin-bottom: 6px;">
<br>
<span style="font-size: 22px; font-weight: 800; color: #0e7490; letter-spacing: -0.02em;">think.er <span style="color: #0f172a;">HUB</span></span>
</a>
</td>
</tr>
