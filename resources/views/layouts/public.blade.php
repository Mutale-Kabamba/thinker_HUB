<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('partials.seo-meta', [
        'title' => ($title ?? 'Knowledge & Opportunities Hub').' | think.er HUB',
        'description' => 'Explore tips & tricks, short blogs, career opportunities, and educational YouTube videos.',
        'keywords' => 'knowledge hub, career opportunities, programming tips, tutorials, thinker hub',
        'type' => 'website',
    ])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.pwa-register')
    @livewireStyles
</head>
<body class="hub-public bg-[#f8fcf9] text-slate-900 font-sans antialiased min-h-dvh" x-data="{ mobileMenu: false }">
    {{ $slot }}
    @livewireScripts
</body>
</html>
