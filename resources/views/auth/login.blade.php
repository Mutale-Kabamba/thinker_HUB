<x-guest-layout>
    <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900">
        <div class="mb-6 flex items-start justify-between gap-3">
            <a href="{{ route('home') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-teal-300 hover:text-teal-600 dark:border-slate-700 dark:text-slate-400 dark:hover:border-teal-700 dark:hover:text-teal-400" aria-label="Back to home">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M9.293 2.293a1 1 0 011.414 0l7 7a1 1 0 01-1.414 1.414L16 10.414V17a1 1 0 01-1 1h-3a1 1 0 01-1-1v-3H9v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-6.586l-.293.293a1 1 0 01-1.414-1.414l7-7z" />
                </svg>
            </a>
            <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-600">Shared Access</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-100">Sign In</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Admin and student accounts use this same login panel.</p>
            </div>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <x-input-label for="email" class="text-slate-700 dark:text-slate-300" :value="__('Email')" />
                <x-text-input id="email" class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-none focus:border-teal-500 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" class="text-slate-700 dark:text-slate-300" :value="__('Password')" />
                <div class="relative mt-1">
                    <x-text-input id="password" class="block w-full rounded-xl border-slate-300 pr-24 text-sm shadow-none focus:border-teal-500 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" type="password" name="password" required autocomplete="current-password" />
                    <button type="button" data-toggle-password="password" class="absolute inset-y-0 right-0 px-3 text-xs font-semibold text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">Show</button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between pt-1">
                <label for="remember_me" class="inline-flex items-center text-sm text-slate-600 dark:text-slate-400">
                    <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-teal-600 shadow-sm focus:ring-teal-500" name="remember">
                    <span class="ms-2">Remember me</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-sm text-slate-600 underline-offset-4 hover:text-slate-900 hover:underline dark:text-slate-400 dark:hover:text-slate-200" href="{{ route('password.request') }}">
                        Forgot password?
                    </a>
                @endif
            </div>

            <x-primary-button class="mt-2 w-full justify-center rounded-xl bg-teal-600 py-2.5 text-[0.75rem] hover:bg-teal-700 focus:ring-teal-500">
                {{ __('Login') }}
            </x-primary-button>

            <button
                type="button"
                id="google-signin-login"
                class="w-full justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-[0.75rem] font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200"
            >
                Continue with Google
            </button>

            <button
                type="button"
                id="facebook-signin-login"
                class="w-full justify-center rounded-xl border border-slate-300 bg-[#1877F2] px-4 py-2.5 text-[0.75rem] font-semibold text-white transition hover:bg-[#1669d7] dark:border-slate-700"
            >
                Continue with Facebook
            </button>

            <p id="google-signin-login-feedback" class="text-xs text-center text-slate-500"></p>

            <p class="text-center text-sm text-slate-600 dark:text-slate-400">
                New here?
                <a href="{{ route('register') }}" class="font-semibold text-teal-700 underline-offset-4 hover:underline">Register your account</a>
            </p>
        </form>
    </div>

    <script>
        document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
            button.addEventListener('click', function () {
                var field = document.getElementById(button.getAttribute('data-toggle-password'));
                if (!field) {
                    return;
                }

                var isHidden = field.type === 'password';
                field.type = isHidden ? 'text' : 'password';
                button.textContent = isHidden ? 'Hide' : 'Show';
            });
        });
    </script>

    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.5/firebase-app.js";
        import { getAuth, FacebookAuthProvider, GoogleAuthProvider, getRedirectResult, signInWithPopup, signInWithRedirect } from "https://www.gstatic.com/firebasejs/10.12.5/firebase-auth.js";

        const googleButton = document.getElementById('google-signin-login');
        const facebookButton = document.getElementById('facebook-signin-login');
        const feedback = document.getElementById('google-signin-login-feedback');

        const firebaseConfig = @json(config('services.firebase.web'));
        const requiredKeys = ['apiKey', 'authDomain', 'projectId', 'appId'];
        const missingKey = requiredKeys.find((key) => !firebaseConfig?.[key]);

        if (googleButton && facebookButton && feedback) {
            if (missingKey) {
                googleButton.disabled = true;
                facebookButton.disabled = true;
                googleButton.classList.add('opacity-60', 'cursor-not-allowed');
                facebookButton.classList.add('opacity-60', 'cursor-not-allowed');
                feedback.textContent = 'Social sign-in is unavailable. Missing Firebase config.';
            } else {
            const app = initializeApp(firebaseConfig);
            const auth = getAuth(app);
            const googleProvider = new GoogleAuthProvider();
            const facebookProvider = new FacebookAuthProvider();
            facebookProvider.addScope('email');

            const submitSocialToken = async (idToken) => {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                const response = await fetch("{{ route('auth.firebase.google') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({ id_token: idToken }),
                });

                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.message || 'Social sign-in failed.');
                }

                window.location.assign(payload.redirect || "{{ route('dashboard', absolute: false) }}");
            };

            const setIdleButtons = () => {
                googleButton.disabled = false;
                facebookButton.disabled = false;
                googleButton.textContent = 'Continue with Google';
                facebookButton.textContent = 'Continue with Facebook';
            };

            const explainSocialError = (error) => {
                const code = error?.code || '';

                if (code === 'auth/popup-closed-by-user') {
                    return 'Popup was closed before completing sign-in. Try again and keep it open.';
                }

                if (code === 'auth/cancelled-popup-request') {
                    return 'A sign-in popup is already open. Complete that popup first.';
                }

                return error?.message || 'Social sign-in failed.';
            };

            (async () => {
                try {
                    const redirectResult = await getRedirectResult(auth);

                    if (redirectResult?.user) {
                        const idToken = await redirectResult.user.getIdToken();
                        await submitSocialToken(idToken);
                    }
                } catch (error) {
                    feedback.textContent = explainSocialError(error);
                    setIdleButtons();
                }
            })();

            const bindProvider = (button, provider, loadingLabel, idleLabel) => {
                button.addEventListener('click', async () => {
                    feedback.textContent = '';
                    googleButton.disabled = true;
                    facebookButton.disabled = true;
                    button.textContent = loadingLabel;

                    try {
                        const result = await signInWithPopup(auth, provider);
                        const idToken = await result.user.getIdToken();
                        await submitSocialToken(idToken);
                    } catch (error) {
                        if (error?.code === 'auth/popup-blocked') {
                            try {
                                await signInWithRedirect(auth, provider);
                                return;
                            } catch (redirectError) {
                                feedback.textContent = explainSocialError(redirectError);
                                setIdleButtons();
                                return;
                            }
                        }

                        feedback.textContent = explainSocialError(error);
                        setIdleButtons();
                    }
                });
            };

            bindProvider(googleButton, googleProvider, 'Signing in...', 'Continue with Google');
            bindProvider(facebookButton, facebookProvider, 'Signing in...', 'Continue with Facebook');
            }
        }
    </script>
</x-guest-layout>
