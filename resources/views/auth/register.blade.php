<x-guest-layout>
    <div class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 dark:border-slate-800 dark:bg-slate-900">
        <div class="mb-6 flex items-start justify-between gap-3">
            <a href="{{ route('home') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-teal-300 hover:text-teal-600 dark:border-slate-700 dark:text-slate-400 dark:hover:border-teal-700 dark:hover:text-teal-400" aria-label="Back to home">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M9.293 2.293a1 1 0 011.414 0l7 7a1 1 0 01-1.414 1.414L16 10.414V17a1 1 0 01-1 1h-3a1 1 0 01-1-1v-3H9v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-6.586l-.293.293a1 1 0 01-1.414-1.414l7-7z" />
                </svg>
            </a>
            <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-600">Account Setup</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-100">Create Your Account</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Register to enroll in curated courses and start upskilling on your path.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <button
                type="button"
                id="google-signin-register"
                class="flex w-full items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-[0.75rem] font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200"
            >
                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-white dark:bg-slate-900" aria-hidden="true">
                    <i class="fa-brands fa-google text-sm" style="background: conic-gradient(from 300deg, #4285F4 0deg 90deg, #34A853 90deg 180deg, #FBBC05 180deg 270deg, #EA4335 270deg 360deg); -webkit-background-clip: text; background-clip: text; color: transparent;"></i>
                </span>
                Continue with Google
            </button>

            <p id="google-signin-register-feedback" class="text-xs text-center text-slate-500"></p>

            <div id="google-register-state" class="hidden rounded-xl border border-teal-200 bg-teal-50 px-4 py-3 text-xs text-teal-800 dark:border-teal-800 dark:bg-teal-950/40 dark:text-teal-200"></div>

            <div class="relative py-1">
                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                    <div class="w-full border-t border-slate-200 dark:border-slate-700"></div>
                </div>
                <div class="relative flex justify-center">
                    <span class="bg-white px-3 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400 dark:bg-slate-900">Or create account manually</span>
                </div>
            </div>

            {{-- Account Type Switcher --}}
            <div class="mb-2">
                <div class="grid grid-cols-2 p-1 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                    <button type="button" id="tab-btn-student" class="py-2 px-3 rounded-lg text-xs font-bold transition flex items-center justify-center gap-2 bg-white dark:bg-slate-900 text-teal-800 dark:text-teal-300 shadow-xs cursor-pointer">
                        <i class="fa-solid fa-graduation-cap"></i>
                        <span>Student Learner</span>
                    </button>
                    <button type="button" id="tab-btn-parent" class="py-2 px-3 rounded-lg text-xs font-semibold transition flex items-center justify-center gap-2 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white cursor-pointer">
                        <i class="fa-solid fa-user-group"></i>
                        <span>Parent (Link Child)</span>
                    </button>
                </div>
                <input type="hidden" name="account_type" id="account_type_input" value="{{ old('account_type', old('kid_unique_code') ? 'parent' : 'student') }}">
            </div>

            {{-- Parent Intro Banner --}}
            <div id="parent-intro-banner" class="hidden rounded-xl border border-indigo-200 bg-indigo-50/90 p-3.5 text-xs text-indigo-950 dark:border-indigo-900 dark:bg-indigo-950/40 dark:text-indigo-200">
                <div class="flex items-start gap-2.5">
                    <i class="fa-solid fa-circle-check text-indigo-600 dark:text-indigo-400 mt-0.5 text-sm shrink-0"></i>
                    <div>
                        <p class="font-bold text-indigo-900 dark:text-indigo-200">Administered Child Registration</p>
                        <p class="mt-0.5 text-indigo-700 dark:text-indigo-300 leading-relaxed text-[11px]">
                            If Thinker HUB administration or school already registered your child, enter their <strong>Unique Login Code</strong> below. This will link your parent account immediately without any extra payment or forms.
                        </p>
                    </div>
                </div>
            </div>

            <div>
                <x-input-label for="name" id="name-label" class="text-slate-700 dark:text-slate-300" :value="__('Full Name')" />
                <x-text-input id="name" class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-none focus:border-teal-500 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="email" id="email-label" class="text-slate-700 dark:text-slate-300" :value="__('Email Address')" />
                <x-text-input id="email" class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-none focus:border-teal-500 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" type="email" name="email" :value="old('email')" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            {{-- Parent-Specific Linking Fields --}}
            <div id="parent-fields-wrapper" class="hidden space-y-4">
                <div>
                    <x-input-label for="kid_unique_code" class="text-slate-700 dark:text-slate-300" :value="__('Child\'s Unique Login Code *')" />
                    <x-text-input id="kid_unique_code" class="mt-1 block w-full uppercase font-mono tracking-wider rounded-xl border-slate-300 text-sm shadow-none focus:border-teal-500 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" type="text" name="kid_unique_code" :value="old('kid_unique_code')" placeholder="e.g. KID-4892" />
                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                        Provided when administration registered your child. Format: KID-XXXX
                    </p>
                    <x-input-error :messages="$errors->get('kid_unique_code')" class="mt-2" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="relationship" class="text-slate-700 dark:text-slate-300" :value="__('Your Relationship to Child *')" />
                        <select id="relationship" name="relationship" class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-none focus:border-teal-500 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                            <option value="Father" @selected(old('relationship') === 'Father')>Father</option>
                            <option value="Mother" @selected(old('relationship') === 'Mother')>Mother</option>
                            <option value="Guardian" @selected(old('relationship') === 'Guardian')>Legal Guardian</option>
                            <option value="Parent" @selected(old('relationship', 'Parent') === 'Parent')>Parent</option>
                        </select>
                        <x-input-error :messages="$errors->get('relationship')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="phone_number" class="text-slate-700 dark:text-slate-300" :value="__('Contact / WhatsApp Number')" />
                        <x-text-input id="phone_number" class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-none focus:border-teal-500 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" type="text" name="phone_number" :value="old('phone_number')" placeholder="e.g. 0977123456" />
                        <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="occupation" class="text-slate-700 dark:text-slate-300" :value="__('Occupation (Optional)')" />
                        <x-text-input id="occupation" class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-none focus:border-teal-500 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" type="text" name="occupation" :value="old('occupation')" placeholder="e.g. Engineer / Accountant" />
                        <x-input-error :messages="$errors->get('occupation')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="company" class="text-slate-700 dark:text-slate-300" :value="__('Employer / Organization (Optional)')" />
                        <x-text-input id="company" class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-none focus:border-teal-500 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" type="text" name="company" :value="old('company')" placeholder="e.g. Bank of Zambia" />
                        <x-input-error :messages="$errors->get('company')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="address" class="text-slate-700 dark:text-slate-300" :value="__('Home Address (Optional)')" />
                    <x-text-input id="address" class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-none focus:border-teal-500 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" type="text" name="address" :value="old('address')" placeholder="e.g. Plot 45, Woodlands, Lusaka" />
                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                </div>
            </div>

            {{-- Student-Only Fields Wrapper --}}
            <div id="student-fields-wrapper" class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="course_id" class="text-slate-700 dark:text-slate-300" :value="__('Course')" />
                        <select id="course_id" name="course_id" required class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-none focus:border-teal-500 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                            <option value="">Select course</option>
                            @foreach ($courses as $course)
                                @php
                                    $isLockedCourse = $course->is_open_enrollment === false;
                                    $feesByLevel = method_exists($course, 'getLevelFees') ? $course->getLevelFees() : ['Beginner' => 1500, 'Intermediate' => 1500, 'Advanced' => 1500];
                                    $courseFeeOptions = method_exists($course, 'getFeeOptions') ? $course->getFeeOptions() : [];
                                @endphp
                                <option
                                    value="{{ $course->id }}"
                                    data-requires-payment="{{ ! empty($course->requires_payment_approval) ? '1' : '0' }}"
                                    data-payment-message="{{ $course->payment_contact_message }}"
                                    data-title="{{ $course->title }}"
                                    data-fee="{{ method_exists($course, 'getNumericFee') ? $course->getNumericFee() : 1500 }}"
                                    data-fees-by-level="{{ json_encode($feesByLevel) }}"
                                    data-fee-options="{{ json_encode($courseFeeOptions) }}"
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
                        <input type="hidden" name="mode" id="reg_mode" value="{{ old('mode', '') }}">
                        <select id="track" name="track" data-initial-value="{{ old('track', '') }}" required class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-none focus:border-teal-500 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                            <option value="">Select level</option>
                            @foreach (['Beginner', 'Intermediate', 'Advanced'] as $track)
                                <option value="{{ $track }}" @selected(old('track') === $track)>{{ $track }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('track')" class="mt-2" />
                    </div>
                </div>

                {{-- Dynamic Level Fee Display --}}
                <div id="reg-level-amount" class="hidden rounded-xl border border-teal-200 bg-teal-50/90 px-3.5 py-2.5 dark:border-teal-800 dark:bg-teal-950/50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-tag text-teal-600 text-xs"></i>
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Level Fee:</span>
                            <span id="reg-selected-level-pill" class="rounded-md bg-teal-100 px-2 py-0.5 text-[11px] font-bold text-teal-800 dark:bg-teal-900 dark:text-teal-200">Beginner</span>
                        </div>
                        <span id="reg-amount-display" class="text-sm font-black text-teal-800 dark:text-teal-200">ZMW 1,500.00</span>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="gender" class="text-slate-700 dark:text-slate-300" :value="__('Gender (Optional)')" />
                        <select id="gender" name="gender" class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-none focus:border-teal-500 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                            <option value="">Select gender</option>
                            <option value="Male" @selected(old('gender') === 'Male')>Male</option>
                            <option value="Female" @selected(old('gender') === 'Female')>Female</option>
                            <option value="Other" @selected(old('gender') === 'Other')>Other</option>
                        </select>
                        <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="nrc_passport" class="text-slate-700 dark:text-slate-300" :value="__('NRC / Passport Number (Optional)')" />
                        <x-text-input id="nrc_passport" class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-none focus:border-teal-500 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" type="text" name="nrc_passport" :value="old('nrc_passport')" placeholder="e.g. 123456/11/1 or Passport No." />
                        <x-input-error :messages="$errors->get('nrc_passport')" class="mt-2" />
                    </div>
                </div>

                <div id="payment-notice" class="hidden rounded-2xl border border-teal-200 bg-teal-50/80 p-4 text-xs text-teal-950 dark:border-teal-800 dark:bg-teal-950/40 dark:text-teal-200">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-bold flex items-center gap-1.5 text-teal-900 dark:text-teal-200">
                            <i class="fa-solid fa-bolt text-amber-500"></i>
                            Interactive Payment Gateway
                        </span>
                        <span class="rounded-full bg-teal-200/60 px-2.5 py-0.5 text-[10px] font-bold uppercase text-teal-900 dark:bg-teal-800 dark:text-teal-100">Instant Activation</span>
                    </div>
                    <p id="payment-notice-message" class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        Instant enrollment via Mobile Money (Airtel, MTN, Zamtel), Card, or Demo Checkout.
                    </p>
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
                <span>I agree to the Terms and Conditions for platform access.</span>
            </label>
            <x-input-error :messages="$errors->get('accept_terms')" class="mt-2" />

            <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50/80 p-3 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300">
                <input type="checkbox" name="accept_requirements" value="1" class="mt-0.5 rounded border-slate-300 text-teal-600 focus:ring-teal-500" @checked(old('accept_requirements'))>
                <span>I confirm the submitted profile details are correct for account setup.</span>
            </label>
            <x-input-error :messages="$errors->get('accept_requirements')" class="mt-2" />

            <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
                <a class="text-sm font-medium text-slate-600 underline-offset-4 hover:text-slate-900 hover:underline dark:text-slate-400 dark:hover:text-slate-200" href="{{ route('login') }}">
                    {{ __('Already registered? Login') }}
                </a>

                <x-primary-button id="register-submit-btn" class="justify-center rounded-xl bg-teal-600 px-6 py-2.5 text-[0.75rem] hover:bg-teal-700 focus:ring-teal-500">
                    {{ __('Create Account') }}
                </x-primary-button>
            </div>

        </form>
    </div>

    <div id="google-enrollment-modal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="google-enrollment-modal-title">
        <div class="absolute inset-0 bg-slate-900/50"></div>
        <div class="relative mx-auto mt-10 w-[92%] max-w-lg rounded-2xl border border-slate-200 bg-white p-5 sm:mt-16 dark:border-slate-700 dark:bg-slate-900">
            <div class="mb-4">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-600">Course Setup</p>
                <h3 id="google-enrollment-modal-title" class="mt-2 text-lg font-bold text-slate-900 dark:text-slate-100">Finish Your Google Account Setup</h3>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Select your course and level so we can place your account in the correct workflow.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="google_modal_course_id" class="text-sm font-medium text-slate-700 dark:text-slate-300">Course</label>
                    <select id="google_modal_course_id" class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-none focus:border-teal-500 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                        <option value="">Select course</option>
                        @foreach ($courses as $course)
                            @php
                                $isLockedCourse = $course->is_open_enrollment === false;
                                $feesByLevel = method_exists($course, 'getLevelFees') ? $course->getLevelFees() : ['Beginner' => 1500, 'Intermediate' => 1500, 'Advanced' => 1500];
                                $courseFeeOptions = method_exists($course, 'getFeeOptions') ? $course->getFeeOptions() : [];
                            @endphp
                            <option value="{{ $course->id }}"
                                data-requires-payment="{{ ! empty($course->requires_payment_approval) ? '1' : '0' }}"
                                data-payment-message="{{ $course->payment_contact_message }}"
                                data-title="{{ $course->title }}"
                                data-fee="{{ method_exists($course, 'getNumericFee') ? $course->getNumericFee() : 1500 }}"
                                data-fees-by-level="{{ json_encode($feesByLevel) }}"
                                data-fee-options="{{ json_encode($courseFeeOptions) }}"
                                @disabled($isLockedCourse)>
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

            {{-- Dynamic Level Amount Card for Google Modal --}}
            <div id="google-modal-level-amount" class="mt-3 hidden items-center justify-between rounded-xl border border-teal-200 bg-teal-50/90 px-3.5 py-2.5 dark:border-teal-800 dark:bg-teal-950/50">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-tag text-teal-600 text-xs"></i>
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Level Fee:</span>
                    <span id="google-modal-selected-level-pill" class="rounded-md bg-teal-100 px-2 py-0.5 text-[11px] font-bold text-teal-800 dark:bg-teal-900 dark:text-teal-200">Beginner</span>
                </div>
                <span id="google-modal-amount-display" class="text-sm font-black text-teal-800 dark:text-teal-200">ZMW 1,500.00</span>
            </div>

            <div id="google-payment-notice" class="mt-3 hidden rounded-2xl border border-teal-200 bg-teal-50/80 p-4 text-xs text-teal-950 dark:border-teal-800 dark:bg-teal-950/40 dark:text-teal-200">
                <div class="flex items-center justify-between mb-1">
                    <span class="font-bold flex items-center gap-1.5 text-teal-900 dark:text-teal-200">
                        <i class="fa-solid fa-bolt text-amber-500"></i>
                        Interactive Payment Gateway
                    </span>
                    <span class="rounded-full bg-teal-200/60 px-2.5 py-0.5 text-[10px] font-bold uppercase text-teal-900 dark:bg-teal-800 dark:text-teal-100">Instant Activation</span>
                </div>
                <p id="google-payment-notice-message" class="text-slate-600 dark:text-slate-300 leading-relaxed">
                    Complete setup to proceed to Mobile Money (Airtel, MTN, Zamtel) or Card checkout for instant enrollment activation.
                </p>
            </div>

            <div class="mt-4 space-y-3">
                <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50/80 p-3 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300">
                    <input id="google_modal_accept_terms" type="checkbox" class="mt-0.5 rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                    <span>I agree to the Terms and Conditions for platform access.</span>
                </label>

                <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50/80 p-3 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300">
                    <input id="google_modal_accept_requirements" type="checkbox" class="mt-0.5 rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                    <span>I confirm the submitted profile details are correct for account setup.</span>
                </label>
            </div>

            <div class="mt-5 flex items-center justify-end gap-3">
                <button type="button" id="google-enrollment-cancel" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200">Cancel</button>
                <button type="button" id="google-enrollment-submit" class="rounded-xl bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">Complete Setup</button>
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

        (function () {
            var pageCourseSelect = document.getElementById('course_id');
            var pageTrackSelect = document.getElementById('track');
            var pagePaymentNotice = document.getElementById('payment-notice');
            var pagePaymentNoticeMessage = document.getElementById('payment-notice-message');
            var pageLevelAmount = document.getElementById('reg-level-amount');
            var pageLevelPill = document.getElementById('reg-selected-level-pill');
            var pageAmountDisplay = document.getElementById('reg-amount-display');

            var modalCourseSelect = document.getElementById('google_modal_course_id');
            var modalTrackSelect = document.getElementById('google_modal_track');
            var modalPaymentNotice = document.getElementById('google-payment-notice');
            var modalPaymentNoticeMessage = document.getElementById('google-payment-notice-message');
            var modalLevelAmount = document.getElementById('google-modal-level-amount');
            var modalLevelPill = document.getElementById('google-modal-selected-level-pill');
            var modalAmountDisplay = document.getElementById('google-modal-amount-display');

            var pageModeInput = document.getElementById('reg_mode');

            var populateTrackOptions = function (courseSelect, trackSelect, modeInput) {
                if (!courseSelect || !trackSelect) return;
                var opt = courseSelect.options[courseSelect.selectedIndex];
                var currentVal = trackSelect.value || trackSelect.getAttribute('data-initial-value') || '';
                var feeOptions = [];
                var feesByLevel = {};

                if (opt && courseSelect.value) {
                    try {
                        feeOptions = JSON.parse(opt.getAttribute('data-fee-options') || '[]') || [];
                    } catch (e) {
                        feeOptions = [];
                    }
                    try {
                        feesByLevel = JSON.parse(opt.getAttribute('data-fees-by-level') || '{}') || {};
                    } catch (e) {
                        feesByLevel = {};
                    }
                }

                trackSelect.innerHTML = '';
                var placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = 'Select level';
                trackSelect.appendChild(placeholder);

                var items = [];
                if (Array.isArray(feeOptions) && feeOptions.length > 0) {
                    var hasMultipleModes = false;
                    var firstCat = feeOptions[0].category || '';
                    for (var i = 1; i < feeOptions.length; i++) {
                        if ((feeOptions[i].category || '') !== firstCat) {
                            hasMultipleModes = true;
                            break;
                        }
                    }

                    feeOptions.forEach(function (fo) {
                        var amt = parseFloat(fo.amount || 0);
                        var formatted = 'ZMW ' + Number(amt).toLocaleString('en-ZM', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        var labelParts = [fo.level];
                        if (hasMultipleModes && fo.mode_label) {
                            labelParts.push(fo.mode_label);
                        }
                        labelParts.push(formatted + (fo.duration ? ' · ' + fo.duration : ''));
                        items.push({
                            level: fo.level,
                            mode: fo.category || fo.mode || 'group',
                            amount: amt,
                            label: labelParts.join(' — ')
                        });
                    });
                } else {
                    ['Beginner', 'Intermediate', 'Advanced'].forEach(function (lvl) {
                        var amt = feesByLevel[lvl] ? parseFloat(feesByLevel[lvl]) : (opt ? (parseFloat(opt.getAttribute('data-fee') || '0') || 1500) : 0);
                        var formatted = amt > 0 ? ' — ZMW ' + Number(amt).toLocaleString('en-ZM', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '';
                        items.push({
                            level: lvl,
                            mode: 'group',
                            amount: amt,
                            label: lvl + formatted
                        });
                    });
                }

                var matched = false;
                items.forEach(function (item) {
                    var o = document.createElement('option');
                    o.value = item.level;
                    o.setAttribute('data-amount', String(item.amount || 0));
                    o.setAttribute('data-mode', item.mode || 'group');
                    o.textContent = item.label;
                    if (!matched && currentVal && item.level.toLowerCase() === currentVal.toLowerCase()) {
                        o.selected = true;
                        matched = true;
                    }
                    trackSelect.appendChild(o);
                });

                if (!matched && courseSelect.value && items.length > 0) {
                    trackSelect.selectedIndex = 1;
                }

                if (modeInput) {
                    var selOpt = trackSelect.options[trackSelect.selectedIndex];
                    modeInput.value = selOpt ? (selOpt.getAttribute('data-mode') || '') : '';
                }
            };

            window.getFeeForSelection = function (courseSelect, trackSelect) {
                if (!courseSelect || !courseSelect.value) return 0;
                var opt = courseSelect.options[courseSelect.selectedIndex];
                if (!opt) return 0;

                if (trackSelect && trackSelect.selectedIndex >= 0) {
                    var selTrackOpt = trackSelect.options[trackSelect.selectedIndex];
                    if (selTrackOpt && selTrackOpt.hasAttribute('data-amount')) {
                        var directAmt = parseFloat(selTrackOpt.getAttribute('data-amount') || '0');
                        if (directAmt > 0) return directAmt;
                    }
                }

                var track = trackSelect && trackSelect.value ? trackSelect.value : 'Beginner';
                var defaultFee = parseFloat(opt.getAttribute('data-fee') || '0') || 1500;
                try {
                    var map = JSON.parse(opt.getAttribute('data-fees-by-level') || '{}');
                    if (track && map && map[track]) {
                        return parseFloat(map[track]);
                    }
                } catch (e) {}
                return defaultFee;
            };

            var updateNotice = function (courseSelect, trackSelect, noticeEl, messageEl, amountCardEl, pillEl, amountDisplayEl, modeInput) {
                if (!courseSelect || !noticeEl || !messageEl) {
                    return;
                }

                if (modeInput && trackSelect && trackSelect.selectedIndex >= 0) {
                    var selTrackOpt = trackSelect.options[trackSelect.selectedIndex];
                    modeInput.value = selTrackOpt ? (selTrackOpt.getAttribute('data-mode') || '') : '';
                }

                var option = courseSelect.options[courseSelect.selectedIndex];
                var requiresPayment = option && option.getAttribute('data-requires-payment') === '1';
                var courseTitle = option ? option.getAttribute('data-title') || option.textContent.trim() : '';
                var selectedTrack = trackSelect ? trackSelect.value : '';
                var fee = window.getFeeForSelection(courseSelect, trackSelect);
                var formattedFee = 'ZMW ' + Number(fee).toLocaleString('en-ZM', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                if (amountCardEl) {
                    if (courseSelect.value && selectedTrack) {
                        amountCardEl.classList.remove('hidden');
                        amountCardEl.classList.add('flex');
                        if (pillEl) pillEl.textContent = selectedTrack;
                        if (amountDisplayEl) amountDisplayEl.textContent = formattedFee;
                    } else if (courseSelect.value) {
                        amountCardEl.classList.remove('hidden');
                        amountCardEl.classList.add('flex');
                        if (pillEl) pillEl.textContent = 'Standard';
                        if (amountDisplayEl) amountDisplayEl.textContent = formattedFee;
                    } else {
                        amountCardEl.classList.add('hidden');
                        amountCardEl.classList.remove('flex');
                    }
                }

                noticeEl.classList.toggle('hidden', !requiresPayment);

                if (requiresPayment) {
                    var trackSuffix = selectedTrack ? ' (' + selectedTrack + ')' : '';
                    messageEl.textContent = 'Complete setup for ' + (courseTitle ? courseTitle + trackSuffix : 'this course') + ' at ' + formattedFee + ' to proceed to Mobile Money (Airtel, MTN, Zamtel) or Card checkout for instant enrollment activation.';
                }
            };

            if (pageCourseSelect && pagePaymentNotice && pagePaymentNoticeMessage) {
                pageCourseSelect.addEventListener('change', function () {
                    populateTrackOptions(pageCourseSelect, pageTrackSelect, pageModeInput);
                    updateNotice(pageCourseSelect, pageTrackSelect, pagePaymentNotice, pagePaymentNoticeMessage, pageLevelAmount, pageLevelPill, pageAmountDisplay, pageModeInput);
                });
                if (pageTrackSelect) {
                    pageTrackSelect.addEventListener('change', function () {
                        updateNotice(pageCourseSelect, pageTrackSelect, pagePaymentNotice, pagePaymentNoticeMessage, pageLevelAmount, pageLevelPill, pageAmountDisplay, pageModeInput);
                    });
                }
                if (pageCourseSelect.value) {
                    populateTrackOptions(pageCourseSelect, pageTrackSelect, pageModeInput);
                }
                updateNotice(pageCourseSelect, pageTrackSelect, pagePaymentNotice, pagePaymentNoticeMessage, pageLevelAmount, pageLevelPill, pageAmountDisplay, pageModeInput);
            }

            if (modalCourseSelect && modalPaymentNotice && modalPaymentNoticeMessage) {
                modalCourseSelect.addEventListener('change', function () {
                    populateTrackOptions(modalCourseSelect, modalTrackSelect, null);
                    updateNotice(modalCourseSelect, modalTrackSelect, modalPaymentNotice, modalPaymentNoticeMessage, modalLevelAmount, modalLevelPill, modalAmountDisplay, null);
                });
                if (modalTrackSelect) {
                    modalTrackSelect.addEventListener('change', function () {
                        updateNotice(modalCourseSelect, modalTrackSelect, modalPaymentNotice, modalPaymentNoticeMessage, modalLevelAmount, modalLevelPill, modalAmountDisplay, null);
                    });
                }
                if (modalCourseSelect.value) {
                    populateTrackOptions(modalCourseSelect, modalTrackSelect, null);
                }
                updateNotice(modalCourseSelect, modalTrackSelect, modalPaymentNotice, modalPaymentNoticeMessage, modalLevelAmount, modalLevelPill, modalAmountDisplay, null);
            }

            // Account type switcher (Student Learner vs Parent linking child)
            var tabBtnStudent = document.getElementById('tab-btn-student');
            var tabBtnParent = document.getElementById('tab-btn-parent');
            var accountTypeInput = document.getElementById('account_type_input');
            var parentIntroBanner = document.getElementById('parent-intro-banner');
            var parentFieldsWrapper = document.getElementById('parent-fields-wrapper');
            var studentFieldsWrapper = document.getElementById('student-fields-wrapper');
            var kidUniqueCodeInput = document.getElementById('kid_unique_code');
            var relationshipSelect = document.getElementById('relationship');
            var registerSubmitBtn = document.getElementById('register-submit-btn');
            var nameLabel = document.getElementById('name-label');
            var emailLabel = document.getElementById('email-label');

            var activeTabClasses = ['bg-white', 'dark:bg-slate-900', 'text-teal-800', 'dark:text-teal-300', 'shadow-xs', 'font-bold'];
            var inactiveTabClasses = ['text-slate-600', 'dark:text-slate-400', 'font-semibold'];

            function setAccountType(type) {
                if (!accountTypeInput) return;
                accountTypeInput.value = type;

                if (type === 'parent') {
                    if (tabBtnParent && tabBtnStudent) {
                        activeTabClasses.forEach(function (c) { tabBtnParent.classList.add(c); tabBtnStudent.classList.remove(c); });
                        inactiveTabClasses.forEach(function (c) { tabBtnParent.classList.remove(c); tabBtnStudent.classList.add(c); });
                    }
                    if (parentIntroBanner) parentIntroBanner.classList.remove('hidden');
                    if (parentFieldsWrapper) parentFieldsWrapper.classList.remove('hidden');
                    if (studentFieldsWrapper) studentFieldsWrapper.classList.add('hidden');

                    if (pageCourseSelect) pageCourseSelect.removeAttribute('required');
                    if (pageTrackSelect) pageTrackSelect.removeAttribute('required');
                    if (kidUniqueCodeInput) kidUniqueCodeInput.setAttribute('required', 'required');
                    if (relationshipSelect) relationshipSelect.setAttribute('required', 'required');
                    if (registerSubmitBtn) registerSubmitBtn.textContent = 'Create Parent Account & Link Child';
                    if (nameLabel) nameLabel.textContent = 'Parent Full Name';
                    if (emailLabel) emailLabel.textContent = 'Parent Email Address';
                } else {
                    if (tabBtnStudent && tabBtnParent) {
                        activeTabClasses.forEach(function (c) { tabBtnStudent.classList.add(c); tabBtnParent.classList.remove(c); });
                        inactiveTabClasses.forEach(function (c) { tabBtnStudent.classList.remove(c); tabBtnParent.classList.add(c); });
                    }
                    if (parentIntroBanner) parentIntroBanner.classList.add('hidden');
                    if (parentFieldsWrapper) parentFieldsWrapper.classList.add('hidden');
                    if (studentFieldsWrapper) studentFieldsWrapper.classList.remove('hidden');

                    if (pageCourseSelect) pageCourseSelect.setAttribute('required', 'required');
                    if (pageTrackSelect) pageTrackSelect.setAttribute('required', 'required');
                    if (kidUniqueCodeInput) kidUniqueCodeInput.removeAttribute('required');
                    if (relationshipSelect) relationshipSelect.removeAttribute('required');
                    if (registerSubmitBtn) registerSubmitBtn.textContent = 'Create Account';
                    if (nameLabel) nameLabel.textContent = 'Full Name';
                    if (emailLabel) emailLabel.textContent = 'Email Address';
                }
            }

            if (tabBtnStudent) {
                tabBtnStudent.addEventListener('click', function () {
                    setAccountType('student');
                });
            }

            if (tabBtnParent) {
                tabBtnParent.addEventListener('click', function () {
                    setAccountType('parent');
                });
            }

            // Initialize account type state on load
            if (accountTypeInput && accountTypeInput.value === 'parent') {
                setAccountType('parent');
            } else {
                setAccountType('student');
            }

            // Intercept form submission to show checkout modal if paid course
            var registerForm = document.querySelector('form[action="{{ route('register') }}"]');
            if (registerForm) {
                registerForm.addEventListener('submit', function (e) {
                    if (accountTypeInput && accountTypeInput.value === 'parent') {
                        // Parent linking existing child skips payment and course selection completely
                        return;
                    }

                    var selectEl = document.getElementById('course_id');
                    if (!selectEl || selectEl.selectedIndex < 0) return;
                    var option = selectEl.options[selectEl.selectedIndex];
                    var requiresPayment = option && option.getAttribute('data-requires-payment') === '1';

                    if (requiresPayment) {
                        e.preventDefault(); // Stop standard registration

                        // Copy fields to modal
                        document.querySelector('#chk-guest-fields input[name="name"]').value = document.getElementById('name').value;
                        document.querySelector('#chk-guest-fields input[name="email"]').value = document.getElementById('email').value;
                        document.querySelector('#chk-guest-fields input[name="password"]').value = document.getElementById('password').value;
                        document.querySelector('#chk-guest-fields input[name="password_confirmation"]').value = document.getElementById('password_confirmation').value;

                        var courseId = option.value;
                        var courseTitle = option.getAttribute('data-title');
                        var track = document.getElementById('track').value;
                        var fee = window.getFeeForSelection(selectEl, document.getElementById('track'));

                        // Open modal with exact level & fee
                        if (typeof window.openCheckoutModal === 'function') {
                            window.openCheckoutModal(courseId, courseTitle, fee, track);
                        } else {
                            console.error("Checkout modal script not loaded.");
                            registerForm.submit(); // fallback
                        }
                    }
                });
            }
        })();
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
            googleProvider.setCustomParameters({
                prompt: 'select_account consent',
            });

            const buildRegistrationPayload = () => {
                const courseId = document.getElementById('course_id')?.value;
                const track = document.getElementById('track')?.value;
                const acceptTerms = document.querySelector('input[name="accept_terms"]')?.checked;
                const acceptRequirements = document.querySelector('input[name="accept_requirements"]')?.checked;

                if (!courseId || !track || !acceptTerms || !acceptRequirements) {
                    throw new Error('Select course, level, and both confirmations before social sign-in.');
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

                const response = await fetch("{{ route('auth.firebase.google', absolute: false) }}", {
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

                if (result.redirect && result.redirect.includes('/checkout/')) {
                    // Hide the enrollment modal
                    hideEnrollmentModal();

                    // Get course details from the modal select
                    const selectEl = document.getElementById('google_modal_course_id');
                    const trackEl = document.getElementById('google_modal_track');
                    const option = selectEl.options[selectEl.selectedIndex];
                    const courseId = option.value;
                    const courseTitle = option.getAttribute('data-title');
                    const track = trackEl ? trackEl.value : 'Beginner';
                    const fee = typeof window.getFeeForSelection === 'function'
                        ? window.getFeeForSelection(selectEl, trackEl)
                        : (option.getAttribute('data-fee') || 1500);

                    // The user is authenticated on the backend now, so hide guest fields
                    const guestFields = document.getElementById('chk-guest-fields');
                    if (guestFields) guestFields.classList.add('hidden');

                    if (typeof window.openCheckoutModal === 'function') {
                        window.openCheckoutModal(courseId, courseTitle, fee, track);
                    } else {
                        window.location.assign(result.redirect);
                    }
                } else {
                    window.location.assign(result.redirect || "{{ route('dashboard', absolute: false) }}");
                }
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
                    throw new Error('Complete course, level, and both confirmations.');
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

                if (code === 'auth/operation-not-supported-in-this-environment') {
                    return 'Popup sign-in is not supported in this browser context. Redirect sign-in will be used instead.';
                }

                if (code === 'auth/web-storage-unsupported') {
                    return 'This browser environment blocks web storage required for Google sign-in.';
                }

                if (code === 'auth/unauthorized-domain') {
                    return `Google sign-in is not enabled for this domain (${window.location.hostname}). Add it in Firebase Console > Authentication > Settings > Authorized domains.`;
                }

                return error?.message || 'Social sign-in failed.';
            };

            const shouldFallbackToRedirect = (error) => {
                const code = error?.code || '';

                return [
                    'auth/popup-blocked',
                    'auth/popup-closed-by-user',
                    'auth/cancelled-popup-request',
                    'auth/operation-not-supported-in-this-environment',
                    'auth/web-storage-unsupported',
                ].includes(code);
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
                document.getElementById('google_modal_course_id').dispatchEvent(new Event('change'));

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
                    enrollmentSubmitButton.textContent = 'Complete Setup';

                    if ((error?.message || '').includes('Complete setup fields')) {
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
                        if (shouldFallbackToRedirect(error)) {
                            try {
                                feedback.textContent = 'Opening Google sign-in as a full-page redirect...';
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
