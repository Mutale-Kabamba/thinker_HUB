<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Course Classroom' }} | {{ config('app.name', 'think.er HUB') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sora:400,500,600,700,800|space-grotesk:500,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-950 text-slate-100 font-sans antialiased h-full overflow-hidden select-none">
    {{ $slot }}

    <!-- Livewire Notification Toast System -->
    <div 
        x-data="{
            notifications: [],
            add(e) {
                const id = Date.now();
                const item = {
                    id,
                    type: e.detail[0]?.type || e.detail?.type || 'info',
                    message: e.detail[0]?.message || e.detail?.message || ''
                };
                this.notifications.push(item);
                setTimeout(() => {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                }, 4000);
            }
        }"
        x-on:notify.window="add($event)"
        class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-sm pointer-events-none"
    >
        <template x-for="item in notifications" :key="item.id">
            <div 
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="pointer-events-auto px-4 py-3 rounded-xl border shadow-2xl flex items-center gap-3 text-sm"
                :class="{
                    'bg-emerald-950/90 border-emerald-500/50 text-emerald-200': item.type === 'success',
                    'bg-amber-950/90 border-amber-500/50 text-amber-200': item.type === 'warning',
                    'bg-rose-950/90 border-rose-500/50 text-rose-200': item.type === 'error',
                    'bg-slate-900/90 border-slate-700 text-slate-200': item.type === 'info'
                }"
            >
                <i class="fa-solid" :class="{
                    'fa-circle-check text-emerald-400': item.type === 'success',
                    'fa-triangle-exclamation text-amber-400': item.type === 'warning',
                    'fa-circle-xmark text-rose-400': item.type === 'error',
                    'fa-circle-info text-cyan-400': item.type === 'info'
                }"></i>
                <span x-text="item.message" class="font-medium"></span>
            </div>
        </template>
    </div>

    @livewireScripts
</body>
</html>
