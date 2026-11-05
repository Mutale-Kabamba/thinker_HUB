@php
    $siteName = (string) config('seo.site_name', 'think.er HUB');
    $rawTitle = trim((string) ($title ?? config('seo.default_title', $siteName)));
    $seoTitle = $rawTitle !== '' ? $rawTitle : $siteName;

    $rawDesc = (string) ($description ?? config('seo.default_description', 'think.er HUB is a practical digital upskilling platform in Zambia where tutors publish curated courses and learners enroll to build career-ready skills.'));
    $cleanDesc = trim((string) preg_replace('/\s+/', ' ', strip_tags($rawDesc)));
    $seoDescription = \Illuminate\Support\Str::limit($cleanDesc, 165, '...');

    $defaultImage = (string) config('seo.default_image', '/images/logos/green.png');
    $resolvedDefaultImage = str_starts_with($defaultImage, 'http') ? $defaultImage : asset(ltrim($defaultImage, '/'));
    $seoImage = ! empty($image)
        ? (str_starts_with((string) $image, 'http') ? (string) $image : asset(ltrim((string) $image, '/')))
        : $resolvedDefaultImage;

    $seoUrl = rtrim((string) ($url ?? url()->current()), '/');
    if ($seoUrl === '') {
        $seoUrl = url('/');
    }

    $seoType = (string) ($type ?? 'website');
    $seoKeywords = $keywords ?? 'thinker hub, think.er HUB, digital skills training Zambia, online courses Zambia, coding bootcamp Livingstone, ICT courses Zambia, practical upskilling';
    $indexable = $indexable ?? true;
    $robots = $indexable ? 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1' : 'noindex,nofollow';
    $seoLocale = (string) config('seo.default_locale', 'en_US');
    $twitterCard = (string) config('seo.twitter_card', 'summary_large_image');
    $twitterSite = config('seo.twitter_site');
    $twitterCreator = config('seo.twitter_creator');

    // Escape '@' keys from Blade's directive compiler
    $kContext = '@' . 'context';
    $kType = '@' . 'type';
    $kId = '@' . 'id';
    $kGraph = '@' . 'graph';

    $baseUrl = rtrim(url('/'), '/');
    $orgId = $baseUrl . '/#organization';
    $websiteId = $baseUrl . '/#website';
    $webpageId = $seoUrl . '#webpage';

    $breadcrumbItems = [
        [
            $kType => 'ListItem',
            'position' => 1,
            'name' => 'Home',
            'item' => $baseUrl,
        ],
    ];

    if (! empty($breadcrumbs) && is_array($breadcrumbs)) {
        $pos = 2;
        foreach ($breadcrumbs as $crumb) {
            if (! empty($crumb['name']) && ! empty($crumb['url'])) {
                $breadcrumbItems[] = [
                    $kType => 'ListItem',
                    'position' => $pos++,
                    'name' => (string) $crumb['name'],
                    'item' => (string) $crumb['url'],
                ];
            }
        }
    } elseif ($seoUrl !== $baseUrl) {
        $breadcrumbItems[] = [
            $kType => 'ListItem',
            'position' => 2,
            'name' => preg_replace('/\s*\|\s*think\.er HUB$/i', '', $seoTitle),
            'item' => $seoUrl,
        ];
    }

    $graph = [
        [
            $kType => 'EducationalOrganization',
            $kId => $orgId,
            'name' => $siteName,
            'alternateName' => ['Thinker Hub', 'think.er HUB', 'Thinker HUB Zambia'],
            'url' => $baseUrl,
            'logo' => [
                $kType => 'ImageObject',
                'url' => asset('images/logos/green.png'),
            ],
            'image' => $seoImage,
            'description' => (string) config('seo.default_description', 'Thinker Hub delivers practical, career-focused digital skills training with an 80% hands-on approach.'),
            'email' => 'thinkerhub@oristudiozm.com',
            'telephone' => '+260772640546',
            'address' => [
                $kType => 'PostalAddress',
                'streetAddress' => '10A Off Natwange Street, Airport',
                'addressLocality' => 'Livingstone',
                'addressCountry' => 'ZM',
            ],
            'contactPoint' => [
                $kType => 'ContactPoint',
                'telephone' => '+260772640546',
                'email' => 'thinkerhub@oristudiozm.com',
                'contactType' => 'customer support',
                'areaServed' => 'ZM',
                'availableLanguage' => ['English'],
            ],
        ],
        [
            $kType => 'WebSite',
            $kId => $websiteId,
            'url' => $baseUrl,
            'name' => $siteName,
            'description' => $seoDescription,
            'publisher' => [$kId => $orgId],
            'inLanguage' => 'en',
            'potentialAction' => [
                $kType => 'SearchAction',
                'target' => [
                    $kType => 'EntryPoint',
                    'urlTemplate' => $baseUrl . '/hub?search={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ],
        [
            $kType => 'WebPage',
            $kId => $webpageId,
            'url' => $seoUrl,
            'name' => $seoTitle,
            'description' => $seoDescription,
            'isPartOf' => [$kId => $websiteId],
            'about' => [$kId => $orgId],
            'inLanguage' => 'en',
        ],
        [
            $kType => 'BreadcrumbList',
            $kId => $seoUrl . '#breadcrumb',
            'itemListElement' => $breadcrumbItems,
        ],
    ];

    if (! empty($schema) && is_array($schema)) {
        $graph[] = $schema;
    }

    if (! empty($schemas) && is_array($schemas)) {
        foreach ($schemas as $extraSchema) {
            if (is_array($extraSchema)) {
                $graph[] = $extraSchema;
            }
        }
    }

    $jsonLdPayload = [
        $kContext => 'https://schema.org',
        $kGraph => $graph,
    ];
@endphp

<title>{{ $seoTitle }}</title>
<meta name="description" content="{{ $seoDescription }}">
@if ($seoKeywords)
    <meta name="keywords" content="{{ $seoKeywords }}">
@endif
<meta name="author" content="{{ $siteName }}">
<meta name="robots" content="{{ $robots }}">
<meta name="googlebot" content="{{ $robots }}">
<meta name="geo.region" content="ZM">
<meta name="geo.placename" content="Livingstone, Zambia">
<link rel="canonical" href="{{ $seoUrl }}">
<link rel="alternate" hreflang="en" href="{{ $seoUrl }}">
<link rel="alternate" hreflang="x-default" href="{{ $seoUrl }}">
<link rel="icon" type="image/png" href="{{ asset('images/logos/icon_green.png') }}">

<meta property="og:locale" content="{{ $seoLocale }}">
<meta property="og:type" content="{{ $seoType }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ $seoUrl }}">
<meta property="og:image" content="{{ $seoImage }}">
<meta property="og:image:alt" content="{{ $seoTitle }}">

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
    <script type="application/ld+json">{!! json_encode($jsonLdPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif
