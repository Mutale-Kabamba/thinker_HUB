<x-guest-layout>
    <div style="max-width: 780px; margin: 1rem auto 0;">
        <div style="position: relative; border: 1px solid #d5dee6; border-radius: 22px; padding: 2rem 2rem 1.5rem; background: linear-gradient(140deg, #f0f8fa 0%, #f6f8ff 48%, #fff8ef 100%); box-shadow: 0 24px 46px rgba(8, 34, 58, 0.12); overflow: hidden;">
            <div aria-hidden="true" style="position:absolute; width:240px; height:240px; border-radius:50%; right:-90px; top:-100px; background:radial-gradient(circle, rgba(26,130,127,0.22) 0%, rgba(26,130,127,0) 65%);"></div>
            <div aria-hidden="true" style="position:absolute; width:220px; height:220px; border-radius:50%; left:-100px; bottom:-120px; background:radial-gradient(circle, rgba(248,184,91,0.18) 0%, rgba(248,184,91,0) 62%);"></div>

            <div style="position:relative; z-index:1;">
                <p style="margin:0; font-size:0.76rem; letter-spacing:0.14em; text-transform:uppercase; color:#53747e; font-weight:700;">Thinker Hub</p>
                <h1 style="margin:0.45rem 0 0; color:#0f2f3e; font-size:1.75rem; line-height:1.2; font-weight:800;">Verify Your Email Address</h1>
                <p style="margin:0.85rem 0 0; color:#304b5b; font-size:0.98rem; max-width: 640px; line-height:1.6;">
                    {{ __('Thanks for signing up. To activate your account, click the verification link we sent to your inbox. If you did not receive it, you can request a new one below.') }}
                </p>
            </div>
        </div>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div style="max-width: 780px; margin: 1rem auto 0; border: 1px solid #7bce9f; background: #ebfff2; color: #0f6e3a; border-radius: 12px; padding: 0.85rem 1rem; font-size: 0.92rem; font-weight: 600;">
            {{ __('A new verification link has been sent to your email address.') }}
        </div>
    @endif

    <div style="max-width: 780px; margin: 1rem auto 0; display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
        <form method="POST" action="{{ route('verification.send') }}" style="margin:0;">
            @csrf
            <div>
                <button type="submit" style="appearance:none; border:none; cursor:pointer; border-radius:12px; background:#1b3b5a; color:#fff; font-size:0.82rem; letter-spacing:0.1em; text-transform:uppercase; font-weight:800; padding:0.85rem 1.1rem; box-shadow:0 10px 18px rgba(27,59,90,0.22);">
                    {{ __('Resend Verification Email') }}
                </button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}" style="margin:0;">
            @csrf

            <button type="submit" style="appearance:none; border:1px solid #c7d4df; background:#fff; color:#2d4a5a; cursor:pointer; border-radius:12px; font-size:0.86rem; font-weight:600; padding:0.72rem 1rem;">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
