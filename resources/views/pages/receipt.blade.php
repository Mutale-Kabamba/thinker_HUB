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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.pwa-register')
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .receipt-box { box-shadow: none !important; border: 1px solid #cbd5e1 !important; }
        }
    </style>
</head>
<body class="bg-[#f8fcf9] text-slate-900 font-sans antialiased min-h-screen flex flex-col justify-between py-8">

    <div class="mx-auto w-full max-w-2xl px-4 sm:px-6">
        
        <!-- Top Action Bar -->
        <div class="no-print mb-6 flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-teal-700 hover:text-teal-900 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Go to Dashboard
            </a>
            
            <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-sm hover:bg-slate-50 transition cursor-pointer">
                <i class="fa-solid fa-print text-teal-600"></i>
                Print / Save Receipt
            </button>
        </div>

        <!-- Success Alert -->
        @if (session('payment_success'))
            <div class="no-print mb-6 rounded-2xl border border-emerald-300 bg-emerald-50 p-4 text-sm text-emerald-800 flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                <div>
                    <p class="font-bold">Payment Verified &amp; Account Active!</p>
                    <p class="text-xs text-emerald-700">A receipt confirmation has been emailed to <strong>{{ $student->email }}</strong>.</p>
                </div>
            </div>
        @endif

        <!-- Printable Receipt Box -->
        <div class="receipt-box rounded-3xl border border-slate-200 bg-white p-8 sm:p-10 shadow-sm relative overflow-hidden">
            
            <!-- Watermark Verified Stamp -->
            <div class="absolute -right-8 -top-8 pointer-events-none opacity-10">
                <i class="fa-solid fa-stamp text-9xl text-teal-900"></i>
            </div>

            <!-- Receipt Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-8 border-b border-slate-100">
                <div>
                    <a href="{{ route('home') }}" class="inline-block text-2xl font-black text-[#0a2d27] tracking-tight">
                        think.er <span class="text-teal-600">HUB</span>
                    </a>
                    <p class="text-xs text-slate-500 mt-0.5">10A Off Natwange Street, Airport, Livingstone, Zambia</p>
                </div>

                <div class="text-left sm:text-right">
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800 uppercase tracking-wider">
                        <svg class="h-2 w-2 fill-emerald-600" viewBox="0 0 6 6" aria-hidden="true"><circle cx="3" cy="3" r="3" /></svg>
                        PAID &amp; VERIFIED
                    </span>
                    <p class="mt-1 font-mono text-xs text-slate-500 font-semibold">{{ $payment->reference }}</p>
                </div>
            </div>

            <!-- Receipt Metadata Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-6 py-6 border-b border-slate-100 text-xs">
                <div>
                    <p class="font-bold uppercase tracking-wider text-slate-400">Payment Date</p>
                    <p class="mt-1 font-semibold text-slate-800">{{ $payment->paid_at?->format('M j, Y, h:i A') ?? now()->format('M j, Y, h:i A') }}</p>
                </div>

                <div>
                    <p class="font-bold uppercase tracking-wider text-slate-400">Payment Method</p>
                    <p class="mt-1 font-semibold text-slate-800 capitalize">{{ str_replace('_', ' ', $payment->payment_method) }}</p>
                    <p class="text-[10px] text-slate-500">{{ strtoupper($payment->provider ?? 'Gateway') }}</p>
                </div>

                <div>
                    <p class="font-bold uppercase tracking-wider text-slate-400">Enrolled Student</p>
                    <p class="mt-1 font-semibold text-slate-800">{{ $student->name }}</p>
                    <p class="text-[10px] text-slate-500">{{ $student->email }}</p>
                </div>
            </div>

            <!-- Course Line Item -->
            <div class="py-6 border-b border-slate-100">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-xs uppercase tracking-wider text-slate-400 border-b border-slate-100">
                            <th class="pb-3 font-bold">Course Item</th>
                            <th class="pb-3 font-bold text-center">Track</th>
                            <th class="pb-3 font-bold text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr>
                            <td class="py-4">
                                <span class="inline-block px-2 py-0.5 rounded bg-teal-50 text-teal-800 text-[10px] font-bold uppercase mb-1">{{ $course->code }}</span>
                                <p class="font-bold text-slate-900">{{ $course->title }}</p>
                                <p class="text-xs text-slate-500">Full lifetime access to live sessions, coursework &amp; certificate</p>
                            </td>
                            <td class="py-4 text-center text-xs font-semibold text-slate-700">
                                {{ $student->track ?? 'Standard' }}
                            </td>
                            <td class="py-4 text-right font-bold text-slate-900">
                                {{ $payment->formattedAmount() }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Total Amount -->
            <div class="pt-6 flex justify-between items-baseline">
                <div>
                    <p class="text-xs text-slate-500">Authorized by think.er HUB Payment Gateway</p>
                    <p class="text-[10px] text-slate-400">All fees include digital portal access and grading.</p>
                </div>
                <div class="text-right">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Paid</p>
                    <p class="text-3xl font-black text-[#0a2d27]">{{ $payment->formattedAmount() }}</p>
                </div>
            </div>

            <!-- Primary Action Call to Action in Receipt -->
            <div class="no-print mt-8 pt-6 border-t border-slate-100 text-center">
                <a href="{{ route('dashboard') }}" class="inline-flex w-full items-center justify-center rounded-full bg-[#0a2d27] py-4 px-8 text-sm font-bold text-white shadow-lg shadow-[#0a2d27]/20 transition duration-300 hover:bg-[#115e59]">
                    Proceed to Student Dashboard &amp; Start Course &rarr;
                </a>
            </div>

        </div>

    </div>

</body>
</html>
