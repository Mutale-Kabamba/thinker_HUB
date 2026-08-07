<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo-meta', [
        'title' => 'Course Enrollment Checkout | ' . $course->title,
        'description' => 'Secure course enrollment and simulated payment gateway for ' . $course->title . ' on think.er HUB.',
        'type' => 'website',
        'indexable' => false,
    ])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.pwa-register')
</head>
<body class="bg-[#f8fcf9] text-slate-900 font-sans antialiased min-h-screen flex flex-col justify-between" x-data="paymentGateway()">

    @include('partials.public-header')

    <main class="py-12 lg:py-16 flex-1">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            
            <!-- Page Header -->
            <div class="mb-8 text-center sm:text-left">
                <a href="{{ route('landing.courses.show', ['course' => $course->id, 'slug' => \Illuminate\Support\Str::slug($course->title ?: $course->code)]) }}" class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-teal-700 hover:text-teal-900 transition mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Course Details
                </a>
                <h1 class="text-3xl font-black text-slate-900 sm:text-4xl">Enrollment &amp; Payment Gateway</h1>
                <p class="mt-2 text-sm text-slate-600">Complete your course enrollment via our secure payment gateway simulator.</p>
            </div>

            <div class="grid gap-8 lg:grid-cols-[1.5fr_1fr] items-start">
                
                <!-- Left: Payment Form & Gateway Simulator -->
                <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
                    
                    <form method="POST" action="{{ route('checkout.process', $course) }}" id="payment-form" @submit.prevent="submitPayment()">
                        @csrf

                        <!-- Step 1: Student Details (if not authenticated) -->
                        @guest
                            <div class="mb-8 pb-8 border-b border-slate-200">
                                <div class="flex items-center justify-between mb-4">
                                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-teal-100 text-teal-800 text-xs font-black">1</span>
                                        Student Information
                                    </h2>
                                    <a href="{{ route('login') }}" class="text-xs font-semibold text-teal-700 hover:underline">Already have an account? Sign In</a>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Full Name</label>
                                        <input type="text" id="name" name="name" x-model="student.name" placeholder="Mutale Kabamba" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-teal-500 focus:outline-none">
                                        @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Email Address (for course access &amp; receipt)</label>
                                        <input type="email" id="email" name="email" x-model="student.email" placeholder="kabamba@example.com" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-teal-500 focus:outline-none">
                                        @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Create Password</label>
                                            <input type="password" id="password" name="password" x-model="student.password" placeholder="••••••••" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-teal-500 focus:outline-none">
                                        </div>
                                        <div>
                                            <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Confirm Password</label>
                                            <input type="password" id="password_confirmation" name="password_confirmation" x-model="student.password_confirmation" placeholder="••••••••" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-teal-500 focus:outline-none">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="mb-6 rounded-2xl bg-teal-50/80 border border-teal-200 p-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-full bg-[#0a2d27] text-white flex items-center justify-center font-bold text-sm">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wider text-teal-800">Enrolling as</p>
                                        <p class="text-sm font-bold text-slate-900">{{ Auth::user()->name }} <span class="text-xs font-normal text-slate-500">({{ Auth::user()->email }})</span></p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center rounded-full bg-teal-200/60 px-3 py-1 text-xs font-bold text-teal-900">
                                    Logged In
                                </span>
                            </div>
                        @endguest

                        <!-- Step 2: Course Level / Track Selection -->
                        <div class="mb-8">
                            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2 mb-3">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-teal-100 text-teal-800 text-xs font-black">@guest 2 @else 1 @endguest</span>
                                Select Learning Level
                            </h2>
                            <div class="grid grid-cols-3 gap-3">
                                <template x-for="lvl in ['Beginner', 'Intermediate', 'Advanced']" :key="lvl">
                                    <button 
                                        type="button" 
                                        @click="track = lvl" 
                                        :class="track === lvl ? 'border-teal-600 bg-teal-50/60 text-teal-950 ring-2 ring-teal-600/20' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300'"
                                        class="rounded-2xl border p-3.5 text-center transition cursor-pointer"
                                    >
                                        <p class="font-bold text-sm" x-text="lvl"></p>
                                        <p class="text-[11px] text-slate-500 mt-0.5" x-text="lvl === 'Beginner' ? 'Foundational' : (lvl === 'Intermediate' ? 'Core Skills' : 'Mastery')"></p>
                                    </button>
                                </template>
                            </div>
                            <input type="hidden" name="track" :value="track">
                        </div>

                        <!-- Step 3: Payment Method Tabs & Simulator -->
                        <div class="mb-8">
                            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2 mb-4">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-teal-100 text-teal-800 text-xs font-black">@guest 3 @else 2 @endguest</span>
                                Choose Payment Method
                            </h2>

                            <!-- Payment Tabs -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-6 p-1 bg-slate-100 rounded-2xl">
                                <button type="button" @click="paymentMethod = 'mobile_money'" :class="paymentMethod === 'mobile_money' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'" class="rounded-xl py-2.5 px-3 text-xs text-center transition cursor-pointer flex items-center justify-center gap-1.5">
                                    <i class="fa-solid fa-mobile-screen-button text-teal-600"></i>
                                    <span>Mobile Money</span>
                                </button>
                                <button type="button" @click="paymentMethod = 'card'" :class="paymentMethod === 'card' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'" class="rounded-xl py-2.5 px-3 text-xs text-center transition cursor-pointer flex items-center justify-center gap-1.5">
                                    <i class="fa-regular fa-credit-card text-teal-600"></i>
                                    <span>Card (Visa/MC)</span>
                                </button>
                                <button type="button" @click="paymentMethod = 'bank_transfer'" :class="paymentMethod === 'bank_transfer' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'" class="rounded-xl py-2.5 px-3 text-xs text-center transition cursor-pointer flex items-center justify-center gap-1.5">
                                    <i class="fa-solid fa-building-columns text-teal-600"></i>
                                    <span>Bank Transfer</span>
                                </button>
                                <button type="button" @click="paymentMethod = 'demo'" :class="paymentMethod === 'demo' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'" class="rounded-xl py-2.5 px-3 text-xs text-center transition cursor-pointer flex items-center justify-center gap-1.5">
                                    <i class="fa-solid fa-bolt text-amber-500"></i>
                                    <span>1-Click Test</span>
                                </button>
                            </div>

                            <input type="hidden" name="payment_method" :value="paymentMethod">
                            <input type="hidden" name="provider" :value="provider">

                            <!-- Tab 1: Mobile Money Simulator -->
                            <div x-show="paymentMethod === 'mobile_money'" x-transition class="space-y-5">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Select Mobile Network</label>
                                    <div class="grid grid-cols-3 gap-3">
                                        <button type="button" @click="provider = 'airtel'; phoneNumber = '0977264054'" :class="provider === 'airtel' ? 'border-red-500 bg-red-50/50 ring-2 ring-red-500/20' : 'border-slate-200 bg-white'" class="rounded-2xl border p-3 flex flex-col items-center justify-center gap-1 transition cursor-pointer">
                                            <span class="inline-block h-3 w-3 rounded-full bg-red-600"></span>
                                            <span class="text-xs font-bold text-slate-900">Airtel Money</span>
                                            <span class="text-[10px] text-slate-500">097 / 077</span>
                                        </button>
                                        <button type="button" @click="provider = 'mtn'; phoneNumber = '0966123456'" :class="provider === 'mtn' ? 'border-amber-400 bg-amber-50/50 ring-2 ring-amber-400/20' : 'border-slate-200 bg-white'" class="rounded-2xl border p-3 flex flex-col items-center justify-center gap-1 transition cursor-pointer">
                                            <span class="inline-block h-3 w-3 rounded-full bg-amber-400"></span>
                                            <span class="text-xs font-bold text-slate-900">MTN MoMo</span>
                                            <span class="text-[10px] text-slate-500">096 / 076</span>
                                        </button>
                                        <button type="button" @click="provider = 'zamtel'; phoneNumber = '0955987654'" :class="provider === 'zamtel' ? 'border-emerald-500 bg-emerald-50/50 ring-2 ring-emerald-500/20' : 'border-slate-200 bg-white'" class="rounded-2xl border p-3 flex flex-col items-center justify-center gap-1 transition cursor-pointer">
                                            <span class="inline-block h-3 w-3 rounded-full bg-emerald-600"></span>
                                            <span class="text-xs font-bold text-slate-900">Zamtel Kwacha</span>
                                            <span class="text-[10px] text-slate-500">095 / 075</span>
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label for="phone_number" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Mobile Money Number</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-bold text-slate-400">+260</span>
                                        <input type="tel" id="phone_number" name="phone_number" x-model="phoneNumber" placeholder="977264054" class="w-full rounded-xl border border-slate-300 pl-16 pr-4 py-3 text-sm focus:border-teal-500 focus:outline-none">
                                    </div>
                                    <p class="mt-1.5 text-xs text-slate-500">A simulated USSD authorization prompt will be triggered for this number.</p>
                                </div>
                            </div>

                            <!-- Tab 2: Credit / Debit Card Simulator -->
                            <div x-show="paymentMethod === 'card'" x-transition class="space-y-4">
                                <!-- Card Preview -->
                                <div class="rounded-2xl bg-gradient-to-tr from-[#0a2d27] to-[#115e59] p-5 text-white shadow-md relative overflow-hidden">
                                    <div class="flex justify-between items-center mb-6">
                                        <span class="text-xs font-bold tracking-widest uppercase text-teal-300">THINKER HUB CARD</span>
                                        <i class="fa-brands fa-cc-visa text-2xl"></i>
                                    </div>
                                    <p class="text-lg tracking-widest font-mono mb-4" x-text="cardNumber ? cardNumber : '•••• •••• •••• 4242'"></p>
                                    <div class="flex justify-between text-xs font-medium">
                                        <div>
                                            <p class="text-[10px] uppercase text-teal-200/80">Cardholder</p>
                                            <p class="font-bold tracking-wide uppercase" x-text="cardHolder ? cardHolder : 'MUTALE KABAMBA'"></p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] uppercase text-teal-200/80">Expires</p>
                                            <p class="font-bold tracking-wide" x-text="cardExpiry ? cardExpiry : '12/28'"></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card Form Inputs -->
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Card Number</label>
                                        <input type="text" name="card_number" x-model="cardNumber" placeholder="4242 4242 4242 4242" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-teal-500 focus:outline-none font-mono">
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Cardholder Name</label>
                                            <input type="text" name="card_holder" x-model="cardHolder" placeholder="Mutale Kabamba" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-teal-500 focus:outline-none">
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Expiry</label>
                                                <input type="text" x-model="cardExpiry" placeholder="12/28" class="w-full rounded-xl border border-slate-300 px-3 py-3 text-sm text-center focus:border-teal-500 focus:outline-none">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">CVV</label>
                                                <input type="password" maxlength="4" placeholder="123" class="w-full rounded-xl border border-slate-300 px-3 py-3 text-sm text-center focus:border-teal-500 focus:outline-none">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex gap-2 pt-1">
                                        <button type="button" @click="fillTestCard('success')" class="text-[11px] font-semibold text-teal-700 bg-teal-50 hover:bg-teal-100 rounded-lg px-2.5 py-1.5 transition">
                                            + Fill Test Card (Visa Success)
                                        </button>
                                        <button type="button" @click="fillTestCard('3ds')" class="text-[11px] font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg px-2.5 py-1.5 transition">
                                            + Test 3DS Secure OTP
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab 3: Bank Transfer Details -->
                            <div x-show="paymentMethod === 'bank_transfer'" x-transition class="space-y-4">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-xs text-slate-700 space-y-2">
                                    <div class="flex justify-between py-1 border-b border-slate-200">
                                        <span class="font-semibold text-slate-500">Bank Name:</span>
                                        <span class="font-bold text-slate-900">Stanbic Bank Zambia / Zanaco</span>
                                    </div>
                                    <div class="flex justify-between py-1 border-b border-slate-200">
                                        <span class="font-semibold text-slate-500">Account Name:</span>
                                        <span class="font-bold text-slate-900">Ori Studio / Thinker HUB</span>
                                    </div>
                                    <div class="flex justify-between py-1 border-b border-slate-200">
                                        <span class="font-semibold text-slate-500">Account Number:</span>
                                        <span class="font-mono font-bold text-slate-900">9130004829104</span>
                                    </div>
                                    <div class="flex justify-between py-1">
                                        <span class="font-semibold text-slate-500">Branch &amp; Code:</span>
                                        <span class="font-bold text-slate-900">Livingstone (Branch 04)</span>
                                    </div>
                                </div>
                                <p class="text-xs text-slate-500">Simulate direct EFT transfer confirmation below to receive instant activation and digital voucher.</p>
                            </div>

                            <!-- Tab 4: 1-Click Sandbox Test -->
                            <div x-show="paymentMethod === 'demo'" x-transition class="rounded-2xl border border-amber-200 bg-amber-50/70 p-4 text-xs text-amber-900 space-y-2">
                                <p class="font-bold text-sm flex items-center gap-1.5 text-amber-950">
                                    <i class="fa-solid fa-bolt text-amber-600"></i>
                                    Instant Sandbox Demo Checkout
                                </p>
                                <p>This one-click simulation approves your enrollment instantly without requiring real payment or test card entries.</p>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2">
                            <button 
                                type="submit" 
                                :disabled="isProcessing"
                                class="w-full rounded-full bg-[#0a2d27] py-4 px-8 text-sm font-bold text-white shadow-lg shadow-[#0a2d27]/20 transition duration-300 hover:bg-[#115e59] focus:outline-none focus:ring-4 focus:ring-teal-500/20 disabled:opacity-50 flex items-center justify-center gap-2 cursor-pointer"
                            >
                                <template x-if="!isProcessing">
                                    <span>Complete Enrollment Payment (ZMW {{ number_format($feeAmount, 2) }}) &rarr;</span>
                                </template>
                                <template x-if="isProcessing">
                                    <span class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span x-text="processingStep">Processing...</span>
                                    </span>
                                </template>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right: Course Order Summary Card -->
                <div class="space-y-6">
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-4">Order Summary</h3>
                        
                        <div class="flex items-start gap-4 pb-6 border-b border-slate-100">
                            <div class="h-12 w-12 rounded-2xl bg-teal-50 border border-teal-200 flex items-center justify-center text-teal-800 font-bold text-sm shrink-0">
                                {{ substr($course->code, 0, 3) }}
                            </div>
                            <div>
                                <span class="inline-block px-2.5 py-0.5 bg-teal-100 text-teal-800 rounded-md text-[11px] font-bold uppercase tracking-wide">
                                    {{ $course->code }}
                                </span>
                                <h4 class="font-bold text-slate-900 mt-1 text-base leading-snug">{{ $course->title }}</h4>
                                <p class="text-xs text-slate-500 mt-0.5">Level: <span class="font-semibold text-slate-700" x-text="track"></span></p>
                            </div>
                        </div>

                        <!-- Pricing Breakdown -->
                        <div class="py-4 space-y-2.5 text-sm border-b border-slate-100">
                            <div class="flex justify-between text-slate-600">
                                <span>Course Tuition Fee</span>
                                <span class="font-semibold text-slate-900">ZMW {{ number_format($feeAmount, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Digital Portal &amp; Materials</span>
                                <span class="font-semibold text-emerald-600">Included (Free)</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Verified Certificate</span>
                                <span class="font-semibold text-emerald-600">Included (Free)</span>
                            </div>
                        </div>

                        <!-- Total Due -->
                        <div class="pt-4 flex justify-between items-baseline">
                            <span class="text-base font-bold text-slate-900">Total Payable</span>
                            <div class="text-right">
                                <span class="text-2xl font-black text-[#0a2d27]">ZMW {{ number_format($feeAmount, 2) }}</span>
                                <p class="text-[11px] text-slate-400">One-time enrollment fee</p>
                            </div>
                        </div>
                    </div>

                    <!-- Security & Trust Badges -->
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 space-y-3">
                        <div class="flex items-center gap-3 text-xs text-slate-600">
                            <i class="fa-solid fa-shield-halved text-teal-600 text-base"></i>
                            <span>256-Bit SSL Encrypted Simulated Checkout</span>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-slate-600">
                            <i class="fa-solid fa-bolt text-amber-500 text-base"></i>
                            <span>Instant Course Access &amp; Dashboard Activation</span>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-slate-600">
                            <i class="fa-solid fa-file-invoice text-teal-600 text-base"></i>
                            <span>Official Printable Payment Receipt &amp; Voucher</span>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <!-- Interactive Mobile Money USSD Push Prompt Simulation Modal -->
    <div 
        x-show="showMomoModal" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
        style="display: none;"
    >
        <div class="w-full max-w-md rounded-3xl bg-white p-8 shadow-2xl text-center border border-slate-100">
            <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-teal-50 text-teal-700 animate-pulse">
                <i class="fa-solid fa-mobile-screen-button text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900">USSD Push Prompt Sent!</h3>
            <p class="mt-2 text-sm text-slate-600">
                A prompt of <strong class="text-slate-900">ZMW {{ number_format($feeAmount, 2) }}</strong> has been sent to <strong class="text-teal-700" x-text="'+260 ' + phoneNumber"></strong>.
            </p>

            <!-- Simulated Phone Screen Box -->
            <div class="mt-5 rounded-2xl border-2 border-dashed border-teal-300 bg-teal-50/50 p-4 text-left">
                <p class="text-[11px] font-bold uppercase tracking-wider text-teal-800">Phone Simulator:</p>
                <p class="mt-1 text-xs text-slate-700">"Authorize payment of ZMW {{ number_format($feeAmount, 2) }} to think.er HUB? Enter PIN:"</p>
                <div class="mt-2 flex items-center justify-center gap-2">
                    <span class="inline-block h-3 w-3 rounded-full bg-slate-800"></span>
                    <span class="inline-block h-3 w-3 rounded-full bg-slate-800"></span>
                    <span class="inline-block h-3 w-3 rounded-full bg-slate-800"></span>
                    <span class="inline-block h-3 w-3 rounded-full bg-slate-800"></span>
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-2">
                <button type="button" @click="confirmMomoSuccess()" class="w-full rounded-xl bg-teal-700 py-3 text-sm font-bold text-white hover:bg-teal-800 transition cursor-pointer">
                    Simulate PIN Authorization &amp; Approve
                </button>
                <button type="button" @click="showMomoModal = false; isProcessing = false" class="text-xs text-slate-500 hover:text-slate-700 py-2">
                    Cancel Simulation
                </button>
            </div>
        </div>
    </div>

    <!-- Interactive 3D Secure OTP Modal for Card Simulation -->
    <div 
        x-show="showOtpModal" 
        x-transition 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
        style="display: none;"
    >
        <div class="w-full max-w-md rounded-3xl bg-white p-8 shadow-2xl text-center border border-slate-100">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-blue-50 text-blue-700">
                <i class="fa-solid fa-lock text-xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900">Verified by Visa / 3DS OTP</h3>
            <p class="mt-2 text-xs text-slate-600">Enter the simulated SMS One-Time Password sent to your phone:</p>

            <div class="mt-4">
                <input type="text" x-model="otpCode" maxlength="6" class="w-48 mx-auto text-center rounded-xl border border-slate-300 py-2.5 text-lg font-mono tracking-widest font-bold focus:border-teal-500 focus:outline-none">
                <p class="mt-2 text-[11px] text-slate-500">Test OTP Code: <strong class="text-teal-700">123456</strong></p>
            </div>

            <div class="mt-6 flex flex-col gap-2">
                <button type="button" @click="confirmOtpSuccess()" class="w-full rounded-xl bg-[#0a2d27] py-3 text-sm font-bold text-white hover:bg-[#115e59] transition cursor-pointer">
                    Authorize Transaction
                </button>
                <button type="button" @click="showOtpModal = false; isProcessing = false" class="text-xs text-slate-500 hover:text-slate-700 py-2">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <footer class="bg-white border-t border-slate-200 py-8 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} think.er HUB &bull; 10A Off Natwange Street, Airport, Livingstone, Zambia</p>
    </footer>

    <script>
        function paymentGateway() {
            return {
                track: 'Beginner',
                paymentMethod: 'mobile_money',
                provider: 'airtel',
                phoneNumber: '0977264054',
                cardNumber: '4242 4242 4242 4242',
                cardHolder: 'Mutale Kabamba',
                cardExpiry: '12/28',
                otpCode: '123456',
                isProcessing: false,
                processingStep: 'Processing...',
                showMomoModal: false,
                showOtpModal: false,
                student: {
                    name: '',
                    email: '',
                    password: '',
                    password_confirmation: ''
                },

                fillTestCard(type) {
                    this.paymentMethod = 'card';
                    this.cardNumber = '4242 4242 4242 4242';
                    this.cardHolder = 'Mutale Kabamba';
                    this.cardExpiry = '12/28';
                },

                submitPayment() {
                    if (this.paymentMethod === 'mobile_money') {
                        this.isProcessing = true;
                        this.processingStep = 'Sending USSD prompt...';
                        setTimeout(() => {
                            this.showMomoModal = true;
                        }, 600);
                        return;
                    }

                    if (this.paymentMethod === 'card' && this.cardNumber.includes('4242')) {
                        this.isProcessing = true;
                        this.processingStep = 'Connecting to 3D-Secure...';
                        setTimeout(() => {
                            this.showOtpModal = true;
                        }, 600);
                        return;
                    }

                    this.dispatchForm();
                },

                confirmMomoSuccess() {
                    this.showMomoModal = false;
                    this.processingStep = 'Confirming Mobile Money payment...';
                    this.dispatchForm();
                },

                confirmOtpSuccess() {
                    this.showOtpModal = false;
                    this.processingStep = 'Verifying 3D-Secure OTP...';
                    this.dispatchForm();
                },

                dispatchForm() {
                    this.isProcessing = true;
                    const form = document.getElementById('payment-form');
                    form.submit();
                }
            }
        }
    </script>
</body>
</html>
