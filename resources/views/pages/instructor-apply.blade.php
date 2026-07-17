<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo-meta', [
        'title' => 'Apply as Instructor | think.er HUB',
        'description' => 'Register as a tutor on think.er HUB, publish your course, and manage your learners in one platform.',
        'keywords' => 'tutor application, register a course, manage learners, thinker hub',
        'type' => 'website',
    ])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.pwa-register')
</head>
<body class="bg-[#f8fcf9] text-slate-900 font-sans antialiased" x-data="{ mobileMenu: false }">

    @include('partials.public-header')

    <main>
        <section class="bg-[#0a2d27] relative overflow-hidden py-12 lg:py-16">
            <div class="mx-auto max-w-6xl px-6 lg:px-8 text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-yellow-400">Teach on think.er HUB</p>
                <h1 class="mt-4 text-3xl font-black text-white sm:text-4xl">Apply as an Instructor</h1>
                <p class="mx-auto mt-4 max-w-2xl text-slate-300 text-sm">Share your expertise, launch your course, and manage learners who enroll to upskill.</p>
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
                    <form
                        action="{{ route('landing.instructors.apply.store') }}"
                        method="POST"
                        enctype="multipart/form-data"
                        class="space-y-8"
                        x-data="{ step: 1, proposalType: '{{ old('proposal_type', '') }}', showCurriculumGuide: false }"
                    >
                        @csrf

                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="grid grid-cols-4 gap-3 text-xs font-semibold">
                                <button type="button" @click="step = 1" :class="step >= 1 ? 'bg-[#0a2d27] text-white border-[#0a2d27]' : 'bg-slate-50 text-slate-500 border-slate-200'" class="rounded-xl border px-3 py-2 transition">Profile</button>
                                <button type="button" @click="step = 2" :class="step >= 2 ? 'bg-[#0a2d27] text-white border-[#0a2d27]' : 'bg-slate-50 text-slate-500 border-slate-200'" class="rounded-xl border px-3 py-2 transition">Experience</button>
                                <button type="button" @click="step = 3" :class="step >= 3 ? 'bg-[#0a2d27] text-white border-[#0a2d27]' : 'bg-slate-50 text-slate-500 border-slate-200'" class="rounded-xl border px-3 py-2 transition">Proposal</button>
                                <button type="button" @click="step = 4" :class="step >= 4 ? 'bg-[#0a2d27] text-white border-[#0a2d27]' : 'bg-slate-50 text-slate-500 border-slate-200'" class="rounded-xl border px-3 py-2 transition">Submit</button>
                            </div>
                        </div>

                        <div x-show="step === 1" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                            <h2 class="text-lg font-bold text-slate-900 mb-4"><i class="fa-solid fa-user text-teal-600 mr-2"></i>Instructor Profile Information</h2>
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
                                    <label for="proficiency" class="block text-sm font-semibold text-slate-700 mb-1">Proficiency / Expertise</label>
                                    <input type="text" id="proficiency" name="proficiency" value="{{ old('proficiency') }}" placeholder="e.g. Data Analytics, Web Development" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
                                </div>
                                <div>
                                    <label for="occupation" class="block text-sm font-semibold text-slate-700 mb-1">Occupation</label>
                                    <input type="text" id="occupation" name="occupation" value="{{ old('occupation') }}" placeholder="e.g. Software Engineer" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
                                </div>
                                <div>
                                    <label for="whatsapp" class="block text-sm font-semibold text-slate-700 mb-1">WhatsApp Number</label>
                                    <input type="text" id="whatsapp" name="whatsapp" value="{{ old('whatsapp') }}" placeholder="e.g. 260772640546" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
                                </div>
                                <div>
                                    <label for="phone" class="block text-sm font-semibold text-slate-700 mb-1">Phone Number</label>
                                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
                                </div>
                                <div>
                                    <label for="linkedin_url" class="block text-sm font-semibold text-slate-700 mb-1">LinkedIn URL</label>
                                    <input type="url" id="linkedin_url" name="linkedin_url" value="{{ old('linkedin_url') }}" placeholder="https://linkedin.com/in/..." class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
                                </div>
                                <div>
                                    <label for="facebook_url" class="block text-sm font-semibold text-slate-700 mb-1">Facebook URL</label>
                                    <input type="url" id="facebook_url" name="facebook_url" value="{{ old('facebook_url') }}" placeholder="https://facebook.com/..." class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
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

                        <div x-show="step === 2" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                            <h2 class="text-lg font-bold text-slate-900 mb-4"><i class="fa-solid fa-graduation-cap text-teal-600 mr-2"></i>Qualifications & Experience</h2>
                            <div class="space-y-4">
                                <div>
                                    <label for="qualifications" class="block text-sm font-semibold text-slate-700 mb-1">Qualifications *</label>
                                    <textarea id="qualifications" name="qualifications" rows="5" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="List your degrees, certifications, and training.">{{ old('qualifications') }}</textarea>
                                </div>
                                <div>
                                    <label for="experience" class="block text-sm font-semibold text-slate-700 mb-1">Professional Experience</label>
                                    <textarea id="experience" name="experience" rows="5" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Describe your relevant teaching or industry experience.">{{ old('experience') }}</textarea>
                                </div>
                                <div>
                                    <label for="cv" class="block text-sm font-semibold text-slate-700 mb-1">Upload CV (PDF/DOC)</label>
                                    <input type="file" id="cv" name="cv" accept=".pdf,.doc,.docx" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                                    <p class="mt-1 text-xs text-slate-500">Max 5MB. PDF or Word document.</p>
                                </div>
                            </div>
                        </div>

                        <div x-show="step === 3" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                            <h2 class="text-lg font-bold text-slate-900 mb-4"><i class="fa-solid fa-lightbulb text-teal-600 mr-2"></i>Course Proposal</h2>

                            <div class="mb-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Proposal Type *</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="relative flex items-center justify-center gap-2 rounded-xl border-2 px-4 py-3 cursor-pointer transition-all" :class="proposalType === 'existing' ? 'border-teal-500 bg-teal-50 text-teal-700' : 'border-slate-200 hover:border-slate-300'">
                                        <input type="radio" name="proposal_type" value="existing" x-model="proposalType" class="sr-only">
                                        <i class="fa-solid fa-book text-sm"></i>
                                        <span class="text-sm font-semibold">Existing Course</span>
                                    </label>
                                    <label class="relative flex items-center justify-center gap-2 rounded-xl border-2 px-4 py-3 cursor-pointer transition-all" :class="proposalType === 'new' ? 'border-teal-500 bg-teal-50 text-teal-700' : 'border-slate-200 hover:border-slate-300'">
                                        <input type="radio" name="proposal_type" value="new" x-model="proposalType" class="sr-only">
                                        <i class="fa-solid fa-plus text-sm"></i>
                                        <span class="text-sm font-semibold">New Course</span>
                                    </label>
                                </div>
                                @error('proposal_type') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div x-show="proposalType === 'existing'" class="space-y-4">
                                <div>
                                    <label for="preferred_course_id" class="block text-sm font-semibold text-slate-700 mb-1">Select Course *</label>
                                    <select id="preferred_course_id" name="preferred_course_id" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
                                        <option value="">-- Choose a course --</option>
                                        @foreach ($courses as $id => $title)
                                            <option value="{{ $id }}" {{ old('preferred_course_id') == $id ? 'selected' : '' }}>{{ $title }}</option>
                                        @endforeach
                                    </select>
                                    @error('preferred_course_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="motivation_note" class="block text-sm font-semibold text-slate-700 mb-1">Motivation Note *</label>
                                    <textarea id="motivation_note" name="motivation_note" rows="5" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Why do you want to teach this course?">{{ old('motivation_note') }}</textarea>
                                    @error('motivation_note') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="competence_note" class="block text-sm font-semibold text-slate-700 mb-1">Competence Note *</label>
                                    <textarea id="competence_note" name="competence_note" rows="5" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Why are you qualified to teach this course?">{{ old('competence_note') }}</textarea>
                                    @error('competence_note') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="roadmap" class="block text-sm font-semibold text-slate-700 mb-1">Course Roadmap (PDF, max 2 pages) *</label>
                                    <input type="file" id="roadmap" name="roadmap" accept=".pdf" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                                    <p class="mt-1 text-xs text-slate-500">Max 5MB.</p>
                                    @error('roadmap') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div x-show="proposalType === 'new'" class="space-y-4">
                                <div class="rounded-xl border border-teal-200 bg-teal-50 p-4 text-sm text-teal-900">
                                    <p class="font-semibold">New course proposals must match our course build structure.</p>
                                    <button type="button" @click="showCurriculumGuide = true" class="mt-2 inline-flex items-center rounded-full border border-teal-300 px-3 py-1.5 text-xs font-semibold text-teal-900 hover:bg-teal-100">View curriculum structure guide</button>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="proposed_course_name" class="block text-sm font-semibold text-slate-700 mb-1">Course Title *</label>
                                        <input type="text" id="proposed_course_name" name="proposed_course_name" value="{{ old('proposed_course_name') }}" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="e.g. Advanced Data Science with Python">
                                        @error('proposed_course_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label for="proposed_course_code" class="block text-sm font-semibold text-slate-700 mb-1">Course Code *</label>
                                        <input type="text" id="proposed_course_code" name="proposed_course_code" value="{{ old('proposed_course_code') }}" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="e.g. DS-401">
                                        @error('proposed_course_code') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <div>
                                    <label for="proposed_course_description" class="block text-sm font-semibold text-slate-700 mb-1">Description *</label>
                                    <textarea id="proposed_course_description" name="proposed_course_description" rows="4" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Describe the course in detail.">{{ old('proposed_course_description') }}</textarea>
                                    @error('proposed_course_description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="proposed_course_overview" class="block text-sm font-semibold text-slate-700 mb-1">Overview *</label>
                                    <textarea id="proposed_course_overview" name="proposed_course_overview" rows="4" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="High-level introduction shown on course details.">{{ old('proposed_course_overview') }}</textarea>
                                    @error('proposed_course_overview') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="proposed_course_timeline" class="block text-sm font-semibold text-slate-700 mb-1">Timeline *</label>
                                        <input type="text" id="proposed_course_timeline" name="proposed_course_timeline" value="{{ old('proposed_course_timeline') }}" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="e.g. 4 Weeks (approx. 4-5 hours per week)">
                                        @error('proposed_course_timeline') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label for="teaching_location" class="block text-sm font-semibold text-slate-700 mb-1">Teaching Location *</label>
                                        <input type="text" id="teaching_location" name="teaching_location" value="{{ old('teaching_location') }}" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="e.g. Online, Lusaka, Hybrid">
                                        @error('teaching_location') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <div>
                                    <label for="proposed_course_fees" class="block text-sm font-semibold text-slate-700 mb-1">Fees Structure *</label>
                                    <textarea id="proposed_course_fees" name="proposed_course_fees" rows="4" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Use one entry per line: Category + Level + Amount + Duration&#10;Example: One-On-One + Beginner + K450 + 6 Weeks">{{ old('proposed_course_fees') }}</textarea>
                                    @error('proposed_course_fees') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="proposed_course_requirements" class="block text-sm font-semibold text-slate-700 mb-1">Requirements *</label>
                                    <textarea id="proposed_course_requirements" name="proposed_course_requirements" rows="4" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Add each requirement on a new line.">{{ old('proposed_course_requirements') }}</textarea>
                                    @error('proposed_course_requirements') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="proposed_course_level_progression" class="block text-sm font-semibold text-slate-700 mb-1">Level Progression *</label>
                                    <textarea id="proposed_course_level_progression" name="proposed_course_level_progression" rows="4" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Use one entry per line: Level + Description&#10;Example: Beginner + Foundations and setup">{{ old('proposed_course_level_progression') }}</textarea>
                                    @error('proposed_course_level_progression') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="proposed_course_key_outcome" class="block text-sm font-semibold text-slate-700 mb-1">Key Outcome *</label>
                                    <textarea id="proposed_course_key_outcome" name="proposed_course_key_outcome" rows="3" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Summarize the expected learning outcome.">{{ old('proposed_course_key_outcome') }}</textarea>
                                    @error('proposed_course_key_outcome') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="full_roadmap" class="block text-sm font-semibold text-slate-700 mb-1">Full Course Roadmap (PDF) *</label>
                                        <input type="file" id="full_roadmap" name="full_roadmap" accept=".pdf" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                                        @error('full_roadmap') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label for="curriculum" class="block text-sm font-semibold text-slate-700 mb-1">Detailed Curriculum (PDF) *</label>
                                        <input type="file" id="curriculum" name="curriculum" accept=".pdf" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                                        @error('curriculum') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                    <input type="checkbox" name="proposed_course_is_open_enrollment" value="1" class="mt-0.5 rounded border-slate-300 text-teal-600 focus:ring-teal-500" {{ old('proposed_course_is_open_enrollment', '1') ? 'checked' : '' }}>
                                    <span class="text-sm text-slate-700">
                                        <span class="font-semibold text-slate-900">Open For Public Enrollment</span><br>
                                        Turn off only if this course should be private until selected learners are added.
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div x-show="step === 4" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 text-center">
                            <h2 class="text-lg font-bold text-slate-900">Review & Submit</h2>
                            <p class="mt-2 text-sm text-slate-600">Confirm your details across profile, qualifications, and course proposal before submitting.</p>
                            <p class="mt-2 text-xs text-slate-500">On approval, your instructor account will be activated, your profile will be listed publicly, and approved course assignment will be made public.</p>
                        </div>

                        <div class="flex items-center justify-between">
                            <button
                                type="button"
                                @click="step = Math.max(1, step - 1)"
                                x-show="step > 1"
                                class="rounded-full border border-slate-300 px-6 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition"
                            >
                                Back
                            </button>

                            <div class="ml-auto flex items-center gap-3">
                                <button
                                    type="button"
                                    @click="step = Math.min(4, step + 1)"
                                    x-show="step < 4"
                                    class="rounded-full bg-[#0a2d27] px-8 py-3 text-sm font-bold text-white hover:bg-[#11443c] transition-all"
                                >
                                    Continue
                                </button>

                                <button
                                    type="submit"
                                    x-show="step === 4"
                                    class="rounded-full bg-[#0a2d27] px-10 py-3.5 text-sm font-bold text-white hover:bg-[#11443c] transition-all shadow-lg"
                                >
                                    <i class="fa-solid fa-paper-plane mr-2"></i>Submit Application
                                </button>
                            </div>
                        </div>

                        <div x-show="showCurriculumGuide" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/55 px-4" style="display: none;">
                            <div @click.outside="showCurriculumGuide = false" class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl">
                                <div class="flex items-start justify-between border-b border-slate-200 px-6 py-4">
                                    <h3 class="text-lg font-bold text-slate-900">New Course Structure Guide</h3>
                                    <button type="button" @click="showCurriculumGuide = false" class="text-slate-500 hover:text-slate-700"><i class="fa-solid fa-xmark"></i></button>
                                </div>
                                <div class="space-y-4 px-6 py-5 text-sm text-slate-700">
                                    <p class="font-semibold text-slate-900">Use this order to structure your proposal:</p>
                                    <ol class="list-decimal pl-5 space-y-2">
                                        <li>Course title and code</li>
                                        <li>Description and overview</li>
                                        <li>Timeline and teaching location</li>
                                        <li>Fees entries in this format: Category + Level + Amount + Duration</li>
                                        <li>Requirements listed line-by-line</li>
                                        <li>Level progression listed line-by-line: Level + Description</li>
                                        <li>Key outcome statement for completion</li>
                                        <li>Attach full roadmap and curriculum PDFs</li>
                                    </ol>
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs text-slate-600">
                                        Example fee line: One-On-One + Beginner + K450 + 6 Weeks
                                        <br>
                                        Example progression line: Intermediate + Build real projects and deploy solutions
                                    </div>
                                </div>
                                <div class="border-t border-slate-200 px-6 py-4 text-right">
                                    <button type="button" @click="showCurriculumGuide = false" class="rounded-full bg-[#0a2d27] px-5 py-2 text-xs font-semibold text-white">Understood</button>
                                </div>
                            </div>
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

</body>
</html>
