@php
    $siteName = (string) config('seo.site_name', 'think.er HUB');
    $seoTitle = $title ?? config('seo.default_title', $siteName);
    $seoDescription = $description ?? config('seo.default_description', 'think.er HUB is a platform where tutors create curated courses and learners enroll to upskill through practical training.');
    $defaultImage = (string) config('seo.default_image', '/images/logos/green.png');
    $seoImage = $image ?? (str_starts_with($defaultImage, 'http') ? $defaultImage : asset(ltrim($defaultImage, '/')));
    $seoUrl = $url ?? url()->current();
    $seoType = $type ?? 'website';
    $seoKeywords = $keywords ?? null;
    $indexable = $indexable ?? true;
    $robots = $indexable ? 'index,follow,max-image-preview:large' : 'noindex,nofollow';
    $seoLocale = (string) config('seo.default_locale', 'en_US');
    $twitterCard = (string) config('seo.twitter_card', 'summary_large_image');
    $twitterSite = config('seo.twitter_site');
    $twitterCreator = config('seo.twitter_creator');
@endphp

<title>{{ $seoTitle }}</title>
<meta name="description" content="{{ $seoDescription }}">
@if ($seoKeywords)
    <meta name="keywords" content="{{ $seoKeywords }}">
@endif
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $seoUrl }}">
<link rel="icon" type="image/png" href="{{ asset('images/logos/icon_green.png') }}">

<meta property="og:locale" content="{{ $seoLocale }}">
<meta property="og:type" content="{{ $seoType }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ $seoUrl }}">
<meta property="og:image" content="{{ $seoImage }}">

<meta name="twitter:card" content="{{ $twitterCard }}">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
<meta name="twitter:image" content="{{ $seoImage }}">
@if ($twitterSite)
    <meta name="twitter:site" content="{{ $twitterSite }}">
@endif
@if ($twitterCreator)
    <meta name="twitter:creator" content="{{ $twitterCreator }}">
@endif

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
