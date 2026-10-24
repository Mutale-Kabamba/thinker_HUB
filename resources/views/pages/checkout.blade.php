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
        .pay-card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 4px 25px -4px rgba(13, 148, 136, 0.08);
            width: 100%;
        }

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
        .form-label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #0d9488;
            margin-bottom: 6px;
            display: block;
        }
        .form-input {
            width: 100%;
            border: 1.5px solid #d4eae7;
            border-radius: 12px;
            padding: 11px 14px;
            font-size: .875rem;
            color: #0a2d27;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
            background: #fff;
        }
        .form-input:focus {
            border-color: #0d9488;
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.12);
            outline: none;
        }
        .input-prefix { display:flex; }
        .input-prefix-label {
            border: 1.5px solid #d4eae7;
            border-right: none;
            border-radius: 12px 0 0 12px;
            padding: 11px 13px;
            font-size: .82rem;
            font-weight: 700;
            color: #0a2d27;
            background: #f0faf8;
        }
        .input-prefix .form-input { border-radius: 0 12px 12px 0; }

        /* Card preview */
        .card-preview { border-radius:14px; background:linear-gradient(135deg, #0a2d27 0%, #0f766e 100%); padding:20px; color:#fff; position:relative; overflow:hidden; margin-bottom:14px; }
        .card-preview::before { content:''; position:absolute; top:-30px; right:-30px; width:120px; height:120px; border-radius:50%; background:rgba(255,255,255,.06); }
        .card-preview::after { content:''; position:absolute; bottom:-40px; right:20px; width:90px; height:90px; border-radius:50%; background:rgba(255,255,255,.04); }

        /* Pay button */
        .pay-btn {
            width: 100%;
            background: #0a2d27;
            color: #fff;
            border: none;
            border-radius: 14px;
            padding: 14px 20px;
            font-size: .92rem;
            font-weight: 700;
            cursor: pointer;
            transition: background .18s, transform .12s, box-shadow .18s;
            box-shadow: 0 4px 14px rgba(10, 45, 39, 0.2);
        }
        .pay-btn:hover { background:#0f3d35; }
        .pay-btn:active { transform:scale(.98); }
        .pay-btn:disabled { opacity:.55; cursor:not-allowed; }

        /* Amount badge */
        .amount-chip { background:#e6f7f5; color:#0d9488; font-size:.78rem; font-weight:700; padding:5px 12px; border-radius:99px; }

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
<body class="public-layout flex flex-col" x-data="checkout()">

    {{-- Minimal top bar --}}
    <div class="w-full bg-white/90 backdrop-blur-sm border-b border-teal-100/60 py-3 px-6 flex items-center justify-between sticky top-0 z-30">
        <a href="{{ url('/') }}" class="font-black text-[#0a2d27] text-base tracking-tight">think.er<span class="text-teal-600">HUB</span></a>
        <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <i class="fa-solid fa-lock text-teal-600 text-xs"></i>
            <span>Secure Checkout</span>
        </div>
    </div>

    @php
        $initialStep = 1;
        if ($errors->any()) {
            if ($isForKids) {
                if ($errors->hasAny(['kid_name', 'kid_age', 'kid_school', 'kid_grade', 'relationship', 'kid_password'])) {
                    $initialStep = 1;
                } elseif ($errors->hasAny(['parent_name', 'parent_email', 'parent_password', 'parent_phone', 'parent_occupation', 'parent_company', 'parent_address'])) {
                    $initialStep = 2;
                } elseif ($errors->hasAny(['phone_number', 'phone', 'gateway', 'card_number', 'card_holder', 'card_expiry', 'card_cvv', 'payment_method'])) {
                    $initialStep = 4;
                }
            } else {
                if ($errors->hasAny(['name', 'email', 'password', 'password_confirmation'])) {
                    $initialStep = 1;
                } elseif ($errors->hasAny(['phone_number', 'phone', 'gateway', 'card_number', 'card_holder', 'card_expiry', 'card_cvv', 'payment_method'])) {
                    $initialStep = 3;
                }
            }
        }
    @endphp

    <main class="flex-1 flex flex-col items-center justify-start py-8 lg:py-12 px-4 sm:px-6">
        <div class="w-full max-w-2xl lg:max-w-3xl">

            {{-- ── Minimal Course Title & Price Header ── --}}
            <div class="mb-5 px-1 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <p class="text-xs font-semibold text-teal-600 uppercase tracking-widest">Course Enrollment</p>
                        @if($isForKids)
                            <span class="inline-flex items-center gap-1 font-bold text-teal-800 bg-teal-50 px-2 py-0.5 rounded-full border border-teal-200 text-[10px]">
                                <i class="fa-solid fa-child-reaching text-[10px]"></i> Kids Program
                            </span>
                        @endif
                    </div>
                    <h1 class="text-xl font-black text-[#0a2d27] leading-tight">{{ $course->title }}</h1>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ $course->code }} &bull; Level: <span class="font-semibold text-slate-700" x-text="track"></span> &bull; <span class="font-semibold text-teal-700" x-text="modeLabel"></span>
                    </p>
                </div>
                <div class="shrink-0 text-left sm:text-right">
                    <template x-if="appliedPromoCode && discountAmount > 0">
                        <div>
                            <div class="text-[11px] text-slate-400 line-through">
                                ZMW <span x-text="Number(originalFeeAmount).toLocaleString('en-ZM', { minimumFractionDigits: 2 })"></span>
                            </div>
                            <span class="amount-chip" :class="isFree ? 'bg-emerald-100 text-emerald-800' : ''">
                                <span x-show="!isFree">ZMW <span x-text="Number(finalFeeAmount).toLocaleString('en-ZM', { minimumFractionDigits: 2 })"></span></span>
                                <span x-show="isFree">FREE</span>
                            </span>
                        </div>
                    </template>
                    <template x-if="!appliedPromoCode || discountAmount <= 0">
                        <span class="amount-chip">ZMW <span x-text="Number(originalFeeAmount).toLocaleString('en-ZM', { minimumFractionDigits: 2 })"></span></span>
                    </template>
                </div>
            </div>

            {{-- ── Sleek Progressive Step-by-Step Stepper Bar ── --}}
            <div class="mb-6 bg-white/90 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-teal-100/90 shadow-xs">
                <div class="relative">
                    {{-- Progress connecting track --}}
                    <div class="absolute top-4 left-6 right-6 sm:left-10 sm:right-10 h-1 bg-slate-100 rounded-full -translate-y-1/2 z-0">
                        <div
                            class="h-full bg-gradient-to-r from-teal-500 to-emerald-500 rounded-full transition-all duration-300"
                            :style="'width: ' + ((currentStep - 1) / (totalSteps - 1) * 100) + '%'"
                        ></div>
                    </div>

                    {{-- Step Nodes --}}
                    <div class="relative z-10 flex items-center justify-between">
                        <template x-for="step in steps" :key="step.number">
                            <button
                                type="button"
                                @click="goToStep(step.number)"
                                class="group flex flex-col items-center focus:outline-none transition-transform duration-200"
                                :class="step.number <= currentStep ? 'cursor-pointer' : 'cursor-not-allowed opacity-75'"
                            >
                                {{-- Circle badge with checkmark or number --}}
                                <div
                                    class="w-8 h-8 sm:w-9 sm:h-9 rounded-full flex items-center justify-center font-bold text-xs transition-all duration-300 shadow-xs"
                                    :class="step.number < currentStep 
                                        ? 'bg-emerald-600 text-white hover:bg-emerald-700' 
                                        : (step.number === currentStep 
                                            ? 'bg-[#0a2d27] text-white ring-4 ring-teal-500/25 scale-110 shadow-md' 
                                            : 'bg-white border-2 border-slate-200 text-slate-400 group-hover:border-slate-300')"
                                >
                                    <template x-if="step.number < currentStep">
                                        <i class="fa-solid fa-check text-xs"></i>
                                    </template>
                                    <template x-if="step.number >= currentStep">
                                        <span x-text="step.number"></span>
                                    </template>
                                </div>

                                {{-- Step label --}}
                                <span
                                    class="mt-2 text-[10px] sm:text-xs font-bold text-center transition-colors line-clamp-1 max-w-[70px] sm:max-w-none"
                                    :class="step.number === currentStep 
                                        ? 'text-[#0a2d27] font-black' 
                                        : (step.number < currentStep ? 'text-emerald-700' : 'text-slate-400')"
                                    x-text="step.title"
                                ></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Error message banner --}}
            <template x-if="errorMessage">
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-xs text-red-700 flex items-start gap-3 shadow-xs">
                    <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5 text-base shrink-0"></i>
                    <div class="flex-1 font-medium" x-text="errorMessage"></div>
                    <button type="button" @click="errorMessage = ''" class="text-red-400 hover:text-red-700 p-0.5">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </template>

            {{-- ── Progressive Step Wizard Card ── --}}
            <div class="pay-card border border-teal-100/90 overflow-hidden bg-white p-6 sm:p-9 rounded-3xl">
                <form method="POST" action="{{ route('checkout.process', $course) }}" id="payment-form" @submit.prevent="submitPayment()">
                    @csrf
                    <input type="hidden" name="payment_method" :value="isFree ? 'promo_code' : paymentMethod">
                    <input type="hidden" name="provider" :value="provider">
                    <input type="hidden" name="track" :value="track">
                    <input type="hidden" name="mode" :value="mode">
                    <input type="hidden" name="promo_code" :value="appliedPromoCode">
                    <input type="hidden" name="is_for_kids" value="{{ $isForKids ? '1' : '0' }}">

                    @if($isForKids)
                        {{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
                             KIDS FLOW: STEP 1 — CHILD DETAILS
                        ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
                        <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-5">
                            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-teal-50 border border-teal-200/60 text-teal-700 flex items-center justify-center text-base shrink-0">
                                        <i class="fa-solid fa-child-reaching"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-base font-black text-[#0a2d27]">Junior Learner's Information</h2>
                                        <p class="text-xs text-slate-500">Provide the child's details for learning materials and classroom assignment.</p>
                                    </div>
                                </div>
                                <span class="hidden sm:inline-flex text-[11px] font-bold text-teal-800 bg-teal-50 px-3 py-1 rounded-full border border-teal-200">
                                    Step 1 of 4
                                </span>
                            </div>

                            <div class="space-y-4">
                                @if($existingKid)
                                    <input type="hidden" name="existing_kid_id" value="{{ $existingKid->id }}">
                                    <input type="hidden" name="kid_name" value="{{ $existingKid->name }}">

                                    <div class="rounded-2xl border border-teal-200 bg-teal-50/90 p-5 text-teal-950 dark:border-teal-800 dark:bg-teal-950/40 dark:text-teal-200 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold flex items-center gap-2 text-sm text-teal-900 dark:text-teal-200">
                                                <i class="fa-solid fa-child-reaching text-teal-600 text-base"></i>
                                                Enrolling Registered Child: {{ $existingKid->name }}
                                            </span>
                                            <span class="rounded-full bg-teal-200/60 px-3 py-1 text-xs font-mono font-bold uppercase text-teal-900 dark:bg-teal-800 dark:text-teal-100">
                                                {{ $existingKid->unique_code }}
                                            </span>
                                        </div>
                                        <p class="text-slate-600 dark:text-slate-300 leading-relaxed text-xs">
                                            This child is already enrolled on Thinker HUB. Re-entering child information and password is not required. You can proceed directly to select course options and complete payment.
                                        </p>
                                        <div class="flex flex-wrap gap-2 pt-1 text-[11px] text-teal-800 dark:text-teal-300 font-medium">
                                            @if($existingKid->age) <span class="bg-teal-100/70 dark:bg-teal-900/60 px-2 py-0.5 rounded-md">Age: {{ $existingKid->age }}</span> @endif
                                            @if($existingKid->grade) <span class="bg-teal-100/70 dark:bg-teal-900/60 px-2 py-0.5 rounded-md">Grade: {{ $existingKid->grade }}</span> @endif
                                            @if($existingKid->school) <span class="bg-teal-100/70 dark:bg-teal-900/60 px-2 py-0.5 rounded-md truncate max-w-[220px]">School: {{ $existingKid->school }}</span> @endif
                                        </div>
                                    </div>
                                @else
                                    <div>
                                        <label class="form-label">Kid's Full Name <span class="text-red-500">*</span></label>
                                        <input type="text" name="kid_name" x-model="kid.name" placeholder="e.g. Chileshe Mwape" class="form-input" required>
                                        @error('kid_name')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="form-label">Child's Age (Years) <span class="text-red-500">*</span></label>
                                            <input type="number" min="3" max="18" name="kid_age" x-model="kid.age" placeholder="e.g. 10" class="form-input" required>
                                            @error('kid_age')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                                        </div>
                                        <div>
                                            <label class="form-label">Grade / Year in School <span class="text-red-500">*</span></label>
                                            <input type="text" name="kid_grade" x-model="kid.grade" placeholder="e.g. Grade 5 / Year 6" class="form-input" required>
                                            @error('kid_grade')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                                        </div>
                                    </div>

                                    <div>
                                        <label class="form-label">School They Attend <span class="text-red-500">*</span></label>
                                        <input type="text" name="kid_school" x-model="kid.school" placeholder="e.g. Baobab College / Lusaka Boys" class="form-input" required>
                                        @error('kid_school')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="form-label">Your Relationship to Child <span class="text-red-500">*</span></label>
                                            <select name="relationship" x-model="kid.relationship" class="form-input cursor-pointer" required>
                                                <option value="Mother">Mother</option>
                                                <option value="Father">Father</option>
                                                <option value="Aunt">Aunt</option>
                                                <option value="Uncle">Uncle</option>
                                                <option value="Elder Sister">Elder Sister</option>
                                                <option value="Elder Brother">Elder Brother</option>
                                                <option value="Grandmother">Grandmother</option>
                                                <option value="Grandfather">Grandfather</option>
                                                <option value="Guardian">Legal Guardian</option>
                                            </select>
                                            @error('relationship')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                                        </div>
                                        <div>
                                            <label class="form-label">Create Kid's Password <span class="text-red-500">*</span></label>
                                            <input type="password" name="kid_password" x-model="kid.password" placeholder="••••••••" class="form-input" required>
                                            @error('kid_password')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                                        </div>
                                    </div>

                                    <div class="rounded-xl bg-teal-50/70 border border-teal-100 p-3.5 flex items-start gap-3">
                                        <i class="fa-solid fa-circle-info text-teal-600 mt-0.5 text-sm shrink-0"></i>
                                        <p class="text-xs text-teal-900 leading-relaxed">
                                            Your child will receive an exclusive <strong>Unique Login Code</strong> linked to your parent email. They will use that code and this password to sign into thinker HUB.
                                        </p>
                                    </div>
                                @endif
                            </div>

                            {{-- Navigation Buttons --}}
                            <div class="pt-6 mt-6 border-t border-slate-100 flex items-center justify-between">
                                <a href="{{ route('landing.courses.show', ['course' => $course->id, 'slug' => \Illuminate\Support\Str::slug($course->title ?: $course->code)]) }}"
                                   class="text-xs text-slate-400 hover:text-slate-600 font-medium inline-flex items-center gap-1.5 transition">
                                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                                    <span>Back to Course</span>
                                </a>
                                <button
                                    type="button"
                                    @click="nextStep()"
                                    class="px-6 py-3 rounded-xl bg-[#0a2d27] hover:bg-teal-900 text-white font-bold text-xs inline-flex items-center gap-2 shadow-sm transition active:scale-98 cursor-pointer"
                                >
                                    <span x-text="existingKidId ? 'Continue to Plan & Discounts' : 'Continue to Parent Details'">Continue to Parent Details</span>
                                    <i class="fa-solid fa-arrow-right text-[11px]"></i>
                                </button>
                            </div>
                        </div>

                        {{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
                             KIDS FLOW: STEP 2 — PARENT / GUARDIAN ACCOUNT
                        ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
                        <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-5" style="display: none;">
                            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-teal-50 border border-teal-200/60 text-teal-700 flex items-center justify-center text-base shrink-0">
                                        <i class="fa-solid fa-user-shield"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-base font-black text-[#0a2d27]">Parent / Guardian Account</h2>
                                        <p class="text-xs text-slate-500">Your parent credentials to track lessons, attendance, and connect with other parents.</p>
                                    </div>
                                </div>
                                <span class="hidden sm:inline-flex text-[11px] font-bold text-teal-800 bg-teal-50 px-3 py-1 rounded-full border border-teal-200">
                                    Step 2 of 4
                                </span>
                            </div>

                            @guest
                            <div class="space-y-4">
                                <div>
                                    <label class="form-label">Parent / Guardian Full Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="parent_name" x-model="parent.name" placeholder="Your full name" class="form-input" required>
                                    @error('parent_name')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="form-label">Email Address <span class="text-red-500">*</span></label>
                                        <input type="email" name="parent_email" x-model="parent.email" placeholder="parent@email.com" class="form-input" required>
                                        @error('parent_email')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label class="form-label">Create Parent Password <span class="text-red-500">*</span></label>
                                        <input type="password" name="parent_password" x-model="parent.password" placeholder="••••••••" class="form-input" required>
                                        @error('parent_password')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label">Contact / WhatsApp Number <span class="text-red-500">*</span></label>
                                    <input type="text" name="parent_phone" x-model="parent.phone" placeholder="e.g. 0977123456" class="form-input" required>
                                    @error('parent_phone')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="form-label">Occupation <span class="text-red-500">*</span></label>
                                        <input type="text" name="parent_occupation" x-model="parent.occupation" placeholder="e.g. Accountant / Engineer" class="form-input" required>
                                        @error('parent_occupation')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label class="form-label">Employer / Organization <span class="text-red-500">*</span></label>
                                        <input type="text" name="parent_company" x-model="parent.company" placeholder="e.g. Bank of Zambia / Self-employed" class="form-input" required>
                                        @error('parent_company')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label">Home Address <span class="text-red-500">*</span></label>
                                    <input type="text" name="parent_address" x-model="parent.address" placeholder="e.g. Plot 1234, Woodlands, Lusaka" class="form-input" required>
                                    @error('parent_address')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                                </div>
                            </div>
                            @else
                            <div class="space-y-4">
                                <div class="p-4 rounded-2xl border border-teal-200/80 bg-teal-50/50 flex items-center gap-3.5">
                                    <div class="h-10 w-10 rounded-full bg-teal-800 text-white flex items-center justify-center font-bold text-sm shrink-0">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-bold text-[#1e1e3f] truncate">{{ Auth::user()->name }}</p>
                                        <p class="text-xs text-slate-500 truncate">{{ Auth::user()->email }} (Logged-in Parent Account)</p>
                                    </div>
                                    <span class="text-[10px] font-bold text-emerald-700 bg-white px-2.5 py-1 rounded-full border border-emerald-100 shrink-0">
                                        ✓ Verified
                                    </span>
                                </div>

                                <div>
                                    <label class="form-label">Contact / WhatsApp Number</label>
                                    <input type="text" name="parent_phone" value="{{ Auth::user()->phone_number }}" x-model="parent.phone" placeholder="e.g. 0977123456" class="form-input">
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="form-label">Occupation</label>
                                        <input type="text" name="parent_occupation" value="{{ Auth::user()->occupation }}" x-model="parent.occupation" placeholder="e.g. Architect" class="form-input">
                                    </div>
                                    <div>
                                        <label class="form-label">Employer / Organization</label>
                                        <input type="text" name="parent_company" value="{{ Auth::user()->company }}" x-model="parent.company" placeholder="e.g. Mining Corp" class="form-input">
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label">Home Address</label>
                                    <input type="text" name="parent_address" value="{{ Auth::user()->address }}" x-model="parent.address" placeholder="e.g. Plot 1234, Woodlands, Lusaka" class="form-input">
                                </div>
                            </div>
                            @endguest

                            {{-- Navigation Buttons --}}
                            <div class="pt-6 mt-6 border-t border-slate-100 flex items-center justify-between">
                                <button
                                    type="button"
                                    @click="prevStep()"
                                    class="px-5 py-3 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs inline-flex items-center gap-1.5 transition cursor-pointer"
                                >
                                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                                    <span>Back to Child Info</span>
                                </button>
                                <button
                                    type="button"
                                    @click="nextStep()"
                                    class="px-6 py-3 rounded-xl bg-[#0a2d27] hover:bg-teal-900 text-white font-bold text-xs inline-flex items-center gap-2 shadow-sm transition active:scale-98 cursor-pointer"
                                >
                                    <span>Continue to Plan &amp; Discounts</span>
                                    <i class="fa-solid fa-arrow-right text-[11px]"></i>
                                </button>
                            </div>
                        </div>

                    @else
                        {{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
                             REGULAR FLOW: STEP 1 — STUDENT ACCOUNT
                        ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
                        <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-5">
                            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-teal-50 border border-teal-200/60 text-teal-700 flex items-center justify-center text-base shrink-0">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-base font-black text-[#0a2d27]">Student Account</h2>
                                        <p class="text-xs text-slate-500">Your profile credentials to access lessons, materials, and student discussions.</p>
                                    </div>
                                </div>
                                <span class="hidden sm:inline-flex text-[11px] font-bold text-teal-800 bg-teal-50 px-3 py-1 rounded-full border border-teal-200">
                                    Step 1 of 3
                                </span>
                            </div>

                            @guest
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-slate-500">Enter your details to create your account:</span>
                                    <a href="{{ route('login') }}" class="text-xs font-semibold text-teal-700 hover:underline">Already registered? Sign in</a>
                                </div>

                                <div>
                                    <label class="form-label">Full Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" x-model="student.name" placeholder="Your full name" class="form-input" required>
                                    @error('name')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label class="form-label">Email Address <span class="text-red-500">*</span></label>
                                    <input type="email" name="email" x-model="student.email" placeholder="you@email.com" class="form-input" required>
                                    @error('email')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="form-label">Create Password <span class="text-red-500">*</span></label>
                                        <input type="password" name="password" x-model="student.password" placeholder="••••••••" class="form-input" required>
                                        @error('password')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label class="form-label">Confirm Password <span class="text-red-500">*</span></label>
                                        <input type="password" name="password_confirmation" x-model="student.password_confirmation" placeholder="••••••••" class="form-input" required>
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="space-y-4">
                                <div class="p-4 rounded-2xl border border-teal-200/80 bg-teal-50/50 flex items-center gap-3.5">
                                    <div class="h-10 w-10 rounded-full bg-teal-800 text-white flex items-center justify-center font-bold text-sm shrink-0">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-bold text-[#1e1e3f] truncate">{{ Auth::user()->name }}</p>
                                        <p class="text-xs text-slate-500 truncate">{{ Auth::user()->email }} (Logged-in Student Account)</p>
                                    </div>
                                    <span class="text-[10px] font-bold text-emerald-700 bg-white px-2.5 py-1 rounded-full border border-emerald-100 shrink-0">
                                        ✓ Verified Active
                                    </span>
                                </div>
                            </div>
                            @endguest

                            {{-- Navigation Buttons --}}
                            <div class="pt-6 mt-6 border-t border-slate-100 flex items-center justify-between">
                                <a href="{{ route('landing.courses.show', ['course' => $course->id, 'slug' => \Illuminate\Support\Str::slug($course->title ?: $course->code)]) }}"
                                   class="text-xs text-slate-400 hover:text-slate-600 font-medium inline-flex items-center gap-1.5 transition">
                                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                                    <span>Back to Course</span>
                                </a>
                                <button
                                    type="button"
                                    @click="nextStep()"
                                    class="px-6 py-3 rounded-xl bg-[#0a2d27] hover:bg-teal-900 text-white font-bold text-xs inline-flex items-center gap-2 shadow-sm transition active:scale-98 cursor-pointer"
                                >
                                    <span>Continue to Plan &amp; Discounts</span>
                                    <i class="fa-solid fa-arrow-right text-[11px]"></i>
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
                         STEP: PLAN SELECTION, STUDY MODE & PROMO VOUCHER
                         (Step 3 for Kids, Step 2 for Regular)
                    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
                    <div x-show="currentStep === (isForKids ? 3 : 2)" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6" style="display: none;">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-teal-50 border border-teal-200/60 text-teal-700 flex items-center justify-center text-base shrink-0">
                                    <i class="fa-solid fa-ticket"></i>
                                </div>
                                <div>
                                    <h2 class="text-base font-black text-[#0a2d27]">Plan, Mode &amp; Discounts</h2>
                                    <p class="text-xs text-slate-500">Confirm your curriculum track, class schedule mode, and apply any voucher codes.</p>
                                </div>
                            </div>
                            <span class="hidden sm:inline-flex text-[11px] font-bold text-teal-800 bg-teal-50 px-3 py-1 rounded-full border border-teal-200">
                                <span x-text="'Step ' + (isForKids ? '3 of 4' : '2 of 3')"></span>
                            </span>
                        </div>

                        {{-- Selected Learning Level & Mode Card --}}
                        <div class="rounded-2xl border border-teal-600/20 bg-teal-50/50 p-4 sm:p-5 space-y-3">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-bold text-teal-900 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="fa-solid fa-layer-group text-teal-600"></i>
                                    <span>Selected Track &amp; Study Mode</span>
                                </p>
                                <template x-if="options && options.length > 1">
                                    <button
                                        type="button"
                                        @click="showOptionSwitcher = !showOptionSwitcher"
                                        class="text-xs font-bold text-teal-800 hover:text-teal-950 bg-white hover:bg-teal-100/60 px-3 py-1 rounded-full border border-teal-200 shadow-2xs transition cursor-pointer"
                                    >
                                        <span x-text="showOptionSwitcher ? 'Close Options' : 'Change Mode / Level'"></span>
                                    </button>
                                </template>
                            </div>

                            <div class="flex items-center justify-between pt-1">
                                <div class="min-w-0 flex-1">
                                    <p class="text-base font-black text-[#0a2d27]">
                                        <span x-text="track"></span> Level &bull; <span x-text="modeLabel"></span>
                                    </p>
                                    <p class="text-xs text-slate-600 mt-0.5">Full access to curriculum modules, resources, and live mentor support.</p>
                                </div>
                                <span class="text-sm font-black text-teal-900 bg-white px-3 py-1.5 rounded-xl border border-teal-100 shadow-2xs shrink-0 ml-3">
                                    ZMW <span x-text="Number(feeAmount).toLocaleString('en-ZM', { minimumFractionDigits: 2 })"></span>
                                </span>
                            </div>

                            {{-- Inline Option Switcher Drawer (if multiple options available) --}}
                            <template x-if="options && options.length > 1 && showOptionSwitcher">
                                <div class="mt-3 space-y-2 p-2.5 rounded-xl bg-white border border-teal-100 max-h-56 overflow-y-auto">
                                    <template x-for="opt in options" :key="opt.id">
                                        <div
                                            @click="selectCourseOption(opt)"
                                            class="p-2.5 rounded-xl border text-xs flex items-center justify-between cursor-pointer transition"
                                            :class="track === opt.level && mode === (opt.category || opt.mode)
                                                ? 'border-teal-600 bg-teal-50 text-teal-950 font-bold ring-1 ring-teal-500/20'
                                                : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
                                        >
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-4 h-4 rounded-full border flex items-center justify-center shrink-0"
                                                     :class="track === opt.level && mode === (opt.category || opt.mode) ? 'border-teal-600 bg-teal-600' : 'border-slate-300'">
                                                    <i x-show="track === opt.level && mode === (opt.category || opt.mode)" class="fa-solid fa-check text-[9px] text-white"></i>
                                                </div>
                                                <div>
                                                    <span class="font-bold" x-text="opt.level"></span>
                                                    <span class="text-[10px] ml-1.5 px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-medium" x-text="opt.mode_label"></span>
                                                </div>
                                            </div>
                                            <span class="font-black text-teal-800 text-xs" x-text="opt.formatted_amount"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        {{-- ── Promo Code / Voucher Box ── --}}
                        <div class="rounded-2xl border border-slate-200/90 p-4 sm:p-5 bg-slate-50/50 space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="fa-solid fa-tag text-teal-600"></i>
                                    <span>Have a Promo Code or Voucher?</span>
                                </label>
                                <span class="text-[11px] text-slate-400">Optional</span>
                            </div>

                            {{-- Unapplied State: Input Box & Apply Button --}}
                            <div x-show="!appliedPromoCode" class="space-y-2">
                                <div class="flex gap-2">
                                    <div class="relative flex-1">
                                        <input
                                            type="text"
                                            x-model="promoInput"
                                            @keydown.enter.prevent="applyPromoCode(promoInput)"
                                            placeholder="Enter discount voucher code (e.g. WELCOME20)"
                                            class="form-input text-xs uppercase tracking-wider font-mono font-bold"
                                            :disabled="isApplyingPromo"
                                        >
                                    </div>
                                    <button
                                        type="button"
                                        @click="applyPromoCode(promoInput)"
                                        :disabled="isApplyingPromo || !promoInput.trim()"
                                        class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-teal-800 hover:bg-teal-700 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center gap-1.5 shrink-0 cursor-pointer shadow-xs"
                                    >
                                        <template x-if="!isApplyingPromo">
                                            <span>Apply Code</span>
                                        </template>
                                        <template x-if="isApplyingPromo">
                                            <span class="flex items-center gap-1.5">
                                                <svg class="spinner h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <span>Applying…</span>
                                            </span>
                                        </template>
                                    </button>
                                </div>

                                <template x-if="promoError">
                                    <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-xs text-red-600 flex items-start justify-between gap-2">
                                        <div class="flex items-start gap-2">
                                            <i class="fa-solid fa-circle-exclamation text-xs mt-0.5 text-red-500 shrink-0"></i>
                                            <span x-text="promoError"></span>
                                        </div>
                                        <button type="button" @click="promoError = ''" class="text-red-400 hover:text-red-700">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>

                            {{-- Applied State: Badge with details & Remove button --}}
                            <div x-show="appliedPromoCode" class="space-y-2">
                                <div class="rounded-xl border border-emerald-200 bg-emerald-50/80 p-3.5 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs shrink-0 font-bold shadow-xs">
                                            <i class="fa-solid fa-check"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-black text-emerald-950 font-mono tracking-wider" x-text="appliedPromoCode"></span>
                                                <span class="text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-full border border-emerald-200" x-text="appliedDiscountLabel"></span>
                                            </div>
                                            <p class="text-xs text-emerald-700 leading-tight mt-0.5 truncate" x-text="promoMessage"></p>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        @click="removePromoCode()"
                                        title="Remove promo code"
                                        class="text-xs text-slate-400 hover:text-red-600 hover:bg-white p-2 rounded-lg transition shrink-0 cursor-pointer"
                                    >
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- ── Price Breakdown Summary ── --}}
                        <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 border border-slate-200/90 space-y-2.5 text-xs">
                            <div class="flex justify-between text-slate-500 font-medium">
                                <span>Original Course Tuition</span>
                                <span>ZMW <span x-text="Number(originalFeeAmount).toLocaleString('en-ZM', { minimumFractionDigits: 2 })"></span></span>
                            </div>

                            <template x-if="appliedPromoCode && discountAmount > 0">
                                <div class="flex justify-between text-emerald-700 font-bold">
                                    <span class="flex items-center gap-1.5">
                                        <i class="fa-solid fa-tag text-[10px]"></i>
                                        <span>Promo Discount (<span x-text="appliedPromoCode"></span>)</span>
                                    </span>
                                    <span>- ZMW <span x-text="Number(discountAmount).toLocaleString('en-ZM', { minimumFractionDigits: 2 })"></span></span>
                                </div>
                            </template>

                            <div class="pt-3 border-t border-slate-200 flex justify-between items-center text-slate-900 font-black">
                                <span class="text-xs uppercase tracking-wide text-slate-600">Total Balance Due</span>
                                <span :class="isFree ? 'text-emerald-700 font-black text-base' : 'text-teal-950 text-base'">
                                    <span x-show="!isFree">ZMW <span x-text="Number(finalFeeAmount).toLocaleString('en-ZM', { minimumFractionDigits: 2 })"></span></span>
                                    <span x-show="isFree" class="text-xs px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">100% FREE</span>
                                </span>
                            </div>
                        </div>

                        {{-- Navigation Buttons --}}
                        <div class="pt-6 mt-6 border-t border-slate-100 flex items-center justify-between">
                            <button
                                type="button"
                                @click="prevStep()"
                                class="px-5 py-3 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs inline-flex items-center gap-1.5 transition cursor-pointer"
                            >
                                <i class="fa-solid fa-arrow-left text-[10px]"></i>
                                <span x-text="isForKids ? 'Back to Parent Details' : 'Back to Account'"></span>
                            </button>
                            <button
                                type="button"
                                @click="nextStep()"
                                class="px-6 py-3 rounded-xl bg-[#0a2d27] hover:bg-teal-900 text-white font-bold text-xs inline-flex items-center gap-2 shadow-sm transition active:scale-98 cursor-pointer"
                            >
                                <span x-text="isFree ? 'Proceed to Free Confirmation' : 'Continue to Payment'"></span>
                                <i class="fa-solid fa-arrow-right text-[11px]"></i>
                            </button>
                        </div>
                    </div>

                    {{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
                         STEP: PAYMENT METHOD & FINAL CONFIRMATION
                         (Step 4 for Kids, Step 3 for Regular)
                    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
                    <div x-show="currentStep === (isForKids ? 4 : 3)" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6" style="display: none;">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-teal-50 border border-teal-200/60 text-teal-700 flex items-center justify-center text-base shrink-0">
                                    <i class="fa-solid fa-credit-card"></i>
                                </div>
                                <div>
                                    <h2 class="text-base font-black text-[#0a2d27]">Payment &amp; Activation</h2>
                                    <p class="text-xs text-slate-500">Authorize your payment to immediately activate access and issue credentials.</p>
                                </div>
                            </div>
                            <span class="hidden sm:inline-flex text-[11px] font-bold text-teal-800 bg-teal-50 px-3 py-1 rounded-full border border-teal-200">
                                <span x-text="'Step ' + (isForKids ? '4 of 4' : '3 of 3')"></span>
                            </span>
                        </div>

                        {{-- 100% Free Promo Code Applied View --}}
                        <template x-if="isFree">
                            <div class="py-8 px-6 text-center rounded-2xl bg-gradient-to-b from-teal-50/80 to-emerald-50/60 border border-teal-100/90 my-2">
                                <div class="w-16 h-16 rounded-2xl bg-teal-800 text-teal-100 flex items-center justify-center mx-auto mb-4 shadow-sm">
                                    <i class="fa-solid fa-gift text-2xl"></i>
                                </div>
                                <h4 class="font-black text-[#0a2d27] text-lg">100% Free Enrollment!</h4>
                                <p class="text-xs text-slate-600 max-w-sm mx-auto mt-1.5 leading-relaxed">
                                    Promo code <strong class="text-teal-900 font-mono font-bold" x-text="appliedPromoCode"></strong> covers this course tuition in full.
                                </p>
                                <div class="mt-4 py-2 px-3.5 rounded-xl bg-white/90 border border-emerald-100 text-xs text-emerald-800 font-semibold inline-flex items-center gap-2 shadow-2xs">
                                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                    <span>No Mobile Money or Card payment required</span>
                                </div>
                            </div>
                        </template>

                        {{-- Standard Payment Methods Tabs & Inputs (hidden if 100% free) --}}
                        <div x-show="!isFree" class="space-y-4">
                            {{-- Quick Total Notice --}}
                            <div class="flex items-center justify-between p-3.5 rounded-xl bg-teal-50/60 border border-teal-100">
                                <span class="text-xs text-teal-900 font-bold">Total Amount to Pay:</span>
                                <span class="text-sm font-black text-teal-950">
                                    ZMW <span x-text="Number(finalFeeAmount).toLocaleString('en-ZM', { minimumFractionDigits: 2 })"></span>
                                </span>
                            </div>

                            {{-- Payment Tabs --}}
                            <div class="tab-bar">
                                <button type="button" @click="setTab('mobile')" class="tab-item" :class="tab === 'mobile' && 'active'">
                                    <i class="fa-solid fa-mobile-screen-button mb-1 text-[13px]"></i><br>
                                    Mobile Money (Airtel, MTN, Zamtel)
                                </button>
                                <button type="button" @click="setTab('card')" class="tab-item" :class="tab === 'card' && 'active'">
                                    <i class="fa-solid fa-credit-card mb-1 text-[13px]"></i><br>
                                    Debit / Credit Card
                                </button>
                            </div>

                            {{-- Mobile Money Tab --}}
                            <div x-show="tab === 'mobile'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                                <div class="py-1 space-y-2">
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

                                <div class="space-y-3">
                                    <div>
                                        <label class="form-label">Card Number</label>
                                        <input type="text" name="card_number" x-model="cardNumber" placeholder="0000 0000 0000 0000" maxlength="19"
                                               class="form-input font-mono" @input="formatCardInput($event)">
                                    </div>
                                    <div>
                                        <label class="form-label">Cardholder Name</label>
                                        <input type="text" name="card_holder" x-model="cardHolder" placeholder="Full name on card" class="form-input">
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
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

                        {{-- Final Pay CTA & Back Button --}}
                        <div class="pt-6 mt-6 border-t border-slate-100 space-y-3">
                            <div class="flex flex-col sm:flex-row items-center gap-3">
                                <button
                                    type="button"
                                    @click="prevStep()"
                                    class="w-full sm:w-auto px-5 py-3.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs inline-flex items-center justify-center gap-1.5 transition cursor-pointer"
                                >
                                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                                    <span>Back to Review</span>
                                </button>

                                <button
                                    type="submit"
                                    :disabled="isProcessing"
                                    class="pay-btn flex-1 flex items-center justify-center gap-2"
                                    :class="isFree ? 'bg-teal-700 hover:bg-teal-800' : ''"
                                >
                                    <template x-if="!isProcessing">
                                        <span>
                                            <span x-show="!isFree">Pay ZMW <span x-text="Number(finalFeeAmount).toLocaleString('en-ZM', { minimumFractionDigits: 2 })"></span></span>
                                            <span x-show="isFree" class="flex items-center gap-2">
                                                <i class="fa-solid fa-graduation-cap"></i>
                                                <span>Complete Free Enrollment</span>
                                            </span>
                                        </span>
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
                            </div>

                            <p class="text-center text-[11px] text-slate-400 flex items-center justify-center gap-1.5 pt-1">
                                <i class="fa-solid fa-shield-halved text-teal-600"></i>
                                <span>256-bit encryption &bull; Processed securely via Lenco</span>
                            </p>
                        </div>
                    </div>

                </form>
            </div>

            {{-- Course details footer link --}}
            <div class="mt-5 text-center">
                <a href="{{ route('landing.courses.show', ['course' => $course->id, 'slug' => \Illuminate\Support\Str::slug($course->title ?: $course->code)]) }}"
                   class="text-xs text-slate-400 hover:text-slate-600 transition inline-flex items-center gap-1.5 font-medium">
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
                        A payment prompt of <strong class="text-[#0a2d27]">ZMW <span x-text="Number(finalFeeAmount).toLocaleString('en-ZM', { minimumFractionDigits: 2 })"></span></strong> was sent to <strong class="text-teal-600" x-text="'+260 ' + phoneNumber"></strong>.
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
                    <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center mx-auto mb-4 border border-emerald-100">
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
                currentStep: {{ $initialStep }},
                tab: 'mobile',
                track: '{{ $selectedLevel }}',
                mode: '{{ $selectedMode }}',
                originalFeeAmount: {{ (float) $feeAmount }},
                options: @js($feeOptions),
                showOptionSwitcher: false,
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

                // Promo code state
                promoInput: '{{ $initialPromoCode ?? "" }}',
                appliedPromoCode: '',
                appliedDiscountLabel: '',
                promoMessage: '',
                promoError: '',
                discountAmount: 0,
                isApplyingPromo: false,

                isForKids: {{ $isForKids ? 'true' : 'false' }},
                existingKidId: {{ $existingKid ? $existingKid->id : 'null' }},
                kid: {
                    name: '{{ $existingKid ? addslashes($existingKid->name) : addslashes(old('kid_name', '')) }}',
                    age: '{{ $existingKid ? $existingKid->age : addslashes(old('kid_age', '')) }}',
                    school: '{{ $existingKid ? addslashes($existingKid->school) : addslashes(old('kid_school', '')) }}',
                    grade: '{{ $existingKid ? addslashes($existingKid->grade) : addslashes(old('kid_grade', '')) }}',
                    relationship: '{{ addslashes(old('relationship', 'Mother')) }}',
                    password: '{{ $existingKid ? "existing" : "" }}'
                },
                parent: {
                    name: '{{ Auth::check() ? Auth::user()->name : addslashes(old('parent_name', '')) }}',
                    email: '{{ Auth::check() ? Auth::user()->email : addslashes(old('parent_email', '')) }}',
                    password: '',
                    phone: '{{ Auth::check() ? Auth::user()->phone_number : addslashes(old('parent_phone', '')) }}',
                    occupation: '{{ Auth::check() ? Auth::user()->occupation : addslashes(old('parent_occupation', '')) }}',
                    company: '{{ Auth::check() ? Auth::user()->company : addslashes(old('parent_company', '')) }}',
                    address: '{{ Auth::check() ? Auth::user()->address : addslashes(old('parent_address', '')) }}'
                },

                @php
                    $pendingReg = session('pending_registration', []);
                @endphp
                student: {
                    name: '{{ Auth::check() ? Auth::user()->name : addslashes($pendingReg['name'] ?? old('name', '')) }}',
                    email: '{{ Auth::check() ? Auth::user()->email : addslashes($pendingReg['email'] ?? old('email', '')) }}',
                    password: '{{ addslashes($pendingReg['password'] ?? "") }}',
                    password_confirmation: '{{ addslashes($pendingReg['password'] ?? "") }}'
                },

                get totalSteps() {
                    return this.isForKids ? 4 : 3;
                },

                get steps() {
                    if (this.isForKids) {
                        return [
                            { number: 1, title: 'Child Info' },
                            { number: 2, title: 'Parent Account' },
                            { number: 3, title: 'Plan & Discounts' },
                            { number: 4, title: 'Payment' },
                        ];
                    }
                    return [
                        { number: 1, title: 'Student Account' },
                        { number: 2, title: 'Plan & Discounts' },
                        { number: 3, title: 'Payment' },
                    ];
                },

                get feeAmount() {
                    return this.originalFeeAmount;
                },

                get finalFeeAmount() {
                    return Math.max(0, this.originalFeeAmount - this.discountAmount);
                },

                get isFree() {
                    return this.appliedPromoCode !== '' && this.discountAmount > 0 && this.finalFeeAmount <= 0;
                },

                get modeLabel() {
                    if (this.mode === 'one_on_one') return 'One-on-One';
                    if (this.mode === 'self_paced') return 'Self-Paced';
                    return 'Group Class';
                },

                init() {
                    if (this.promoInput && this.promoInput.trim().length > 0) {
                        this.applyPromoCode(this.promoInput);
                    }
                    if (this.isForKids && this.parent.phone && !this.phoneNumber) {
                        this.phoneNumber = this.parent.phone.replace(/^\+?260/, '').replace(/^0/, '');
                    }
                },

                validateCurrentStep() {
                    this.errorMessage = '';

                    if (this.isForKids) {
                        if (this.currentStep === 1) {
                            if (this.existingKidId) {
                                return true;
                            }
                            if (!this.kid.name || !this.kid.name.trim()) {
                                this.errorMessage = "Please enter the child's full name.";
                                return false;
                            }
                            if (!this.kid.age || Number(this.kid.age) < 3 || Number(this.kid.age) > 18) {
                                this.errorMessage = "Please enter a valid age for the child (between 3 and 18).";
                                return false;
                            }
                            if (!this.kid.grade || !this.kid.grade.trim()) {
                                this.errorMessage = "Please enter the child's grade or year.";
                                return false;
                            }
                            if (!this.kid.school || !this.kid.school.trim()) {
                                this.errorMessage = "Please enter the child's school.";
                                return false;
                            }
                            if (!this.kid.relationship || !this.kid.relationship.trim()) {
                                this.errorMessage = "Please specify your relationship to the child.";
                                return false;
                            }
                            if (!this.kid.password || this.kid.password.length < 6) {
                                this.errorMessage = "Please create a password for the child's login (minimum 6 characters).";
                                return false;
                            }
                            return true;
                        }

                        if (this.currentStep === 2) {
                            @guest
                            if (!this.parent.name || !this.parent.name.trim()) {
                                this.errorMessage = "Please enter the parent/guardian full name.";
                                return false;
                            }
                            if (!this.parent.email || !this.parent.email.trim() || !this.parent.email.includes('@')) {
                                this.errorMessage = "Please enter a valid parent email address.";
                                return false;
                            }
                            if (!this.parent.password || this.parent.password.length < 6) {
                                this.errorMessage = "Please create a parent password (minimum 6 characters).";
                                return false;
                            }
                            if (!this.parent.phone || !this.parent.phone.trim()) {
                                this.errorMessage = "Please enter your contact or WhatsApp number.";
                                return false;
                            }
                            if (!this.parent.occupation || !this.parent.occupation.trim()) {
                                this.errorMessage = "Please enter your occupation.";
                                return false;
                            }
                            if (!this.parent.company || !this.parent.company.trim()) {
                                this.errorMessage = "Please enter your employer or organization.";
                                return false;
                            }
                            if (!this.parent.address || !this.parent.address.trim()) {
                                this.errorMessage = "Please enter your home address.";
                                return false;
                            }
                            @endguest
                            return true;
                        }
                    } else {
                        if (this.currentStep === 1) {
                            @guest
                            if (!this.student.name || !this.student.name.trim()) {
                                this.errorMessage = "Please enter your full name.";
                                return false;
                            }
                            if (!this.student.email || !this.student.email.trim() || !this.student.email.includes('@')) {
                                this.errorMessage = "Please enter a valid email address.";
                                return false;
                            }
                            if (!this.student.password || this.student.password.length < 6) {
                                this.errorMessage = "Please create a password (minimum 6 characters).";
                                return false;
                            }
                            if (this.student.password !== this.student.password_confirmation) {
                                this.errorMessage = "Passwords do not match.";
                                return false;
                            }
                            @endguest
                            return true;
                        }
                    }

                    return true;
                },

                nextStep() {
                    if (!this.validateCurrentStep()) {
                        window.scrollTo({ top: 100, behavior: 'smooth' });
                        return;
                    }

                    if (this.isForKids && this.existingKidId && this.currentStep === 1 && {{ Auth::check() ? 'true' : 'false' }}) {
                        this.currentStep = 3;
                        window.scrollTo({ top: 100, behavior: 'smooth' });
                        return;
                    }

                    if (this.isForKids && this.parent.phone && !this.phoneNumber) {
                        this.phoneNumber = this.parent.phone.replace(/^\+?260/, '').replace(/^0/, '');
                    }

                    this.errorMessage = '';
                    if (this.currentStep < this.totalSteps) {
                        this.currentStep++;
                        window.scrollTo({ top: 100, behavior: 'smooth' });
                    }
                },

                prevStep() {
                    this.errorMessage = '';
                    if (this.isForKids && this.existingKidId && this.currentStep === 3 && {{ Auth::check() ? 'true' : 'false' }}) {
                        this.currentStep = 1;
                        window.scrollTo({ top: 100, behavior: 'smooth' });
                        return;
                    }
                    if (this.currentStep > 1) {
                        this.currentStep--;
                        window.scrollTo({ top: 100, behavior: 'smooth' });
                    }
                },

                goToStep(step) {
                    if (step === this.currentStep) return;
                    if (step < this.currentStep) {
                        this.errorMessage = '';
                        this.currentStep = step;
                        window.scrollTo({ top: 100, behavior: 'smooth' });
                        return;
                    }

                    if (!this.validateCurrentStep()) {
                        window.scrollTo({ top: 100, behavior: 'smooth' });
                        return;
                    }

                    this.errorMessage = '';
                    this.currentStep = step;
                    window.scrollTo({ top: 100, behavior: 'smooth' });
                },

                selectCourseOption(opt) {
                    this.track = opt.level;
                    this.mode = opt.category || opt.mode || 'group';
                    this.originalFeeAmount = Number(opt.amount);
                    this.showOptionSwitcher = false;
                    if (this.appliedPromoCode) {
                        this.applyPromoCode(this.appliedPromoCode);
                    }
                },

                async applyPromoCode(codeToApply) {
                    const code = (codeToApply || this.promoInput || '').trim();
                    if (!code) {
                        this.promoError = 'Please enter a promo code.';
                        return;
                    }

                    this.isApplyingPromo = true;
                    this.promoError = '';

                    try {
                        const token = document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}';
                        const res = await fetch('{{ route("checkout.apply-promo", $course) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify({
                                code: code,
                                track: this.track,
                                mode: this.mode,
                                email: this.isForKids ? (this.parent?.email || '{{ Auth::user()?->email ?? "" }}') : (this.student?.email || '{{ Auth::user()?->email ?? "" }}')
                            })
                        });

                        const data = await res.json();
                        this.isApplyingPromo = false;

                        if (res.ok && data.success) {
                            this.appliedPromoCode = data.code;
                            this.discountAmount = Number(data.discount_amount);
                            this.appliedDiscountLabel = data.formatted_discount;
                            this.promoMessage = data.message;
                            this.promoError = '';
                            this.promoInput = data.code;
                        } else {
                            this.promoError = data.message || 'Promo code could not be applied.';
                        }
                    } catch (e) {
                        this.isApplyingPromo = false;
                        this.promoError = 'Unable to verify promo code right now. Please try again.';
                    }
                },

                removePromoCode() {
                    this.appliedPromoCode = '';
                    this.discountAmount = 0;
                    this.appliedDiscountLabel = '';
                    this.promoMessage = '';
                    this.promoError = '';
                    this.promoInput = '';
                },

                setTab(t) {
                    this.tab = t;
                    this.paymentMethod = t === 'mobile' ? 'mobile_money' : 'card';
                    this.errorMessage = '';
                },

                selectProvider(p) {
                    this.provider = p;
                    this.paymentMethod = 'mobile_money';
                    if (!this.phoneNumber && this.isForKids && this.parent.phone) {
                        this.phoneNumber = this.parent.phone.replace(/^\+?260/, '').replace(/^0/, '');
                    }
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
                    if (this.currentStep < this.totalSteps) {
                        this.nextStep();
                        return;
                    }

                    this.errorMessage = '';

                    // Validate payment gateway inputs only if not 100% free
                    if (!this.isFree) {
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
                    }

                    const form = document.getElementById('payment-form');
                    const formData = new FormData(form);

                    this.isProcessing = true;
                    this.processingStep = this.isFree ? 'Activating your enrollment…' : 'Connecting to gateway…';

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
