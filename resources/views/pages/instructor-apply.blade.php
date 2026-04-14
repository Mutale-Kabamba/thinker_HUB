<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo-meta', [
        'title' => 'Apply as Instructor | think.er HUB',
        'description' => 'Join think.er HUB as an instructor. Share your expertise and guide learners through practical, project-based training.',
        'keywords' => 'instructor application, teach, mentor, thinker hub',
        'type' => 'website',
    ])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.pwa-register')
</head>
<body class="bg-[#f8fcf9] text-slate-900 font-sans antialiased" x-data="{ mobileMenu: false }">

    <header class="sticky top-0 z-50 bg-[#0a2d27] py-4 shadow-lg">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-bold text-white shrink-0">
                <img src="{{ asset('images/logos/yellow_white.png') }}" alt="think.er HUB logo" class="h-8 w-auto">
            </a>

            <nav class="hidden md:flex items-center gap-10 text-[13px] font-semibold uppercase tracking-wider text-slate-300">
                <a href="{{ route('home') }}" class="hover:text-yellow-400 transition-colors">Home</a>
                <a href="{{ route('landing.courses') }}" class="hover:text-yellow-400 transition-colors">Courses</a>
                <a href="{{ route('landing.instructors') }}" class="text-yellow-400">Instructors</a>
                <a href="{{ route('landing.contact') }}" class="hover:text-yellow-400 transition-colors">Contact</a>
            </nav>

            <div class="hidden md:flex items-center gap-6">
                <a href="{{ route('login') }}" class="text-sm font-bold text-white hover:text-yellow-400">Login</a>
                <a href="{{ route('enroll') }}" class="rounded-full bg-yellow-400 px-6 py-2.5 text-sm font-bold text-[#0a2d27] hover:bg-white transition-all">Enroll Now</a>
            </div>

            <button class="md:hidden text-white text-2xl" @click="mobileMenu = !mobileMenu">
                <i class="fa-solid" :class="mobileMenu ? 'fa-xmark' : 'fa-bars-staggered'"></i>
            </button>
        </div>

        <div class="md:hidden bg-[#0a2d27] border-t border-white/10" x-show="mobileMenu" x-transition>
            <nav class="flex flex-col p-6 gap-4 text-white font-semibold">
                <a href="{{ route('home') }}">Home</a>
                <a href="{{ route('landing.courses') }}">Courses</a>
                <a href="{{ route('landing.instructors') }}" class="text-yellow-400">Instructors</a>
                <a href="{{ route('landing.contact') }}">Contact</a>
            </nav>
        </div>
    </header>

    <main>
        <section class="bg-[#0a2d27] relative overflow-hidden py-12 lg:py-16">
            <div class="mx-auto max-w-6xl px-6 lg:px-8 text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-yellow-400">Join Our Team</p>
                <h1 class="mt-4 text-3xl font-black text-white sm:text-4xl">Apply as an Instructor</h1>
                <p class="mx-auto mt-4 max-w-2xl text-slate-300 text-sm">Share your expertise and help shape the next generation of tech professionals.</p>
            </div>
        </section>

        <section class="py-12 lg:py-16">
            <div class="mx-auto max-w-3xl px-6 lg:px-8">

                @if (session('success'))
                    <div class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-200 p-6 text-center">
                        <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-check text-emerald-600 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-emerald-800">Application Submitted!</h3>
                        <p class="mt-2 text-sm text-emerald-700">{{ session('success') }}</p>
                        <a href="{{ route('landing.instructors') }}" class="mt-4 inline-block rounded-full bg-[#0a2d27] px-6 py-2 text-sm font-bold text-white">Back to Instructors</a>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="mb-6 rounded-2xl bg-amber-50 border border-amber-200 p-6 text-center">
                        <p class="text-sm font-semibold text-amber-800">{{ session('warning') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-2xl bg-red-50 border border-red-200 p-4">
                        <p class="font-semibold text-red-800 text-sm mb-2">Please fix the following errors:</p>
                        <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (!session('success'))
                    <form action="{{ route('landing.instructors.apply.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                        @csrf

                        {{-- Personal Information --}}
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                            <h2 class="text-lg font-bold text-slate-900 mb-4"><i class="fa-solid fa-user text-teal-600 mr-2"></i>Personal Information</h2>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-1">Full Name *</label>
                                    <input type="text" id="name" name="name" value="{{ old('name') }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">Email Address *</label>
                                    <input type="email" id="email" name="email" value="{{ old('email') }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
                                </div>
                                <div>
                                    <label for="phone" class="block text-sm font-semibold text-slate-700 mb-1">Phone Number</label>
                                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
                                </div>
                                <div>
                                    <label for="linkedin_url" class="block text-sm font-semibold text-slate-700 mb-1">LinkedIn Profile</label>
                                    <input type="url" id="linkedin_url" name="linkedin_url" value="{{ old('linkedin_url') }}" placeholder="https://linkedin.com/in/..." class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="portfolio_url" class="block text-sm font-semibold text-slate-700 mb-1">Portfolio / Website</label>
                                    <input type="url" id="portfolio_url" name="portfolio_url" value="{{ old('portfolio_url') }}" placeholder="https://..." class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="bio" class="block text-sm font-semibold text-slate-700 mb-1">Short Bio</label>
                                    <textarea id="bio" name="bio" rows="3" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Tell us about yourself...">{{ old('bio') }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Qualifications --}}
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                            <h2 class="text-lg font-bold text-slate-900 mb-4"><i class="fa-solid fa-graduation-cap text-teal-600 mr-2"></i>Qualifications & Experience</h2>
                            <div class="space-y-4">
                                <div>
                                    <label for="qualifications" class="block text-sm font-semibold text-slate-700 mb-1">Qualifications *</label>
                                    <textarea id="qualifications" name="qualifications" rows="4" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="List your academic qualifications, certifications, degrees...">{{ old('qualifications') }}</textarea>
                                </div>
                                <div>
                                    <label for="experience" class="block text-sm font-semibold text-slate-700 mb-1">Professional Experience</label>
                                    <textarea id="experience" name="experience" rows="4" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Describe your relevant professional experience...">{{ old('experience') }}</textarea>
                                </div>
                                <div>
                                    <label for="cv" class="block text-sm font-semibold text-slate-700 mb-1">Upload CV (PDF/DOC)</label>
                                    <input type="file" id="cv" name="cv" accept=".pdf,.doc,.docx" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                                    <p class="mt-1 text-xs text-slate-500">Max 5MB. PDF or Word document.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Course Proposal --}}
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                            <h2 class="text-lg font-bold text-slate-900 mb-4"><i class="fa-solid fa-lightbulb text-teal-600 mr-2"></i>Course Proposal</h2>
                            <div class="space-y-4">
                                <div>
                                    <label for="preferred_course_id" class="block text-sm font-semibold text-slate-700 mb-1">Preferred Course (if existing)</label>
                                    <select id="preferred_course_id" name="preferred_course_id" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
                                        <option value="">-- Select or propose a new course --</option>
                                        @foreach ($courses as $id => $title)
                                            <option value="{{ $id }}" {{ old('preferred_course_id') == $id ? 'selected' : '' }}>{{ $title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="course_concept_note" class="block text-sm font-semibold text-slate-700 mb-1">Course Concept Note / Roadmap *</label>
                                    <textarea id="course_concept_note" name="course_concept_note" rows="6" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Describe your course concept, learning objectives, target audience, and teaching methodology...">{{ old('course_concept_note') }}</textarea>
                                </div>
                                <div>
                                    <label for="proposed_curriculum" class="block text-sm font-semibold text-slate-700 mb-1">Proposed Curriculum *</label>
                                    <textarea id="proposed_curriculum" name="proposed_curriculum" rows="6" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Outline the weekly curriculum (Week 1: ..., Week 2: ...) or module breakdown...">{{ old('proposed_curriculum') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="rounded-full bg-[#0a2d27] px-10 py-3.5 text-sm font-bold text-white hover:bg-[#11443c] transition-all shadow-lg">
                                <i class="fa-solid fa-paper-plane mr-2"></i>Submit Application
                            </button>
                            <p class="mt-3 text-xs text-slate-500">Your application will be reviewed by our admin team.</p>
                        </div>
                    </form>
                @endif
            </div>
        </section>
    </main>

    <footer class="bg-white border-t border-slate-200 py-12">
        <div class="mx-auto max-w-6xl px-6 lg:px-8 text-center">
            <p class="text-xs text-slate-500">© {{ now()->year }} Thinker Hub. All rights reserved.</p>
        </div>
    </footer>

    @include('partials.legal-modals')
</body>
</html>
