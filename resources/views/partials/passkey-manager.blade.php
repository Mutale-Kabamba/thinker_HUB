<section class="edtech-card bg-white dark:bg-[#102028] p-6 sm:p-7 rounded-2xl border border-slate-100 dark:border-[#233842] shadow-sm space-y-6 font-sans" x-data="passkeyManager()">
    {{-- Header Ribbon --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-slate-100 dark:border-[#233842]">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-teal-50 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300 border border-teal-200/60 dark:border-teal-800">
                    <x-heroicon-s-shield-check class="w-3.5 h-3.5 text-teal-600 dark:text-teal-400" />
                    <span>Authentication &amp; Security</span>
                </span>
            </div>
            <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-slate-100 flex items-center gap-2 mt-1">
                <x-heroicon-o-finger-print class="w-6 h-6 text-teal-600 dark:text-teal-400 shrink-0" />
                <span>Fingerprint &amp; Biometrics</span>
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium max-w-2xl">
                Sign in instantly using Touch ID, Face ID, Android Fingerprint, or Windows Hello on this device.
            </p>
        </div>

        {{-- Top Primary Action Button --}}
        <div class="shrink-0">
            <button
                type="button"
                @click="registerPasskey()"
                :disabled="loading"
                class="w-full sm:w-auto px-5 py-2.5 rounded-xl text-xs font-black text-white bg-teal-600 hover:bg-teal-700 active:bg-teal-800 shadow-sm transition-all transform hover:-translate-y-0.5 disabled:opacity-50 disabled:transform-none disabled:cursor-not-allowed inline-flex items-center justify-center gap-2"
            >
                <x-heroicon-m-arrow-path class="w-4 h-4 animate-spin" x-show="loading" x-cloak />
                <x-heroicon-m-plus class="w-4 h-4" x-show="!loading" />
                <span x-text="loading ? 'Registering Device...' : '+ Enable Fingerprint / Passkey'">+ Enable Fingerprint / Passkey</span>
            </button>
        </div>
    </div>

    {{-- Feedback Alert Banner --}}
    <div 
        x-show="message" 
        x-cloak 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="p-3.5 rounded-xl text-xs font-bold flex items-center justify-between gap-3"
        :class="isSuccess ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-rose-50 text-rose-800 dark:bg-rose-950/50 dark:text-rose-300 border border-rose-200 dark:border-rose-800'"
    >
        <div class="flex items-center gap-2">
            <x-heroicon-s-check-circle class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" x-show="isSuccess" />
            <x-heroicon-s-exclamation-circle class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0" x-show="!isSuccess" />
            <span x-text="message"></span>
        </div>
        <button type="button" @click="message = ''" class="text-xs opacity-60 hover:opacity-100 font-bold">&times;</button>
    </div>

    {{-- Desktop 2-Column Balanced Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        
        {{-- Left Column: Security Benefits & Supported Platforms (5 Cols on LG) --}}
        <div class="lg:col-span-5 rounded-2xl p-5 bg-slate-50/80 dark:bg-slate-800/40 border border-slate-100 dark:border-[#233842] flex flex-col justify-between space-y-4">
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-teal-100/80 dark:bg-teal-900/50 text-teal-700 dark:text-teal-300 flex items-center justify-center">
                        <x-heroicon-o-lock-closed class="w-4 h-4" />
                    </div>
                    <h3 class="text-xs font-extrabold text-slate-900 dark:text-slate-100 uppercase tracking-wider">
                        Hardware-Backed Security
                    </h3>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-medium">
                    Passkeys replace passwords with cryptographic key pairs stored directly inside your device's Secure Enclave or TPM.
                </p>

                <div class="space-y-2 pt-2">
                    <div class="flex items-start gap-2.5 text-xs text-slate-600 dark:text-slate-300">
                        <x-heroicon-s-check-circle class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" />
                        <div>
                            <strong class="text-slate-800 dark:text-slate-100">1-Touch Instant Sign-in:</strong>
                            <span class="text-slate-500 dark:text-slate-400"> Fast login without waiting for email OTP codes.</span>
                        </div>
                    </div>

                    <div class="flex items-start gap-2.5 text-xs text-slate-600 dark:text-slate-300">
                        <x-heroicon-s-check-circle class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" />
                        <div>
                            <strong class="text-slate-800 dark:text-slate-100">Phishing Resistant:</strong>
                            <span class="text-slate-500 dark:text-slate-400"> Protected against credential leaks and stolen passwords.</span>
                        </div>
                    </div>

                    <div class="flex items-start gap-2.5 text-xs text-slate-600 dark:text-slate-300">
                        <x-heroicon-s-check-circle class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" />
                        <div>
                            <strong class="text-slate-800 dark:text-slate-100">Zero Cloud Storage:</strong>
                            <span class="text-slate-500 dark:text-slate-400"> Biometric scans never leave your local physical device.</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Supported Devices Badges Pill Row --}}
            <div class="pt-3 border-t border-slate-200/60 dark:border-[#233842]">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-2">
                    Supported Systems &amp; Hardware
                </span>
                <div class="flex flex-wrap gap-1.5">
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-[#233842] text-slate-700 dark:text-slate-300">
                        Touch ID / Face ID
                    </span>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-[#233842] text-slate-700 dark:text-slate-300">
                        Windows Hello
                    </span>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-[#233842] text-slate-700 dark:text-slate-300">
                        Android Biometrics
                    </span>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-[#233842] text-slate-700 dark:text-slate-300">
                        YubiKey / FIDO2
                    </span>
                </div>
            </div>
        </div>

        {{-- Right Column: Active Passkeys List or Polished Empty State (7 Cols on LG) --}}
        <div class="lg:col-span-7 flex flex-col justify-center">
            @php
                $userPasskeys = auth()->user()->passkeys()->latest()->get();
            @endphp

            @if ($userPasskeys->isNotEmpty())
                <div class="space-y-3">
                    <div class="flex items-center justify-between px-1">
                        <span class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">
                            Registered Devices ({{ $userPasskeys->count() }})
                        </span>
                        <span class="text-[11px] font-bold text-teal-600 dark:text-teal-400">
                            Active &bull; WebAuthn Ready
                        </span>
                    </div>

                    <div class="space-y-2.5">
                        @foreach ($userPasskeys as $pk)
                            <div 
                                id="passkey-row-{{ $pk->id }}" 
                                class="p-3.5 rounded-xl border border-slate-200/80 dark:border-[#233842] bg-slate-50/50 dark:bg-slate-800/40 hover:bg-slate-100/70 dark:hover:bg-slate-800/70 transition-colors flex items-center justify-between gap-3 flex-wrap"
                            >
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <div class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 border border-teal-100 dark:border-teal-900/60 flex items-center justify-center shrink-0 shadow-2xs">
                                        <x-heroicon-o-finger-print class="w-5 h-5" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-extrabold text-xs text-slate-900 dark:text-slate-100 truncate">
                                            {{ $pk->name }}
                                        </p>
                                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                                            Added {{ $pk->created_at?->format('M d, Y') }}
                                            @if ($pk->last_used_at)
                                                &bull; Last used {{ $pk->last_used_at->diffForHumans() }}
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        Active
                                    </span>
                                    <button
                                        type="button"
                                        @click="deletePasskey({{ $pk->id }})"
                                        class="px-2.5 py-1 rounded-lg text-xs font-bold text-rose-600 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/50 border border-transparent hover:border-rose-200 dark:hover:border-rose-900 transition-colors inline-flex items-center gap-1"
                                        title="Remove this passkey device"
                                    >
                                        <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                        <span>Remove</span>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                {{-- Polished Empty State for Desktop View --}}
                <div class="rounded-2xl border-2 border-dashed border-slate-200 dark:border-[#233842] bg-slate-50/40 dark:bg-slate-900/30 p-8 sm:p-10 text-center flex flex-col items-center justify-center space-y-3">
                    <div class="w-14 h-14 rounded-2xl bg-teal-50 text-teal-600 dark:bg-teal-950/60 dark:text-teal-400 flex items-center justify-center ring-4 ring-teal-500/10 dark:ring-teal-400/10 shadow-2xs border border-teal-100 dark:border-teal-900/60">
                        <x-heroicon-o-finger-print class="w-7 h-7" />
                    </div>
                    <div class="space-y-1 max-w-sm">
                        <h4 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
                            No fingerprint or biometric credentials registered yet.
                        </h4>
                        <p class="text-xs text-slate-400 dark:text-slate-500 font-medium">
                            Click "+ Enable Fingerprint / Passkey" above to allow fast one-touch login.
                        </p>
                    </div>
                    <div class="pt-2">
                        <button
                            type="button"
                            @click="registerPasskey()"
                            :disabled="loading"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-teal-700 dark:text-teal-300 bg-teal-50 hover:bg-teal-100 dark:bg-teal-950/60 dark:hover:bg-teal-900/80 border border-teal-200/80 dark:border-teal-800 transition-all inline-flex items-center gap-1.5 cursor-pointer"
                        >
                            <x-heroicon-m-plus class="w-3.5 h-3.5" />
                            <span>Set Up This Device</span>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>

<script>
    function passkeyManager() {
        function b64ToUint8(str) {
            if (!str) return new Uint8Array(0);
            const pad = '='.repeat((4 - (str.length % 4)) % 4);
            const base64 = (str + pad).replace(/-/g, '+').replace(/_/g, '/');
            const raw = atob(base64);
            const arr = new Uint8Array(raw.length);
            for (let i = 0; i < raw.length; ++i) arr[i] = raw.charCodeAt(i);
            return arr;
        }

        function bufToB64Url(buf) {
            if (!buf) return '';
            const bytes = new Uint8Array(buf);
            let binary = '';
            for (let i = 0; i < bytes.byteLength; i++) {
                binary += String.fromCharCode(bytes[i]);
            }
            return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
        }

        return {
            loading: false,
            message: '',
            isSuccess: false,

            async registerPasskey() {
                if (!window.PublicKeyCredential) {
                    this.message = 'Biometric passkeys are not supported by this browser.';
                    this.isSuccess = false;
                    return;
                }

                const deviceName = prompt('Enter a name for this biometric device (e.g. My Phone, MacBook Touch ID):', 'My Device');
                if (!deviceName) return;

                this.loading = true;
                this.message = '';

                try {
                    if (window.Passkeys && typeof window.Passkeys.register === 'function') {
                        await window.Passkeys.register({ name: deviceName });
                    } else {
                        // Direct WebAuthn registration fallback
                        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                        const optionsRes = await fetch('/user/passkeys/options', {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf
                            }
                        });
                        if (!optionsRes.ok) throw new Error('Could not get registration options.');
                        const { options } = await optionsRes.json();

                        const challenge = b64ToUint8(options.challenge);
                        const userHandle = b64ToUint8(options.user.id);
                        const excludeCredentials = (options.excludeCredentials || []).map(item => ({
                            ...item,
                            id: b64ToUint8(item.id)
                        }));

                        const credential = await navigator.credentials.create({
                            publicKey: {
                                ...options,
                                challenge,
                                user: {
                                    ...options.user,
                                    id: userHandle,
                                },
                                excludeCredentials
                            }
                        });

                        if (!credential) throw new Error('Registration was cancelled.');

                        const saveRes = await fetch('/user/passkeys', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf
                            },
                            body: JSON.stringify({
                                name: deviceName,
                                credential: {
                                    id: credential.id,
                                    rawId: bufToB64Url(credential.rawId),
                                    type: credential.type,
                                    response: {
                                        clientDataJSON: bufToB64Url(credential.response.clientDataJSON),
                                        attestationObject: bufToB64Url(credential.response.attestationObject),
                                        transports: credential.response.getTransports ? credential.response.getTransports() : ['internal', 'hybrid']
                                    },
                                    clientExtensionResults: credential.getClientExtensionResults ? credential.getClientExtensionResults() : {},
                                    authenticatorAttachment: credential.authenticatorAttachment || 'platform'
                                }
                            })
                        });

                        if (!saveRes.ok) {
                            const errData = await saveRes.json();
                            throw new Error(errData.message || 'Failed to save passkey on server.');
                        }
                    }

                    this.message = 'Fingerprint / Biometric passkey successfully registered!';
                    this.isSuccess = true;
                    setTimeout(() => window.location.reload(), 1200);
                } catch (err) {
                    console.error('Passkey register error:', err);
                    this.isSuccess = false;
                    const msg = (err.message || '').toLowerCase();
                    if (err.name === 'NotAllowedError' || msg.includes('cancelled') || msg.includes('canceled')) {
                        this.message = 'Biometric registration was cancelled or timed out.';
                    } else if (err.name === 'InvalidDomainError' || msg.includes('domain')) {
                        this.message = 'Domain security mismatch. Please ensure you are accessing via HTTPS on the official domain.';
                    } else if (msg.includes('credential manager') || msg.includes('lock screen') || msg.includes('screen lock')) {
                        this.message = 'Device security prompt error. Please ensure a secure screen lock (PIN, fingerprint, or pattern) is enabled in your Android settings and try again.';
                    } else if (msg.includes('already registered')) {
                        this.message = 'This biometric credential is already registered on your account.';
                    } else {
                        this.message = err.message || 'Failed to register biometric device.';
                    }
                } finally {
                    this.loading = false;
                }
            },

            async deletePasskey(id) {
                if (!confirm('Are you sure you want to remove this biometric login?')) return;

                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const res = await fetch(`/user/passkeys/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        }
                    });

                    if (res.ok) {
                        const el = document.getElementById(`passkey-row-${id}`);
                        if (el) el.remove();
                        this.message = 'Biometric passkey removed.';
                        this.isSuccess = true;
                    } else {
                        throw new Error('Failed to delete passkey.');
                    }
                } catch (err) {
                    this.message = err.message || 'Could not remove passkey.';
                    this.isSuccess = false;
                }
            }
        };
    }
</script>
