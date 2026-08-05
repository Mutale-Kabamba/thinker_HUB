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

                {{-- Submit Resource CTA Button --}}
                <div class="mt-6 flex justify-center">
                    <button
                        wire:click="openSubmitModal"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-full bg-yellow-400 px-6 py-2.5 text-xs font-bold text-[#0a2d27] shadow-md hover:bg-white transition-all transform hover:-translate-y-0.5"
                    >
                        <i class="fa-solid fa-plus"></i> Submit Resource / Opportunity
                    </button>
                </div>

                {{-- Notice Banner --}}
                @if ($submitNoticeMessage)
                    <div class="mx-auto mt-6 max-w-xl p-4 bg-emerald-500/20 border border-emerald-400/40 rounded-2xl text-emerald-200 text-xs font-semibold flex items-center justify-between gap-3">
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
                                {{-- Expressive Video Card --}}
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

                            @elseif ($post->type === 'opportunity')
                                {{-- Expressive Opportunity Card --}}
                                <article class="group bg-white rounded-[2rem] p-6 shadow-xs hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 border border-slate-200/80 hover:border-emerald-300 flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between gap-2 mb-4">
                                            <span class="bg-emerald-600 text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full shadow-sm">
                                                <i class="fa-solid fa-briefcase mr-1"></i> Opportunity
                                            </span>
                                            @if ($post->opportunity_deadline)
                                                @php
                                                    $isPast = $post->opportunity_deadline->isPast() && ! $post->opportunity_deadline->isToday();
                                                @endphp
                                                @if ($isPast)
                                                    <span class="bg-slate-100 text-slate-500 text-[11px] font-bold px-3 py-1 rounded-full">
                                                        Closed
                                                    </span>
                                                @else
                                                    <span class="bg-amber-100 text-amber-800 text-[11px] font-bold px-3 py-1 rounded-full flex items-center gap-1 shadow-2xs">
                                                        <i class="fa-regular fa-clock"></i> Deadline: {{ $post->opportunity_deadline->format('M j') }}
                                                    </span>
                                                @endif
                                            @endif
                                        </div>

                                        <div class="mb-2.5">
                                            <span class="text-xs font-bold text-emerald-800 bg-emerald-50 px-3 py-0.5 rounded-full border border-emerald-100">
                                                {{ $post->category }}
                                            </span>
                                        </div>

                                        <h3 class="text-lg font-bold text-slate-900 group-hover:text-emerald-700 transition-colors leading-snug line-clamp-2">
                                            <a href="{{ route('hub.show', $post->slug) }}">{{ $post->title }}</a>
                                        </h3>

                                        @if ($post->excerpt)
                                            <p class="mt-3 text-xs text-slate-600 leading-relaxed line-clamp-3">
                                                {{ $post->excerpt }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                                        <span class="text-xs text-slate-400 font-medium">
                                            Posted {{ $post->created_at->diffForHumans() }}
                                        </span>
                                        @if ($post->opportunity_link)
                                            <a
                                                href="{{ $post->opportunity_link }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1.5 rounded-full bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-emerald-700 transition-colors"
                                            >
                                                Apply <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
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
                                {{-- Expressive Blog / Tip Card --}}
                                <article class="group bg-white rounded-[2rem] p-6 shadow-xs hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 border border-slate-200/80 hover:border-teal-300 flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between gap-2 mb-4">
                                            @if ($post->type === 'tip_trick')
                                                <span class="bg-teal-700 text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full shadow-sm">
                                                    <i class="fa-solid fa-wand-magic-sparkles mr-1"></i> Tip &amp; Trick
                                                </span>
                                            @else
                                                <span class="bg-indigo-700 text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full shadow-sm">
                                                    <i class="fa-solid fa-newspaper mr-1"></i> Article
                                                </span>
                                            @endif
                                            <span class="text-xs font-medium text-slate-400 flex items-center gap-1">
                                                <i class="fa-regular fa-clock"></i> {{ $post->reading_time }} min read
                                            </span>
                                        </div>

                                        <div class="mb-2.5">
                                            <span class="text-xs font-bold text-slate-700 bg-slate-100 px-3 py-0.5 rounded-full border border-slate-200">
                                                {{ $post->category }}
                                            </span>
                                        </div>

                                        <h3 class="text-lg font-bold text-slate-900 group-hover:text-teal-700 transition-colors leading-snug line-clamp-2">
                                            <a href="{{ route('hub.show', $post->slug) }}">{{ $post->title }}</a>
                                        </h3>

                                        @if ($post->excerpt)
                                            <p class="mt-3 text-xs text-slate-600 leading-relaxed line-clamp-3">
                                                {{ $post->excerpt }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-teal-100 text-teal-800 font-bold text-xs flex items-center justify-center">
                                                {{ strtoupper(substr($post->author->name ?? 'T', 0, 1)) }}
                                            </div>
                                            <span class="text-xs font-semibold text-slate-700 truncate max-w-[100px]">
                                                {{ $post->author->name ?? 'Thinker HUB' }}
                                            </span>
                                        </div>

                                        <a
                                            href="{{ route('hub.show', $post->slug) }}"
                                            class="inline-flex items-center gap-1 rounded-full bg-[#0a2d27] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#11443c]"
                                        >
                                            Read More <i class="fa-solid fa-arrow-right text-[10px] ml-1"></i>
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

    {{-- Submit Resource Modal --}}
    @if ($showSubmitModal)
        <div
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/75 backdrop-blur-sm flex items-center justify-center p-4 sm:p-6"
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
                        Resource Submission
                    </span>
                    <h2 class="text-2xl font-black text-white mt-2">Submit a Resource or Opportunity</h2>
                    <p class="text-xs text-slate-300 mt-1">
                        @if (auth()->user()?->isAdmin())
                            Publish a new item directly to the Knowledge Hub.
                        @else
                            Submissions will be reviewed and approved by an Admin before going public.
                        @endif
                    </p>
                </div>

                <form wire:submit.prevent="submitResource" class="p-6 sm:p-8 overflow-y-auto grow space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Title *</label>
                        <input
                            type="text"
                            wire:model="submitTitle"
                            placeholder="Resource title..."
                            class="w-full rounded-xl bg-slate-50 border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none"
                            required
                        >
                        @error('submitTitle') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Type *</label>
                            <select
                                wire:model.live="submitType"
                                class="w-full rounded-xl bg-slate-50 border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none"
                            >
                                <option value="blog">Short Blog</option>
                                <option value="tip_trick">Tip &amp; Trick</option>
                                <option value="video">Video Tutorial</option>
                                <option value="opportunity">Opportunity</option>
                            </select>
                            @error('submitType') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Category *</label>
                            <input
                                type="text"
                                wire:model="submitCategory"
                                placeholder="e.g. Programming, Career, Technology"
                                class="w-full rounded-xl bg-slate-50 border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none"
                                required
                            >
                            @error('submitCategory') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
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
                            @error('submitYoutubeUrl') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    @if ($submitType === 'opportunity')
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">External Application Link</label>
                                <input
                                    type="url"
                                    wire:model="submitOpportunityLink"
                                    placeholder="https://example.com/apply"
                                    class="w-full rounded-xl bg-slate-50 border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none"
                                >
                                @error('submitOpportunityLink') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Deadline Date</label>
                                <input
                                    type="date"
                                    wire:model="submitOpportunityDeadline"
                                    class="w-full rounded-xl bg-slate-50 border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none"
                                >
                                @error('submitOpportunityDeadline') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Brief Summary / Excerpt</label>
                        <textarea
                            wire:model="submitExcerpt"
                            rows="2"
                            placeholder="Short description displayed on resource cards..."
                            class="w-full rounded-xl bg-slate-50 border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none"
                        ></textarea>
                    </div>

                    @if (in_array($submitType, ['blog', 'tip_trick', 'opportunity']))
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Full Content</label>
                            <textarea
                                wire:model="submitContent"
                                rows="5"
                                placeholder="Detailed content, steps, instructions, or requirements..."
                                class="w-full rounded-xl bg-slate-50 border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none"
                            ></textarea>
                        </div>
                    @endif

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
                            class="px-6 py-2.5 rounded-full bg-[#0a2d27] text-white text-xs font-bold shadow-md hover:bg-[#11443c] transition"
                        >
                            Submit Resource
                        </button>
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
                        <li><a href="{{ route('landing.instructors') }}" class="transition hover:text-[#0a2d27]">Instructors</a></li>
                        <li><a href="{{ route('landing.contact') }}" class="transition hover:text-[#0a2d27]">Contact</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-slate-900">Contacts</h3>
                    <div class="mt-4 space-y-2.5 text-sm text-slate-500">
                        <div class="relative" x-data="{ phoneMenu: false }">
                            <span class="font-semibold text-slate-700">Phone:</span>
                            <button type="button" @click="phoneMenu = !phoneMenu" class="ml-1 text-[#0a2d27] underline-offset-2 hover:underline">+260772640546</button>
                            <div x-show="phoneMenu" x-transition @click.outside="phoneMenu = false" class="absolute left-0 z-20 mt-2 w-44 rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg" style="display: none;">
                                <a href="tel:+260772640546" class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"><i class="fa-solid fa-phone text-teal-600"></i>Call</a>
                                <a href="https://wa.me/260772640546" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"><i class="fa-brands fa-whatsapp text-green-600"></i>WhatsApp</a>
                            </div>
                        </div>
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
</div>
