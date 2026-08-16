<section x-data="passkeyManager()">
    <header class="flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h2 class="text-lg font-medium text-gray-900 flex items-center gap-2">
                <i class="fa-solid fa-fingerprint text-teal-600"></i>
                {{ __('Fingerprint & Biometric Login') }}
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                {{ __('Sign in securely using Touch ID, Face ID, Windows Hello, or Android Fingerprint without entering your password.') }}
            </p>
        </div>

        <button
            type="button"
            @click="registerPasskey()"
            :disabled="loading"
            class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 disabled:opacity-50 transition"
        >
            <i class="fa-solid fa-plus" x-show="!loading"></i>
            <i class="fa-solid fa-spinner fa-spin" x-show="loading"></i>
            <span x-text="loading ? 'Registering...' : '+ Enable Fingerprint / Passkey'"></span>
        </button>
    </header>

    <div x-show="message" x-cloak class="mt-4 p-3 rounded-lg text-sm" :class="isSuccess ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'">
        <span x-text="message"></span>
    </div>

    <div class="mt-6 space-y-3">
        @php
            $userPasskeys = auth()->user()->passkeys()->latest()->get();
        @endphp

        @forelse ($userPasskeys as $pk)
            <div id="profile-passkey-{{ $pk->id }}" class="flex items-center justify-between p-3.5 border border-slate-200 rounded-xl bg-slate-50/50 hover:bg-slate-50 transition">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center">
                        <i class="fa-solid fa-fingerprint text-base"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ $pk->name }}</p>
                        <p class="text-xs text-slate-500">
                            Registered {{ $pk->created_at?->format('M d, Y') }}
                            @if ($pk->last_used_at)
                                &bull; Last active {{ $pk->last_used_at->diffForHumans() }}
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                        Active
                    </span>
                    <button
                        type="button"
                        @click="deletePasskey({{ $pk->id }})"
                        class="text-xs text-rose-600 hover:text-rose-800 font-medium px-2 py-1 rounded hover:bg-rose-50 transition"
                    >
                        Remove
                    </button>
                </div>
            </div>
        @empty
            <div class="p-6 text-center border border-dashed border-slate-200 rounded-xl bg-slate-50/30">
                <i class="fa-solid fa-fingerprint text-3xl text-slate-300 mb-2"></i>
                <p class="text-sm font-medium text-slate-600">No biometric credentials registered</p>
                <p class="text-xs text-slate-400 mt-1">Click "+ Enable Fingerprint / Passkey" to register this device's sensor.</p>
            </div>
        @endforelse
    </div>
</section>

<script>
    if (typeof window.passkeyManager === 'undefined') {
        window.passkeyManager = function() {
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
                            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                            const optionsRes = await fetch('/user/passkeys/options', {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrf
                                }
                            });
                            if (!optionsRes.ok) throw new Error('Could not get registration options.');
                            const { options } = await optionsRes.json();

                            const challenge = Uint8Array.from(atob(options.challenge.replace(/-/g, '+').replace(/_/g, '/')), c => c.charCodeAt(0));
                            const userHandle = Uint8Array.from(atob(options.user.id.replace(/-/g, '+').replace(/_/g, '/')), c => c.charCodeAt(0));

                            const credential = await navigator.credentials.create({
                                publicKey: {
                                    ...options,
                                    challenge,
                                    user: {
                                        ...options.user,
                                        id: userHandle,
                                    }
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
                                    id: credential.id,
                                    rawId: btoa(String.fromCharCode(...new Uint8Array(credential.rawId))),
                                    type: credential.type,
                                    response: {
                                        clientDataJSON: btoa(String.fromCharCode(...new Uint8Array(credential.response.clientDataJSON))),
                                        attestationObject: btoa(String.fromCharCode(...new Uint8Array(credential.response.attestationObject))),
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
                        if (err.name === 'NotAllowedError') {
                            this.message = 'Biometric registration was cancelled or timed out.';
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
                            const el = document.getElementById(`profile-passkey-${id}`);
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
        };
    }
</script>
