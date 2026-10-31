@php
    $navItems = [
        ['label' => 'Home', 'route' => 'home', 'match' => 'home'],
        ['label' => 'Courses', 'route' => 'landing.courses', 'match' => 'landing.courses*'],
        ['label' => 'Knowledge Hub', 'route' => 'hub.index', 'match' => 'hub.*'],
        ['label' => 'Network', 'route' => 'landing.instructors', 'match' => 'landing.instructors*'],
        ['label' => 'Contact', 'route' => 'landing.contact', 'match' => 'landing.contact*'],
    ];

    $tickerAnnouncements = [];
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('promo_codes')) {
            $activePromos = \App\Models\PromoCode::query()
                ->active()
                ->with('course:id,title,code,is_active,is_open_enrollment')
                ->where(function ($q) {
                    $q->whereNotNull('course_id')
                        ->orWhere('discount_type', '!=', 'percentage')
                        ->orWhere('discount_value', '<', 100);
                })
                ->orderByRaw('CASE WHEN course_id IS NOT NULL THEN 0 ELSE 1 END')
                ->orderByDesc('discount_value')
                ->limit(5)
                ->get();

            foreach ($activePromos as $promo) {
                $discountLabel = $promo->isPercentage()
                    ? rtrim(rtrim(number_format((float) $promo->discount_value, 2), '0'), '.') . '% OFF'
                    : 'K' . number_format((float) $promo->discount_value, 0) . ' OFF';

                if ($promo->course && $promo->course->is_active && $promo->course->is_open_enrollment !== false) {
                    $tickerAnnouncements[] = [
                        'badge' => 'COURSE PROMO',
                        'badge_class' => 'bg-gradient-to-r from-rose-500 to-amber-500 text-white',
                        'icon' => 'fa-solid fa-tag',
                        'text' => "{$discountLabel} on {$promo->course->title}",
                        'code' => $promo->code,
                        'cta' => 'Claim Promo',
                        'url' => route('checkout.show', ['course' => $promo->course->id, 'promo' => $promo->code]),
                    ];
                } elseif (! $promo->course_id) {
                    $tickerAnnouncements[] = [
                        'badge' => 'SPECIAL OFFER',
                        'badge_class' => 'bg-yellow-400 text-[#0a2d27]',
                        'icon' => 'fa-solid fa-bolt',
                        'text' => $promo->description ?: "Save {$discountLabel} on eligible courses",
                        'code' => $promo->code,
                        'cta' => 'Explore Courses',
                        'url' => route('landing.courses', ['promo' => $promo->code]),
                    ];
                }
            }
        }
    } catch (\Throwable) {
        // Fallback to default announcements if database is unavailable
    }

    $tickerAnnouncements[] = [
        'badge' => 'OPEN ENROLLMENT',
        'badge_class' => 'bg-emerald-500/25 text-emerald-300 border border-emerald-400/30',
        'icon' => 'fa-solid fa-graduation-cap',
        'text' => 'Practical tutor-led digital skills cohorts are open for registration',
        'code' => null,
        'cta' => 'Browse Catalog',
        'url' => route('landing.courses'),
    ];

    $tickerAnnouncements[] = [
        'badge' => 'LEARN & EARN',
        'badge_class' => 'bg-teal-500/25 text-teal-200 border border-teal-400/30',
        'icon' => 'fa-solid fa-award',
        'text' => 'Complete courses, earn verified certificates, XP & Thinker Coins',
        'code' => null,
        'cta' => 'Get Started',
        'url' => route('enroll'),
    ];
@endphp

<header class="hub-public-header sticky top-0 z-50 bg-white/90 backdrop-blur-xl border-b border-slate-200/80">
    {{-- Thin Scrolling Sticky Promo & Announcement Banner on top of the nav --}}
    <div class="hub-top-ticker bg-[#061f1b] text-white border-b border-teal-900/80 overflow-hidden">
        <div class="hub-ticker-viewport relative flex items-center h-7 overflow-hidden">
            <div class="hub-ticker-track flex items-center whitespace-nowrap">
                @for ($copy = 0; $copy < 2; $copy++)
                    <div class="flex items-center shrink-0" @if ($copy === 1) aria-hidden="true" @endif>
                        @foreach ($tickerAnnouncements as $item)
                            <a href="{{ $item['url'] }}" class="group inline-flex items-center gap-2 px-6 text-[11px] leading-none text-slate-200 hover:text-white transition-colors">
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[9px] font-black uppercase tracking-wider {{ $item['badge_class'] }}">
                                    <i class="{{ $item['icon'] }} text-[8px]"></i>
                                    <span>{{ $item['badge'] }}</span>
                                </span>
                                <span class="font-medium text-slate-100">{{ $item['text'] }}</span>
                                @if (! empty($item['code']))
                                    <span class="inline-flex items-center gap-1 rounded bg-white/10 border border-yellow-400/40 px-1.5 py-0.5 font-mono text-[10px] font-extrabold text-yellow-300 tracking-wider">
                                        CODE: {{ $item['code'] }}
                                    </span>
                                @endif
                                <span class="font-bold text-yellow-300 group-hover:underline inline-flex items-center gap-1">
                                    <span>{{ $item['cta'] }}</span>
                                    <i class="fa-solid fa-arrow-right text-[9px] transition-transform group-hover:translate-x-0.5"></i>
                                </span>
                            </a>
                            <span class="text-teal-600/70 text-[10px] select-none">•</span>
                        @endforeach
                    </div>
                @endfor
            </div>
        </div>
    </div>

    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 sm:px-6 lg:px-8 py-3">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5 text-xl font-bold text-slate-900 transition-opacity hover:opacity-90">
            <img src="{{ asset('images/logos/green.png') }}" alt="think.er HUB logo" class="h-8 max-h-8 md:h-9 md:max-h-9 w-auto object-contain dark:hidden">
            <img src="{{ asset('images/logos/yellow_white.png') }}" alt="think.er HUB logo" class="h-8 max-h-8 md:h-9 md:max-h-9 w-auto object-contain hidden dark:block">
        </a>

        <nav class="hub-public-nav hidden items-center gap-1 rounded-full border border-slate-200/70 bg-slate-50/80 p-1 text-xs font-semibold text-slate-600 md:flex">
            @foreach ($navItems as $item)
                @php
                    $isActive = request()->routeIs($item['match']);
                @endphp
                <a
                    href="{{ route($item['route']) }}"
                    class="{{ $isActive ? 'bg-[#0a2d27] text-white shadow-2xs font-bold' : 'text-slate-600 hover:text-teal-800 hover:bg-white/80' }} px-3.5 py-1.5 rounded-full transition-all duration-150"
                >{{ $item['label'] }}</a>
            @endforeach
        </nav>

        <div class="hidden md:flex items-center gap-3">
            @auth
                <a
                    href="{{ route('dashboard') }}"
                    class="inline-flex items-center gap-2 rounded-full bg-[#0a2d27] px-5 py-2 text-xs font-bold text-white shadow-xs hover:bg-[#11443c] transition-all whitespace-nowrap"
                >
                    <i class="fa-solid fa-gauge-high text-[11px] text-teal-300"></i>
                    <span>Dashboard ({{ Str::words(auth()->user()->name, 1, '') }})</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 transition-all cursor-pointer whitespace-nowrap">
                        <i class="fa-solid fa-arrow-right-from-bracket text-[10px]"></i>
                        <span>Logout</span>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="hub-public-auth-link inline-flex items-center justify-center rounded-full px-4 py-2 text-xs font-semibold text-slate-700 hover:text-teal-700 transition whitespace-nowrap">
                    Login
                </a>
                <a href="{{ route('enroll') }}" class="hub-public-cta inline-flex items-center justify-center rounded-full bg-[#0a2d27] px-5 py-2 text-xs font-semibold text-white hover:bg-[#11443c] transition duration-300 whitespace-nowrap">
                    Enroll Now
                </a>
            @endauth
        </div>

        <button class="text-xl text-slate-700 md:hidden p-2 rounded-xl border border-slate-200/80 bg-white hover:bg-slate-50 touch-target active:scale-95 transition" @click="mobileMenu = !mobileMenu" aria-label="Toggle menu">
            <i class="fa-solid" :class="mobileMenu ? 'fa-xmark' : 'fa-bars-staggered'"></i>
        </button>
    </div>

    <div class="border-t border-slate-200/80 bg-white/98 backdrop-blur-xl md:hidden max-h-[calc(100dvh-4.5rem)] overflow-y-auto safe-pb shadow-lg" x-show="mobileMenu" x-cloak x-transition>
        <nav class="hub-public-nav flex flex-col gap-1 p-4 font-semibold text-slate-700">
            @foreach ($navItems as $item)
                @php
                    $isActive = request()->routeIs($item['match']);
                @endphp
                <a
                    href="{{ route($item['route']) }}"
                    class="min-h-[42px] flex items-center justify-between px-4 py-2 rounded-xl transition text-sm {{ $isActive ? 'bg-[#0a2d27] text-white font-bold' : 'hover:bg-slate-100 text-slate-700' }}"
                >
                    <span>{{ $item['label'] }}</span>
                    @if ($isActive)
                        <i class="fa-solid fa-circle text-[6px] text-yellow-400"></i>
                    @endif
                </a>
            @endforeach

            @auth
                <div class="pt-3 mt-2 border-t border-slate-100 flex flex-col gap-2">
                    <a href="{{ route('dashboard') }}" class="w-full min-h-[44px] flex items-center justify-center gap-2 rounded-xl bg-[#0a2d27] py-2.5 text-center text-xs font-bold text-white active:scale-98">
                        <i class="fa-solid fa-gauge-high text-teal-300"></i>
                        <span>Dashboard ({{ auth()->user()->name }})</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full min-h-[42px] rounded-xl border border-slate-200 py-2 text-center text-xs font-bold text-rose-600 hover:bg-rose-50 active:scale-98 transition">
                            Log Out
                        </button>
                    </form>
                </div>
            @else
                <div class="pt-3 mt-2 grid grid-cols-2 gap-2.5 border-t border-slate-100">
                    <a href="{{ route('login') }}" class="min-h-[44px] flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-center text-xs font-bold text-slate-700 transition hover:bg-slate-50 active:scale-98">Login</a>
                    <a href="{{ route('register') }}" class="hub-public-cta min-h-[44px] flex items-center justify-center rounded-xl bg-[#0a2d27] px-5 py-2.5 text-center text-xs font-bold text-white transition active:scale-98">
                        Enroll Now
                    </a>
                </div>
            @endauth
        </nav>
    </div>
    @include('partials.public-motion')
</header>
