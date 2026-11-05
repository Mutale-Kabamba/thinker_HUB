<footer class="bg-white border-t border-slate-200 py-12 lg:py-14">
    <div class="mx-auto max-w-6xl px-6 lg:px-8 text-center lg:text-left">
        <div class="grid gap-10 lg:grid-cols-[1.4fr_1fr_1fr]">
            <div>
                <div class="flex items-center justify-center gap-3 lg:justify-start">
                    <img src="{{ asset('images/logos/green.png') }}" alt="think.er HUB logo" class="h-8 w-auto">
                </div>
                <p class="mt-4 max-w-sm text-sm leading-relaxed text-slate-500 mx-auto lg:mx-0">
                    think.er HUB is a course platform where tutors can launch and manage courses, and learners can enroll to upskill through curated training.
                </p>
                <div class="mt-5 flex flex-wrap items-center justify-center gap-3 text-sm text-slate-500 lg:justify-start">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 rounded-full bg-[#0a2d27] px-5 py-2 text-xs font-semibold text-white transition hover:bg-[#11443c]">
                            <i class="fa-solid fa-gauge-high text-[10px] text-teal-300"></i> Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center rounded-full bg-[#0a2d27] px-5 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-[#11443c]">Login</a>
                    @endauth
                </div>
            </div>

            <div class="hidden lg:block">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">Quick Links</h3>
                <ul class="mt-4 space-y-2 text-sm text-slate-500">
                    <li><a href="{{ route('home') }}" class="transition hover:text-[#0a2d27]">Home</a></li>
                    <li><a href="{{ route('landing.courses') }}" class="transition hover:text-[#0a2d27]">Courses</a></li>
                    <li><a href="{{ route('hub.index') }}" class="transition hover:text-[#0a2d27]">Knowledge Hub</a></li>
                    <li><a href="{{ route('landing.instructors') }}" class="transition hover:text-[#0a2d27]">Network</a></li>
                    <li><a href="{{ route('landing.contact') }}" class="transition hover:text-[#0a2d27]">Contact</a></li>
                    @auth
                        <li><a href="{{ route('dashboard') }}" class="transition hover:text-[#0a2d27]">Dashboard</a></li>
                    @else
                        <li><a href="{{ route('login') }}" class="transition hover:text-[#0a2d27]">Login</a></li>
                    @endauth
                </ul>
            </div>

            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">Contacts</h3>
                <div class="mt-4 space-y-2 text-sm text-slate-500">
                    <p><span class="font-semibold text-slate-700">Phone:</span> <button type="button" @click="$dispatch('open-contact')" class="ml-1 text-[#0a2d27] font-medium underline-offset-2 hover:underline cursor-pointer">+260772640546</button></p>
                    <p><span class="font-semibold text-slate-700">Email:</span> <a href="mailto:thinkerhub@oristudiozm.com" class="text-[#0a2d27] underline-offset-2 hover:underline">thinkerhub@oristudiozm.com</a></p>
                    <p><span class="font-semibold text-slate-700">Address:</span> 10A Off Natwange Street, Airport, Livingstone, Zambia</p>
                </div>
                <div class="mt-4 flex items-center justify-center gap-3 text-slate-500 lg:justify-start">
                    <a href="#" class="h-8 w-8 rounded-full border border-slate-200 flex items-center justify-center transition hover:border-[#0a2d27] hover:text-[#0a2d27]" aria-label="Facebook"><i class="fa-brands fa-facebook-f text-xs"></i></a>
                    <a href="#" class="h-8 w-8 rounded-full border border-slate-200 flex items-center justify-center transition hover:border-[#0a2d27] hover:text-[#0a2d27]" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in text-xs"></i></a>
                    <a href="#" class="h-8 w-8 rounded-full border border-slate-200 flex items-center justify-center transition hover:border-[#0a2d27] hover:text-[#0a2d27]" aria-label="YouTube"><i class="fa-brands fa-youtube text-xs"></i></a>
                </div>
            </div>
        </div>

        <div class="mt-8 border-t border-slate-200 pt-5">
            <div class="flex flex-col items-center gap-4 text-center text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:text-left">
                <p>© {{ now()->year }} Thinker Hub. All rights reserved.</p>
                <div class="flex flex-wrap items-center gap-4">
                    <a href="{{ route('landing.privacy') }}" class="underline-offset-4 hover:text-slate-700 hover:underline">Privacy</a>
                    <a href="{{ route('landing.cookies') }}" class="underline-offset-4 hover:text-slate-700 hover:underline">Cookies</a>
                    <a href="{{ route('landing.terms') }}" class="underline-offset-4 hover:text-slate-700 hover:underline">T&amp;Cs</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<livewire:ai.public-chat-widget />

