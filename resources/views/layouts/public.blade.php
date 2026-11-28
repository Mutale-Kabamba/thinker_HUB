<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('partials.seo-meta', [
        'title' => ($title ?? 'Knowledge & Opportunities Hub').' | think.er HUB',
        'description' => $description ?? 'Explore tips & tricks, short blogs, career opportunities, and educational YouTube videos on think.er HUB.',
        'keywords' => $keywords ?? 'knowledge hub, career opportunities, programming tips, tutorials, thinker hub',
        'image' => $image ?? null,
        'url' => $url ?? url()->current(),
        'type' => $type ?? 'website',
        'breadcrumbs' => $breadcrumbs ?? null,
        'schema' => $schema ?? null,
    ])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.pwa-register')
    @livewireStyles
</head>
<body class="public-layout hub-public bg-[#f8fcf9] text-slate-900 font-sans antialiased min-h-dvh" x-data="{ mobileMenu: false }">
    @include('partials.app-preloader')
    {{ $slot }}
    <livewire:ai.public-chat-widget />
    @livewireScripts
</body>
</html>
