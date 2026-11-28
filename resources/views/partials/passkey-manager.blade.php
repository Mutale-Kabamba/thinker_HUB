<style>
    .hub-pk-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.02);
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        box-sizing: border-box;
    }
    .dark .hub-pk-card {
        background: #111b21;
        border-color: #1f2c34;
        box-shadow: 0 4px 16px -4px rgba(0, 0, 0, 0.35);
    }
    .hub-pk-header {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        padding-bottom: 1.15rem;
        border-bottom: 1px solid #f1f5f9;
    }
    @media (min-width: 640px) {
        .hub-pk-header {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }
    }
    .dark .hub-pk-header {
        border-bottom-color: #1f2c34;
    }
    .hub-pk-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.2rem 0.65rem;
        border-radius: 9999px;
        font-size: 0.68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        background: #f0fdfa;
        color: #0f766e;
        border: 1px solid #99f6e4;
        width: fit-content;
    }
    .dark .hub-pk-badge {
        background: rgba(13, 148, 136, 0.15);
        color: #5eead4;
        border-color: rgba(20, 184, 166, 0.3);
    }
    .hub-pk-title-row {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.35rem;
    }
    .hub-pk-title {
        font-size: 1.125rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        letter-spacing: -0.01em;
    }
    .dark .hub-pk-title {
        color: #f8fafc;
    }
    .hub-pk-subtitle {
        font-size: 0.8125rem;
        color: #64748b;
        margin: 0.2rem 0 0 0;
        line-height: 1.45;
    }
    .dark .hub-pk-subtitle {
        color: #94a3b8;
    }
    .hub-pk-body {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.25rem;
        align-items: stretch;
    }
    .hub-pk-info-box {
        border-radius: 14px;
        padding: 1.25rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 1rem;
    }
    .dark .hub-pk-info-box {
        background: #0d171d;
        border-color: #1f2c34;
    }
    .hub-pk-info-head {
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    .hub-pk-info-icon {
        width: 2rem;
        height: 2rem;
        border-radius: 8px;
        background: #ccfbf1;
        color: #0f766e;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .dark .hub-pk-info-icon {
        background: rgba(13, 148, 136, 0.2);
        color: #5eead4;
    }
    .hub-pk-info-title {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #0f172a;
        margin: 0;
    }
    .dark .hub-pk-info-title {
        color: #f1f5f9;
    }
    .hub-pk-info-desc {
        font-size: 0.78rem;
        color: #64748b;
        line-height: 1.5;
        margin: 0.5rem 0 0 0;
    }
    .dark .hub-pk-info-desc {
        color: #94a3b8;
    }
    .hub-pk-feature-list {
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
        margin-top: 0.75rem;
    }
    .hub-pk-feature-item {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        font-size: 0.78rem;
        line-height: 1.4;
        color: #475569;
    }
    .dark .hub-pk-feature-item {
        color: #cbd5e1;
    }
    .hub-pk-feature-item strong {
        color: #0f172a;
        font-weight: 700;
    }
    .dark .hub-pk-feature-item strong {
        color: #f8fafc;
    }
    .hub-pk-tags-wrap {
        padding-top: 0.85rem;
        border-top: 1px solid #e2e8f0;
    }
    .dark .hub-pk-tags-wrap {
        border-top-color: #1f2c34;
    }
    .hub-pk-tags-label {
        display: block;
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        margin-bottom: 0.45rem;
    }
    .dark .hub-pk-tags-label {
        color: #94a3b8;
    }
    .hub-pk-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
    }
    .hub-pk-tag {
        padding: 0.2rem 0.55rem;
        border-radius: 6px;
        font-size: 0.68rem;
        font-weight: 700;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: #334155;
    }
    .dark .hub-pk-tag {
        background: #16242d;
        border-color: #263844;
        color: #cbd5e1;
    }
    .hub-pk-empty {
        border-radius: 14px;
        border: 1.5px dashed #cbd5e1;
        background: rgba(248, 250, 252, 0.6);
        padding: 2rem 1.5rem;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.65rem;
        height: 100%;
        box-sizing: border-box;
    }
    .dark .hub-pk-empty {
        border-color: #263844;
        background: rgba(13, 23, 29, 0.5);
    }
    .hub-pk-empty-icon {
        width: 3.25rem;
        height: 3.25rem;
        border-radius: 14px;
        background: #f0fdfa;
        color: #0d9488;
        border: 1px solid #99f6e4;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .dark .hub-pk-empty-icon {
        background: rgba(13, 148, 136, 0.15);
        color: #2dd4bf;
        border-color: rgba(20, 184, 166, 0.3);
    }
    .hub-pk-device-row {
        padding: 0.85rem 1rem;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
        transition: border-color 0.15s ease, background-color 0.15s ease;
    }
    .dark .hub-pk-device-row {
        border-color: #1f2c34;
        background: #0d171d;
    }
    .hub-pk-device-row:hover {
        border-color: #99f6e4;
    }
    .dark .hub-pk-device-row:hover {
        border-color: #115e59;
    }
</style>

<section class="hub-pk-card" x-data="passkeyManager()">
    {{-- Header Ribbon --}}
    <div class="hub-pk-header">
        <div>
            <div class="hub-pk-badge">
                <x-heroicon-s-shield-check style="width:0.85rem;height:0.85rem;flex-shrink:0;" />
                <span>Authentication &amp; Security</span>
            </div>
            <div class="hub-pk-title-row">
                <x-heroicon-o-finger-print style="width:1.35rem;height:1.35rem;color:#0d9488;flex-shrink:0;" />
                <h2 class="hub-pk-title">Fingerprint &amp; Biometrics</h2>
            </div>
            <p class="hub-pk-subtitle">
                Sign in instantly using Touch ID, Face ID, Android Fingerprint, or Windows Hello on this device.
            </p>
        </div>

        {{-- Top Primary Action Button --}}
        <div style="flex-shrink:0;">
            <button
                type="button"
                @click="registerPasskey()"
                :disabled="loading"
                class="hub-btn hub-btn-primary"
                style="padding:0.55rem 1.15rem;font-size:0.78rem;"
            >
                <x-heroicon-m-arrow-path class="animate-spin" style="width:1rem;height:1rem;" x-show="loading" x-cloak />
                <x-heroicon-m-plus style="width:1rem;height:1rem;" x-show="!loading" />
                <span x-text="loading ? 'Registering Device...' : 'Enable Fingerprint / Passkey'">Enable Fingerprint / Passkey</span>
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
        style="padding:0.75rem 1rem;border-radius:12px;font-size:0.78rem;font-weight:700;display:flex;align-items:center;justify-content:space-between;gap:0.75rem;"
        :style="isSuccess ? 'background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;' : 'background:#fff1f2;color:#9f1239;border:1px solid #fecdd3;'"
    >
        <div style="display:flex;align-items:center;gap:0.5rem;">
            <x-heroicon-s-check-circle style="width:1rem;height:1rem;color:#059669;flex-shrink:0;" x-show="isSuccess" />
            <x-heroicon-s-exclamation-circle style="width:1rem;height:1rem;color:#e11d48;flex-shrink:0;" x-show="!isSuccess" />
            <span x-text="message"></span>
        </div>
        <button type="button" @click="message = ''" style="background:transparent;border:none;font-size:1rem;line-height:1;cursor:pointer;opacity:0.65;color:inherit;">&times;</button>
    </div>

    {{-- Responsive 2-Column Balanced Layout --}}
    <div class="hub-pk-body">
        {{-- Left Column: Security Benefits & Supported Platforms --}}
        <div class="hub-pk-info-box">
            <div>
                <div class="hub-pk-info-head">
                    <div class="hub-pk-info-icon">
                        <x-heroicon-o-lock-closed style="width:1rem;height:1rem;" />
                    </div>
                    <h3 class="hub-pk-info-title">Hardware-Backed Security</h3>
                </div>
                <p class="hub-pk-info-desc">
                    Passkeys replace passwords with cryptographic key pairs stored directly inside your device's Secure Enclave or TPM.
                </p>

                <div class="hub-pk-feature-list">
                    <div class="hub-pk-feature-item">
                        <x-heroicon-s-check-circle style="width:1rem;height:1rem;color:#059669;flex-shrink:0;margin-top:0.1rem;" />
                        <div>
                            <strong>1-Touch Instant Sign-in:</strong>
                            <span> Fast login without waiting for email OTP codes.</span>
                        </div>
                    </div>

                    <div class="hub-pk-feature-item">
                        <x-heroicon-s-check-circle style="width:1rem;height:1rem;color:#059669;flex-shrink:0;margin-top:0.1rem;" />
                        <div>
                            <strong>Phishing Resistant:</strong>
                            <span> Protected against credential leaks and stolen passwords.</span>
                        </div>
                    </div>

                    <div class="hub-pk-feature-item">
                        <x-heroicon-s-check-circle style="width:1rem;height:1rem;color:#059669;flex-shrink:0;margin-top:0.1rem;" />
                        <div>
                            <strong>Zero Cloud Storage:</strong>
                            <span> Biometric scans never leave your local physical device.</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Supported Devices Badges Pill Row --}}
            <div class="hub-pk-tags-wrap">
                <span class="hub-pk-tags-label">Supported Systems &amp; Hardware</span>
                <div class="hub-pk-tags">
                    <span class="hub-pk-tag">Touch ID / Face ID</span>
                    <span class="hub-pk-tag">Windows Hello</span>
                    <span class="hub-pk-tag">Android Biometrics</span>
                    <span class="hub-pk-tag">YubiKey / FIDO2</span>
                </div>
            </div>
        </div>

        {{-- Right Column: Active Passkeys List or Polished Empty State --}}
        <div style="display:flex;flex-direction:column;justify-content:center;">
            @php
                $userPasskeys = auth()->user()->passkeys()->latest()->get();
            @endphp

            @if ($userPasskeys->isNotEmpty())
                <div style="display:flex;flex-direction:column;gap:0.75rem;">
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:0 0.25rem;">
                        <span style="font-size:0.7rem;font-weight:800;text-transform:uppercase;letter-spacing:0.06em;color:#64748b;">
                            Registered Devices ({{ $userPasskeys->count() }})
                        </span>
                        <span style="font-size:0.72rem;font-weight:700;color:#0d9488;">
                            Active &bull; WebAuthn Ready
                        </span>
                    </div>

                    <div style="display:flex;flex-direction:column;gap:0.6rem;">
                        @foreach ($userPasskeys as $pk)
                            <div id="passkey-row-{{ $pk->id }}" class="hub-pk-device-row">
                                <div style="display:flex;align-items:center;gap:0.75rem;min-width:0;flex:1;">
                                    <div style="width:2.35rem;height:2.35rem;border-radius:10px;background:#f0fdfa;color:#0d9488;border:1px solid #99f6e4;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <x-heroicon-o-finger-print style="width:1.2rem;height:1.2rem;" />
                                    </div>
                                    <div style="min-width:0;">
                                        <p style="font-weight:800;font-size:0.82rem;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                            {{ $pk->name }}
                                        </p>
                                        <p style="font-size:0.7rem;color:#64748b;margin:0.15rem 0 0 0;">
                                            Added {{ $pk->created_at?->format('M d, Y') }}
                                            @if ($pk->last_used_at)
                                                &bull; Last used {{ $pk->last_used_at->diffForHumans() }}
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div style="display:flex;align-items:center;gap:0.5rem;">
                                    <span class="hub-chip hub-chip-green">Active</span>
                                    <button
                                        type="button"
                                        @click="deletePasskey({{ $pk->id }})"
                                        class="hub-btn hub-btn-danger"
                                        style="padding:0.3rem 0.65rem;font-size:0.72rem;"
                                        title="Remove this passkey device"
                                    >
                                        <x-heroicon-o-trash style="width:0.85rem;height:0.85rem;" />
                                        <span>Remove</span>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                {{-- Polished Empty State --}}
                <div class="hub-pk-empty">
                    <div class="hub-pk-empty-icon">
                        <x-heroicon-o-finger-print style="width:1.65rem;height:1.65rem;" />
                    </div>
                    <div style="max-width:22rem;">
                        <h4 style="font-size:0.88rem;font-weight:800;margin:0;color:inherit;">
                            No fingerprint or biometric credentials registered yet.
                        </h4>
                        <p style="font-size:0.76rem;color:#64748b;margin:0.3rem 0 0 0;line-height:1.45;">
                            Register this device to sign in with your fingerprint or face recognition instead of typing a password.
                        </p>
                    </div>
                    <div style="padding-top:0.35rem;">
                        <button
                            type="button"
                            @click="registerPasskey()"
                            :disabled="loading"
                            class="hub-btn hub-btn-secondary"
                            style="font-size:0.76rem;padding:0.45rem 1rem;"
                        >
                            <x-heroicon-m-plus style="width:0.9rem;height:0.9rem;" />
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
