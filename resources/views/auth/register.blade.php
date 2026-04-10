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
                            <option value="{{ $course->id }}" @selected((string) old('course_id') === (string) $course->id)>
                                {{ $course->code }} - {{ $course->title }}
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
</x-guest-layout>
