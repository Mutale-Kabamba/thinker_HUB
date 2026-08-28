{{--
    Interactive Course Checkout Modal (Lenco by BroadPay)
    Embed anywhere with:  @include('partials.checkout-modal')
    Open from JS with:    openCheckoutModal(courseId, courseTitle, feeAmount, track)
--}}

<div id="checkout-modal"
     class="fixed inset-0 z-[200] hidden flex items-center justify-center p-4"
     role="dialog"
     aria-modal="true"
     aria-labelledby="chk-course-title">

    {{-- Backdrop --}}
    <div id="checkout-backdrop"
         class="absolute inset-0 bg-[#0a2d27]/60 backdrop-blur-sm transition-opacity duration-200 opacity-0"></div>

    {{-- Sheet --}}
    <div id="checkout-wrapper" class="relative z-10 w-full flex items-center justify-center pointer-events-none">
        <div id="checkout-sheet"
             class="pointer-events-auto w-full max-w-sm bg-white rounded-3xl border border-slate-200 overflow-hidden transition-all duration-200 translate-y-6 opacity-0">

            {{-- ── Top header bar ── --}}
            <div class="px-5 pt-4 pb-3 flex items-center justify-between border-b border-slate-100 bg-[#f7fdfb]">
                <div class="flex items-center gap-2">
                    <span class="font-black text-[#0a2d27] text-sm tracking-tight">think.er<span class="text-teal-600">HUB</span></span>
                    <span class="text-[10px] text-teal-700 bg-teal-50 border border-teal-100 font-semibold px-2 py-0.5 rounded-full">Secure Checkout</span>
                </div>
                <button type="button" id="chk-close"
                        class="h-7 w-7 rounded-full bg-slate-100 text-slate-400 hover:text-slate-700 hover:bg-slate-200 flex items-center justify-center transition cursor-pointer">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>

            {{-- ── Course Order Summary ── --}}
            <div class="px-5 py-3.5 bg-slate-50/70 border-b border-slate-100 flex items-center justify-between">
                <div class="min-w-0 flex-1 pr-3">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-teal-600">Enrolling into</p>
                    <p id="chk-course-title" class="text-sm font-black text-[#0a2d27] truncate leading-tight mt-0.5">—</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Level: <span id="chk-track-label" class="font-semibold text-slate-600">Beginner</span></p>
                </div>
                <div class="text-right shrink-0">
                    <span id="chk-amount-label" class="inline-block bg-teal-50 text-teal-700 font-black text-xs px-2.5 py-1 rounded-full border border-teal-100">
                        —
                    </span>
                </div>
            </div>

            {{-- Error banner --}}
            <div id="chk-error-banner" class="hidden mx-5 mt-3 rounded-xl border border-red-200 bg-red-50 p-3 text-xs text-red-700 flex items-start gap-2">
                <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5 text-xs shrink-0"></i>
                <span id="chk-error-message" class="flex-1"></span>
            </div>

            {{-- ── Form ── --}}
            <form id="chk-form" method="POST" action="">
                @csrf
                <input type="hidden" name="payment_method" id="chk-payment-method" value="mobile_money">
                <input type="hidden" name="provider"       id="chk-provider"       value="airtel">
                <input type="hidden" name="track"          id="chk-track"          value="Beginner">
                <input type="hidden" name="phone_number"   id="chk-phone"          value="">

                {{-- Guest fields (only rendered for guest users) --}}
                @guest
                <div id="chk-guest-fields" class="px-5 pt-4 pb-1 space-y-2.5">
                    <div>
                        <label class="chk-label">Full Name</label>
                        <input type="text" name="name" id="chk-guest-name" placeholder="Your full name" class="chk-input" required>
                    </div>
                    <div>
                        <label class="chk-label">Email Address</label>
                        <input type="email" name="email" id="chk-guest-email" placeholder="you@email.com" class="chk-input" required>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="chk-label">Password</label>
                            <input type="password" name="password" id="chk-guest-password" placeholder="••••••••" class="chk-input" required>
                        </div>
                        <div>
                            <label class="chk-label">Confirm</label>
                            <input type="password" name="password_confirmation" id="chk-guest-password-confirmation" placeholder="••••••••" class="chk-input" required>
                        </div>
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

                {{-- Tabs: Mobile Money | Card --}}
                <div class="flex border-b border-slate-100" id="chk-tab-bar">
                    <button type="button" data-tab="mobile" class="chk-tab active flex-1">
                        <i class="fa-solid fa-mobile-screen-button mb-1 text-[13px]"></i><br>
                        Mobile Money
                    </button>
                    <button type="button" data-tab="card" class="chk-tab flex-1">
                        <i class="fa-solid fa-credit-card mb-1 text-[13px]"></i><br>
                        Card Payment
                    </button>
                </div>

                {{-- ── MOBILE MONEY tab ── --}}
                {{-- ── MOBILE MONEY tab ── --}}
                <div data-tab-panel="mobile" class="chk-panel px-3 py-2 space-y-1.5">

                    {{-- Airtel --}}
                    <div class="chk-provider-item selected" data-provider="airtel">
                        <div class="chk-provider-row">
                            <img src="{{ asset('images/momo/airtel.png') }}" alt="Airtel Money"
                                 class="h-9 w-9 object-contain rounded-lg border border-slate-100 p-0.5 bg-white chk-provider-logo">
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-[#0a2d27]">Airtel Money</p>
                                <p class="text-[10px] text-slate-400">097 / 077</p>
                            </div>
                            <i class="fa-solid fa-chevron-right text-slate-300 text-xs chk-chevron"></i>
                        </div>
                        <div class="chk-provider-drawer open" data-provider-drawer="airtel">
                            <div class="chk-drawer-content">
                                <label class="chk-label mt-1">Airtel Money Number</label>
                                <div class="flex">
                                    <span class="chk-prefix">+260</span>
                                    <input type="tel" class="chk-input rounded-l-none border-l-0 chk-phone-input"
                                           placeholder="97X XXX XXX" maxlength="10">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- MTN --}}
                    <div class="chk-provider-item" data-provider="mtn">
                        <div class="chk-provider-row">
                            <img src="{{ asset('images/momo/mtn.png') }}" alt="MTN MoMo"
                                 class="h-9 w-9 object-contain rounded-lg border border-slate-100 p-0.5 bg-white chk-provider-logo">
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-[#0a2d27]">MTN MoMo</p>
                                <p class="text-[10px] text-slate-400">096 / 076</p>
                            </div>
                            <i class="fa-solid fa-chevron-right text-slate-300 text-xs chk-chevron"></i>
                        </div>
                        <div class="chk-provider-drawer" data-provider-drawer="mtn">
                            <div class="chk-drawer-content">
                                <label class="chk-label mt-1">MTN MoMo Number</label>
                                <div class="flex">
                                    <span class="chk-prefix">+260</span>
                                    <input type="tel" class="chk-input rounded-l-none border-l-0 chk-phone-input"
                                           placeholder="96X XXX XXX" maxlength="10">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Zamtel --}}
                    <div class="chk-provider-item" data-provider="zamtel">
                        <div class="chk-provider-row">
                            <img src="{{ asset('images/momo/zamtel.png') }}" alt="Zamtel Kwacha"
                                 class="h-9 w-9 object-contain rounded-lg border border-slate-100 p-0.5 bg-white chk-provider-logo">
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-[#0a2d27]">Zamtel Kwacha</p>
                                <p class="text-[10px] text-slate-400">095 / 075</p>
                            </div>
                            <i class="fa-solid fa-chevron-right text-slate-300 text-xs chk-chevron"></i>
                        </div>
                        <div class="chk-provider-drawer" data-provider-drawer="zamtel">
                            <div class="chk-drawer-content">
                                <label class="chk-label mt-1">Zamtel Kwacha Number</label>
                                <div class="flex">
                                    <span class="chk-prefix">+260</span>
                                    <input type="tel" class="chk-input rounded-l-none border-l-0 chk-phone-input"
                                           placeholder="95X XXX XXX" maxlength="10">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ── CARD tab ── --}}
                <div data-tab-panel="card" class="chk-panel hidden px-5 py-4 space-y-3">
                    <div class="flex items-center gap-2 mb-2">
                        <img src="{{ asset('images/momo/visa.png') }}" alt="Visa" class="h-5 object-contain">
                        <img src="{{ asset('images/momo/Mastercard-logo.svg') }}" alt="Mastercard" class="h-5 object-contain">
                        <span class="text-[10px] text-slate-400 ml-1">Visa &bull; Mastercard</span>
                    </div>
                    <div>
                        <label class="chk-label">Card Number</label>
                        <input type="text" name="card_number" id="chk-card-number"
                               placeholder="0000 0000 0000 0000" maxlength="19" class="chk-input font-mono">
                    </div>
                    <div>
                        <label class="chk-label">Cardholder Name</label>
                        <input type="text" name="card_holder" id="chk-card-holder" placeholder="Name on card" class="chk-input">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="chk-label">Expiry</label>
                            <input type="text" name="card_expiry" id="chk-card-expiry" placeholder="MM/YY" maxlength="5" class="chk-input text-center font-mono">
                        </div>
                        <div>
                            <label class="chk-label">CVV</label>
                            <input type="password" name="card_cvv" id="chk-card-cvv" maxlength="4" placeholder="•••" class="chk-input text-center font-mono">
                        </div>
                    </div>
                </div>

                {{-- Pay Button --}}
                <div class="px-5 pb-5 pt-2">
                    <button type="button" id="chk-pay-btn"
                            class="w-full bg-[#0a2d27] text-white font-bold text-sm rounded-xl py-3.5 transition hover:bg-[#0f3d35] active:scale-[.99] cursor-pointer flex items-center justify-center gap-2">
                        <span id="chk-pay-label">Pay <span id="chk-pay-amount">—</span></span>
                        <svg id="chk-spinner" class="hidden h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </button>
                    <p class="text-center text-[10px] text-slate-400 mt-2.5 flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-shield-halved text-teal-500"></i>
                        <span>Encrypted Checkout &bull; BroadPay Gateway</span>
                    </p>
                </div>
            </form>

        </div>
    </div>
</div>

{{-- ── Real-Time USSD Waiting Modal ── --}}
<div id="chk-ussd-modal" class="fixed inset-0 z-[210] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-[#0a2d27]/65 backdrop-blur-sm"></div>
    <div class="relative w-full max-w-xs bg-white rounded-2xl border border-slate-200 p-6 text-center">
        <div id="chk-ussd-waiting-state">
            {{-- Animated Radar Signal & Mobile Orbit Spinner --}}
            <div class="pay-radar-container">
                <div class="pay-radar-ripple"></div>
                <div class="pay-radar-ripple"></div>
                <div class="pay-radar-center">
                    <div class="pay-orbit-ring"></div>
                    <i class="fa-solid fa-mobile-screen-button text-lg pulse-phone"></i>
                </div>
            </div>

            <h3 class="text-base font-black text-[#0a2d27]">Prompt Sent to Phone</h3>
            <p class="text-xs text-slate-500 mt-1">
                Approve <strong id="chk-ussd-amount" class="text-[#0a2d27]">—</strong> on <strong id="chk-ussd-phone" class="text-teal-600">—</strong>
            </p>
            <div class="mt-3 rounded-xl border border-teal-100 bg-teal-50/60 p-3.5 text-center">
                <div class="inline-flex items-center justify-center gap-2 text-teal-900 font-bold text-xs mb-1">
                    <span class="flex gap-1 items-center">
                        <span class="h-1.5 w-1.5 rounded-full bg-teal-600 animate-ping"></span>
                    </span>
                    <span>Awaiting PIN Authorization</span>
                </div>
                <p class="text-[11px] text-slate-500 leading-relaxed">
                    Enter your PIN on your mobile phone. We will automatically activate your enrollment once verified.
                </p>
            </div>
            <div class="mt-4">
                <button type="button" id="chk-ussd-cancel"
                        class="w-full text-xs text-slate-400 hover:text-slate-600 py-1.5 cursor-pointer transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    <span>Cancel &amp; change details</span>
                </button>
            </div>
        </div>

        <div id="chk-ussd-success-state" class="hidden py-4">
            <div class="w-14 h-14 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center mx-auto mb-3 border border-emerald-100">
                <i class="fa-solid fa-check text-2xl font-bold"></i>
            </div>
            <h3 class="text-base font-black text-[#0a2d27]">Payment Approved!</h3>
            <p class="text-xs text-slate-400 mt-1">Redirecting to receipt...</p>
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
        transition: border-color .15s;
        background: #fff;
    }
    .chk-input:focus {
        border-color: #0d9488;
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
    }
    .chk-provider-item {
        border-radius: 12px;
        transition: all 300ms cubic-bezier(0.34, 1.35, 0.7, 1);
        border: 1.5px solid transparent;
        overflow: hidden;
    }
    .chk-provider-item.selected {
        background: #e6f7f5;
        border-color: rgba(13, 148, 136, 0.25);
        transform: scale(1.01);
    }
    .chk-provider-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 9px 10px;
        border-radius: 10px;
        cursor: pointer;
        transition: background 200ms ease, transform 200ms cubic-bezier(0.34, 1.5, 0.64, 1);
        user-select: none;
    }
    .chk-provider-row:hover { background: rgba(240, 250, 248, 0.8); }
    .chk-provider-row:active { transform: scale(0.98); }
    .chk-provider-logo {
        transition: transform 320ms cubic-bezier(0.34, 1.6, 0.64, 1), border-color 240ms ease;
    }
    .chk-provider-item.selected .chk-provider-logo {
        transform: scale(1.08);
        border-color: #0d9488;
    }
    .chk-chevron {
        transition: transform 340ms cubic-bezier(0.34, 1.6, 0.64, 1), color 240ms ease;
    }
    .chk-provider-item.selected .chk-chevron {
        transform: rotate(90deg);
        color: #0d9488;
    }
    .chk-provider-drawer {
        display: grid;
        grid-template-rows: 0fr;
        transition: grid-template-rows 340ms cubic-bezier(0.34, 1.35, 0.7, 1), opacity 240ms ease;
        opacity: 0;
    }
    .chk-provider-drawer.open {
        grid-template-rows: 1fr;
        opacity: 1;
    }
    .chk-drawer-content {
        min-height: 0;
        overflow: hidden;
        padding: 0 10px 10px;
    }

    /* Radar & Orbit Loader */
    .pay-radar-container {
        position: relative;
        width: 80px;
        height: 80px;
        margin: 0 auto 14px;
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
        width: 48px;
        height: 48px;
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
    @keyframes spin { to { transform: rotate(360deg); } }
</style>

<script>
(function () {
    /* ─── state ─── */
    let _courseId   = null;
    let _feeAmount  = null;
    let _amountText = '—';
    let _tab        = 'mobile';
    let _provider   = 'airtel';
    let _processing = false;
    let _pollTimer  = null;
    let _pendingRef = null;

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
    const errBanner = document.getElementById('chk-error-banner');
    const errMsg    = document.getElementById('chk-error-message');

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

        hideError();
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        requestAnimationFrame(() => {
            backdrop.classList.add('opacity-100');
            sheet.classList.remove('translate-y-6', 'opacity-0');
        });
    };

    const closeModal = () => {
        stopPolling();
        backdrop.classList.remove('opacity-100');
        sheet.classList.add('translate-y-6', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }, 220);
    };

    const showError = (msg) => {
        if (errBanner && errMsg) {
            errMsg.textContent = msg;
            errBanner.classList.remove('hidden');
        }
    };

    const hideError = () => {
        if (errBanner) errBanner.classList.add('hidden');
    };

    closeBtn?.addEventListener('click', closeModal);
    backdrop?.addEventListener('click', (e) => {
        if (e.target === backdrop) closeModal();
    });
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
                _tab === 'mobile' ? 'mobile_money' : 'card';
            hideError();
        });
    });

    /* ─── provider rows ─── */
    document.querySelectorAll('.chk-provider-item').forEach(item => {
        item.addEventListener('click', () => {
            const p = item.dataset.provider;

            document.querySelectorAll('.chk-provider-item').forEach(i => i.classList.remove('selected'));
            document.querySelectorAll('.chk-provider-drawer').forEach(d => d.classList.remove('open'));

            _provider = p;
            item.classList.add('selected');
            const targetDrawer = document.querySelector('[data-provider-drawer="' + p + '"]');
            if (targetDrawer) targetDrawer.classList.add('open');
            document.getElementById('chk-provider').value = p;
            document.getElementById('chk-payment-method').value = 'mobile_money';
        });
    });

    /* Sync active phone input */
    document.querySelectorAll('.chk-phone-input').forEach(input => {
        input.addEventListener('input', (e) => {
            document.getElementById('chk-phone').value = e.target.value;
        });
    });

    /* Format card number input */
    document.getElementById('chk-card-number')?.addEventListener('input', (e) => {
        let val = e.target.value.replace(/\D/g, '');
        let formatted = '';
        for (let i = 0; i < val.length; i++) {
            if (i > 0 && i % 4 === 0) formatted += ' ';
            formatted += val[i];
        }
        e.target.value = formatted;
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

    const startPolling = (ref) => {
        stopPolling();
        _pendingRef = ref;
        _pollTimer = setInterval(async () => {
            try {
                const res = await fetch('/payments/status/' + ref, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                if (data.is_completed) {
                    stopPolling();
                    document.getElementById('chk-ussd-waiting-state')?.classList.add('hidden');
                    document.getElementById('chk-ussd-success-state')?.classList.remove('hidden');
                    setTimeout(() => {
                        window.location.href = data.redirect_url || ('/payments/receipt/' + ref);
                    }, 1200);
                } else if (data.status === 'failed') {
                    stopPolling();
                    ussdModal.classList.add('hidden');
                    ussdModal.classList.remove('flex');
                    showError('Payment authorization was declined or timed out.');
                }
            } catch (e) {
                console.error(e);
            }
        }, 3000);
    };

    const stopPolling = () => {
        if (_pollTimer) {
            clearInterval(_pollTimer);
            _pollTimer = null;
        }
    };

    payBtn?.addEventListener('click', async () => {
        if (_processing) return;
        hideError();

        const phone = document.getElementById('chk-phone').value;
        if (_tab === 'mobile' && (!phone || phone.trim().length < 9)) {
            showError('Please enter a valid Mobile Money phone number.');
            return;
        }

        setProcessing(true, 'Connecting to gateway…');

        const formData = new FormData(form);

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await res.json();

            if (!res.ok) {
                setProcessing(false);
                showError(data.message || 'Payment request failed. Please check your details.');
                return;
            }

            if (data.status === 'redirect' && data.redirect_url) {
                window.location.href = data.redirect_url;
                return;
            }

            if (data.status === 'completed' && data.redirect_url) {
                window.location.href = data.redirect_url;
                return;
            }

            if (data.status === 'pending') {
                setProcessing(false);
                document.getElementById('chk-ussd-phone').textContent = '+260 ' + phone;
                document.getElementById('chk-ussd-waiting-state')?.classList.remove('hidden');
                document.getElementById('chk-ussd-success-state')?.classList.add('hidden');
                ussdModal.classList.remove('hidden');
                ussdModal.classList.add('flex');
                startPolling(data.reference);
                return;
            }

            if (data.redirect_url) {
                window.location.href = data.redirect_url;
            }
        } catch (e) {
            setProcessing(false);
            showError('Connection error: could not reach payment gateway.');
        }
    });

    document.getElementById('chk-ussd-cancel')?.addEventListener('click', () => {
        stopPolling();
        ussdModal.classList.add('hidden');
        ussdModal.classList.remove('flex');
    });

    document.getElementById('chk-ussd-check-now')?.addEventListener('click', async () => {
        if (!_pendingRef) return;
        try {
            const res = await fetch('/payments/status/' + _pendingRef, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (data.is_completed) {
                stopPolling();
                document.getElementById('chk-ussd-waiting-state')?.classList.add('hidden');
                document.getElementById('chk-ussd-success-state')?.classList.remove('hidden');
                setTimeout(() => {
                    window.location.href = data.redirect_url || ('/payments/receipt/' + _pendingRef);
                }, 1000);
            } else {
                alert('Payment is still awaiting authorization on your phone.');
            }
        } catch (e) {}
    });

    /* ESC key */
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            if (!ussdModal.classList.contains('hidden')) {
                stopPolling();
                ussdModal.classList.add('hidden');
                ussdModal.classList.remove('flex');
                return;
            }
            closeModal();
        }
    });
})();
</script>
