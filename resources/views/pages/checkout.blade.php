<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo-meta', [
        'title' => 'Checkout — ' . $course->title . ' | think.er HUB',
        'description' => 'Complete your enrollment for ' . $course->title . '.',
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
        .pay-card { background:#fff; border-radius:20px; box-shadow:0 8px 40px rgba(10,45,39,.10); width:100%; max-width:420px; }

        /* Tabs */
        .tab-bar { display:flex; border-bottom:1.5px solid #e8f5f3; }
        .tab-item { flex:1; padding:13px 0 11px; font-size:.78rem; font-weight:600; color:#9db5b2; text-align:center; cursor:pointer; border-bottom:2.5px solid transparent; margin-bottom:-1.5px; transition:all .18s; user-select:none; }
        .tab-item.active { color:#0a2d27; border-bottom-color:#0d9488; }

        /* Provider row */
        .provider-row { display:flex; align-items:center; gap:14px; padding:14px 20px; cursor:pointer; border-radius:12px; transition:background .15s; }
        .provider-row:hover { background:#f0faf8; }
        .provider-row.selected { background:#e6f7f5; }
        .provider-logo { width:44px; height:44px; border-radius:10px; border:1.5px solid #e8f5f3; object-fit:contain; display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0; }
        .provider-logo img { width:36px; height:36px; object-fit:contain; }
        .provider-name { font-size:.875rem; font-weight:600; color:#0a2d27; flex:1; }
        .provider-arrow { color:#b2d0cc; font-size:.75rem; transition:transform .18s; }
        .provider-row.selected .provider-arrow { transform:rotate(90deg); color:#0d9488; }
        .provider-divider { height:1px; background:#f0faf8; margin:0 20px; }

        /* Expanded form */
        .provider-form { padding:16px 20px 20px; background:#f7fdfb; border-top:1px solid #e8f5f3; }
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

        /* OTP boxes */
        .otp-box { width:40px; height:48px; border:1.5px solid #d4eae7; border-radius:10px; text-align:center; font-size:1.2rem; font-weight:700; color:#0a2d27; outline:none; transition:border-color .15s, box-shadow .15s; }
        .otp-box:focus { border-color:#0d9488; box-shadow:0 0 0 3px rgba(13,148,136,.10); }

        /* Pay button */
        .pay-btn { width:100%; background:#0a2d27; color:#fff; border:none; border-radius:12px; padding:14px; font-size:.9rem; font-weight:700; cursor:pointer; transition:background .18s, transform .12s; }
        .pay-btn:hover { background:#0f3d35; }
        .pay-btn:active { transform:scale(.98); }
        .pay-btn:disabled { opacity:.55; cursor:not-allowed; }

        /* Amount badge */
        .amount-chip { background:#e6f7f5; color:#0d9488; font-size:.75rem; font-weight:700; padding:4px 10px; border-radius:99px; }

        /* USSD/OTP modal overlay */
        .modal-overlay { background:rgba(10,45,39,.60); backdrop-filter:blur(4px); }

        /* Spinner */
        @keyframes spin { to { transform:rotate(360deg); } }
        .spinner { animation:spin .7s linear infinite; }

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
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <i class="fa-solid fa-lock text-teal-500 text-[10px]"></i>
            Secure checkout
        </div>
    </div>

    <main class="flex-1 flex items-start justify-center py-10 px-4">
        <div class="w-full max-w-xl">

            {{-- ── Order info ── --}}
            <div class="mb-5 px-1">
                <p class="text-xs font-semibold text-teal-600 uppercase tracking-widest mb-1">Enrollment Payment</p>
                <div class="flex items-start justify-between">
                    <div>
                        <h1 class="text-lg font-black text-[#0a2d27] leading-tight">{{ $course->title }}</h1>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $course->code }} &bull; Level: <span class="font-semibold text-slate-600" x-text="track"></span></p>
                    </div>
                    <span class="amount-chip shrink-0 ml-4 mt-1">ZMW {{ number_format($feeAmount, 2) }}</span>
                </div>
            </div>

            {{-- ── Main payment card ── --}}
            <div class="pay-card">

                <form method="POST" action="{{ route('checkout.process', $course) }}" id="payment-form" @submit.prevent="submitPayment()">
                    @csrf
                    <input type="hidden" name="payment_method" :value="paymentMethod">
                    <input type="hidden" name="provider" :value="provider">
                    <input type="hidden" name="track" :value="track">

                    {{-- ── Guest student info ── --}}
                    @guest
                    <div class="p-5 pb-0">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Your Details</p>
                            <a href="{{ route('login') }}" class="text-[11px] font-semibold text-teal-600 hover:underline">Sign in instead</a>
                        </div>
                        <div class="space-y-3 pb-5 border-b border-slate-100">
                            <div>
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" x-model="student.name" placeholder="Your full name" class="form-input" required>
                                @error('name')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="form-label">Email</label>
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
                    <div class="px-5 pt-5 pb-4 border-b border-slate-100 flex items-center gap-3">
                        <div class="h-9 w-9 rounded-full bg-teal-800 text-white flex items-center justify-center font-bold text-sm shrink-0">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-[#1e1e3f] truncate">{{ Auth::user()->name }}</p>
                            <p class="text-[11px] text-slate-400 truncate">{{ Auth::user()->email }}</p>
                        </div>
                        <span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full shrink-0">✓ Signed in</span>
                    </div>
                    @endguest

                    {{-- ── Level selector ── --}}
                    <div class="px-5 pt-4 pb-4 border-b border-slate-100">
                        <p class="form-label mb-3">Select Level</p>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach(['Beginner', 'Intermediate', 'Advanced'] as $lvl)
                            <button type="button" @click="track = '{{ $lvl }}'"
                                    :class="track === '{{ $lvl }}' ? 'border-teal-600 bg-teal-50 text-teal-800' : 'border-slate-200 text-slate-500 hover:border-teal-200'"
                                    class="rounded-xl border py-2.5 text-xs font-bold transition cursor-pointer">
                                {{ $lvl }}
                            </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- ── Tabs ── --}}
                    <div class="tab-bar px-1">
                        <button type="button" @click="tab = 'mobile'" class="tab-item" :class="tab === 'mobile' && 'active'">
                            <i class="fa-solid fa-mobile-screen-button mb-1 text-[14px]"></i><br>
                            Mobile Money
                        </button>
                        <button type="button" @click="tab = 'card'" class="tab-item" :class="tab === 'card' && 'active'">
                            <i class="fa-solid fa-credit-card mb-1 text-[14px]"></i><br>
                            Card
                        </button>
                        <button type="button" @click="tab = 'demo'; paymentMethod = 'demo'" class="tab-item" :class="tab === 'demo' && 'active'">
                            <i class="fa-solid fa-bolt mb-1 text-[14px]"></i><br>
                            Sandbox
                        </button>
                    </div>

                    {{-- ── Mobile Money ── --}}
                    <div x-show="tab === 'mobile'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <div class="py-2">
                            {{-- Zamtel --}}
                            <div>
                                <div class="provider-row" :class="provider === 'zamtel' && 'selected'"
                                     @click="selectProvider('zamtel', '0955987654')">
                                    <img src="{{ asset('images/momo/zamtel.png') }}" alt="Zamtel Kwacha" class="h-10 w-10 object-contain rounded-lg border border-slate-100 bg-white">
                                    <div class="flex-1 min-w-0">
                                        <p class="provider-name">Zamtel Kwacha</p>
                                        <p class="text-[10px] text-slate-400">095 / 075</p>
                                    </div>
                                    <i class="fa-solid fa-chevron-right provider-arrow"></i>
                                </div>
                                <div x-show="provider === 'zamtel'" x-transition class="provider-form">
                                    @include('partials.checkout-momo-form', ['color' => 'green'])
                                </div>
                                <div class="provider-divider"></div>
                            </div>
                            {{-- MTN --}}
                            <div>
                                <div class="provider-row" :class="provider === 'mtn' && 'selected'"
                                     @click="selectProvider('mtn', '0966123456')">
                                    <img src="{{ asset('images/momo/mtn.png') }}" alt="MTN MoMo" class="h-10 w-10 object-contain rounded-lg border border-slate-100 bg-white">
                                    <div class="flex-1 min-w-0">
                                        <p class="provider-name">MTN MoMo</p>
                                        <p class="text-[10px] text-slate-400">096 / 076</p>
                                    </div>
                                    <i class="fa-solid fa-chevron-right provider-arrow"></i>
                                </div>
                                <div x-show="provider === 'mtn'" x-transition class="provider-form">
                                    @include('partials.checkout-momo-form', ['color' => 'amber'])
                                </div>
                                <div class="provider-divider"></div>
                            </div>
                            {{-- Airtel --}}
                            <div>
                                <div class="provider-row" :class="provider === 'airtel' && 'selected'"
                                     @click="selectProvider('airtel', '0977264054')">
                                    <img src="{{ asset('images/momo/airtel.png') }}" alt="Airtel Money" class="h-10 w-10 object-contain rounded-lg border border-slate-100 bg-white">
                                    <div class="flex-1 min-w-0">
                                        <p class="provider-name">Airtel Money</p>
                                        <p class="text-[10px] text-slate-400">097 / 077</p>
                                    </div>
                                    <i class="fa-solid fa-chevron-right provider-arrow"></i>
                                </div>
                                <div x-show="provider === 'airtel'" x-transition class="provider-form">
                                    @include('partials.checkout-momo-form', ['color' => 'red'])
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Card ── --}}
                    <div x-show="tab === 'card'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="p-5">
                        <div class="flex items-center gap-2 mb-4">
                            <img src="{{ asset('images/momo/visa.png') }}" alt="Visa" class="h-6 object-contain">
                            <img src="{{ asset('images/momo/Mastercard-logo.svg') }}" alt="Mastercard" class="h-6 object-contain">
                            <span class="text-[10px] text-slate-400 ml-1">Accepted cards</span>
                        </div>
                        {{-- Card preview --}}
                        <div class="card-preview mb-4" :class="showCvvFocus && 'opacity-60'">
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-[10px] font-bold tracking-widest text-teal-200 uppercase">think.er HUB</span>
                                <i class="fa-brands fa-cc-visa text-xl text-white/80"></i>
                            </div>
                            <p class="font-mono text-lg tracking-[.18em] font-bold mb-4" x-text="cardNumber || '•••• •••• •••• ••••'"></p>
                            <div class="flex justify-between text-xs">
                                <div>
                                    <p class="text-teal-200/60 text-[9px] uppercase tracking-wider">Cardholder</p>
                                    <p class="font-bold uppercase" x-text="cardHolder || 'YOUR NAME'"></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-teal-200/60 text-[9px] uppercase tracking-wider">Expires</p>
                                    <p class="font-bold" x-text="cardExpiry || 'MM/YY'"></p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label class="form-label">Card Number</label>
                                <input type="text" name="card_number" x-model="cardNumber" placeholder="4242 4242 4242 4242" maxlength="19"
                                       class="form-input font-mono">
                            </div>
                            <div>
                                <label class="form-label">Cardholder Name</label>
                                <input type="text" name="card_holder" x-model="cardHolder" placeholder="Name on card" class="form-input">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="form-label">Expiry</label>
                                    <input type="text" x-model="cardExpiry" placeholder="MM/YY" maxlength="5" class="form-input text-center">
                                </div>
                                <div>
                                    <label class="form-label">CVV</label>
                                    <input type="password" maxlength="4" placeholder="•••"
                                           @focus="showCvvFocus=true" @blur="showCvvFocus=false"
                                           class="form-input text-center">
                                </div>
                            </div>
                            <button type="button" @click="fillTestCard()" class="text-[11px] text-teal-600 font-semibold hover:underline">
                                + Use test card (Visa 4242)
                            </button>
                        </div>
                    </div>



                    {{-- ── Demo ── --}}
                    <div x-show="tab === 'demo'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="p-5">
                        <div class="rounded-xl border border-dashed border-teal-200 bg-teal-50/50 p-4">
                            <p class="text-xs font-bold text-teal-800 mb-1 flex items-center gap-1.5">
                                <i class="fa-solid fa-bolt text-amber-500"></i> Sandbox Demo Mode
                            </p>
                            <p class="text-[11px] text-slate-500">Instantly approves enrollment without real payment — for testing only.</p>
                        </div>
                    </div>

                    {{-- ── Pay button ── --}}
                    <div class="px-5 pb-5 pt-3">
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

                        <p class="mt-3 text-center text-[10px] text-slate-400 flex items-center justify-center gap-1">
                            <i class="fa-solid fa-shield-halved text-teal-400"></i>
                            Encrypted simulated checkout &bull; Instant receipt on confirmation
                        </p>
                    </div>

                </form>
            </div>

            {{-- Back link --}}
            <div class="mt-4 text-center">
                <a href="{{ route('landing.courses.show', ['course' => $course->id, 'slug' => \Illuminate\Support\Str::slug($course->title ?: $course->code)]) }}"
                   class="text-xs text-slate-400 hover:text-slate-600 transition">
                    ← Back to course
                </a>
            </div>

        </div>
    </main>

    {{-- ━━━━━━━━━━━━━━━━━━━━━ MODALS ━━━━━━━━━━━━━━━━━━━━━ --}}

    {{-- USSD modal --}}
    <div x-show="showMomoModal"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="modal-overlay fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="pay-card p-6 text-center w-full max-w-xs">
            <div class="w-12 h-12 rounded-full bg-teal-50 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-mobile-screen-button text-teal-600 text-lg"></i>
            </div>
            <h3 class="text-base font-black text-[#0a2d27]">USSD Push Sent</h3>
            <p class="text-xs text-slate-400 mt-1.5">
                Approve <strong class="text-[#0a2d27]">ZMW {{ number_format($feeAmount, 2) }}</strong> on <strong class="text-teal-600" x-text="'+260 ' + phoneNumber"></strong>
            </p>

            <div class="mt-4 rounded-xl border border-dashed border-slate-200 bg-slate-50 p-3 text-left">
                <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Phone screen</p>
                <p class="text-xs text-slate-600 mt-1">"Pay ZMW {{ number_format($feeAmount, 2) }} to think.er HUB?"</p>
                <div class="flex gap-1.5 mt-2">
                    <span class="blink-dot h-2 w-2 rounded-full bg-teal-500"></span>
                    <span class="blink-dot h-2 w-2 rounded-full bg-teal-500"></span>
                    <span class="blink-dot h-2 w-2 rounded-full bg-teal-500"></span>
                </div>
            </div>

            <div class="mt-5 space-y-2">
                <button type="button" @click="confirmMomoSuccess()" class="pay-btn">Approve &amp; Enter PIN ✓</button>
                <button type="button" @click="showMomoModal = false; isProcessing = false"
                        class="w-full text-xs text-slate-400 hover:text-slate-600 py-2 cursor-pointer transition">Cancel</button>
            </div>
        </div>
    </div>

    {{-- 3DS OTP modal --}}
    <div x-show="showOtpModal"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="modal-overlay fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="pay-card p-6 text-center w-full max-w-xs">
            <div class="w-12 h-12 rounded-full bg-teal-50 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-lock text-teal-600 text-lg"></i>
            </div>
            <h3 class="text-base font-black text-[#0a2d27]">3D Secure — OTP</h3>
            <p class="text-xs text-slate-400 mt-1.5">Enter the 6-digit code sent to your phone</p>

            <div class="mt-4 flex justify-center gap-2">
                <template x-for="i in 6" :key="i">
                    <input type="text" maxlength="1" class="otp-box"
                           x-model="otpDigits[i-1]"
                           @input="focusNext($event, i)"
                           @keydown.backspace="focusPrev($event, i)">
                </template>
            </div>
            <p class="mt-2 text-[10px] text-slate-400">Test OTP: <strong class="text-teal-600">1 2 3 4 5 6</strong></p>

            <div class="mt-5 space-y-2">
                <button type="button" @click="confirmOtpSuccess()" class="pay-btn">Authorise Transaction</button>
                <button type="button" @click="showOtpModal = false; isProcessing = false"
                        class="w-full text-xs text-slate-400 hover:text-slate-600 py-2 cursor-pointer transition">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        function checkout() {
            return {
                tab: 'mobile',
                track: 'Beginner',
                paymentMethod: 'mobile_money',
                provider: '',
                phoneNumber: '',
                cardNumber: '',
                cardHolder: '',
                cardExpiry: '',
                showCvvFocus: false,
                isProcessing: false,
                processingStep: 'Processing…',
                showMomoModal: false,
                showOtpModal: false,
                otpDigits: ['','','','','',''],
                student: { name:'', email:'', password:'', password_confirmation:'' },

                get otpCode() { return this.otpDigits.join(''); },

                selectProvider(p, phone) {
                    this.provider = this.provider === p ? '' : p;
                    if (this.provider) {
                        this.phoneNumber = phone;
                        this.paymentMethod = 'mobile_money';
                    }
                },

                fillTestCard() {
                    this.cardNumber = '4242 4242 4242 4242';
                    this.cardHolder = '{{ Auth::check() ? Auth::user()->name : "Test User" }}';
                    this.cardExpiry = '12/28';
                    this.paymentMethod = 'card';
                },

                focusNext(e, i) {
                    if (e.target.value && i < 6) e.target.closest('.flex').querySelectorAll('input')[i]?.focus();
                },
                focusPrev(e, i) {
                    if (!e.target.value && i > 1) e.target.closest('.flex').querySelectorAll('input')[i-2]?.focus();
                },

                submitPayment() {
                    // Sync paymentMethod with active tab
                    if (this.tab === 'card') this.paymentMethod = 'card';
                    if (this.tab === 'demo') this.paymentMethod = 'demo';

                    if (this.tab === 'mobile' || this.paymentMethod === 'mobile_money') {
                        this.isProcessing = true;
                        this.processingStep = 'Sending USSD prompt…';
                        setTimeout(() => { this.showMomoModal = true; }, 600);
                        return;
                    }
                    if (this.tab === 'card') {
                        this.isProcessing = true;
                        this.processingStep = 'Connecting to 3D-Secure…';
                        setTimeout(() => { this.showOtpModal = true; }, 600);
                        return;
                    }
                    this.dispatchForm();
                },

                confirmMomoSuccess() {
                    this.showMomoModal = false;
                    this.processingStep = 'Confirming payment…';
                    this.dispatchForm();
                },
                confirmOtpSuccess() {
                    this.showOtpModal = false;
                    this.processingStep = 'Verifying OTP…';
                    this.dispatchForm();
                },
                dispatchForm() {
                    this.isProcessing = true;
                    document.getElementById('payment-form').submit();
                }
            }
        }
    </script>
</body>
</html>
