<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo-meta', [
        'title' => 'Checkout — ' . $course->title . ' | think.er HUB',
        'description' => 'Secure course enrollment payment for ' . $course->title . '.',
        'type' => 'website',
        'indexable' => false,
    ])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.pwa-register')
    <style>
        body {
            background: #f0faf8;
            background-image: repeating-linear-gradient(
                -45deg,
                transparent,
                transparent 28px,
                rgba(13,148,136,.06) 28px,
                rgba(13,148,136,.06) 30px
            );
            min-height: 100vh;
        }

        /* Sheet card */
        .pay-card { background:#fff; border-radius:20px; box-shadow:0 8px 40px rgba(10,45,39,.08); width:100%; }

        /* Tabs */
        .tab-bar { display:flex; border-bottom:1.5px solid #e8f5f3; }
        .tab-item { flex:1; padding:13px 0 11px; font-size:.78rem; font-weight:600; color:#9db5b2; text-align:center; cursor:pointer; border-bottom:2.5px solid transparent; margin-bottom:-1.5px; transition:all .18s; user-select:none; }
        .tab-item.active { color:#0a2d27; border-bottom-color:#0d9488; font-weight:700; }

        /* Provider item & smooth bouncy accordion */
        .provider-item {
            border-radius: 14px;
            transition: all 300ms cubic-bezier(0.34, 1.35, 0.7, 1);
            margin-bottom: 6px;
            border: 1.5px solid transparent;
            overflow: hidden;
        }
        .provider-item.selected {
            background: #e6f7f5;
            border-color: rgba(13, 148, 136, 0.25);
            box-shadow: 0 4px 16px rgba(13, 148, 136, 0.08);
            transform: scale(1.01);
        }
        .provider-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            cursor: pointer;
            border-radius: 12px;
            transition: background 200ms ease, transform 200ms cubic-bezier(0.34, 1.5, 0.64, 1);
            user-select: none;
        }
        .provider-row:hover {
            background: rgba(240, 250, 248, 0.8);
        }
        .provider-row:active {
            transform: scale(0.98);
        }
        .provider-logo {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: 1.5px solid #e8f5f3;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
            background: #fff;
            transition: transform 320ms cubic-bezier(0.34, 1.6, 0.64, 1), border-color 240ms ease;
        }
        .provider-item.selected .provider-logo {
            transform: scale(1.08);
            border-color: #0d9488;
            box-shadow: 0 2px 8px rgba(13, 148, 136, 0.2);
        }
        .provider-logo img {
            width: 32px;
            height: 32px;
            object-fit: contain;
        }
        .provider-name {
            font-size: .875rem;
            font-weight: 600;
            color: #0a2d27;
            flex: 1;
        }
        .provider-arrow {
            color: #9db5b2;
            font-size: .75rem;
            transition: transform 340ms cubic-bezier(0.34, 1.6, 0.64, 1), color 240ms ease;
        }
        .provider-item.selected .provider-arrow {
            transform: rotate(90deg);
            color: #0d9488;
        }

        /* Smooth bouncy accordion drawer */
        .provider-drawer {
            display: grid;
            grid-template-rows: 0fr;
            transition: grid-template-rows 340ms cubic-bezier(0.34, 1.35, 0.7, 1), opacity 240ms ease;
            opacity: 0;
        }
        .provider-drawer.open {
            grid-template-rows: 1fr;
            opacity: 1;
        }
        .provider-drawer-content {
            min-height: 0;
            overflow: hidden;
            padding: 2px 14px 14px;
        }
        .provider-divider { height:1px; background:#f0faf8; margin:0 14px; }

        /* Expanded form */
        .form-label { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#9db5b2; margin-bottom:6px; display:block; }
        .form-input { width:100%; border:1.5px solid #d4eae7; border-radius:10px; padding:10px 14px; font-size:.85rem; color:#0a2d27; outline:none; transition:border-color .15s, box-shadow .15s; background:#fff; }
        .form-input:focus { border-color:#0d9488; box-shadow:0 0 0 3px rgba(13,148,136,.10); }
        .input-prefix { display:flex; }
        .input-prefix-label { border:1.5px solid #d4eae7; border-right:none; border-radius:10px 0 0 10px; padding:10px 12px; font-size:.8rem; font-weight:700; color:#9db5b2; background:#f0faf8; }
        .input-prefix .form-input { border-radius:0 10px 10px 0; }

        /* Card preview */
        .card-preview { border-radius:14px; background:linear-gradient(135deg, #0a2d27 0%, #0f766e 100%); padding:20px; color:#fff; position:relative; overflow:hidden; margin-bottom:14px; }
        .card-preview::before { content:''; position:absolute; top:-30px; right:-30px; width:120px; height:120px; border-radius:50%; background:rgba(255,255,255,.06); }
        .card-preview::after { content:''; position:absolute; bottom:-40px; right:20px; width:90px; height:90px; border-radius:50%; background:rgba(255,255,255,.04); }

        /* Pay button */
        .pay-btn { width:100%; background:#0a2d27; color:#fff; border:none; border-radius:12px; padding:14px; font-size:.9rem; font-weight:700; cursor:pointer; transition:background .18s, transform .12s; }
        .pay-btn:hover { background:#0f3d35; }
        .pay-btn:active { transform:scale(.98); }
        .pay-btn:disabled { opacity:.55; cursor:not-allowed; }

        /* Amount badge */
        .amount-chip { background:#e6f7f5; color:#0d9488; font-size:.75rem; font-weight:700; padding:4px 10px; border-radius:99px; }

        /* Overlay modal */
        .modal-overlay { background:rgba(10,45,39,.65); backdrop-filter:blur(5px); }

        /* Spinner & Orbit Animation */
        @keyframes spin { to { transform:rotate(360deg); } }
        .spinner { animation:spin .7s linear infinite; }

        /* Modern Radar & Orbit Loader */
        .pay-radar-container {
            position: relative;
            width: 88px;
            height: 88px;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .pay-radar-ripple {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 2px solid #0d9488;
            opacity: 0;
            animation: radar-ripple 2.2s cubic-bezier(0.1, 0.8, 0.3, 1) infinite;
        }
        .pay-radar-ripple:nth-child(2) {
            animation-delay: 0.75s;
        }
        .pay-radar-center {
            position: relative;
            width: 54px;
            height: 54px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0d9488 0%, #0a2d27 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            box-shadow: 0 4px 20px rgba(13, 148, 136, 0.35);
            z-index: 2;
        }
        .pay-orbit-ring {
            position: absolute;
            inset: -5px;
            border-radius: 50%;
            border: 2.5px solid transparent;
            border-top-color: #2dd4bf;
            border-right-color: #2dd4bf;
            animation: spin 1.1s linear infinite;
        }
        @keyframes radar-ripple {
            0% { transform: scale(0.6); opacity: 0.9; }
            50% { opacity: 0.4; }
            100% { transform: scale(1.45); opacity: 0; }
        }
        .pulse-phone {
            animation: pulse-icon 1.8s ease-in-out infinite;
        }
        @keyframes pulse-icon {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.12); }
        }

        /* Pulse dots */
        @keyframes blink { 0%,80%,100%{opacity:.2} 40%{opacity:1} }
        .blink-dot { animation:blink 1.4s infinite both; }
        .blink-dot:nth-child(2) { animation-delay:.2s; }
        .blink-dot:nth-child(3) { animation-delay:.4s; }
    </style>
</head>
<body class="flex flex-col" x-data="checkout()">

    {{-- Minimal top bar --}}
    <div class="w-full bg-white/90 backdrop-blur-sm border-b border-teal-100/60 py-3 px-6 flex items-center justify-between">
        <a href="{{ url('/') }}" class="font-black text-[#0a2d27] text-base tracking-tight">think.er<span class="text-teal-600">HUB</span></a>
        <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <i class="fa-solid fa-lock text-teal-600 text-xs"></i>
            <span>Secure Lenco Checkout</span>
        </div>
    </div>

    <main class="flex-1 flex flex-col items-center justify-center py-8 lg:py-12 px-4 sm:px-6">
        <div class="w-full max-w-3xl">

            {{-- ── Minimal Top Header ── --}}
            <div class="mb-4 px-1 flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-teal-600 uppercase tracking-widest mb-1">Course Enrollment</p>
                    <h1 class="text-lg font-black text-[#0a2d27] leading-tight">{{ $course->title }}</h1>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $course->code }} &bull; Level: <span class="font-semibold text-slate-600" x-text="track"></span></p>
                </div>
                <span class="amount-chip shrink-0 ml-4 mt-1">ZMW {{ number_format($feeAmount, 2) }}</span>
            </div>

            {{-- Error message banner --}}
            <template x-if="errorMessage">
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3.5 text-xs text-red-700 flex items-start gap-2.5 shadow-sm">
                    <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5 text-sm shrink-0"></i>
                    <div class="flex-1" x-text="errorMessage"></div>
                    <button type="button" @click="errorMessage = ''" class="text-red-400 hover:text-red-600">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </template>

            {{-- ── Unified Side-by-Side Centered Card ── --}}
            <div class="pay-card border border-teal-100/80 overflow-hidden">
                <form method="POST" action="{{ route('checkout.process', $course) }}" id="payment-form" @submit.prevent="submitPayment()">
                    @csrf
                    <input type="hidden" name="payment_method" :value="paymentMethod">
                    <input type="hidden" name="provider" :value="provider">
                    <input type="hidden" name="track" :value="track">

                    <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-slate-100">

                        {{-- ── Left Side: Student Details & Selected Level ── --}}
                        <div class="p-6 flex flex-col justify-between space-y-4">
                            <div>
                                @guest
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Student Account</p>
                                        <a href="{{ route('login') }}" class="text-[11px] font-semibold text-teal-600 hover:underline">Sign in instead</a>
                                    </div>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="form-label">Full Name</label>
                                            <input type="text" name="name" x-model="student.name" placeholder="Your full name" class="form-input" required>
                                            @error('name')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                                        </div>
                                        <div>
                                            <label class="form-label">Email Address</label>
                                            <input type="email" name="email" x-model="student.email" placeholder="you@email.com" class="form-input" required>
                                            @error('email')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="form-label">Password</label>
                                                <input type="password" name="password" x-model="student.password" placeholder="••••••••" class="form-input" required>
                                            </div>
                                            <div>
                                                <label class="form-label">Confirm</label>
                                                <input type="password" name="password_confirmation" x-model="student.password_confirmation" placeholder="••••••••" class="form-input" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="mb-3">
                                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">Student Account</p>
                                    <div class="p-3.5 rounded-xl border border-teal-100 bg-teal-50/40 flex items-center gap-3">
                                        <div class="h-9 w-9 rounded-full bg-teal-800 text-white flex items-center justify-center font-bold text-sm shrink-0">
                                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-bold text-[#1e1e3f] truncate">{{ Auth::user()->name }}</p>
                                            <p class="text-[11px] text-slate-400 truncate">{{ Auth::user()->email }}</p>
                                        </div>
                                        <span class="text-[10px] font-bold text-emerald-600 bg-white px-2 py-0.5 rounded-full border border-emerald-100 shrink-0">✓ Active</span>
                                    </div>
                                </div>
                                @endguest
                            </div>

                            {{-- Selected Learning Level Card --}}
                            <div class="pt-3 border-t border-slate-100">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="form-label mb-0">Selected Learning Level</p>
                                    <span class="text-[10px] font-bold text-teal-700 bg-teal-50 px-2 py-0.5 rounded-full border border-teal-100">Confirmed</span>
                                </div>
                                <div class="flex items-center justify-between rounded-xl border border-teal-600/25 bg-teal-50/60 p-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="h-9 w-9 rounded-xl bg-teal-600 text-white flex items-center justify-center text-sm font-bold shadow-xs shrink-0">
                                            <i class="fa-solid fa-layer-group"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-black text-[#0a2d27]">{{ $selectedLevel }} Level</p>
                                            <p class="text-[11px] text-slate-500">Curriculum &amp; exercises configured for {{ strtolower($selectedLevel) }}</p>
                                        </div>
                                    </div>
                                    <span class="text-xs font-black text-teal-800 bg-white px-2.5 py-1 rounded-lg border border-teal-100 shadow-2xs shrink-0 ml-2">
                                        ZMW {{ number_format($feeAmount, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- ── Right Side: Payment Methods & Execution ── --}}
                        <div class="p-6 flex flex-col justify-between">
                            <div>
                                {{-- Tabs --}}
                                <div class="tab-bar mb-3">
                                    <button type="button" @click="setTab('mobile')" class="tab-item" :class="tab === 'mobile' && 'active'">
                                        <i class="fa-solid fa-mobile-screen-button mb-1 text-[13px]"></i><br>
                                        Mobile Money
                                    </button>
                                    <button type="button" @click="setTab('card')" class="tab-item" :class="tab === 'card' && 'active'">
                                        <i class="fa-solid fa-credit-card mb-1 text-[13px]"></i><br>
                                        Debit / Credit Card
                                    </button>
                                </div>

                                {{-- Mobile Money Tab --}}
                                <div x-show="tab === 'mobile'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                                    <div class="py-1 space-y-1.5">
                                        {{-- Airtel Money --}}
                                        <div class="provider-item" :class="provider === 'airtel' && 'selected'">
                                            <div class="provider-row" @click="selectProvider('airtel')">
                                                <div class="provider-logo">
                                                    <img src="{{ asset('images/momo/airtel.png') }}" alt="Airtel Money">
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="provider-name">Airtel Money</p>
                                                    <p class="text-[10px] text-slate-400">097 / 077</p>
                                                </div>
                                                <i class="fa-solid fa-chevron-right provider-arrow"></i>
                                            </div>
                                            <div class="provider-drawer" :class="provider === 'airtel' && 'open'">
                                                <div class="provider-drawer-content">
                                                    @include('partials.checkout-momo-form', ['color' => 'red'])
                                                </div>
                                            </div>
                                        </div>

                                        {{-- MTN MoMo --}}
                                        <div class="provider-item" :class="provider === 'mtn' && 'selected'">
                                            <div class="provider-row" @click="selectProvider('mtn')">
                                                <div class="provider-logo">
                                                    <img src="{{ asset('images/momo/mtn.png') }}" alt="MTN MoMo">
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="provider-name">MTN MoMo</p>
                                                    <p class="text-[10px] text-slate-400">096 / 076</p>
                                                </div>
                                                <i class="fa-solid fa-chevron-right provider-arrow"></i>
                                            </div>
                                            <div class="provider-drawer" :class="provider === 'mtn' && 'open'">
                                                <div class="provider-drawer-content">
                                                    @include('partials.checkout-momo-form', ['color' => 'amber'])
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Zamtel Kwacha --}}
                                        <div class="provider-item" :class="provider === 'zamtel' && 'selected'">
                                            <div class="provider-row" @click="selectProvider('zamtel')">
                                                <div class="provider-logo">
                                                    <img src="{{ asset('images/momo/zamtel.png') }}" alt="Zamtel Kwacha">
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="provider-name">Zamtel Kwacha</p>
                                                    <p class="text-[10px] text-slate-400">095 / 075</p>
                                                </div>
                                                <i class="fa-solid fa-chevron-right provider-arrow"></i>
                                            </div>
                                            <div class="provider-drawer" :class="provider === 'zamtel' && 'open'">
                                                <div class="provider-drawer-content">
                                                    @include('partials.checkout-momo-form', ['color' => 'green'])
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Card Tab --}}
                                <div x-show="tab === 'card'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="py-1">
                                    <div class="flex items-center gap-2 mb-3">
                                        <img src="{{ asset('images/momo/visa.png') }}" alt="Visa" class="h-5 object-contain">
                                        <img src="{{ asset('images/momo/Mastercard-logo.svg') }}" alt="Mastercard" class="h-5 object-contain">
                                        <span class="text-[10px] text-slate-400 ml-1">Visa &bull; Mastercard</span>
                                    </div>

                                    <div class="space-y-2.5">
                                        <div>
                                            <label class="form-label">Card Number</label>
                                            <input type="text" name="card_number" x-model="cardNumber" placeholder="0000 0000 0000 0000" maxlength="19"
                                                   class="form-input font-mono" @input="formatCardInput($event)">
                                        </div>
                                        <div>
                                            <label class="form-label">Cardholder Name</label>
                                            <input type="text" name="card_holder" x-model="cardHolder" placeholder="Full name on card" class="form-input">
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="form-label">Expiry Date</label>
                                                <input type="text" name="card_expiry" x-model="cardExpiry" placeholder="MM/YY" maxlength="5" class="form-input text-center font-mono">
                                            </div>
                                            <div>
                                                <label class="form-label">CVV / CVC</label>
                                                <input type="password" name="card_cvv" x-model="cardCvv" maxlength="4" placeholder="•••" class="form-input text-center font-mono">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Pay CTA & Security --}}
                            <div class="pt-4 mt-4 border-t border-slate-100">
                                <button type="submit" :disabled="isProcessing" class="pay-btn flex items-center justify-center gap-2">
                                    <template x-if="!isProcessing">
                                        <span>Pay ZMW {{ number_format($feeAmount, 2) }}</span>
                                    </template>
                                    <template x-if="isProcessing">
                                        <span class="flex items-center gap-2">
                                            <svg class="spinner h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span x-text="processingStep"></span>
                                        </span>
                                    </template>
                                </button>

                                <p class="mt-2.5 text-center text-[10px] text-slate-400 flex items-center justify-center gap-1.5">
                                    <i class="fa-solid fa-shield-halved text-teal-500"></i>
                                    <span>256-bit encryption &bull; Lenco by BroadPay</span>
                                </p>
                            </div>

                        </div>

                    </div>
                </form>
            </div>

            {{-- Back link --}}
            <div class="mt-4 text-center">
                <a href="{{ route('landing.courses.show', ['course' => $course->id, 'slug' => \Illuminate\Support\Str::slug($course->title ?: $course->code)]) }}"
                   class="text-xs text-slate-400 hover:text-slate-600 transition inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    <span>Back to course details</span>
                </a>
            </div>

        </div>
    </main>

    {{-- ━━━━━━━━━━━━━━━━━━━━━ USSD AUTHORIZATION MODAL ━━━━━━━━━━━━━━━━━━━━━ --}}
    <div x-show="showWaitingModal"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="modal-overlay fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="pay-card p-6 text-center w-full max-w-sm">
            <template x-if="!paymentConfirmed">
                <div>
                    {{-- Animated Radar Signal & Mobile Orbit Spinner --}}
                    <div class="pay-radar-container">
                        <div class="pay-radar-ripple"></div>
                        <div class="pay-radar-ripple"></div>
                        <div class="pay-radar-center">
                            <div class="pay-orbit-ring"></div>
                            <i class="fa-solid fa-mobile-screen-button text-lg pulse-phone"></i>
                        </div>
                    </div>

                    <h3 class="text-base font-black text-[#0a2d27]">Approve on Your Mobile Phone</h3>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                        A payment prompt of <strong class="text-[#0a2d27]">ZMW {{ number_format($feeAmount, 2) }}</strong> was sent to <strong class="text-teal-600" x-text="'+260 ' + phoneNumber"></strong>.
                    </p>

                    <div class="mt-4 rounded-xl border border-teal-100 bg-teal-50/60 p-3.5 text-center">
                        <div class="inline-flex items-center justify-center gap-2 text-teal-900 font-bold text-xs mb-1">
                            <span class="flex gap-1 items-center">
                                <span class="h-1.5 w-1.5 rounded-full bg-teal-600 animate-ping"></span>
                            </span>
                            <span>Awaiting PIN Authorization</span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            Please check your mobile phone and enter your PIN. This page will automatically confirm and activate your enrollment once verified.
                        </p>
                    </div>

                    <div class="mt-4">
                        <button type="button" @click="cancelPaymentWaiting()"
                                class="w-full text-xs text-slate-400 hover:text-slate-600 py-2 cursor-pointer transition flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-arrow-left text-[10px]"></i>
                            <span>Cancel &amp; Change details</span>
                        </button>
                    </div>
                </div>
            </template>

            <template x-if="paymentConfirmed">
                <div class="py-4">
                    <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center mx-auto mb-4 border border-emerald-100 shadow-sm">
                        <i class="fa-solid fa-check text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-black text-[#0a2d27]">Payment Approved!</h3>
                    <p class="text-xs text-slate-500 mt-1">Redirecting to your official receipt...</p>
                </div>
            </template>
        </div>
    </div>

    <script>
        function checkout() {
            return {
                tab: 'mobile',
                track: '{{ $selectedLevel }}',
                feeAmount: {{ (float) $feeAmount }},
                paymentMethod: 'mobile_money',
                provider: 'airtel',
                phoneNumber: '',
                cardNumber: '',
                cardHolder: '{{ Auth::check() ? Auth::user()->name : "" }}',
                cardExpiry: '',
                cardCvv: '',
                isProcessing: false,
                isCheckingStatus: false,
                processingStep: 'Processing…',
                errorMessage: '',
                showWaitingModal: false,
                paymentConfirmed: false,
                pendingReference: null,
                pollIntervalId: null,
                @php
                    $pendingReg = session('pending_registration', []);
                @endphp
                student: {
                    name: '{{ Auth::check() ? Auth::user()->name : addslashes($pendingReg['name'] ?? old('name', '')) }}',
                    email: '{{ Auth::check() ? Auth::user()->email : addslashes($pendingReg['email'] ?? old('email', '')) }}',
                    password: '{{ addslashes($pendingReg['password'] ?? "") }}',
                    password_confirmation: '{{ addslashes($pendingReg['password'] ?? "") }}'
                },

                setTab(t) {
                    this.tab = t;
                    this.paymentMethod = t === 'mobile' ? 'mobile_money' : 'card';
                    this.errorMessage = '';
                },

                selectProvider(p) {
                    this.provider = p;
                    this.paymentMethod = 'mobile_money';
                },

                formatCardPreview(num) {
                    if (!num) return '•••• •••• •••• ••••';
                    const clean = num.replace(/\s+/g, '');
                    return clean.replace(/(\d{4})/g, '$1 ').trim();
                },

                formatCardInput(e) {
                    let val = e.target.value.replace(/\D/g, '');
                    let formatted = '';
                    for (let i = 0; i < val.length; i++) {
                        if (i > 0 && i % 4 === 0) formatted += ' ';
                        formatted += val[i];
                    }
                    this.cardNumber = formatted;
                },

                async submitPayment() {
                    this.errorMessage = '';

                    // Validate selection
                    if (this.tab === 'mobile') {
                        if (!this.provider) {
                            this.errorMessage = 'Please select a Mobile Money network (Airtel, MTN, or Zamtel).';
                            return;
                        }
                        if (!this.phoneNumber || this.phoneNumber.trim().length < 9) {
                            this.errorMessage = 'Please enter a valid Zambian Mobile Money phone number.';
                            return;
                        }
                    }

                    if (this.tab === 'card') {
                        if (!this.cardNumber || this.cardNumber.replace(/\s/g, '').length < 13) {
                            this.errorMessage = 'Please enter a valid credit or debit card number.';
                            return;
                        }
                    }

                    const form = document.getElementById('payment-form');
                    const formData = new FormData(form);

                    this.isProcessing = true;
                    this.processingStep = 'Connecting to gateway…';

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            this.isProcessing = false;
                            this.errorMessage = data.message || (data.errors ? Object.values(data.errors).flat()[0] : 'Payment request could not be processed.');
                            return;
                        }

                        if (data.status === 'redirect' && data.redirect_url) {
                            this.processingStep = 'Redirecting to 3D Secure verification…';
                            window.location.href = data.redirect_url;
                            return;
                        }

                        if (data.status === 'completed' && data.redirect_url) {
                            this.isProcessing = false;
                            this.paymentConfirmed = true;
                            this.showWaitingModal = true;
                            setTimeout(() => {
                                window.location.href = data.redirect_url;
                            }, 1000);
                            return;
                        }

                        if (data.status === 'pending') {
                            this.pendingReference = data.reference;
                            this.isProcessing = false;
                            this.showWaitingModal = true;
                            this.startPolling(data.reference);
                            return;
                        }

                        // Fallback redirect
                        if (data.redirect_url) {
                            window.location.href = data.redirect_url;
                        }
                    } catch (err) {
                        this.isProcessing = false;
                        this.errorMessage = 'Network error: Unable to communicate with the payment server. Please try again.';
                    }
                },

                startPolling(reference) {
                    this.stopPolling();
                    this.pollIntervalId = setInterval(async () => {
                        try {
                            const res = await fetch(`/payments/status/${reference}`, {
                                headers: { 'Accept': 'application/json' }
                            });
                            const statusData = await res.json();

                            if (statusData.is_completed) {
                                this.stopPolling();
                                this.paymentConfirmed = true;
                                setTimeout(() => {
                                    window.location.href = statusData.redirect_url || `/payments/receipt/${reference}`;
                                }, 1200);
                            } else if (statusData.status === 'failed') {
                                this.stopPolling();
                                this.showWaitingModal = false;
                                this.errorMessage = 'The transaction was declined or timed out. Please try again.';
                            }
                        } catch (e) {
                            console.error('Polling error:', e);
                        }
                    }, 3000);
                },

                stopPolling() {
                    if (this.pollIntervalId) {
                        clearInterval(this.pollIntervalId);
                        this.pollIntervalId = null;
                    }
                },

                async checkPaymentStatusNow() {
                    if (!this.pendingReference) return;
                    this.isCheckingStatus = true;
                    try {
                        const res = await fetch(`/payments/status/${this.pendingReference}`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const statusData = await res.json();
                        this.isCheckingStatus = false;

                        if (statusData.is_completed) {
                            this.stopPolling();
                            this.paymentConfirmed = true;
                            setTimeout(() => {
                                window.location.href = statusData.redirect_url || `/payments/receipt/${this.pendingReference}`;
                            }, 1200);
                        } else {
                            alert('Payment is still awaiting authorization. Please enter your PIN on your mobile phone.');
                        }
                    } catch (e) {
                        this.isCheckingStatus = false;
                    }
                },

                cancelPaymentWaiting() {
                    this.stopPolling();
                    this.showWaitingModal = false;
                    this.isProcessing = false;
                }
            }
        }
    </script>
</body>
</html>
