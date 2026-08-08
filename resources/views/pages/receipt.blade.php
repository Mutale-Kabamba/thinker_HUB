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
            box-shadow: 0 4px 20px rgba(10, 45, 39, 0.03);
            border: 1px solid rgba(13, 148, 136, 0.05);
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
<body class="antialiased min-h-screen py-10 px-4 flex flex-col justify-center">

    <div class="receipt-container w-full">

        {{-- Top Success Card --}}
        <div class="chk-card p-8 flex flex-col items-center justify-center text-center">
            <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-500 flex items-center justify-center mb-5 shadow-sm">
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
                    <span class="detail-value">{{ $course->code }} ({{ $student->track ?? 'Standard' }})</span>
                </div>

                <div class="dotted-line"></div>

                <div class="detail-row mb-4">
                    <span class="detail-label">Total Payment</span>
                    <span class="detail-value text-base font-bold">{{ $payment->formattedAmount() }}</span>
                </div>
            </div>
        </div>

        {{-- Action Card: Proceed to Dashboard --}}
        <a href="{{ route('dashboard') }}" class="no-print chk-card p-4 flex items-center justify-between hover:bg-slate-50 transition cursor-pointer group">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-teal-50 text-teal-600 flex items-center justify-center shrink-0 border border-teal-100/50">
                    <i class="fa-solid fa-graduation-cap text-sm"></i>
                </div>
                <div>
                    <h4 class="font-bold text-[#0a2d27] text-sm group-hover:text-teal-700 transition">Proceed to Dashboard</h4>
                    <p class="text-[11px] text-slate-400 mt-0.5">Start your coursework now!</p>
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
        <button onclick="window.print()" class="no-print w-full py-3.5 rounded-xl bg-white border border-slate-200 text-[#0a2d27] font-bold text-sm shadow-sm hover:bg-slate-50 transition flex items-center justify-center gap-2 cursor-pointer mb-6">
            <i class="fa-solid fa-download text-slate-400"></i> Get PDF Receipt
        </button>

    </div>

</body>
</html>
