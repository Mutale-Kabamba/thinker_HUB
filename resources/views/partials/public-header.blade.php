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
                        'badge_style' => 'background-color: #e11d48; color: #ffffff;',
                        'icon' => 'fa-solid fa-tag',
                        'text' => "{$discountLabel} on {$promo->course->title}",
                        'code' => $promo->code,
                        'cta' => 'View Course',
                        'url' => route('landing.courses', ['promo' => $promo->code]) . '#courses-catalog',
                    ];
                } elseif (! $promo->course_id) {
                    $tickerAnnouncements[] = [
                        'badge' => 'SPECIAL OFFER',
                        'badge_style' => 'background-color: #facc15; color: #0a2d27;',
                        'icon' => 'fa-solid fa-bolt',
                        'text' => $promo->description ?: "Save {$discountLabel} on eligible courses",
                        'code' => $promo->code,
                        'cta' => 'View Eligible Courses',
                        'url' => route('landing.courses', ['promo' => $promo->code]) . '#courses-catalog',
                    ];
                }
            }
        }
    } catch (\Throwable) {
        // Ignore if database is unavailable
    }

    $repeatsPerHalf = ! empty($tickerAnnouncements)
        ? max(2, (int) ceil(4 / count($tickerAnnouncements)))
        : 0;
@endphp

<header class="hub-public-header sticky top-0 z-50 bg-white border-b border-slate-200/80">
    @if (! empty($tickerAnnouncements))
        {{-- Thin Solid-Color Scrolling Sticky Active Promo Banner on top of the nav --}}
        <div class="hub-top-ticker overflow-hidden" style="background-color: #0a2d27; color: #ffffff; border-bottom: 1px solid #11443c;">
            <div class="hub-ticker-viewport relative flex items-center h-8 overflow-hidden">
                <div class="hub-ticker-track flex items-center whitespace-nowrap">
                    @for ($copy = 0; $copy < 2; $copy++)
                        <div class="flex items-center shrink-0" @if ($copy === 1) aria-hidden="true" @endif>
                            @for ($rep = 0; $rep < $repeatsPerHalf; $rep++)
                                @foreach ($tickerAnnouncements as $item)
                                    <a href="{{ $item['url'] }}" class="group inline-flex items-center gap-2 px-6 text-[11px] leading-none transition-opacity hover:opacity-95" style="color: #ffffff;">
                                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[9px] font-black uppercase tracking-wider" style="{{ $item['badge_style'] }}">
                                            <i class="{{ $item['icon'] }} text-[8px]"></i>
                                            <span>{{ $item['badge'] }}</span>
                                        </span>
                                        <span class="font-semibold" style="color: #f8fafc;">{{ $item['text'] }}</span>
                                        @if (! empty($item['code']))
                                            <span class="inline-flex items-center gap-1 rounded px-1.5 py-0.5 font-mono text-[10px] font-extrabold tracking-wider" style="background-color: #11443c; color: #fde047; border: 1px solid rgba(250, 204, 21, 0.45);">
                                                CODE: {{ $item['code'] }}
                                            </span>
                                        @endif
                                        <span class="font-bold group-hover:underline inline-flex items-center gap-1" style="color: #fde047;">
                                            <span>{{ $item['cta'] }}</span>
                                            <i class="fa-solid fa-arrow-right text-[9px] transition-transform group-hover:translate-x-0.5"></i>
                                        </span>
                                    </a>
                                    <span class="text-[10px] select-none" style="color: #2dd4bf;">•</span>
                                @endforeach
                            @endfor
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    @endif

    <style>
        .hub-header-bar {
            gap: 0.5rem;
        }
        .hub-desktop-nav {
            display: none;
            align-items: center;
            gap: 0.2rem;
            border-radius: 9999px;
            border: 1px solid rgba(226, 232, 240, 0.85);
            background: rgba(248, 250, 252, 0.9);
            padding: 0.25rem;
            flex-shrink: 0;
        }
        .hub-desktop-nav a.hub-nav-pill {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            white-space: nowrap !important;
            min-height: 32px !important;
            height: 32px !important;
            padding: 0 0.7rem !important;
            border-radius: 9999px !important;
            font-size: 0.72rem !important;
            line-height: 1 !important;
            transition: all 150ms ease;
        }
        .hub-desktop-actions {
            display: none;
            align-items: center;
            gap: 0.375rem;
            flex-shrink: 0;
        }
        .hub-desktop-actions .hub-public-auth-link {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            white-space: nowrap !important;
            min-height: 34px !important;
            height: 34px !important;
            padding: 0 0.65rem !important;
            font-size: 0.73rem !important;
            line-height: 1 !important;
        }
        .hub-desktop-actions .hub-public-cta,
        .hub-desktop-actions .hub-dashboard-btn,
        .hub-desktop-actions .hub-logout-btn {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            white-space: nowrap !important;
            min-height: 34px !important;
            height: 34px !important;
            padding: 0 0.95rem !important;
            font-size: 0.73rem !important;
            line-height: 1 !important;
            border-radius: 9999px !important;
        }
        @media (min-width: 768px) {
            .hub-desktop-nav {
                display: inline-flex;
            }
            .hub-desktop-actions {
                display: flex;
            }
        }
        @media (min-width: 1024px) {
            .hub-header-bar {
                gap: 1rem;
            }
            .hub-desktop-nav {
                gap: 0.25rem;
                padding: 0.25rem;
            }
            .hub-desktop-nav a.hub-nav-pill {
                min-height: 34px !important;
                height: 34px !important;
                padding: 0 0.95rem !important;
                font-size: 0.75rem !important;
            }
            .hub-desktop-actions {
                gap: 0.65rem;
            }
            .hub-desktop-actions .hub-public-auth-link {
                min-height: 36px !important;
                height: 36px !important;
                padding: 0 0.9rem !important;
                font-size: 0.75rem !important;
            }
            .hub-desktop-actions .hub-public-cta,
            .hub-desktop-actions .hub-dashboard-btn,
            .hub-desktop-actions .hub-logout-btn {
                min-height: 36px !important;
                height: 36px !important;
                padding: 0 1.2rem !important;
                font-size: 0.75rem !important;
            }
        }
    </style>

    <div class="hub-header-bar mx-auto flex max-w-6xl items-center justify-between px-4 sm:px-6 lg:px-8 py-2.5 lg:py-3">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2 text-xl font-bold text-slate-900 transition-opacity hover:opacity-90">
            <img src="{{ asset('images/logos/green.png') }}" alt="think.er HUB logo" class="h-7 max-h-7 lg:h-9 lg:max-h-9 w-auto object-contain dark:hidden">
            <img src="{{ asset('images/logos/yellow_white.png') }}" alt="think.er HUB logo" class="h-7 max-h-7 lg:h-9 lg:max-h-9 w-auto object-contain hidden dark:block">
        </a>

        <nav class="hub-public-nav hub-desktop-nav text-xs font-semibold text-slate-600">
            @foreach ($navItems as $item)
                @php
                    $isActive = request()->routeIs($item['match']);
                @endphp
                <a
                    href="{{ route($item['route']) }}"
                    class="hub-nav-pill {{ $isActive ? 'bg-[#0a2d27] text-white shadow-2xs font-bold' : 'text-slate-600 hover:text-teal-800 hover:bg-white/80' }}"
                >{{ $item['label'] }}</a>
            @endforeach
        </nav>

        <div class="hub-desktop-actions">
            @auth
                <a
                    href="{{ route('dashboard') }}"
                    class="hub-dashboard-btn gap-2 bg-[#0a2d27] font-bold text-white shadow-xs hover:bg-[#11443c] transition-all"
                >
                    <i class="fa-solid fa-gauge-high text-[11px] text-teal-300"></i>
                    <span>Dashboard ({{ Str::words(auth()->user()->name, 1, '') }})</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="hub-logout-btn gap-1.5 border border-slate-200 bg-white font-semibold text-slate-600 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 transition-all cursor-pointer">
                        <i class="fa-solid fa-arrow-right-from-bracket text-[10px]"></i>
                        <span>Logout</span>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="hub-public-auth-link rounded-full font-semibold text-slate-700 hover:text-teal-700 transition">
                    Login
                </a>
                <a href="{{ route('enroll') }}" class="hub-public-cta bg-[#0a2d27] font-semibold text-white hover:bg-[#11443c] transition duration-300">
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
