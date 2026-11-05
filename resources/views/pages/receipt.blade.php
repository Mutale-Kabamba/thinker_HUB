<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo-meta', [
        'title' => 'Payment Receipt ' . $payment->reference . ' | think.er HUB',
        'description' => 'Official course enrollment receipt and transaction voucher.',
        'type' => 'website',
        'indexable' => false,
    ])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.pwa-register')
    <style>
        body {
            background-color: #f1f5f9; /* Soft grayish-blue background */
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .receipt-container {
            max-width: 400px;
            margin: 0 auto;
        }

        .chk-card {
            background-color: #ffffff;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            margin-bottom: 16px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            font-size: 0.85rem;
        }

        .detail-label {
            color: #64748b;
            font-weight: 500;
        }

        .detail-value {
            color: #0a2d27;
            font-weight: 600;
            text-align: right;
        }

        .dotted-line {
            border-top: 1.5px dashed #e2e8f0;
            margin: 16px 0;
        }

        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .chk-card { box-shadow: none !important; border: 1px solid #cbd5e1 !important; margin-bottom: 24px; }
            .receipt-container { max-width: 100%; padding: 20px; }
        }
    </style>
</head>
<body class="public-layout antialiased min-h-screen py-10 px-4 flex flex-col justify-center">

    <div class="receipt-container w-full">

        {{-- Top Success Card --}}
        <div class="chk-card p-8 flex flex-col items-center justify-center text-center">
            <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-500 flex items-center justify-center mb-5 border border-emerald-200">
                <i class="fa-solid fa-check text-2xl font-bold"></i>
            </div>
            <h2 class="text-slate-500 font-medium text-sm mb-3">Payment Success!</h2>
            <h1 class="text-3xl font-black text-[#0a2d27] tracking-tight">{{ $payment->formattedAmount() }}</h1>
        </div>

        {{-- Payment Details Card --}}
        <div class="chk-card overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-50 flex items-center justify-between">
                <h3 class="font-bold text-[#0a2d27] text-[15px]">Payment Details</h3>
                <i class="fa-solid fa-chevron-up text-slate-400 text-xs"></i>
            </div>
            <div class="px-6 pt-5 pb-2">
                <div class="detail-row">
                    <span class="detail-label">Ref Number</span>
                    <span class="detail-value font-mono">{{ $payment->reference }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Status</span>
                    <span class="detail-value text-emerald-600 flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i> PAID &amp; VERIFIED
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Time</span>
                    <span class="detail-value">{{ $payment->paid_at?->format('d-m-Y, H:i:s') ?? now()->format('d-m-Y, H:i:s') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method</span>
                    <span class="detail-value capitalize">{{ str_replace('_', ' ', $payment->payment_method) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Student Name</span>
                    <span class="detail-value">{{ $student->name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Course Item</span>
                    <span class="detail-value">{{ $course->title }} &bull; {{ $course->code }} ({{ $student->track ?? 'Standard' }})</span>
                </div>

                <div class="dotted-line"></div>

                @if($payment->hasDiscount())
                <div class="detail-row">
                    <span class="detail-label">Original Price</span>
                    <span class="detail-value text-slate-400 line-through">{{ $payment->formattedOriginalAmount() }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Promo Discount ({{ $payment->promoCodeText() }})</span>
                    <span class="detail-value text-emerald-600 font-semibold">-{{ $payment->formattedDiscountAmount() }}</span>
                </div>
                <div class="dotted-line"></div>
                @endif

                <div class="detail-row mb-4">
                    <span class="detail-label">Total Payment</span>
                    <span class="detail-value text-base font-bold">{{ $payment->formattedAmount() }}</span>
                </div>
            </div>
        </div>

        @php
            $isKids = (bool) ($payment->metadata['is_kids_course'] ?? ($course->is_for_kids ?? false));
            $kidCode = $payment->metadata['kid_unique_code'] ?? null;
            $kidName = $payment->metadata['kids_data']['kid_name'] ?? null;
            if (!$kidCode && !empty($payment->metadata['kid_id'])) {
                $kidUser = \App\Models\User::find($payment->metadata['kid_id']);
                $kidCode = $kidUser?->unique_code;
                $kidName = $kidUser?->name ?? $kidName;
            }
        @endphp

        @if($isKids && $kidCode)
        {{-- Kid Login Credentials Voucher Card --}}
        <div class="chk-card p-6 bg-gradient-to-br from-teal-900 to-[#0a2d27] text-white border-none shadow-lg">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-teal-500/20 border border-teal-400/30 flex items-center justify-center text-teal-300">
                    <i class="fa-solid fa-key text-lg"></i>
                </div>
                <div>
                    <h3 class="font-black text-sm text-white tracking-wide uppercase">Child's Login Credentials</h3>
                    <p class="text-[11px] text-teal-200/80">Unique student login code for {{ $kidName ?? 'your child' }}</p>
                </div>
            </div>

            <div class="p-3.5 rounded-xl bg-black/25 border border-white/10 text-center my-3">
                <p class="text-[10px] uppercase font-bold text-teal-300 tracking-widest mb-1">Kid's Unique Login Code</p>
                <p class="text-2xl font-mono font-black tracking-widest text-teal-200 select-all">{{ $kidCode }}</p>
            </div>

            <p class="text-[11px] text-teal-100/90 leading-relaxed mb-4">
                Share this unique code with {{ $kidName ?? 'your child' }}. They can log in directly at <strong class="text-white">thinkerhub.ac.zm/login</strong> using this code and the password you set during enrollment.
            </p>

            <a href="{{ \Illuminate\Support\Facades\Route::has('filament.parent.pages.manage-kids') ? route('filament.parent.pages.manage-kids') : url('/parent/manage-kids') }}" class="no-print w-full py-2.5 rounded-lg bg-teal-400 hover:bg-teal-300 text-teal-950 font-bold text-xs transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-users-viewfinder"></i>
                <span>Open Parent Portal &amp; Manage Kids</span>
            </a>
        </div>
        @endif

        {{-- Action Card: Proceed to Dashboard / Parents Portal --}}
        @php
            if ($isKids) {
                $dashboardUrl = \Illuminate\Support\Facades\Route::has('filament.parent.pages.overview')
                    ? route('filament.parent.pages.overview')
                    : (\Illuminate\Support\Facades\Route::has('filament.parent.home') ? route('filament.parent.home') : url('/parent'));
            } else {
                $dashboardUrl = \Illuminate\Support\Facades\Route::has('dashboard')
                    ? route('dashboard')
                    : url('/dashboard');
            }
        @endphp
        <a href="{{ $dashboardUrl }}" class="no-print chk-card p-4 flex items-center justify-between hover:bg-slate-50 transition cursor-pointer group">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-teal-50 text-teal-600 flex items-center justify-center shrink-0 border border-teal-100/50">
                    <i class="fa-solid fa-graduation-cap text-sm"></i>
                </div>
                <div>
                    <h4 class="font-bold text-[#0a2d27] text-sm group-hover:text-teal-700 transition">
                        {{ $isKids ? 'Proceed to Parents Portal' : 'Proceed to Dashboard' }}
                    </h4>
                    <p class="text-[11px] text-slate-400 mt-0.5">
                        {{ $isKids ? 'Track your child\'s learning and activities!' : 'Start your coursework now!' }}
                    </p>
                </div>
            </div>
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 group-hover:text-teal-600 group-hover:bg-teal-50 transition shrink-0">
                <i class="fa-solid fa-chevron-right text-xs"></i>
            </div>
        </a>

        {{-- Action Card: Support --}}
        <a href="mailto:oristudio.01@gmail.com" class="no-print chk-card p-4 flex items-center justify-between hover:bg-slate-50 transition cursor-pointer group mb-8">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-slate-50 text-slate-500 flex items-center justify-center shrink-0 border border-slate-100">
                    <i class="fa-regular fa-circle-question text-sm"></i>
                </div>
                <div>
                    <h4 class="font-bold text-[#0a2d27] text-sm group-hover:text-teal-700 transition">Trouble With Your Payment?</h4>
                    <p class="text-[11px] text-slate-400 mt-0.5">Let us know on help center now!</p>
                </div>
            </div>
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 group-hover:text-teal-600 group-hover:bg-teal-50 transition shrink-0">
                <i class="fa-solid fa-chevron-right text-xs"></i>
            </div>
        </a>

        {{-- Print Button --}}
        <button onclick="window.print()" class="no-print w-full py-3.5 rounded-xl bg-white border border-slate-200 text-[#0a2d27] font-bold text-sm hover:bg-slate-50 transition flex items-center justify-center gap-2 cursor-pointer mb-6">
            <i class="fa-solid fa-download text-slate-400"></i> Get PDF Receipt
        </button>

    </div>

</body>
</html>
