@php
    $siteName = 'think.er HUB';
    $seoTitle = $title ?? $siteName;
    $seoDescription = $description ?? 'Thinker Hub delivers practical, career-focused digital skills training with an 80% hands-on approach.';
    $seoImage = $image ?? asset('images/logos/green.png');
    $seoUrl = $url ?? url()->current();
    $seoType = $type ?? 'website';
    $seoKeywords = $keywords ?? null;
    $indexable = $indexable ?? true;
    $robots = $indexable ? 'index,follow,max-image-preview:large' : 'noindex,nofollow';
@endphp

<title>{{ $seoTitle }}</title>
<meta name="description" content="{{ $seoDescription }}">
@if ($seoKeywords)
    <meta name="keywords" content="{{ $seoKeywords }}">
@endif
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $seoUrl }}">
<link rel="icon" type="image/png" href="{{ asset('images/logos/icon_green.png') }}">

<meta property="og:locale" content="en_US">
<meta property="og:type" content="{{ $seoType }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ $seoUrl }}">
<meta property="og:image" content="{{ $seoImage }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
<meta name="twitter:image" content="{{ $seoImage }}">

@if ($indexable)
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'EducationalOrganization',
            'name' => $siteName,
            'url' => url('/'),
            'logo' => asset('images/logos/green.png'),
            'description' => $seoDescription,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endif
