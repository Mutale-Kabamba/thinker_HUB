<x-guest-layout>
    <div class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-600">Email Verification</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-100">Verify Your Email Address</h1>
        <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">
            {{ __('Thanks for signing up. Before getting started, verify your email address using the link we sent. If you did not receive it, you can request a new one below.') }}
        </p>

        @if (session('status') == 'verification-link-sent')
            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300">
                {{ __('A new verification link has been sent to your email address.') }}
            </div>
        @endif

        @if ($errors->has('email'))
            <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-300">
                {{ $errors->first('email') }}
            </div>
        @endif

        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-primary-button class="justify-center rounded-xl bg-teal-600 px-6 py-2.5 text-[0.75rem] hover:bg-teal-700 focus:ring-teal-500">
                    {{ __('Resend Verification Email') }}
                </x-primary-button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
