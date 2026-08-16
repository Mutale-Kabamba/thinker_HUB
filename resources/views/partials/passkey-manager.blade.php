<section class="hub-card" x-data="passkeyManager()">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;">
        <div>
            <p class="hub-eyebrow">Authentication & Security</p>
            <h2 class="hub-title" style="display:flex;align-items:center;gap:0.4rem;">
                <i class="fa-solid fa-fingerprint" style="color:var(--hub-primary, #0d9488);"></i>
                <span>Fingerprint & Biometrics</span>
            </h2>
            <p class="hub-copy">Sign in instantly using Touch ID, Face ID, Android Fingerprint, or Windows Hello on this device.</p>
        </div>
        <button
            type="button"
            @click="registerPasskey()"
            :disabled="loading"
            class="hub-btn hub-btn-primary"
            style="font-size:0.8rem;display:inline-flex;align-items:center;gap:0.35rem;"
        >
            <i class="fa-solid fa-plus" x-show="!loading"></i>
            <i class="fa-solid fa-spinner fa-spin" x-show="loading"></i>
            <span x-text="loading ? 'Registering...' : '+ Enable Fingerprint / Passkey'"></span>
        </button>
    </div>

    <!-- Feedback alert -->
    <div x-show="message" x-cloak style="margin-top:0.8rem;" :class="isSuccess ? 'hub-chip-green' : 'hub-chip-red'" class="hub-chip" style="padding:0.4rem 0.75rem;font-size:0.78rem;display:inline-block;">
        <span x-text="message"></span>
    </div>

    <!-- Registered Devices List -->
    <div class="hub-stack" style="margin-top:0.9rem;">
        @php
            $userPasskeys = auth()->user()->passkeys()->latest()->get();
        @endphp

        @forelse ($userPasskeys as $pk)
            <div id="passkey-row-{{ $pk->id }}" style="display:flex;justify-content:space-between;align-items:center;padding:0.65rem 0.85rem;border:1px solid var(--hub-border);border-radius:10px;background:var(--hub-surface);gap:0.75rem;flex-wrap:wrap;">
                <div style="display:flex;align-items:center;gap:0.6rem;">
                    <div style="width:2rem;height:2rem;border-radius:999px;background:rgba(13,148,136,0.12);color:#0d9488;display:flex;align-items:center;justify-content:center;">
                        <i class="fa-solid fa-fingerprint" style="font-size:0.9rem;"></i>
                    </div>
                    <div>
                        <p style="margin:0;font-weight:600;font-size:0.84rem;color:var(--hub-ink);">{{ $pk->name }}</p>
                        <p style="margin:0.1rem 0 0;font-size:0.72rem;color:var(--hub-muted);">
                            Added {{ $pk->created_at?->format('M d, Y') }}
                            @if ($pk->last_used_at)
                                &bull; Last used {{ $pk->last_used_at->diffForHumans() }}
                            @endif
                        </p>
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:0.5rem;">
                    <span class="hub-chip hub-chip-green" style="font-size:0.65rem;">Active</span>
                    <button
                        type="button"
                        @click="deletePasskey({{ $pk->id }})"
                        style="background:none;border:1px solid var(--hub-border);border-radius:6px;padding:0.25rem 0.5rem;font-size:0.72rem;cursor:pointer;color:var(--hub-danger);transition:all 0.15s;"
                        onmouseover="this.style.background='#fee2e2'"
                        onmouseout="this.style.background='none'"
                    >
                        Remove
                    </button>
                </div>
            </div>
        @empty
            <div style="padding:1.2rem;text-align:center;border:1px dashed var(--hub-border);border-radius:10px;">
                <i class="fa-solid fa-fingerprint" style="font-size:1.8rem;color:var(--hub-muted);opacity:0.6;margin-bottom:0.35rem;display:block;"></i>
                <p class="hub-copy" style="margin:0;font-size:0.82rem;font-weight:500;">No fingerprint or biometric credentials registered yet.</p>
                <p style="margin:0.25rem 0 0;font-size:0.74rem;color:var(--hub-muted);">Click "+ Enable Fingerprint / Passkey" above to allow fast one-touch login.</p>
            </div>
        @endforelse
    </div>
</section>

<script>
    function passkeyManager() {
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
                        // Direct WebAuthn registration
                        const optionsRes = await fetch('/user/passkeys/options', {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
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
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
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
                    const res = await fetch(`/user/passkeys/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
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
