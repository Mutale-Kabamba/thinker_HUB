<x-guest-layout>
    <div class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900">
        <div class="mb-6 flex items-start justify-between gap-3">
            <a href="{{ route('home') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-teal-300 hover:text-teal-600 dark:border-slate-700 dark:text-slate-400 dark:hover:border-teal-700 dark:hover:text-teal-400" aria-label="Back to home">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M9.293 2.293a1 1 0 011.414 0l7 7a1 1 0 01-1.414 1.414L16 10.414V17a1 1 0 01-1 1h-3a1 1 0 01-1-1v-3H9v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-6.586l-.293.293a1 1 0 01-1.414-1.414l7-7z" />
                </svg>
            </a>
            <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-600">Enrollment</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-100">Create Your Account</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Fill in your details to join as a student.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <button
                type="button"
                id="google-signin-register"
                class="flex w-full items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-[0.75rem] font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
                    <path fill="#EA4335" d="M12 10.2v3.9h5.5c-.2 1.2-1.4 3.6-5.5 3.6-3.3 0-6-2.8-6-6.2s2.7-6.2 6-6.2c1.9 0 3.2.8 3.9 1.5l2.7-2.7C16.9 2.5 14.6 1.5 12 1.5 6.8 1.5 2.6 5.9 2.6 11.5S6.8 21.5 12 21.5c6.9 0 9.2-5 9.2-7.5 0-.5-.1-.9-.1-1.3H12z"/>
                    <path fill="#34A853" d="M3.4 7.1l3.2 2.4C7.4 7.9 9.5 6 12 6c1.9 0 3.2.8 3.9 1.5l2.7-2.7C16.9 2.5 14.6 1.5 12 1.5 8.2 1.5 4.9 3.8 3.4 7.1z"/>
                    <path fill="#4A90E2" d="M12 21.5c2.5 0 4.7-.9 6.3-2.6l-2.9-2.4c-.8.6-1.9 1.1-3.4 1.1-2.9 0-5.3-2-6.1-4.7l-3.3 2.6c1.5 3.7 5 6 9.4 6z"/>
                    <path fill="#FBBC05" d="M5.9 12.9c-.2-.6-.3-1.1-.3-1.8s.1-1.2.3-1.8L2.6 6.7C2 8 1.6 9.5 1.6 11.1s.4 3.1 1 4.4l3.3-2.6z"/>
                </svg>
                Continue with Google
            </button>

            <p id="google-signin-register-feedback" class="text-xs text-center text-slate-500"></p>

            <div id="google-register-state" class="hidden rounded-xl border border-teal-200 bg-teal-50 px-4 py-3 text-xs text-teal-800 dark:border-teal-800 dark:bg-teal-950/40 dark:text-teal-200"></div>

            <div class="relative py-1">
                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                    <div class="w-full border-t border-slate-200 dark:border-slate-700"></div>
                </div>
                <div class="relative flex justify-center">
                    <span class="bg-white px-3 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400 dark:bg-slate-900">Or register manually</span>
                </div>
            </div>

            <div>
                <x-input-label for="name" class="text-slate-700 dark:text-slate-300" :value="__('Full Name')" />
                <x-text-input id="name" class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-none focus:border-teal-500 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="email" class="text-slate-700 dark:text-slate-300" :value="__('Email Address')" />
                <x-text-input id="email" class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-none focus:border-teal-500 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" type="email" name="email" :value="old('email')" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="course_id" class="text-slate-700 dark:text-slate-300" :value="__('Course')" />
                    <select id="course_id" name="course_id" required class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-none focus:border-teal-500 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                        <option value="">Select course</option>
                        @foreach ($courses as $course)
                            @php $isLockedCourse = $course->is_open_enrollment === false; @endphp
                            <option
                                value="{{ $course->id }}"
                                @selected((string) old('course_id') === (string) $course->id)
                                @disabled($isLockedCourse)
                            >
                                {{ $course->code }} - {{ $course->title }}{{ $isLockedCourse ? ' (Locked)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('course_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="track" class="text-slate-700 dark:text-slate-300" :value="__('Level')" />
                    <select id="track" name="track" required class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-none focus:border-teal-500 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                        <option value="">Select level</option>
                        @foreach (['Beginner', 'Intermediate', 'Advanced'] as $track)
                            <option value="{{ $track }}" @selected(old('track') === $track)>{{ $track }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('track')" class="mt-2" />
                </div>
            </div>

            <div>
                <x-input-label for="password" class="text-slate-700 dark:text-slate-300" :value="__('Password')" />
                <div class="relative mt-1">
                    <x-text-input id="password" class="block w-full rounded-xl border-slate-300 pr-24 text-sm shadow-none focus:border-teal-500 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" type="password" name="password" required autocomplete="new-password" />
                    <button type="button" data-toggle-password="password" class="absolute inset-y-0 right-0 px-3 text-xs font-semibold text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">Show</button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" class="text-slate-700 dark:text-slate-300" :value="__('Confirm Password')" />
                <div class="relative mt-1">
                    <x-text-input id="password_confirmation" class="block w-full rounded-xl border-slate-300 pr-24 text-sm shadow-none focus:border-teal-500 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" type="password" name="password_confirmation" required autocomplete="new-password" />
                    <button type="button" data-toggle-password="password_confirmation" class="absolute inset-y-0 right-0 px-3 text-xs font-semibold text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">Show</button>
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50/80 p-3 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300">
                <input type="checkbox" name="accept_terms" value="1" class="mt-0.5 rounded border-slate-300 text-teal-600 focus:ring-teal-500" @checked(old('accept_terms'))>
                <span>I agree to the Terms and Conditions for learner enrollment.</span>
            </label>
            <x-input-error :messages="$errors->get('accept_terms')" class="mt-2" />

            <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50/80 p-3 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300">
                <input type="checkbox" name="accept_requirements" value="1" class="mt-0.5 rounded border-slate-300 text-teal-600 focus:ring-teal-500" @checked(old('accept_requirements'))>
                <span>I confirm that I meet the basic requirements for this learning program.</span>
            </label>
            <x-input-error :messages="$errors->get('accept_requirements')" class="mt-2" />

            <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
                <a class="text-sm font-medium text-slate-600 underline-offset-4 hover:text-slate-900 hover:underline dark:text-slate-400 dark:hover:text-slate-200" href="{{ route('login') }}">
                    {{ __('Already registered? Login') }}
                </a>

                <x-primary-button class="justify-center rounded-xl bg-teal-600 px-6 py-2.5 text-[0.75rem] hover:bg-teal-700 focus:ring-teal-500">
                    {{ __('Complete Enrollment') }}
                </x-primary-button>
            </div>

        </form>
    </div>

    <div id="google-enrollment-modal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="google-enrollment-modal-title">
        <div class="absolute inset-0 bg-slate-900/50"></div>
        <div class="relative mx-auto mt-10 w-[92%] max-w-lg rounded-2xl border border-slate-200 bg-white p-5 shadow-xl sm:mt-16 dark:border-slate-700 dark:bg-slate-900">
            <div class="mb-4">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-600">Complete Enrollment</p>
                <h3 id="google-enrollment-modal-title" class="mt-2 text-lg font-bold text-slate-900 dark:text-slate-100">Finish Your Google Registration</h3>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Select your course, level, and accept the required agreements to continue.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="google_modal_course_id" class="text-sm font-medium text-slate-700 dark:text-slate-300">Course</label>
                    <select id="google_modal_course_id" class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-none focus:border-teal-500 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                        <option value="">Select course</option>
                        @foreach ($courses as $course)
                            @php $isLockedCourse = $course->is_open_enrollment === false; @endphp
                            <option value="{{ $course->id }}" @disabled($isLockedCourse)>
                                {{ $course->code }} - {{ $course->title }}{{ $isLockedCourse ? ' (Locked)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="google_modal_track" class="text-sm font-medium text-slate-700 dark:text-slate-300">Level</label>
                    <select id="google_modal_track" class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-none focus:border-teal-500 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                        <option value="">Select level</option>
                        @foreach (['Beginner', 'Intermediate', 'Advanced'] as $track)
                            <option value="{{ $track }}">{{ $track }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-4 space-y-3">
                <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50/80 p-3 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300">
                    <input id="google_modal_accept_terms" type="checkbox" class="mt-0.5 rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                    <span>I agree to the Terms and Conditions for learner enrollment.</span>
                </label>

                <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50/80 p-3 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300">
                    <input id="google_modal_accept_requirements" type="checkbox" class="mt-0.5 rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                    <span>I confirm that I meet the basic requirements for this learning program.</span>
                </label>
            </div>

            <div class="mt-5 flex items-center justify-end gap-3">
                <button type="button" id="google-enrollment-cancel" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200">Cancel</button>
                <button type="button" id="google-enrollment-submit" class="rounded-xl bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">Complete Enrollment</button>
            </div>
        </div>
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
        import { getAuth, GoogleAuthProvider, getRedirectResult, signInWithPopup, signInWithRedirect } from "https://www.gstatic.com/firebasejs/10.12.5/firebase-auth.js";

        const googleButton = document.getElementById('google-signin-register');
        const googleState = document.getElementById('google-register-state');
        const feedback = document.getElementById('google-signin-register-feedback');
        const enrollmentModal = document.getElementById('google-enrollment-modal');
        const enrollmentCancelButton = document.getElementById('google-enrollment-cancel');
        const enrollmentSubmitButton = document.getElementById('google-enrollment-submit');
        let pendingGoogleIdToken = null;

        const firebaseConfig = @json(config('services.firebase.web'));
        const requiredKeys = ['apiKey', 'authDomain', 'projectId', 'appId'];
        const missingKey = requiredKeys.find((key) => !firebaseConfig?.[key]);

        if (googleButton && feedback && googleState && enrollmentModal && enrollmentCancelButton && enrollmentSubmitButton) {
            if (missingKey) {
                googleButton.disabled = true;
                googleButton.classList.add('opacity-60', 'cursor-not-allowed');
                feedback.textContent = 'Social sign-in is unavailable. Missing Firebase config.';
            } else {
            const app = initializeApp(firebaseConfig);
            const auth = getAuth(app);
            const googleProvider = new GoogleAuthProvider();

            const buildRegistrationPayload = () => {
                const courseId = document.getElementById('course_id')?.value;
                const track = document.getElementById('track')?.value;
                const acceptTerms = document.querySelector('input[name="accept_terms"]')?.checked;
                const acceptRequirements = document.querySelector('input[name="accept_requirements"]')?.checked;

                if (!courseId || !track || !acceptTerms || !acceptRequirements) {
                    throw new Error('Select course, level, and accept both confirmations before social sign-in.');
                }

                return {
                    course_id: Number(courseId),
                    track: track,
                    accept_terms: true,
                    accept_requirements: true,
                };
            };

            const submitSocialToken = async (idToken, payload) => {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                const response = await fetch("{{ route('auth.firebase.google') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({
                        id_token: idToken,
                        ...payload,
                    }),
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'Social sign-in failed.');
                }

                window.location.assign(result.redirect || "{{ route('dashboard', absolute: false) }}");
            };

            const setIdleButtons = () => {
                googleButton.disabled = false;
                googleButton.textContent = 'Continue with Google';
            };

            const showEnrollmentModal = () => {
                enrollmentModal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            };

            const hideEnrollmentModal = () => {
                enrollmentModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            };

            const getEnrollmentPayload = () => {
                const courseId = document.getElementById('google_modal_course_id')?.value;
                const track = document.getElementById('google_modal_track')?.value;
                const acceptTerms = document.getElementById('google_modal_accept_terms')?.checked;
                const acceptRequirements = document.getElementById('google_modal_accept_requirements')?.checked;

                if (!courseId || !track || !acceptTerms || !acceptRequirements) {
                    throw new Error('Complete Course, Level, and both agreement confirmations.');
                }

                return {
                    course_id: Number(courseId),
                    track,
                    accept_terms: true,
                    accept_requirements: true,
                };
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

            const prepareGoogleEnrollmentStep = async (user) => {
                pendingGoogleIdToken = await user.getIdToken();
                googleState.classList.remove('hidden');
                googleState.textContent = `Google connected as ${user.email || 'your account'}. Complete your enrollment details in the popup to continue.`;

                const pageCourse = document.getElementById('course_id')?.value || '';
                const pageTrack = document.getElementById('track')?.value || '';
                const pageTerms = document.querySelector('input[name="accept_terms"]')?.checked || false;
                const pageReqs = document.querySelector('input[name="accept_requirements"]')?.checked || false;

                document.getElementById('google_modal_course_id').value = pageCourse;
                document.getElementById('google_modal_track').value = pageTrack;
                document.getElementById('google_modal_accept_terms').checked = pageTerms;
                document.getElementById('google_modal_accept_requirements').checked = pageReqs;

                showEnrollmentModal();
                setIdleButtons();
            };

            enrollmentCancelButton.addEventListener('click', () => {
                hideEnrollmentModal();
            });

            enrollmentModal.addEventListener('click', (event) => {
                if (event.target === enrollmentModal) {
                    hideEnrollmentModal();
                }
            });

            enrollmentSubmitButton.addEventListener('click', async () => {
                feedback.textContent = '';

                if (!pendingGoogleIdToken) {
                    feedback.textContent = 'Start with Continue with Google first.';
                    return;
                }

                enrollmentSubmitButton.disabled = true;
                enrollmentSubmitButton.textContent = 'Completing...';

                try {
                    const payload = getEnrollmentPayload();
                    await submitSocialToken(pendingGoogleIdToken, payload);
                } catch (error) {
                    feedback.textContent = explainSocialError(error);
                    enrollmentSubmitButton.disabled = false;
                    enrollmentSubmitButton.textContent = 'Complete Enrollment';

                    if ((error?.message || '').includes('Complete enrollment fields')) {
                        showEnrollmentModal();
                    }
                }
            });

            (async () => {
                try {
                    if (auth.currentUser) {
                        await prepareGoogleEnrollmentStep(auth.currentUser);
                        return;
                    }

                    const redirectResult = await getRedirectResult(auth);

                    if (redirectResult?.user) {
                        await prepareGoogleEnrollmentStep(redirectResult.user);
                    }
                } catch (error) {
                    feedback.textContent = explainSocialError(error);
                    setIdleButtons();
                }
            })();

            const bindProvider = (button, provider, loadingLabel) => {
                button.addEventListener('click', async () => {
                    feedback.textContent = '';
                    googleButton.disabled = true;
                    button.textContent = loadingLabel;

                    try {
                        const result = await signInWithPopup(auth, provider);

                        await prepareGoogleEnrollmentStep(result.user);
                        return;
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

            bindProvider(googleButton, googleProvider, 'Signing in...');
            }
        }
    </script>
</x-guest-layout>
