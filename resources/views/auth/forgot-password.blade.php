<x-guest-layout>
    <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900">
        <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-600">Password Recovery</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-100">Forgot Password</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Enter your email address and we will send you a password reset link.</p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <div>
                <x-input-label for="email" class="text-slate-700 dark:text-slate-300" :value="__('Email Address')" />
                <x-text-input id="email" class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-none focus:border-teal-500 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 underline-offset-4 hover:text-slate-900 hover:underline dark:text-slate-400 dark:hover:text-slate-200">
                    Back to login
                </a>

                <x-primary-button class="justify-center rounded-xl bg-teal-600 px-6 py-2.5 text-[0.75rem] hover:bg-teal-700 focus:ring-teal-500">
                    {{ __('Email Password Reset Link') }}
                </x-primary-button>
            </div>
        </form>

        <p class="mt-5 text-xs text-slate-500 dark:text-slate-400">
            Reset links expire automatically for security.
        </p>
    </div>
</x-guest-layout>
