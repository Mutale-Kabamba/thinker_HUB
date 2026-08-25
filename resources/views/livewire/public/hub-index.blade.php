<div class="hub-public bg-[#f8fcf9] text-slate-900 font-sans antialiased min-h-screen">
    @include('partials.public-header')

    <main>
        {{-- Hero & Search Header --}}
        <section class="bg-[#0a2d27] relative overflow-hidden py-16 sm:py-20 text-white">
            <div class="mx-auto max-w-6xl px-6 lg:px-8 text-center relative z-10">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-yellow-400">Knowledge &amp; Opportunities Hub</p>
                <h1 class="mt-4 text-4xl font-black sm:text-5xl lg:text-6xl tracking-tight leading-tight">
                    Tips, Insights &amp; Opportunities
                </h1>
                <p class="mx-auto mt-4 max-w-2xl text-slate-300 text-base sm:text-lg leading-relaxed">
                    Discover practical tech tips, career opportunities, short articles, and curated video tutorials from our mentors.
                </p>

                {{-- Real-time Minimal & Clean Search Input --}}
                <div class="mx-auto mt-8 max-w-xl relative">
                    <div class="relative flex items-center">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search by keywords, title, or topic..."
                            class="w-full rounded-full bg-white/10 text-white placeholder-slate-300 px-6 py-3 border border-white/20 focus:bg-white focus:text-slate-900 focus:placeholder-slate-400 focus:border-transparent focus:ring-2 focus:ring-teal-400/40 focus:outline-none text-sm font-medium transition-all duration-300 shadow-xs"
                        >
                        @if ($search !== '')
                            <button
                                wire:click="resetSearch"
                                type="button"
                                class="absolute right-4 text-slate-400 hover:text-slate-700 transition-colors p-1"
                                title="Clear search"
                            >
                                <i class="fa-solid fa-circle-xmark text-base"></i>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Submit / Register Contributor CTA Button --}}
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    @auth
                        <button
                            wire:click="openSubmitModal"
                            type="button"
                            class="inline-flex items-center gap-2 rounded-full bg-yellow-400 px-6 py-2.5 text-xs font-bold text-[#0a2d27] shadow-md hover:bg-white transition-all transform hover:-translate-y-0.5"
                        >
                            <i class="fa-solid fa-plus"></i> Submit Resource / Opportunity
                        </button>
                    @else
                        <button
                            wire:click="openRegisterModal"
                            type="button"
                            class="inline-flex items-center gap-2 rounded-full bg-yellow-400 px-6 py-2.5 text-xs font-bold text-[#0a2d27] shadow-md hover:bg-white transition-all transform hover:-translate-y-0.5"
                        >
                            <i class="fa-solid fa-user-plus"></i> Register to Submit (Blogger / Researcher / Employer)
                        </button>
                    @endauth
                </div>

                {{-- Notice Banner --}}
                @if ($submitNoticeMessage)
                    <div class="mx-auto mt-6 max-w-xl p-4 bg-emerald-500/20 border border-emerald-400/40 rounded-2xl text-emerald-200 text-xs font-semibold flex items-center justify-between gap-3 shadow-md">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-check text-emerald-400 text-base"></i>
                            <span>{{ $submitNoticeMessage }}</span>
                        </div>
                        <button wire:click="$set('submitNoticeMessage', null)" type="button" class="text-emerald-300 hover:text-white">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                @endif
            </div>
            <div class="absolute top-0 right-0 w-96 h-96 bg-teal-500/10 rounded-full blur-3xl -mr-32 -mt-32 pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-80 h-80 bg-yellow-400/5 rounded-full blur-2xl -ml-20 -mb-20 pointer-events-none"></div>
        </section>

        {{-- Filters Section --}}
        <section class="border-b border-slate-200/80 bg-white/95 backdrop-blur-md sticky top-[73px] z-30 shadow-xs">
            <div class="mx-auto max-w-6xl px-6 lg:px-8 py-3.5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                {{-- Type Filter Pills --}}
                <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1 md:pb-0">
                    <button
                        wire:click="selectType('all')"
                        type="button"
                        class="px-4 py-2 rounded-full text-xs font-bold transition-all shrink-0 flex items-center gap-1.5 {{ $type === 'all' ? 'bg-[#0a2d27] text-white shadow-md' : 'bg-slate-100/90 text-slate-700 hover:bg-slate-200' }}"
                    >
                        <i class="fa-solid fa-border-all text-[11px]"></i> All
                    </button>
                    <button
                        wire:click="selectType('video')"
                        type="button"
                        class="px-4 py-2 rounded-full text-xs font-bold transition-all shrink-0 flex items-center gap-1.5 {{ $type === 'video' ? 'bg-rose-600 text-white shadow-md' : 'bg-slate-100/90 text-slate-700 hover:bg-rose-50 hover:text-rose-700' }}"
                    >
                        <i class="fa-solid fa-video text-[11px]"></i> Videos
                    </button>
                    <button
                        wire:click="selectType('tip_trick')"
                        type="button"
                        class="px-4 py-2 rounded-full text-xs font-bold transition-all shrink-0 flex items-center gap-1.5 {{ $type === 'tip_trick' ? 'bg-teal-700 text-white shadow-md' : 'bg-slate-100/90 text-slate-700 hover:bg-teal-50 hover:text-teal-700' }}"
                    >
                        <i class="fa-solid fa-wand-magic-sparkles text-[11px]"></i> Tips &amp; Tricks
                    </button>
                    <button
                        wire:click="selectType('blog')"
                        type="button"
                        class="px-4 py-2 rounded-full text-xs font-bold transition-all shrink-0 flex items-center gap-1.5 {{ $type === 'blog' ? 'bg-indigo-700 text-white shadow-md' : 'bg-slate-100/90 text-slate-700 hover:bg-indigo-50 hover:text-indigo-700' }}"
                    >
                        <i class="fa-solid fa-newspaper text-[11px]"></i> Short Blogs
                    </button>
                    <button
                        wire:click="selectType('opportunity')"
                        type="button"
                        class="px-4 py-2 rounded-full text-xs font-bold transition-all shrink-0 flex items-center gap-1.5 {{ $type === 'opportunity' ? 'bg-emerald-700 text-white shadow-md' : 'bg-slate-100/90 text-slate-700 hover:bg-emerald-50 hover:text-emerald-700' }}"
                    >
                        <i class="fa-solid fa-briefcase text-[11px]"></i> Opportunities
                    </button>
                </div>

                {{-- Category Filter & Reset --}}
                <div class="flex items-center gap-3 shrink-0">
                    <select
                        wire:model.live="category"
                        class="rounded-full bg-slate-100 border-none px-4 py-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-teal-500 cursor-pointer shadow-2xs"
                    >
                        <option value="all">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>

                    @if ($search !== '' || $type !== 'all' || $category !== 'all')
                        <button
                            wire:click="resetFilters"
                            type="button"
                            class="text-xs font-bold text-rose-600 hover:text-rose-700 underline underline-offset-2 flex items-center gap-1 transition-colors"
                        >
                            <i class="fa-solid fa-rotate-left"></i> Reset
                        </button>
                    @endif
                </div>
            </div>
        </section>

        {{-- Posts Listing Grid --}}
        <section class="py-12 lg:py-16">
            <div class="mx-auto max-w-6xl px-6 lg:px-8">

                {{-- Result Count Header --}}
                <div class="mb-8 flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">
                        Showing <span class="font-bold text-slate-900">{{ $posts->total() }}</span> {{ Str::plural('resource', $posts->total()) }}
                        @if ($type !== 'all')
                            for <span class="font-bold text-slate-900">{{ App\Models\HubPost::TYPES[$type] ?? ucfirst($type) }}</span>
                        @endif
                        @if ($category !== 'all')
                            in <span class="font-bold text-[#0a2d27]">{{ $category }}</span>
                        @endif
                    </p>
                </div>

                @if ($posts->count() > 0)
                    <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($posts as $post)
                            @if ($post->type === 'video')
                                {{-- 1. Video Tutorial Card --}}
                                <article class="group bg-white rounded-[2rem] overflow-hidden shadow-xs hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 border border-slate-200/80 hover:border-rose-300 flex flex-col justify-between">
                                    <div>
                                        <div class="relative aspect-video overflow-hidden bg-slate-900 cursor-pointer" wire:click="openVideoModal({{ $post->id }})">
                                            @if ($post->video_id)
                                                <img
                                                    src="https://img.youtube.com/vi/{{ $post->video_id }}/hqdefault.jpg"
                                                    alt="{{ $post->title }}"
                                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                                >
                                            @else
                                                <div class="w-full h-full bg-gradient-to-br from-slate-800 to-slate-950 flex items-center justify-center">
                                                    <i class="fa-solid fa-play text-4xl text-rose-500"></i>
                                                </div>
                                            @endif
                                            <div class="absolute inset-0 bg-black/35 group-hover:bg-black/15 transition-colors flex items-center justify-center">
                                                <div class="w-14 h-14 rounded-full bg-rose-600 text-white flex items-center justify-center shadow-xl group-hover:scale-110 transition-transform">
                                                    <i class="fa-solid fa-play text-lg ml-1"></i>
                                                </div>
                                            </div>
                                            <span class="absolute top-3 left-3 bg-rose-600 text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full shadow-md">
                                                <i class="fa-solid fa-video mr-1"></i> Video Tutorial
                                            </span>
                                            @if ($post->media->isNotEmpty())
                                                <span class="absolute bottom-3 left-3 bg-indigo-900/90 text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full backdrop-blur-xs flex items-center gap-1 shadow-sm">
                                                    <i class="fa-solid fa-paperclip"></i> {{ $post->media->count() }} Attached
                                                </span>
                                            @endif
                                            <span class="absolute bottom-3 right-3 bg-black/80 text-white text-[11px] font-semibold px-2.5 py-0.5 rounded-md backdrop-blur-xs">
                                                YouTube
                                            </span>
                                        </div>
                                        <div class="p-6">
                                            <div class="flex items-center gap-2 mb-2.5">
                                                <span class="text-xs font-bold text-rose-700 bg-rose-50 px-3 py-0.5 rounded-full border border-rose-100">
                                                    {{ $post->category }}
                                                </span>
                                                <span class="text-xs text-slate-400 font-medium">• {{ $post->created_at->format('M j, Y') }}</span>
                                            </div>
                                            <h3 class="text-lg font-bold text-slate-900 group-hover:text-rose-600 transition-colors leading-snug cursor-pointer line-clamp-2">
                                                <a href="{{ route('hub.show', $post->slug) }}">{{ $post->title }}</a>
                                            </h3>
                                            @if ($post->excerpt)
                                                <p class="mt-2 text-xs text-slate-600 leading-relaxed line-clamp-2">{{ $post->excerpt }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="px-6 pb-6 pt-3 border-t border-slate-100 flex items-center justify-between">
                                        <a
                                            href="{{ route('hub.show', $post->slug) }}"
                                            class="text-xs font-semibold text-slate-500 hover:text-rose-600"
                                        >
                                            View Details
                                        </a>
                                        <button
                                            wire:click="openVideoModal({{ $post->id }})"
                                            type="button"
                                            class="inline-flex items-center justify-center rounded-full bg-rose-600 px-4 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-rose-700 transition-colors"
                                        >
                                            Play Video
                                        </button>
                                    </div>
                                </article>

                            @elseif ($post->type === 'tip_trick')
                                {{-- 2. Tips & Tricks Card (Styled with top hero cover image like Video cards) --}}
                                <article class="group bg-white rounded-[2rem] overflow-hidden shadow-xs hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 border border-slate-200/80 hover:border-teal-300 flex flex-col justify-between">
                                    <div>
                                        {{-- Top Hero Image / Gradient Cover --}}
                                        <a href="{{ route('hub.show', $post->slug) }}" class="relative block aspect-video overflow-hidden bg-slate-900">
                                            @if ($post->cover_image_url)
                                                <img
                                                    src="{{ $post->cover_image_url }}"
                                                    alt="{{ $post->title }}"
                                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                                >
                                            @else
                                                <div class="w-full h-full bg-gradient-to-br from-[#0a2d27] via-emerald-900 to-slate-950 p-6 pt-12 flex flex-col justify-between relative overflow-hidden">
                                                    <div class="absolute -right-4 -bottom-4 text-white/5 text-8xl font-black select-none pointer-events-none">
                                                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                                                    </div>
                                                    <div class="my-auto z-10">
                                                        <p class="text-white text-base font-extrabold line-clamp-2 leading-tight tracking-tight drop-shadow-sm">
                                                            {{ $post->title }}
                                                        </p>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="absolute inset-0 bg-black/20 group-hover:bg-black/5 transition-colors"></div>

                                            <span class="absolute top-3 left-3 bg-teal-700 text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full shadow-md">
                                                <i class="fa-solid fa-wand-magic-sparkles mr-1"></i> Tip &amp; Trick
                                            </span>

                                            <span class="absolute top-3 right-3 bg-black/70 text-white text-[11px] font-semibold px-2.5 py-0.5 rounded-full backdrop-blur-xs">
                                                {{ $post->category }}
                                            </span>

                                            @if ($post->media->isNotEmpty())
                                                <span class="absolute bottom-3 left-3 bg-teal-900/90 text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full backdrop-blur-xs flex items-center gap-1 shadow-sm">
                                                    <i class="fa-solid fa-paperclip"></i> {{ $post->media->count() }} Attached
                                                </span>
                                            @endif
                                        </a>

                                        {{-- Body Content --}}
                                        <div class="p-6">
                                            <div class="flex items-center gap-2 mb-2.5">
                                                <span class="text-xs font-bold text-teal-700 bg-teal-50 px-3 py-0.5 rounded-full border border-teal-100">
                                                    {{ $post->category }}
                                                </span>
                                                <span class="text-xs text-slate-400 font-medium">• {{ $post->created_at->format('M j, Y') }}</span>
                                            </div>

                                            <h3 class="text-lg font-bold text-slate-900 group-hover:text-teal-700 transition-colors leading-snug cursor-pointer line-clamp-2">
                                                <a href="{{ route('hub.show', $post->slug) }}">{{ $post->title }}</a>
                                            </h3>

                                            @if ($post->excerpt)
                                                <p class="mt-2 text-xs text-slate-600 leading-relaxed font-medium line-clamp-2">
                                                    <strong class="text-slate-900">Insight:</strong> {{ $post->excerpt }}
                                                </p>
                                            @endif

                                            @if ($post->code_snippet)
                                                <div class="mt-3.5 rounded-xl bg-slate-900 p-3 text-slate-100 text-[11px] font-mono overflow-x-auto border border-slate-800 shadow-inner">
                                                    <div class="flex items-center justify-between text-[10px] text-slate-400 mb-1 border-b border-slate-800 pb-1">
                                                        <span><i class="fa-solid fa-code text-teal-400 mr-1"></i> Code Preview</span>
                                                    </div>
                                                    <pre class="line-clamp-2 overflow-hidden"><code>{{ $post->code_snippet }}</code></pre>
                                                </div>
                                            @elseif ($post->pro_tip)
                                                <div class="mt-3.5 rounded-xl bg-amber-50 p-3 border border-amber-200 text-amber-900 text-xs flex items-start gap-2 shadow-2xs">
                                                    <i class="fa-solid fa-lightbulb text-amber-500 mt-0.5 text-sm shrink-0"></i>
                                                    <p class="leading-tight line-clamp-2">
                                                        <strong class="font-bold">Pro Tip:</strong> {{ $post->pro_tip }}
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Card Footer --}}
                                    <div class="px-6 pb-6 pt-3 border-t border-slate-100 flex items-center justify-between">
                                        <span class="text-xs text-slate-400 font-medium">By {{ $post->author?->name ?: 'Researcher' }}</span>
                                        <a
                                            href="{{ route('hub.show', $post->slug) }}"
                                            class="inline-flex items-center justify-center rounded-full bg-[#0a2d27] px-4 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-teal-800 transition-colors"
                                        >
                                            Read Tip <i class="fa-solid fa-arrow-right text-[10px] ml-1"></i>
                                        </a>
                                    </div>
                                </article>

                            @elseif ($post->type === 'opportunity')
                                {{-- 3. Opportunity Card --}}
                                <article class="group bg-white rounded-[2rem] p-6 shadow-xs hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 border border-slate-200/80 hover:border-emerald-300 flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between gap-2 mb-3">
                                            <span class="bg-emerald-700 text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full shadow-sm">
                                                <i class="fa-solid fa-briefcase mr-1"></i> {{ $post->extra['opportunity_type'] ?? 'Opportunity' }}
                                            </span>
                                            @if ($post->opportunity_deadline)
                                                @php
                                                    $isPast = $post->opportunity_deadline->isPast() && ! $post->opportunity_deadline->isToday();
                                                @endphp
                                                @if ($isPast)
                                                    <span class="bg-slate-100 text-slate-500 text-[10px] font-bold px-2.5 py-0.5 rounded-full">
                                                        Closed
                                                    </span>
                                                @else
                                                    <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2.5 py-0.5 rounded-full flex items-center gap-1">
                                                        <i class="fa-regular fa-clock text-[10px]"></i> {{ $post->opportunity_deadline->format('M j') }}
                                                    </span>
                                                @endif
                                            @endif
                                        </div>

                                        {{-- Header: Provider & Title --}}
                                        <div class="mb-2">
                                            <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">
                                                {{ $post->extra['provider'] ?? ($post->author->name ?? 'Thinker HUB') }}
                                            </p>
                                            <h3 class="text-lg font-bold text-slate-900 group-hover:text-emerald-700 transition-colors leading-snug line-clamp-2">
                                                <a href="{{ route('hub.show', $post->slug) }}">{{ $post->title }}</a>
                                            </h3>
                                        </div>

                                        {{-- Meta Grid / Badges --}}
                                        <div class="my-3 grid grid-cols-2 gap-2 text-xs">
                                            <div class="bg-slate-50 p-2 rounded-xl border border-slate-100 flex items-center gap-1.5">
                                                <i class="fa-solid fa-location-dot text-emerald-600"></i>
                                                <span class="font-semibold text-slate-700 truncate">
                                                    {{ $post->extra['location'] ?? 'Remote' }}
                                                </span>
                                            </div>
                                            <div class="bg-slate-50 p-2 rounded-xl border border-slate-100 flex items-center gap-1.5">
                                                <i class="fa-solid fa-coins text-amber-500"></i>
                                                <span class="font-semibold text-slate-700 truncate">
                                                    {{ $post->extra['compensation'] ?? 'Competitive' }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Key Requirements Bullet List --}}
                                        @if (!empty($post->extra['requirements']))
                                            <div class="mt-3 bg-emerald-50/50 p-3 rounded-xl border border-emerald-100">
                                                <p class="text-[11px] font-bold text-emerald-900 uppercase tracking-wider mb-1">Key Requirements:</p>
                                                <ul class="text-xs text-slate-600 space-y-1 pl-4 list-disc">
                                                    @foreach (array_slice($post->extra['requirements'], 0, 2) as $req)
                                                        <li class="line-clamp-1">{{ $req }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @elseif ($post->excerpt)
                                            <p class="mt-2 text-xs text-slate-600 leading-relaxed line-clamp-2">
                                                {{ $post->excerpt }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                                        <div class="flex items-center gap-1">
                                            @if ($post->media->isNotEmpty())
                                                <span class="text-[11px] text-emerald-700 font-semibold flex items-center gap-1">
                                                    <i class="fa-solid fa-paperclip"></i> Spec Doc
                                                </span>
                                            @else
                                                <span class="text-xs text-slate-400 font-medium">
                                                    {{ $post->created_at->diffForHumans() }}
                                                </span>
                                            @endif
                                        </div>

                                        @if ($post->opportunity_link)
                                            <a
                                                href="{{ $post->opportunity_link }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1.5 rounded-full bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-emerald-700 transition-colors"
                                            >
                                                Apply Now <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                            </a>
                                        @else
                                            <a
                                                href="{{ route('hub.show', $post->slug) }}"
                                                class="inline-flex items-center gap-1.5 rounded-full bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-slate-800 transition-colors"
                                            >
                                                View Details
                                            </a>
                                        @endif
                                    </div>
                                </article>

                            @else
                                {{-- 4. Short Blog Card --}}
                                <article class="group bg-white rounded-[2rem] p-6 shadow-xs hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 border border-slate-200/80 hover:border-indigo-300 flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between gap-2 mb-4">
                                            <span class="bg-indigo-700 text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full shadow-sm">
                                                <i class="fa-solid fa-newspaper mr-1"></i> Article
                                            </span>
                                            <div class="flex items-center gap-2">
                                                @if ($post->media->isNotEmpty())
                                                    <span class="bg-indigo-50 text-indigo-700 text-[10px] font-bold px-2 py-0.5 rounded-md border border-indigo-200 flex items-center gap-1">
                                                        <i class="fa-solid fa-paperclip"></i> Attachment
                                                    </span>
                                                @endif
                                                <span class="text-xs font-medium text-slate-400 flex items-center gap-1">
                                                    <i class="fa-regular fa-clock"></i> {{ $post->reading_time }} min read
                                                </span>
                                            </div>
                                        </div>

                                        <div class="mb-2.5">
                                            <span class="text-xs font-bold text-slate-700 bg-slate-100 px-3 py-0.5 rounded-full border border-slate-200">
                                                {{ $post->category }}
                                            </span>
                                        </div>

                                        <h3 class="text-lg font-bold text-slate-900 group-hover:text-indigo-700 transition-colors leading-snug line-clamp-2">
                                            <a href="{{ route('hub.show', $post->slug) }}">{{ $post->title }}</a>
                                        </h3>

                                        @if ($post->excerpt)
                                            <p class="mt-3 text-xs text-slate-600 leading-relaxed line-clamp-3">
                                                {{ $post->excerpt }}
                                            </p>
                                        @endif

                                        {{-- Scannable Content Bullet Highlights --}}
                                        @if ($post->content)
                                            <div class="mt-3 p-3 bg-indigo-50/50 rounded-xl border border-indigo-100/80 text-xs text-slate-700">
                                                <p class="font-bold text-indigo-900 text-[11px] uppercase tracking-wider mb-1">Key Takeaway:</p>
                                                <p class="line-clamp-2 text-slate-600 italic">"{{ Str::limit(strip_tags($post->content), 120) }}"</p>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-800 font-bold text-xs flex items-center justify-center">
                                                {{ strtoupper(substr($post->author->name ?? 'T', 0, 1)) }}
                                            </div>
                                            <span class="text-xs font-semibold text-slate-700 truncate max-w-[100px]">
                                                {{ $post->author->name ?? 'Thinker HUB' }}
                                            </span>
                                        </div>

                                        <a
                                            href="{{ route('hub.show', $post->slug) }}"
                                            class="inline-flex items-center gap-1 rounded-full bg-indigo-900 px-4 py-2 text-xs font-bold text-white transition hover:bg-indigo-950"
                                        >
                                            Read Article <i class="fa-solid fa-arrow-right text-[10px] ml-1"></i>
                                        </a>
                                    </div>
                                </article>
                            @endif
                        @endforeach
                    </div>

                    {{-- Pagination Links --}}
                    <div class="mt-12">
                        {{ $posts->links() }}
                    </div>

                @else
                    {{-- Empty State --}}
                    <div class="py-20 text-center border-2 border-dashed border-slate-200 rounded-[3rem] bg-white">
                        <div class="bg-teal-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto shadow-sm mb-4">
                            <i class="fa-solid fa-folder-open text-teal-600 text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">No resources found</h3>
                        <p class="text-slate-500 text-sm mt-1 max-w-sm mx-auto">
                            We couldn't find any resources matching your selected search or filters.
                        </p>
                        <button
                            wire:click="resetFilters"
                            type="button"
                            class="mt-6 inline-flex items-center gap-2 rounded-full bg-[#0a2d27] px-6 py-2.5 text-xs font-bold text-white transition hover:bg-[#11443c]"
                        >
                            <i class="fa-solid fa-rotate-left"></i> Reset All Filters
                        </button>
                    </div>
                @endif

            </div>
        </section>
    </main>

    {{-- Dynamic Adaptive Submit Resource Modal with Media Attachment Support --}}
    @if ($showSubmitModal)
        <div
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4 sm:p-6"
            wire:click.self="closeSubmitModal"
            @keydown.escape.window="$wire.closeSubmitModal()"
        >
            <div class="bg-white w-full max-w-2xl rounded-[2.5rem] shadow-2xl overflow-hidden relative max-h-[90vh] flex flex-col border border-slate-100">
                <div class="p-6 sm:p-8 bg-[#0a2d27] text-white relative shrink-0">
                    <button
                        wire:click="closeSubmitModal"
                        type="button"
                        class="absolute top-6 right-6 text-slate-300 hover:text-white bg-white/10 hover:bg-white/20 w-9 h-9 rounded-full flex items-center justify-center transition"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                    <span class="bg-yellow-400 text-[#0a2d27] text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full">
                        Community Submission
                    </span>
                    <h2 class="text-2xl font-black text-white mt-2">Submit Resource or Opportunity</h2>
                    <p class="text-xs text-slate-300 mt-1">
                        @if (auth()->user()?->isAdmin())
                            Publish directly to the Knowledge &amp; Opportunities Hub.
                        @else
                            Submissions are reviewed by an Admin before going live.
                        @endif
                    </p>

                    {{-- Role-Based Dynamic Type Switcher Tabs --}}
                    <div class="mt-6 flex items-center gap-1.5 overflow-x-auto no-scrollbar bg-white/10 p-1.5 rounded-full border border-white/15">
                        @if (auth()->user()?->canSubmitType('tip_trick'))
                            <button
                                type="button"
                                wire:click="setSubmitType('tip_trick')"
                                class="px-4 py-1.5 rounded-full text-xs font-bold transition-all shrink-0 {{ $submitType === 'tip_trick' ? 'bg-teal-500 text-white shadow-md' : 'text-slate-200 hover:text-white' }}"
                            >
                                <i class="fa-solid fa-wand-magic-sparkles mr-1"></i> Tip / Trick (Researcher)
                            </button>
                        @endif

                        @if (auth()->user()?->canSubmitType('blog'))
                            <button
                                type="button"
                                wire:click="setSubmitType('blog')"
                                class="px-4 py-1.5 rounded-full text-xs font-bold transition-all shrink-0 {{ $submitType === 'blog' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-200 hover:text-white' }}"
                            >
                                <i class="fa-solid fa-newspaper mr-1"></i> Short Blog (Blogger)
                            </button>
                        @endif

                        @if (auth()->user()?->canSubmitType('opportunity'))
                            <button
                                type="button"
                                wire:click="setSubmitType('opportunity')"
                                class="px-4 py-1.5 rounded-full text-xs font-bold transition-all shrink-0 {{ $submitType === 'opportunity' ? 'bg-emerald-500 text-white shadow-md' : 'text-slate-200 hover:text-white' }}"
                            >
                                <i class="fa-solid fa-briefcase mr-1"></i> Opportunity (Employer)
                            </button>
                        @endif

                        @if (auth()->user()?->canSubmitType('video'))
                            <button
                                type="button"
                                wire:click="setSubmitType('video')"
                                class="px-4 py-1.5 rounded-full text-xs font-bold transition-all shrink-0 {{ $submitType === 'video' ? 'bg-rose-600 text-white shadow-md' : 'text-slate-200 hover:text-white' }}"
                            >
                                <i class="fa-solid fa-video mr-1"></i> Video
                            </button>
                        @endif
                    </div>
                </div>

                <form wire:submit.prevent="submitResource" class="p-6 sm:p-8 overflow-y-auto grow space-y-5">
                    {{-- Common Title --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Title *</label>
                        <input
                            type="text"
                            wire:model="submitTitle"
                            placeholder="e.g. Mastering Flexbox Alignment or Senior Developer Role..."
                            class="w-full rounded-xl bg-slate-50 border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none"
                            required
                        >
                        @error('submitTitle') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    @if ($submitType === 'opportunity')
                        {{-- Opportunity Specific Adaptive Fields --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Organization / Host *</label>
                                <input
                                    type="text"
                                    wire:model="submitProvider"
                                    placeholder="e.g. Thinker HUB, Google, Remote Co..."
                                    class="w-full rounded-xl bg-slate-50 border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none"
                                    required
                                >
                                @error('submitProvider') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Opportunity Type *</label>
                                <select
                                    wire:model="submitOpportunityType"
                                    class="w-full rounded-xl bg-slate-50 border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none"
                                >
                                    <option value="Job">Job</option>
                                    <option value="Hackathon">Hackathon</option>
                                    <option value="Internship">Internship</option>
                                    <option value="Scholarship">Scholarship</option>
                                    <option value="Promo Code">Promo Code</option>
                                    <option value="Event">Event</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Location</label>
                                <input
                                    type="text"
                                    wire:model="submitLocation"
                                    placeholder="e.g. Remote, Lusaka Zambia, Hybrid"
                                    class="w-full rounded-xl bg-slate-50 border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Compensation / Prize</label>
                                <input
                                    type="text"
                                    wire:model="submitCompensation"
                                    placeholder="e.g. $1,500/mo, $5,000 Prize, Free"
                                    class="w-full rounded-xl bg-slate-50 border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none"
                                >
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Application Link</label>
                                <input
                                    type="url"
                                    wire:model="submitOpportunityLink"
                                    placeholder="https://company.com/careers/apply"
                                    class="w-full rounded-xl bg-slate-50 border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Application Deadline</label>
                                <input
                                    type="date"
                                    wire:model="submitOpportunityDeadline"
                                    class="w-full rounded-xl bg-slate-50 border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none"
                                >
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Requirements (One per line)</label>
                            <textarea
                                wire:model="submitRequirements"
                                rows="3"
                                placeholder="3+ years Laravel experience&#10;Strong Tailwind CSS skills..."
                                class="w-full rounded-xl bg-slate-50 border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none"
                            ></textarea>
                        </div>

                    @else
                        {{-- Tip/Trick, Blog, Video Fields --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Category / Track *</label>
                                <input
                                    type="text"
                                    wire:model="submitCategory"
                                    placeholder="e.g. Programming, Design, Career"
                                    class="w-full rounded-xl bg-slate-50 border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none"
                                    required
                                >
                            </div>

                            @if ($submitType === 'video')
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">YouTube URL *</label>
                                    <input
                                        type="url"
                                        wire:model="submitYoutubeUrl"
                                        placeholder="https://www.youtube.com/watch?v=..."
                                        class="w-full rounded-xl bg-slate-50 border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none"
                                    >
                                </div>
                            @endif
                        </div>

                        @if ($submitType === 'tip_trick')
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Code Snippet (Optional)</label>
                                <textarea
                                    wire:model="submitCodeSnippet"
                                    rows="3"
                                    placeholder="Paste your code snippet here..."
                                    class="w-full font-mono text-xs rounded-xl bg-slate-900 text-teal-300 border border-slate-800 px-4 py-2.5 focus:ring-2 focus:ring-teal-500 focus:outline-none"
                                ></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Pro Tip / Quick Takeaway</label>
                                <textarea
                                    wire:model="submitProTip"
                                    rows="2"
                                    placeholder="e.g. Always clear cache after updating environment variables!"
                                    class="w-full rounded-xl bg-amber-50/60 border border-amber-200 px-4 py-2 text-xs font-medium text-amber-900 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none"
                                ></textarea>
                            </div>
                        @endif

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Summary / Excerpt</label>
                            <textarea
                                wire:model="submitExcerpt"
                                rows="2"
                                placeholder="Short overview displayed on resource cards..."
                                class="w-full rounded-xl bg-slate-50 border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none"
                            ></textarea>
                        </div>

                        @if ($submitType === 'blog' || $submitType === 'tip_trick')
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Full Article / Content</label>
                                <textarea
                                    wire:model="submitContent"
                                    rows="4"
                                    placeholder="Write your complete article or detailed tip walkthrough..."
                                    class="w-full rounded-xl bg-slate-50 border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none"
                                ></textarea>
                            </div>
                        @endif
                    @endif

                    {{-- Multi-Format Media Upload Component --}}
                    <div class="rounded-2xl border-2 border-dashed border-slate-200 p-4 bg-slate-50/70 text-center">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1 cursor-pointer">
                            <i class="fa-solid fa-cloud-arrow-up text-teal-600 text-lg mr-1"></i> Attach Media Files (PDF, PPT, Word, Image, MP4)
                        </label>
                        <p class="text-[11px] text-slate-500 mb-2">Upload slide decks, specification documents, or code cheat sheets (Up to 50MB per file)</p>
                        <input
                            type="file"
                            wire:model="submitFiles"
                            multiple
                            accept=".pdf,.ppt,.pptx,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.webp,.mp4"
                            class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 cursor-pointer"
                        >
                        @error('submitFiles.*') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror

                        <div wire:loading wire:target="submitFiles" class="mt-2 text-xs text-teal-600 font-semibold flex items-center justify-center gap-1">
                            <i class="fa-solid fa-spinner fa-spin"></i> Uploading files...
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button
                            wire:click="closeSubmitModal"
                            type="button"
                            class="px-5 py-2.5 rounded-full bg-slate-100 text-slate-600 text-xs font-bold hover:bg-slate-200 transition"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-6 py-2.5 rounded-full bg-[#0a2d27] text-white text-xs font-bold shadow-md hover:bg-[#11443c] transition flex items-center gap-1.5"
                        >
                            <i class="fa-solid fa-paperplane text-[11px]"></i> Submit Resource
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Contributor Registration Modal --}}
    @if ($showRegisterModal)
        <div
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4 sm:p-6 lg:p-8"
            wire:click.self="closeRegisterModal"
            @keydown.escape.window="$wire.closeRegisterModal()"
        >
            <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden relative border border-slate-100 transform transition-all duration-300">
                
                {{-- Header --}}
                <div class="bg-[#0a2d27] px-6 py-6 sm:px-10 sm:py-8 text-white relative overflow-hidden">
                    <div class="absolute -top-10 -right-10 w-40 h-40 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="relative z-10 flex items-start justify-between gap-4">
                        <div class="space-y-1.5">
                            <span class="inline-block text-[10px] font-extrabold uppercase tracking-widest text-yellow-400 bg-yellow-400/15 px-3.5 py-1 rounded-full border border-yellow-400/30">
                                Contributor Signup
                            </span>
                            <h3 class="text-xl sm:text-2xl font-black text-white tracking-tight pt-1">
                                Register to Publish Resources
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-300 font-medium">
                                Join our network to publish blogs, research tips, or job opportunities.
                            </p>
                        </div>
                        <button
                            wire:click="closeRegisterModal"
                            type="button"
                            class="text-white/70 hover:text-white bg-white/10 hover:bg-white/20 w-9 h-9 rounded-full flex items-center justify-center transition-all shrink-0 mt-0.5"
                            aria-label="Close modal"
                        >
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>
                </div>

                <form wire:submit.prevent="registerContributor" class="p-6 sm:p-10 space-y-6 max-h-[80vh] overflow-y-auto">
                    
                    {{-- 🌟 1. CHECK & PULL EXISTING STUDENT / INSTRUCTOR DETAILS --}}
                    <div class="rounded-2xl border border-teal-200/90 bg-gradient-to-br from-teal-50/70 via-emerald-50/40 to-slate-50 p-5 sm:p-6 shadow-xs">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-teal-100/90">
                            <div class="flex items-center gap-3">
                                <span class="w-9 h-9 rounded-xl bg-[#0a2d27] text-yellow-400 flex items-center justify-center text-sm shadow-xs shrink-0">
                                    <i class="fa-solid fa-id-card-clip"></i>
                                </span>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">
                                        Already an Instructor or Student?
                                    </h4>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        Pull your profile, contact details, and social links automatically.
                                    </p>
                                </div>
                            </div>
                            @if ($existingUserRole)
                                <span class="inline-flex items-center gap-1.5 self-start sm:self-auto text-xs font-bold text-emerald-800 bg-emerald-100/90 px-3 py-1 rounded-full border border-emerald-300 shadow-2xs">
                                    <i class="fa-solid fa-circle-check text-emerald-600"></i> {{ $existingUserRole }} Account Linked
                                </span>
                            @endif
                        </div>

                        <div class="mt-4 space-y-3">
                            <div class="flex flex-col sm:flex-row gap-2.5">
                                <div class="relative flex-1">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-envelope text-sm"></i>
                                    </div>
                                    <input
                                        type="email"
                                        wire:model="lookupEmail"
                                        wire:keydown.enter.prevent="checkAndPullDetails"
                                        placeholder="Enter your registered email address (e.g. thinker.net01@gmail.com)..."
                                        class="w-full rounded-xl bg-white border border-teal-200/90 pl-11 pr-4 py-2.5 text-xs sm:text-sm font-medium text-slate-900 placeholder:text-slate-400 focus:bg-white focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 focus:outline-none transition-all shadow-2xs"
                                    >
                                </div>
                                <button
                                    type="button"
                                    wire:click="checkAndPullDetails"
                                    class="px-5 py-2.5 rounded-xl bg-[#0a2d27] hover:bg-[#11443c] text-white text-xs sm:text-sm font-bold shadow-xs transition-all flex items-center justify-center gap-2 shrink-0 cursor-pointer active:scale-98"
                                >
                                    <span wire:loading.remove wire:target="checkAndPullDetails" class="inline-flex items-center gap-1.5">
                                        <i class="fa-solid fa-magnifying-glass text-xs"></i> Check &amp; Pull Details
                                    </span>
                                    <span wire:loading wire:target="checkAndPullDetails" class="inline-flex items-center gap-1.5">
                                        <i class="fa-solid fa-spinner fa-spin text-xs"></i> Checking...
                                    </span>
                                </button>
                            </div>

                            {{-- Status / Feedback Alert Banner --}}
                            @if ($lookupMessage)
                                <div class="p-3.5 rounded-xl text-xs font-medium flex items-start gap-2.5 {{ $lookupStatus === 'success' ? 'bg-emerald-50 text-emerald-900 border border-emerald-300 shadow-2xs' : 'bg-rose-50 text-rose-900 border border-rose-200 shadow-2xs' }}">
                                    <div class="w-5 h-5 rounded-full {{ $lookupStatus === 'success' ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }} flex items-center justify-center shrink-0 mt-0.5 text-xs">
                                        <i class="fa-solid {{ $lookupStatus === 'success' ? 'fa-check' : 'fa-exclamation' }}"></i>
                                    </div>
                                    <span class="leading-relaxed flex-1">{{ $lookupMessage }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Personal Information --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-2">Full Name <span class="text-rose-500">*</span></label>
                            <input
                                type="text"
                                wire:model="regName"
                                placeholder="John Doe"
                                class="w-full rounded-xl bg-slate-50/90 border border-slate-200/90 px-4 py-3 text-sm font-medium text-slate-900 placeholder:text-slate-400 focus:bg-white focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 focus:outline-none transition-all duration-200"
                                required
                            >
                            @error('regName') <span class="text-xs text-rose-600 mt-1.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-2">Email Address <span class="text-rose-500">*</span></label>
                            <input
                                type="email"
                                wire:model="regEmail"
                                placeholder="john@example.com"
                                class="w-full rounded-xl bg-slate-50/90 border border-slate-200/90 px-4 py-3 text-sm font-medium text-slate-900 placeholder:text-slate-400 focus:bg-white focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 focus:outline-none transition-all duration-200"
                                required
                            >
                            @error('regEmail') <span class="text-xs text-rose-600 mt-1.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Role & Specialty --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Role / Specialization <span class="text-rose-500">*</span></label>
                        <select
                            wire:model.live="regRole"
                            class="w-full rounded-xl bg-slate-50/90 border border-slate-200/90 px-4 py-3 text-sm font-semibold text-slate-900 focus:bg-white focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 focus:outline-none transition-all duration-200 cursor-pointer"
                        >
                            <option value="blogger">Blogger (Short Blogs &amp; Tech Articles)</option>
                            <option value="researcher">Researcher (Tips, Tricks &amp; Code Walkthroughs)</option>
                            <option value="employer">Employer (Opportunities &amp; Job Postings)</option>
                        </select>
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed">Select your specialty. All contributor registrations require admin approval before posts go live.</p>
                        @error('regRole') <span class="text-xs text-rose-600 mt-1.5 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        @if ($regRole === 'employer')
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-2">Company / Organization <span class="text-rose-500">*</span></label>
                                <input
                                    type="text"
                                    wire:model="regCompany"
                                    placeholder="e.g. Thinker HUB"
                                    class="w-full rounded-xl bg-slate-50/90 border border-slate-200/90 px-4 py-3 text-sm font-medium text-slate-900 placeholder:text-slate-400 focus:bg-white focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 focus:outline-none transition-all duration-200"
                                >
                                @error('regCompany') <span class="text-xs text-rose-600 mt-1.5 block">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div class="{{ $regRole === 'employer' ? '' : 'sm:col-span-2' }}">
                            <label class="block text-xs font-bold text-slate-700 mb-2">
                                Technical Specialty <span class="text-rose-500">*</span>
                            </label>
                            <select
                                wire:model.live="regSpecialty"
                                class="w-full rounded-xl bg-slate-50/90 border border-slate-200/90 px-4 py-3 text-sm font-semibold text-slate-900 focus:bg-white focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 focus:outline-none transition-all duration-200 cursor-pointer"
                                required
                            >
                                <option value="">-- Select Technical Specialty --</option>
                                @foreach ($this->specialtyOptions as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-slate-500 mt-1.5 leading-relaxed">
                                Suggested specialties tailored dynamically for {{ match($regRole) { 'blogger' => 'Bloggers', 'researcher' => 'Researchers', 'employer' => 'Employers', default => 'Contributors' } }}.
                            </p>
                            @error('regSpecialty') <span class="text-xs text-rose-600 mt-1.5 block">{{ $message }}</span> @enderror

                            @if ($regSpecialty === 'Other / Custom Specialty')
                                <div class="mt-2.5">
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Specify Your Custom Technical Specialty</label>
                                    <input
                                        type="text"
                                        wire:model="customSpecialty"
                                        placeholder="e.g. Distributed Systems, Bio-Informatics, Robotics, FinTech"
                                        class="w-full rounded-xl bg-white border border-teal-300 px-4 py-2.5 text-xs font-medium text-slate-900 placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 focus:outline-none transition-all"
                                        required
                                    >
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Bio --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Bio / Profile Summary</label>
                        <textarea
                            wire:model="regBio"
                            rows="3"
                            placeholder="Tell the community about your expertise, background, and what you plan to share..."
                            class="w-full rounded-xl bg-slate-50/90 border border-slate-200/90 p-4 text-sm font-medium text-slate-900 placeholder:text-slate-400 focus:bg-white focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 focus:outline-none transition-all duration-200 resize-none"
                        ></textarea>
                    </div>

                    {{-- Social & Contact Links --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Social &amp; Contact Links</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">WhatsApp Phone / Link</label>
                                <input
                                    type="text"
                                    wire:model="regWhatsapp"
                                    placeholder="+260..."
                                    class="w-full rounded-xl bg-slate-50/90 border border-slate-200/90 px-3.5 py-2.5 text-xs font-medium text-slate-900 placeholder:text-slate-400 focus:bg-white focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 focus:outline-none transition-all duration-200"
                                >
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">LinkedIn URL</label>
                                <input
                                    type="url"
                                    wire:model="regLinkedinUrl"
                                    placeholder="https://linkedin.com/in/..."
                                    class="w-full rounded-xl bg-slate-50/90 border border-slate-200/90 px-3.5 py-2.5 text-xs font-medium text-slate-900 placeholder:text-slate-400 focus:bg-white focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 focus:outline-none transition-all duration-200"
                                >
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Facebook URL</label>
                                <input
                                    type="url"
                                    wire:model="regFacebookUrl"
                                    placeholder="https://facebook.com/..."
                                    class="w-full rounded-xl bg-slate-50/90 border border-slate-200/90 px-3.5 py-2.5 text-xs font-medium text-slate-900 placeholder:text-slate-400 focus:bg-white focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 focus:outline-none transition-all duration-200"
                                >
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">GitHub / Portfolio</label>
                                <input
                                    type="url"
                                    wire:model="regGithubUrl"
                                    placeholder="https://github.com/..."
                                    class="w-full rounded-xl bg-slate-50/90 border border-slate-200/90 px-3.5 py-2.5 text-xs font-medium text-slate-900 placeholder:text-slate-400 focus:bg-white focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 focus:outline-none transition-all duration-200"
                                >
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Instagram URL</label>
                                <input
                                    type="url"
                                    wire:model="regInstagramUrl"
                                    placeholder="https://instagram.com/..."
                                    class="w-full rounded-xl bg-slate-50/90 border border-slate-200/90 px-3.5 py-2.5 text-xs font-medium text-slate-900 placeholder:text-slate-400 focus:bg-white focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 focus:outline-none transition-all duration-200"
                                >
                            </div>
                        </div>
                    </div>

                    {{-- Security / Password Section --}}
                    @if (! $existingUserId)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-2">
                                    Password <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="password"
                                    wire:model="regPassword"
                                    placeholder="••••••••"
                                    class="w-full rounded-xl bg-slate-50/90 border border-slate-200/90 px-4 py-3 text-sm font-medium text-slate-900 placeholder:text-slate-400 focus:bg-white focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 focus:outline-none transition-all duration-200"
                                    required
                                >
                                @error('regPassword') <span class="text-xs text-rose-600 mt-1.5 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-2">
                                    Confirm Password <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="password"
                                    wire:model="regPasswordConfirmation"
                                    placeholder="••••••••"
                                    class="w-full rounded-xl bg-slate-50/90 border border-slate-200/90 px-4 py-3 text-sm font-medium text-slate-900 placeholder:text-slate-400 focus:bg-white focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 focus:outline-none transition-all duration-200"
                                    required
                                >
                            </div>
                        </div>
                    @else
                        <div class="rounded-2xl border border-teal-200/90 bg-gradient-to-br from-teal-50/80 via-emerald-50/50 to-slate-50 p-5 shadow-xs">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-2.5 border-b border-teal-100/80">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-8 h-8 rounded-lg bg-[#0a2d27] text-yellow-400 flex items-center justify-center text-xs shadow-2xs shrink-0">
                                        <i class="fa-solid fa-arrows-rotate"></i>
                                    </span>
                                    <h5 class="text-xs sm:text-sm font-bold text-slate-900">
                                        Single Sign-On &amp; Role Switching
                                    </h5>
                                </div>
                                <span class="inline-flex items-center gap-1.5 self-start sm:self-auto text-[11px] font-bold text-emerald-800 bg-emerald-100/90 px-2.5 py-0.5 rounded-full border border-emerald-300 shadow-2xs">
                                    <i class="fa-solid fa-lock text-[10px] text-emerald-600"></i> Same Password Used
                                </span>
                            </div>
                            <p class="text-xs text-slate-600 mt-2.5 leading-relaxed">
                                Your existing Thinker HUB password will be used for this profile. Once approved by Admin, you can switch smoothly between your roles in the portal without logging in separately.
                            </p>
                        </div>
                    @endif

                    {{-- Actions --}}
                    <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <span class="text-xs text-slate-500">
                            Already registered? <a href="{{ route('login') }}" class="text-teal-700 font-bold hover:underline">Log in</a>
                        </span>
                        <div class="flex items-center gap-3 w-full sm:w-auto">
                            <button
                                wire:click="closeRegisterModal"
                                type="button"
                                class="flex-1 sm:flex-none px-6 py-3 rounded-full bg-slate-100 text-slate-600 text-xs font-bold hover:bg-slate-200 transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="flex-1 sm:flex-none px-8 py-3 rounded-full bg-[#0a2d27] text-white text-xs font-bold shadow-md hover:bg-[#11443c] hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2"
                            >
                                <span wire:loading.remove wire:target="registerContributor">Register</span>
                                <span wire:loading wire:target="registerContributor" class="flex items-center gap-2">
                                    <i class="fa-solid fa-spinner fa-spin text-xs"></i> Processing...
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Video Player Modal --}}
    @if ($activeVideo)
        <div
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/85 backdrop-blur-sm flex items-center justify-center p-4 sm:p-6"
            wire:click.self="closeVideoModal"
            @keydown.escape.window="$wire.closeVideoModal()"
        >
            <div class="bg-white w-full max-w-4xl rounded-[2.5rem] shadow-2xl overflow-hidden relative border border-slate-800">
                <div class="p-4 bg-slate-950 text-white flex items-center justify-between px-6">
                    <div class="flex items-center gap-2 truncate pr-4">
                        <span class="bg-rose-600 text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full">
                            Video Tutorial
                        </span>
                        <h3 class="text-sm font-bold truncate text-slate-200">{{ $activeVideo->title }}</h3>
                    </div>
                    <button
                        wire:click="closeVideoModal"
                        type="button"
                        class="text-slate-400 hover:text-white bg-white/10 hover:bg-white/20 w-8 h-8 rounded-full flex items-center justify-center transition shrink-0"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                {{-- Responsive 16:9 Video Embed Container --}}
                <div class="relative aspect-video bg-black">
                    @if ($activeVideo->video_id)
                        <iframe
                            src="https://www.youtube-nocookie.com/embed/{{ $activeVideo->video_id }}?autoplay=1"
                            title="{{ $activeVideo->title }}"
                            class="w-full h-full"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                        ></iframe>
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center text-slate-400 p-8 text-center">
                            <i class="fa-solid fa-triangle-exclamation text-4xl text-amber-400 mb-3"></i>
                            <p class="font-semibold text-white">Video unavailable</p>
                            <p class="text-xs mt-1">Direct link: <a href="{{ $activeVideo->youtube_url }}" target="_blank" class="text-teal-400 underline">{{ $activeVideo->youtube_url }}</a></p>
                        </div>
                    @endif
                </div>

                <div class="p-6 bg-white flex items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-bold text-rose-700 bg-rose-50 px-3 py-0.5 rounded-full border border-rose-100">
                                {{ $activeVideo->category }}
                            </span>
                            <span class="text-xs text-slate-400 font-medium">Published {{ $activeVideo->created_at->format('M j, Y') }}</span>
                        </div>
                        <h2 class="text-xl font-bold text-slate-900">{{ $activeVideo->title }}</h2>
                    </div>

                    <a
                        href="{{ route('hub.show', $activeVideo->slug) }}"
                        class="inline-flex items-center gap-1 rounded-full bg-[#0a2d27] px-4 py-2 text-xs font-bold text-white shrink-0 hover:bg-[#11443c]"
                    >
                        Full Details <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Footer (Standard Site Footer) --}}
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
                            <li><a href="{{ route('landing.instructors') }}" class="transition hover:text-[#0a2d27]">Network</a></li>
                            <li><a href="{{ route('landing.contact') }}" class="transition hover:text-[#0a2d27]">Contact</a></li>
                            @auth
                                <li><a href="{{ route('dashboard') }}" class="transition hover:text-[#0a2d27]">Login</a></li>
                            @else
                                <li><a href="{{ route('login') }}" class="transition hover:text-[#0a2d27]">Login</a></li>
                            @endauth
                        </ul>
                    </div>

                <div>
                    <h3 class="text-sm font-bold text-slate-900">Contacts</h3>
                    <div class="mt-4 space-y-2.5 text-sm text-slate-500">
                        <p><span class="font-semibold text-slate-700">Phone:</span> <button type="button" @click="$dispatch('open-contact')" class="ml-1 text-[#0a2d27] font-medium underline-offset-2 hover:underline cursor-pointer">+260772640546</button></p>
                        <p><span class="font-semibold text-slate-700">Email:</span> <a href="mailto:thinkerhub@oristudiozm.com" class="text-[#0a2d27] underline-offset-2 hover:underline">thinkerhub@oristudiozm.com</a></p>
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
