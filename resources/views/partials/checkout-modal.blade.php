{{--
    Checkout Modal Partial
    Embed anywhere with:  @include('partials.checkout-modal')
    Open it via JS:       window.openCheckoutModal(courseId, courseTitle, feeAmount, trackValue)
--}}

{{-- ─────────────── Overlay ─────────────── --}}
<div id="checkout-modal"
     class="fixed inset-0 z-[200] hidden"
     role="dialog" aria-modal="true" aria-labelledby="chk-modal-title">

    {{-- Backdrop --}}
    <div id="checkout-backdrop"
         class="absolute inset-0 bg-[#0a2d27]/60 backdrop-blur-sm transition-opacity duration-200 opacity-0"></div>

    {{-- Sheet --}}
    <div id="checkout-wrapper" class="absolute inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div id="checkout-sheet"
             class="pointer-events-auto relative w-full max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden
                    translate-y-6 opacity-0 transition-all duration-250"
             style="max-height: 92dvh; overflow-y: auto;">

            {{-- Close button --}}
            <button type="button" id="chk-close"
                    class="absolute top-3 right-3 z-10 h-8 w-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 transition cursor-pointer">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            {{-- Header --}}
            <div class="px-5 pt-5 pb-4 border-b border-slate-100">
                <p class="text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-0.5">Enrollment Payment</p>
                <h2 id="chk-course-title" class="text-base font-black text-[#0a2d27] leading-snug pr-8">Course</h2>
                <div class="flex items-center justify-between mt-1">
                    <p class="text-xs text-slate-400">Level: <span class="font-semibold text-slate-600" id="chk-track-label">—</span></p>
                    <span class="text-xs font-bold text-teal-700 bg-teal-50 border border-teal-100 rounded-full px-3 py-0.5" id="chk-amount-label">ZMW —</span>
                </div>
            </div>

            {{-- Form --}}
            <form method="POST" id="chk-form" action="">
                @csrf
                <input type="hidden" name="payment_method" id="chk-payment-method" value="mobile_money">
                <input type="hidden" name="provider"        id="chk-provider"        value="">
                <input type="hidden" name="track"           id="chk-track"           value="Beginner">
                <input type="hidden" name="phone_number"    id="chk-phone"           value="">

                {{-- Guest fields (hidden when logged in) --}}
                @guest
                <div id="chk-guest-fields" class="px-5 pt-5 pb-1 space-y-3">
                    <div>
                        <label class="chk-label">Full Name</label>
                        <input type="text" name="name" id="chk-guest-name" placeholder="Your full name" class="chk-input" required>
                    </div>
                    <div>
                        <label class="chk-label">Email Address</label>
                        <input type="email" name="email" id="chk-guest-email" placeholder="you@email.com" class="chk-input" required>
                    </div>
                    <div>
                        <label class="chk-label">Password</label>
                        <input type="password" name="password" id="chk-guest-password" placeholder="••••••••" class="chk-input" required>
                        <input type="hidden" name="password_confirmation" id="chk-guest-password-confirmation">
                    </div>
                </div>
                @else
                <div class="px-5 py-3 border-b border-slate-100 flex items-center gap-3">
                    <div class="h-8 w-8 rounded-full bg-teal-800 text-white flex items-center justify-center font-bold text-sm shrink-0">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-[#0a2d27] truncate">{{ Auth::user()->name }}</p>
                        <p class="text-[11px] text-slate-400 truncate">{{ Auth::user()->email }}</p>
                    </div>
                    <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full shrink-0">✓ Signed in</span>
                </div>
                @endguest

                {{-- Tabs: Mobile Money | Card | Demo --}}
                <div class="flex border-b border-slate-100" id="chk-tab-bar">
                    <button type="button" data-tab="mobile" class="chk-tab active flex-1">
                        <i class="fa-solid fa-mobile-screen-button mb-1 text-[14px]"></i><br>
                        Mobile Money
                    </button>
                    <button type="button" data-tab="card" class="chk-tab flex-1">
                        <i class="fa-solid fa-credit-card mb-1 text-[14px]"></i><br>
                        Card
                    </button>
                    <button type="button" data-tab="demo" class="chk-tab flex-1">
                        <i class="fa-solid fa-bolt mb-1 text-[14px]"></i><br>
                        Sandbox
                    </button>
                </div>

                {{-- ── MOBILE MONEY tab ── --}}
                <div data-tab-panel="mobile" class="chk-panel px-3 py-2">

                    {{-- Airtel --}}
                    <div class="chk-provider-row" data-provider="airtel" data-phone="0977264054">
                        <img src="{{ asset('images/momo/airtel.png') }}" alt="Airtel Money"
                             class="h-10 w-10 object-contain rounded-lg border border-slate-100">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-[#0a2d27]">Airtel Money</p>
                            <p class="text-[10px] text-slate-400">097 / 077</p>
                        </div>
                        <i class="fa-solid fa-chevron-right text-slate-300 text-xs chk-chevron transition-transform"></i>
                    </div>
                    <div class="chk-provider-form hidden px-2 pb-3" data-provider-form="airtel">
                        <label class="chk-label mt-2">Mobile Number</label>
                        <div class="flex">
                            <span class="chk-prefix">+260</span>
                            <input type="tel" class="chk-input rounded-l-none border-l-0" id="chk-phone-airtel"
                                   placeholder="977 264 054" oninput="document.getElementById('chk-phone').value=this.value">
                        </div>
                    </div>
                    <div class="chk-divider"></div>

                    {{-- MTN --}}
                    <div class="chk-provider-row" data-provider="mtn" data-phone="0966123456">
                        <img src="{{ asset('images/momo/mtn.png') }}" alt="MTN MoMo"
                             class="h-10 w-10 object-contain rounded-lg border border-slate-100">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-[#0a2d27]">MTN MoMo</p>
                            <p class="text-[10px] text-slate-400">096 / 076</p>
                        </div>
                        <i class="fa-solid fa-chevron-right text-slate-300 text-xs chk-chevron transition-transform"></i>
                    </div>
                    <div class="chk-provider-form hidden px-2 pb-3" data-provider-form="mtn">
                        <label class="chk-label mt-2">Mobile Number</label>
                        <div class="flex">
                            <span class="chk-prefix">+260</span>
                            <input type="tel" class="chk-input rounded-l-none border-l-0" id="chk-phone-mtn"
                                   placeholder="966 123 456" oninput="document.getElementById('chk-phone').value=this.value">
                        </div>
                    </div>
                    <div class="chk-divider"></div>

                    {{-- Zamtel --}}
                    <div class="chk-provider-row" data-provider="zamtel" data-phone="0955987654">
                        <img src="{{ asset('images/momo/zamtel.png') }}" alt="Zamtel Kwacha"
                             class="h-10 w-10 object-contain rounded-lg border border-slate-100">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-[#0a2d27]">Zamtel Kwacha</p>
                            <p class="text-[10px] text-slate-400">095 / 075</p>
                        </div>
                        <i class="fa-solid fa-chevron-right text-slate-300 text-xs chk-chevron transition-transform"></i>
                    </div>
                    <div class="chk-provider-form hidden px-2 pb-3" data-provider-form="zamtel">
                        <label class="chk-label mt-2">Mobile Number</label>
                        <div class="flex">
                            <span class="chk-prefix">+260</span>
                            <input type="tel" class="chk-input rounded-l-none border-l-0" id="chk-phone-zamtel"
                                   placeholder="955 987 654" oninput="document.getElementById('chk-phone').value=this.value">
                        </div>
                    </div>

                </div>

                {{-- ── CARD tab ── --}}
                <div data-tab-panel="card" class="chk-panel hidden px-5 py-4 space-y-3">
                    {{-- Card brand icons --}}
                    <div class="flex items-center gap-2 mb-2">
                        <img src="{{ asset('images/momo/visa.png') }}" alt="Visa" class="h-6 object-contain">
                        <img src="{{ asset('images/momo/Mastercard-logo.svg') }}" alt="Mastercard" class="h-6 object-contain">
                        <span class="text-[10px] text-slate-400 ml-1">Accepted cards</span>
                    </div>
                    <div>
                        <label class="chk-label">Card Number</label>
                        <input type="text" name="card_number" id="chk-card-number"
                               placeholder="4242 4242 4242 4242" maxlength="19" class="chk-input font-mono">
                    </div>
                    <div>
                        <label class="chk-label">Cardholder Name</label>
                        <input type="text" name="card_holder" placeholder="Name on card" class="chk-input">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="chk-label">Expiry</label>
                            <input type="text" placeholder="MM/YY" maxlength="5" class="chk-input text-center">
                        </div>
                        <div>
                            <label class="chk-label">CVV</label>
                            <input type="password" maxlength="4" placeholder="•••" class="chk-input text-center">
                        </div>
                    </div>
                    <button type="button" id="chk-fill-test-card"
                            class="text-[11px] text-teal-600 font-semibold hover:underline cursor-pointer">
                        + Use test card (Visa 4242)
                    </button>
                </div>

                {{-- ── DEMO tab ── --}}
                <div data-tab-panel="demo" class="chk-panel hidden px-5 py-4">
                    <div class="rounded-xl border border-dashed border-teal-200 bg-teal-50/60 p-4">
                        <p class="text-xs font-bold text-teal-800 flex items-center gap-1.5 mb-1">
                            <i class="fa-solid fa-bolt text-amber-500"></i> Sandbox Demo Mode
                        </p>
                        <p class="text-[11px] text-slate-500">Instantly approves enrollment without real payment — for testing only.</p>
                    </div>
                </div>

                {{-- Pay Button --}}
                <div class="px-5 pb-5 pt-3">
                    <button type="button" id="chk-pay-btn"
                            class="w-full bg-[#0a2d27] text-white font-bold text-sm rounded-xl py-3.5 transition hover:bg-[#0f3d35] active:scale-[.99] cursor-pointer flex items-center justify-center gap-2">
                        <span id="chk-pay-label">Pay <span id="chk-pay-amount">—</span></span>
                        <svg id="chk-spinner" class="hidden h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </button>
                    <p class="text-center text-[10px] text-slate-400 mt-2.5 flex items-center justify-center gap-1">
                        <i class="fa-solid fa-shield-halved text-teal-400"></i>
                        Encrypted simulated checkout
                    </p>
                </div>
            </form>

        </div>
    </div>
</div>

{{-- ── USSD Modal ── --}}
<div id="chk-ussd-modal" class="fixed inset-0 z-[210] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-[#0a2d27]/65 backdrop-blur-sm"></div>
    <div class="relative w-full max-w-xs bg-white rounded-2xl shadow-xl p-6 text-center">
        <div class="w-12 h-12 rounded-full bg-teal-50 flex items-center justify-center mx-auto mb-3">
            <i class="fa-solid fa-mobile-screen-button text-teal-600 text-lg"></i>
        </div>
        <h3 class="text-base font-black text-[#0a2d27]">USSD Push Sent</h3>
        <p class="text-xs text-slate-400 mt-1">Approve <strong id="chk-ussd-amount" class="text-[#0a2d27]">—</strong> on <strong id="chk-ussd-phone" class="text-teal-600">—</strong></p>
        <div class="mt-3 rounded-xl border border-dashed border-slate-200 bg-slate-50 p-3 text-left">
            <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Phone screen</p>
            <p class="text-xs text-slate-600 mt-1">"Approve payment to think.er HUB?"</p>
            <div class="flex gap-1.5 mt-2">
                <span class="h-2 w-2 rounded-full bg-teal-500 animate-bounce"></span>
                <span class="h-2 w-2 rounded-full bg-teal-500 animate-bounce [animation-delay:.15s]"></span>
                <span class="h-2 w-2 rounded-full bg-teal-500 animate-bounce [animation-delay:.3s]"></span>
            </div>
        </div>
        <div class="mt-4 space-y-2">
            <button type="button" id="chk-ussd-approve"
                    class="w-full bg-[#0a2d27] text-white font-bold text-sm rounded-xl py-3 hover:bg-[#0f3d35] transition cursor-pointer">
                Approve &amp; Enter PIN ✓
            </button>
            <button type="button" id="chk-ussd-cancel"
                    class="w-full text-xs text-slate-400 hover:text-slate-600 py-2 cursor-pointer transition">Cancel</button>
        </div>
    </div>
</div>

{{-- ── OTP Modal ── --}}
<div id="chk-otp-modal" class="fixed inset-0 z-[210] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-[#0a2d27]/65 backdrop-blur-sm"></div>
    <div class="relative w-full max-w-xs bg-white rounded-2xl shadow-xl p-6 text-center">
        <div class="w-12 h-12 rounded-full bg-teal-50 flex items-center justify-center mx-auto mb-3">
            <i class="fa-solid fa-lock text-teal-600 text-lg"></i>
        </div>
        <h3 class="text-base font-black text-[#0a2d27]">3D Secure — OTP</h3>
        <p class="text-xs text-slate-400 mt-1">Enter the 6-digit code sent to your phone</p>
        <div class="mt-3 flex justify-center gap-1.5">
            <input type="text" maxlength="1" class="chk-otp-box" data-otp="0">
            <input type="text" maxlength="1" class="chk-otp-box" data-otp="1">
            <input type="text" maxlength="1" class="chk-otp-box" data-otp="2">
            <input type="text" maxlength="1" class="chk-otp-box" data-otp="3">
            <input type="text" maxlength="1" class="chk-otp-box" data-otp="4">
            <input type="text" maxlength="1" class="chk-otp-box" data-otp="5">
        </div>
        <p class="mt-1.5 text-[10px] text-slate-400">Test OTP: <strong class="text-teal-600">1 2 3 4 5 6</strong></p>
        <div class="mt-4 space-y-2">
            <button type="button" id="chk-otp-approve"
                    class="w-full bg-[#0a2d27] text-white font-bold text-sm rounded-xl py-3 hover:bg-[#0f3d35] transition cursor-pointer">
                Authorise Transaction
            </button>
            <button type="button" id="chk-otp-cancel"
                    class="w-full text-xs text-slate-400 hover:text-slate-600 py-2 cursor-pointer transition">Cancel</button>
        </div>
    </div>
</div>

<style>
    .chk-label {
        display: block;
        font-size: .65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #94a3b8;
        margin-bottom: 5px;
    }
    .chk-input {
        width: 100%;
        border: 1.5px solid #d4eae7;
        border-radius: 10px;
        padding: 9px 12px;
        font-size: .8rem;
        color: #0a2d27;
        outline: none;
        transition: border-color .15s, box-shadow .15s;
        background: #fff;
    }
    .chk-input:focus {
        border-color: #0d9488;
        box-shadow: 0 0 0 3px rgba(13,148,136,.10);
    }
    .chk-prefix {
        border: 1.5px solid #d4eae7;
        border-right: none;
        border-radius: 10px 0 0 10px;
        padding: 9px 10px;
        font-size: .75rem;
        font-weight: 700;
        color: #94a3b8;
        background: #f0faf8;
        white-space: nowrap;
    }
    .chk-tab {
        padding: 11px 0 9px;
        font-size: .72rem;
        font-weight: 600;
        color: #94a3b8;
        border-bottom: 2.5px solid transparent;
        margin-bottom: -1px;
        cursor: pointer;
        transition: all .15s;
        background: none;
        border-top: none;
        border-left: none;
        border-right: none;
    }
    .chk-tab.active { color: #0a2d27; border-bottom-color: #0d9488; }
    .chk-provider-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 8px;
        border-radius: 12px;
        cursor: pointer;
        transition: background .14s;
    }
    .chk-provider-row:hover { background: #f0faf8; }
    .chk-provider-row.selected { background: #e6f7f5; }
    .chk-provider-row.selected .chk-chevron { transform: rotate(90deg); color: #0d9488; }
    .chk-divider { height: 1px; background: #f0faf8; margin: 0 8px; }
    .chk-otp-box {
        width: 38px; height: 44px;
        border: 1.5px solid #d4eae7;
        border-radius: 9px;
        text-align: center;
        font-size: 1.1rem;
        font-weight: 700;
        color: #0a2d27;
        outline: none;
        transition: border-color .15s, box-shadow .15s;
    }
    .chk-otp-box:focus { border-color: #0d9488; box-shadow: 0 0 0 3px rgba(13,148,136,.10); }
</style>

<script>
(function () {
    /* ─── state ─── */
    let _courseId   = null;
    let _feeAmount  = null;
    let _amountText = '—';
    let _tab        = 'mobile';
    let _provider   = null;
    let _processing = false;

    /* ─── elements ─── */
    const modal     = document.getElementById('checkout-modal');
    const backdrop  = document.getElementById('checkout-backdrop');
    const sheet     = document.getElementById('checkout-sheet');
    const closeBtn  = document.getElementById('chk-close');
    const form      = document.getElementById('chk-form');
    const payBtn    = document.getElementById('chk-pay-btn');
    const spinner   = document.getElementById('chk-spinner');
    const payLabel  = document.getElementById('chk-pay-label');
    const payAmount = document.getElementById('chk-pay-amount');
    const ussdModal = document.getElementById('chk-ussd-modal');
    const otpModal  = document.getElementById('chk-otp-modal');

    /* ─── open / close ─── */
    window.openCheckoutModal = function (courseId, courseTitle, feeAmount, track) {
        _courseId  = courseId;
        _feeAmount = feeAmount;
        _amountText = 'ZMW ' + Number(feeAmount).toLocaleString('en-ZM', { minimumFractionDigits: 2 });

        document.getElementById('chk-course-title').textContent = courseTitle;
        document.getElementById('chk-track-label').textContent  = track || 'Beginner';
        document.getElementById('chk-amount-label').textContent = _amountText;
        document.getElementById('chk-track').value              = track || 'Beginner';
        if (payAmount) payAmount.textContent = _amountText;

        /* Route action */
        form.action = '/courses/' + courseId + '/pay';
        document.getElementById('chk-ussd-amount').textContent = _amountText;

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        requestAnimationFrame(() => {
            backdrop.classList.add('opacity-100');
            sheet.classList.remove('translate-y-6', 'opacity-0');
        });
    };

    const closeModal = () => {
        backdrop.classList.remove('opacity-100');
        sheet.classList.add('translate-y-6', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }, 220);
    };

    closeBtn?.addEventListener('click', closeModal);
    backdrop?.addEventListener('click', (e) => {
        if (e.target === backdrop) closeModal();
    });
    
    // Also catch clicks on the wrapper (if the user clicks outside the sheet)
    document.getElementById('checkout-wrapper')?.addEventListener('click', (e) => {
        if (e.target.id === 'checkout-wrapper') closeModal();
    });

    /* ─── tabs ─── */
    document.querySelectorAll('.chk-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            _tab = tab.dataset.tab;
            document.querySelectorAll('.chk-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.chk-panel').forEach(p => p.classList.add('hidden'));
            tab.classList.add('active');
            document.querySelector('[data-tab-panel="' + _tab + '"]')?.classList.remove('hidden');

            document.getElementById('chk-payment-method').value =
                _tab === 'mobile' ? 'mobile_money' : _tab === 'card' ? 'card' : 'demo';

            if (_tab !== 'mobile') { _provider = null; document.getElementById('chk-provider').value = ''; }
        });
    });

    /* ─── provider rows ─── */
    document.querySelectorAll('.chk-provider-row').forEach(row => {
        row.addEventListener('click', () => {
            const p = row.dataset.provider;

            if (_provider === p) {
                /* collapse */
                _provider = null;
                row.classList.remove('selected');
                document.querySelector('[data-provider-form="' + p + '"]')?.classList.add('hidden');
                document.getElementById('chk-provider').value = '';
                return;
            }

            /* collapse all first */
            document.querySelectorAll('.chk-provider-row').forEach(r => r.classList.remove('selected'));
            document.querySelectorAll('.chk-provider-form').forEach(f => f.classList.add('hidden'));

            _provider = p;
            row.classList.add('selected');
            document.querySelector('[data-provider-form="' + p + '"]')?.classList.remove('hidden');
            document.getElementById('chk-provider').value = p;
            document.getElementById('chk-payment-method').value = 'mobile_money';

            /* pre-fill phone from data-phone */
            const phone = row.dataset.phone || '';
            document.getElementById('chk-phone').value = phone;
            const phoneInput = document.getElementById('chk-phone-' + p);
            if (phoneInput) phoneInput.value = phone;
        });
    });

    /* ─── test card fill ─── */
    document.getElementById('chk-fill-test-card')?.addEventListener('click', () => {
        const num = document.getElementById('chk-card-number');
        if (num) { num.value = '4242 4242 4242 4242'; num.dispatchEvent(new Event('input')); }
    });

    /* ─── OTP keyboard nav ─── */
    document.querySelectorAll('.chk-otp-box').forEach((box, idx, all) => {
        box.addEventListener('input', () => { if (box.value && idx < all.length - 1) all[idx+1].focus(); });
        box.addEventListener('keydown', e => { if (e.key === 'Backspace' && !box.value && idx > 0) all[idx-1].focus(); });
    });

    /* ─── submit ─── */
    const setProcessing = (v, label) => {
        _processing = v;
        payBtn.disabled = v;
        spinner.classList.toggle('hidden', !v);
        if (payLabel) payLabel.innerHTML = v
            ? '<span>' + (label || 'Processing…') + '</span>'
            : 'Pay <span id="chk-pay-amount">' + _amountText + '</span>';
    };

    const dispatchForm = () => {
        _processing = true;
        form.submit();
    };

    payBtn?.addEventListener('click', () => {
        if (_processing) return;

        if (_tab === 'mobile') {
            setProcessing(true, 'Sending USSD prompt…');
            document.getElementById('chk-ussd-phone').textContent = '+260 ' + (document.getElementById('chk-phone').value || '—');
            setTimeout(() => {
                ussdModal.classList.remove('hidden');
                ussdModal.classList.add('flex');
            }, 600);
            return;
        }

        if (_tab === 'card') {
            setProcessing(true, 'Connecting to 3D-Secure…');
            setTimeout(() => {
                otpModal.classList.remove('hidden');
                otpModal.classList.add('flex');
            }, 600);
            return;
        }

        dispatchForm();
    });

    /* USSD modal */
    document.getElementById('chk-ussd-approve')?.addEventListener('click', () => {
        ussdModal.classList.add('hidden'); ussdModal.classList.remove('flex');
        setProcessing(true, 'Confirming payment…');
        dispatchForm();
    });
    document.getElementById('chk-ussd-cancel')?.addEventListener('click', () => {
        ussdModal.classList.add('hidden'); ussdModal.classList.remove('flex');
        setProcessing(false);
    });

    /* OTP modal */
    document.getElementById('chk-otp-approve')?.addEventListener('click', () => {
        otpModal.classList.add('hidden'); otpModal.classList.remove('flex');
        setProcessing(true, 'Verifying OTP…');
        dispatchForm();
    });
    document.getElementById('chk-otp-cancel')?.addEventListener('click', () => {
        otpModal.classList.add('hidden'); otpModal.classList.remove('flex');
        setProcessing(false);
    });

    /* ESC key */
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            if (!otpModal.classList.contains('hidden'))  { otpModal.classList.add('hidden'); otpModal.classList.remove('flex'); setProcessing(false); return; }
            if (!ussdModal.classList.contains('hidden')) { ussdModal.classList.add('hidden'); ussdModal.classList.remove('flex'); setProcessing(false); return; }
            closeModal();
        }
    });
})();
</script>
