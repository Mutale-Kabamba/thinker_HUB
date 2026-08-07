<div class="hub-public bg-[#f8fcf9] text-slate-900 font-sans antialiased min-h-screen">
    @include('partials.public-header')

    <main>
        {{-- Post Header Hero --}}
        <section class="bg-[#0a2d27] relative overflow-hidden py-12 lg:py-16 text-white">
            <div class="mx-auto max-w-4xl px-6 lg:px-8 relative z-10">
                <a
                    href="{{ route('hub.index') }}"
                    class="inline-flex items-center gap-2 text-xs font-bold text-teal-300 hover:text-yellow-400 transition-colors mb-6 uppercase tracking-wider"
                >
                    <i class="fa-solid fa-arrow-left"></i> Back to Knowledge &amp; Opportunities Hub
                </a>

                <div class="flex flex-wrap items-center gap-3 mb-4">
                    @if ($post->type === 'video')
                        <span class="bg-rose-600 text-white text-[11px] font-bold uppercase tracking-wider px-3.5 py-1 rounded-full shadow-sm">
                            <i class="fa-solid fa-video mr-1"></i> Video Tutorial
                        </span>
                    @elseif ($post->type === 'opportunity')
                        <span class="bg-emerald-600 text-white text-[11px] font-bold uppercase tracking-wider px-3.5 py-1 rounded-full shadow-sm">
                            <i class="fa-solid fa-briefcase mr-1"></i> {{ $post->extra['opportunity_type'] ?? 'Opportunity' }}
                        </span>
                    @elseif ($post->type === 'tip_trick')
                        <span class="bg-teal-600 text-white text-[11px] font-bold uppercase tracking-wider px-3.5 py-1 rounded-full shadow-sm">
                            <i class="fa-solid fa-wand-magic-sparkles mr-1"></i> Tip &amp; Trick
                        </span>
                    @else
                        <span class="bg-indigo-600 text-white text-[11px] font-bold uppercase tracking-wider px-3.5 py-1 rounded-full shadow-sm">
                            <i class="fa-solid fa-newspaper mr-1"></i> Short Blog
                        </span>
                    @endif

                    <span class="text-xs font-semibold text-slate-300 bg-white/10 px-3.5 py-1 rounded-full border border-white/10">
                        {{ $post->category }}
                    </span>

                    @if ($post->type === 'opportunity' && $post->opportunity_deadline)
                        @php
                            $isPast = $post->opportunity_deadline->isPast() && ! $post->opportunity_deadline->isToday();
                        @endphp
                        @if ($isPast)
                            <span class="bg-slate-700 text-slate-300 text-xs font-bold px-3 py-1 rounded-full">
                                Closed
                            </span>
                        @else
                            <span class="bg-amber-400 text-[#0a2d27] text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1">
                                <i class="fa-regular fa-clock"></i> Deadline: {{ $post->opportunity_deadline->format('M j, Y') }}
                            </span>
                        @endif
                    @elseif ($post->type !== 'opportunity')
                        <span class="text-xs font-medium text-slate-300 flex items-center gap-1">
                            <i class="fa-regular fa-clock"></i> {{ $post->reading_time }} min read
                        </span>
                    @endif
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white leading-tight tracking-tight">
                    {{ $post->title }}
                </h1>

                <div class="mt-6 flex items-center gap-4 text-xs text-slate-300 border-t border-white/10 pt-6">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-full bg-teal-100 text-teal-800 font-bold text-sm flex items-center justify-center border border-white/20">
                            {{ strtoupper(substr($post->author->name ?? 'T', 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-bold text-white text-sm">{{ $post->author->name ?? 'Thinker HUB Team' }}</p>
                            <p class="text-[11px] text-slate-400">Published {{ $post->created_at->format('F j, Y') }} ({{ $post->created_at->diffForHumans() }})</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="absolute top-0 right-0 w-96 h-96 bg-teal-500/10 rounded-full blur-3xl -mr-32 -mt-32 pointer-events-none"></div>
        </section>

        {{-- Main Article Content Section --}}
        <section class="py-12 lg:py-16">
            <div class="mx-auto max-w-4xl px-6 lg:px-8">

                {{-- Video Player Container (For Video Posts) --}}
                @if ($post->type === 'video')
                    <div class="mb-10 rounded-[2.5rem] overflow-hidden shadow-2xl bg-black border border-slate-800 relative aspect-video">
                        @if ($post->video_id)
                            <iframe
                                src="https://www.youtube-nocookie.com/embed/{{ $post->video_id }}?autoplay=0"
                                title="{{ $post->title }}"
                                class="w-full h-full"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                            ></iframe>
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center text-slate-400 p-8 text-center">
                                <i class="fa-solid fa-triangle-exclamation text-4xl text-amber-400 mb-3"></i>
                                <p class="font-semibold text-white">Video preview unavailable</p>
                                <p class="text-xs mt-1">Watch directly on YouTube: <a href="{{ $post->youtube_url }}" target="_blank" class="text-teal-400 underline">{{ $post->youtube_url }}</a></p>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Opportunity Structured Data Table Header --}}
                @if ($post->type === 'opportunity')
                    <div class="mb-10 bg-white rounded-[2rem] p-6 border border-emerald-100 shadow-sm grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Host / Organization</p>
                            <p class="text-sm font-bold text-emerald-800 mt-1">
                                {{ $post->extra['provider'] ?? ($post->author->name ?? 'Thinker HUB') }}
                            </p>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Location</p>
                            <p class="text-sm font-bold text-slate-800 mt-1">
                                <i class="fa-solid fa-location-dot text-emerald-600 mr-1"></i>
                                {{ $post->extra['location'] ?? 'Remote' }}
                            </p>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Compensation / Prize</p>
                            <p class="text-sm font-bold text-slate-800 mt-1">
                                <i class="fa-solid fa-coins text-amber-500 mr-1"></i>
                                {{ $post->extra['compensation'] ?? 'Competitive' }}
                            </p>
                        </div>
                    </div>
                @endif

                {{-- Excerpt / Core Summary Box --}}
                @if ($post->excerpt)
                    <div class="mb-8 p-6 bg-teal-50/80 border-l-4 border-teal-600 rounded-r-3xl text-slate-800 text-base sm:text-lg font-medium leading-relaxed shadow-2xs">
                        {{ $post->excerpt }}
                    </div>
                @endif

                {{-- Code Snippet Box (For Tips & Tricks) --}}
                @if ($post->code_snippet)
                    <div class="mb-8 rounded-2xl bg-slate-900 p-5 text-teal-300 font-mono text-sm shadow-xl border border-slate-800 overflow-x-auto">
                        <div class="flex items-center justify-between text-xs text-slate-400 mb-3 pb-2 border-b border-slate-800">
                            <span class="flex items-center gap-1.5 font-bold text-teal-400">
                                <i class="fa-solid fa-code"></i> Code Snippet
                            </span>
                            <span class="text-[11px] bg-slate-800 px-2.5 py-0.5 rounded text-slate-300">Syntax Preview</span>
                        </div>
                        <pre class="overflow-x-auto"><code>{{ $post->code_snippet }}</code></pre>
                    </div>
                @endif

                {{-- Pro Tip Callout Box (For Tips & Tricks) --}}
                @if ($post->pro_tip)
                    <div class="mb-8 rounded-2xl bg-amber-50 p-5 border border-amber-200 text-amber-900 text-sm flex items-start gap-3 shadow-xs">
                        <i class="fa-solid fa-lightbulb text-amber-500 text-xl mt-0.5 shrink-0"></i>
                        <div>
                            <h4 class="font-bold text-amber-950 uppercase tracking-wider text-xs mb-1">Pro Tip &amp; Best Practice</h4>
                            <p class="leading-relaxed font-medium">{{ $post->pro_tip }}</p>
                        </div>
                    </div>
                @endif

                {{-- Formatted Body Content --}}
                @if ($post->content)
                    <div class="prose prose-lg max-w-none text-slate-800 leading-relaxed space-y-6">
                        {!! $post->content !!}
                    </div>
                @endif

                {{-- Media Attachments Section --}}
                @if ($post->media && $post->media->isNotEmpty())
                    <div class="mt-12 p-6 sm:p-8 rounded-[2rem] bg-white border border-slate-200 shadow-sm">
                        <div class="flex items-center gap-2 mb-4 border-b border-slate-100 pb-4">
                            <i class="fa-solid fa-folder-closed text-teal-700 text-xl"></i>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">Downloadable Media &amp; Resources</h3>
                                <p class="text-xs text-slate-500">Attached files, slide decks, rulebooks, or cheat sheets</p>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ($post->media as $item)
                                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100/80 transition-colors flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 truncate">
                                        <i class="{{ $item->file_icon }} text-2xl shrink-0"></i>
                                        <div class="truncate">
                                            <p class="text-xs font-bold text-slate-900 truncate" title="{{ $item->original_name }}">
                                                {{ $item->original_name }}
                                            </p>
                                            <p class="text-[11px] text-slate-500 mt-0.5">
                                                {{ $item->formatted_size }}
                                            </p>
                                        </div>
                                    </div>

                                    <a
                                        href="{{ route('media.download', $item->id) }}"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-[#0a2d27] px-4 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-[#11443c] shrink-0 transition"
                                    >
                                        <i class="fa-solid fa-download text-[10px]"></i> Download
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Opportunity Action Container --}}
                @if ($post->type === 'opportunity')
                    <div class="mt-12 p-8 rounded-[2.5rem] bg-gradient-to-br from-emerald-900 to-[#0a2d27] text-white shadow-xl flex flex-col sm:flex-row items-center justify-between gap-6 border border-emerald-800">
                        <div>
                            <span class="inline-block bg-emerald-500/20 text-emerald-300 text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-full mb-2 border border-emerald-400/20">
                                Opportunity Action
                            </span>
                            <h3 class="text-xl font-bold text-white">Ready to apply for this opportunity?</h3>
                            @if ($post->opportunity_deadline)
                                <p class="text-xs text-slate-300 mt-1">
                                    <i class="fa-regular fa-calendar-check text-yellow-400 mr-1"></i>
                                    Application Deadline: <strong class="text-white">{{ $post->opportunity_deadline->format('F j, Y') }}</strong>
                                </p>
                            @endif
                        </div>

                        <div>
                            @if ($post->opportunity_link)
                                <a
                                    href="{{ $post->opportunity_link }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-2 rounded-full bg-yellow-400 px-8 py-3.5 text-sm font-bold text-[#0a2d27] shadow-lg hover:bg-white transition-all transform hover:-translate-y-0.5 shrink-0"
                                >
                                    Apply / Access Opportunity <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                </a>
                            @else
                                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-800/80 px-6 py-3 text-xs font-semibold text-emerald-200">
                                    <i class="fa-solid fa-building-columns"></i> Thinker HUB Opportunity
                                </span>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Author Bio Card --}}
                <div class="mt-12 p-6 rounded-[2rem] bg-slate-50 border border-slate-200/80 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-[#0a2d27] text-white font-bold text-lg flex items-center justify-center shrink-0 shadow-md">
                        {{ strtoupper(substr($post->author->name ?? 'T', 0, 1)) }}
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">Written by {{ $post->author->name ?? 'Thinker HUB Mentor' }}</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Contributing practical insights and opportunities to the Thinker HUB learning community.</p>
                    </div>
                </div>

                {{-- Related Posts Grid --}}
                @if ($relatedPosts->count() > 0)
                    <div class="mt-16 pt-12 border-t border-slate-200">
                        <div class="flex items-center justify-between mb-8">
                            <h3 class="text-xl font-bold text-slate-900">More Resources &amp; Insights</h3>
                            <a href="{{ route('hub.index') }}" class="text-xs font-bold text-[#0a2d27] hover:underline flex items-center gap-1">
                                View All <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($relatedPosts as $rel)
                                <article class="bg-white rounded-[1.5rem] p-5 border border-slate-200/80 hover:shadow-lg transition-all duration-300 flex flex-col justify-between">
                                    <div>
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 bg-slate-100 px-2.5 py-0.5 rounded-full">
                                            {{ $rel->category }}
                                        </span>
                                        <h4 class="mt-3 text-sm font-bold text-slate-900 line-clamp-2 hover:text-teal-700">
                                            <a href="{{ route('hub.show', $rel->slug) }}">{{ $rel->title }}</a>
                                        </h4>
                                    </div>
                                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                                        <span>{{ $rel->created_at->format('M j') }}</span>
                                        @if ($rel->type === 'opportunity' && $rel->opportunity_link)
                                            <a
                                                href="{{ $rel->opportunity_link }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="font-bold text-emerald-600 hover:underline flex items-center gap-1"
                                            >
                                                Apply <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('hub.show', $rel->slug) }}" class="font-bold text-[#0a2d27] hover:underline">
                                                Read Page <i class="fa-solid fa-arrow-right text-[9px]"></i>
                                            </a>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </section>
    </main>

    {{-- Footer --}}
    <footer class="bg-white border-t border-slate-200 py-12 lg:py-16">
        <div class="mx-auto max-w-6xl px-6 lg:px-8 text-center lg:text-left">
            <div class="grid gap-10 lg:grid-cols-[1.4fr_1fr_1fr]">
                <div>
                    <div class="flex items-center justify-center gap-3 lg:justify-start">
                        <img src="{{ asset('images/logos/green.png') }}" alt="think.er HUB logo" class="h-8 w-auto">
                    </div>
                    <p class="mt-4 max-w-sm text-sm leading-relaxed text-slate-500">
                        think.er HUB is where tutors create and manage courses, and learners register to upskill with practical outcomes.
                    </p>
                    <div class="mt-6 flex flex-wrap items-center justify-center gap-4 text-sm text-slate-500 lg:justify-start">
                        <a href="{{ route('login') }}" class="inline-flex items-center rounded-full bg-[#0a2d27] px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-[#11443c]">Login</a>
                    </div>
                </div>

                <div class="hidden lg:block">
                    <h3 class="text-sm font-bold text-slate-900">Menu</h3>
                    <ul class="mt-4 space-y-2.5 text-sm text-slate-500">
                        <li><a href="{{ route('home') }}" class="transition hover:text-[#0a2d27]">Home</a></li>
                        <li><a href="{{ route('landing.courses') }}" class="transition hover:text-[#0a2d27]">Courses</a></li>
                        <li><a href="{{ route('hub.index') }}" class="transition hover:text-[#0a2d27]">Knowledge Hub</a></li>
                        <li><a href="{{ route('landing.instructors') }}" class="transition hover:text-[#0a2d27]">Instructors</a></li>
                        <li><a href="{{ route('landing.contact') }}" class="transition hover:text-[#0a2d27]">Contact</a></li>
                        @auth
                            <li><a href="{{ route('dashboard') }}" class="transition hover:text-[#0a2d27]">Dashboard</a></li>
                        @else
                            <li><a href="{{ route('login') }}" class="transition hover:text-[#0a2d27]">Login</a></li>
                        @endauth
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-slate-900">Contacts</h3>
                    <div class="mt-4 space-y-2.5 text-sm text-slate-500">
                        <p><span class="font-semibold text-slate-700">Phone:</span> <button type="button" @click="$dispatch('open-contact')" class="ml-1 text-[#0a2d27] font-medium underline-offset-2 hover:underline cursor-pointer">+260772640546</button></p>
                        <p><span class="font-semibold text-slate-700">Email:</span> <a href="mailto:thinker.learn@gmail.com" class="text-[#0a2d27] underline-offset-2 hover:underline">thinker.learn@gmail.com</a></p>
                        <p><span class="font-semibold text-slate-700">Address:</span> 10A Off Natwange Street, Airpot, Livingstone Zambia</p>
                    </div>
                    <div class="mt-4 flex items-center justify-center gap-4 text-slate-500 lg:justify-start">
                        <a href="#" class="transition hover:text-[#0a2d27]" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" class="transition hover:text-[#0a2d27]" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
                        <a href="#" class="transition hover:text-[#0a2d27]" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
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
    @include('partials.legal-modals')
</div>
