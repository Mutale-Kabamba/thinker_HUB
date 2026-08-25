<x-filament-panels::page>
    <div
        x-data="{
            copied: null,
            shareOpen: null,
            emojiOpen: null,
            emojiInput: {},
            descriptionModalOpen: false,
            descriptionModalTitle: '',
            descriptionModalBody: '',
            copy(code, id) {
                navigator.clipboard.writeText(code).then(() => {
                    this.copied = id;
                    setTimeout(() => { if (this.copied === id) this.copied = null; }, 1500);
                });
            },
            copyLink(url, id) {
                if (!url) return;
                navigator.clipboard.writeText(url).then(() => {
                    this.copied = 'link-' + id;
                    setTimeout(() => { if (this.copied === 'link-' + id) this.copied = null; }, 1500);
                });
            },
            whatsapp(url, title) {
                window.open('https://wa.me/?text=' + encodeURIComponent(title + ' ' + (url || '')), '_blank');
            },
            facebook(url) {
                if (!url) return;
                window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url), '_blank');
            },
            sms(url, title) {
                window.open('sms:?&body=' + encodeURIComponent(title + ' ' + (url || '')), '_self');
            },
            openDescription(title, body) {
                this.descriptionModalTitle = title || 'Details';
                this.descriptionModalBody = body || '';
                this.descriptionModalOpen = true;
            }
        }"
        class="space-y-6 font-sans"
    >
        {{-- Header Quyl SaaS Hero Card --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 sm:p-6 border border-slate-100 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-purple-50 text-[#7C3AED] dark:bg-purple-900/30 dark:text-purple-300 border border-purple-100 dark:border-purple-800">
                    Growth &amp; Career Hub
                </span>
                <h2 class="text-lg sm:text-xl font-extrabold text-slate-800 dark:text-white tracking-tight">
                    Opportunities &amp; Resources
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                    Promo codes, job openings, scholarships, events, and recommended reading — curated for you.
                </p>
            </div>

            {{-- Filter Selector --}}
            <div class="flex items-center gap-2">
                <select 
                    wire:model.live="filterType" 
                    class="rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 px-3.5 py-2 shadow-2xs focus:ring-[#7C3AED] focus:border-[#7C3AED]"
                >
                    <option value="">All Types</option>
                    @foreach ($types as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if (count($opportunities) === 0)
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-10 text-center border border-slate-100 dark:border-slate-800 shadow-sm">
                <p class="text-xs text-slate-400">No opportunities available right now. Check back soon.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                @foreach ($opportunities as $item)
                    @php
                        $badgeStyle = match ($item['type']) {
                            'Promo Code' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-800',
                            'Job' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-800',
                            'Reading Material' => 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-900/30 dark:text-sky-300 dark:border-sky-800',
                            'Scholarship' => 'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-900/30 dark:text-teal-300 dark:border-teal-800',
                            'Event' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-300 dark:border-rose-800',
                            default => 'bg-purple-50 text-[#7C3AED] border-purple-200 dark:bg-purple-900/30 dark:text-purple-300 dark:border-purple-800',
                        };
                        $extra = $item['extra'] ?? [];
                    @endphp

                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-md transition-all flex flex-col justify-between gap-4">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $badgeStyle }}">
                                    {{ $item['type'] }}
                                </span>
                                @if ($item['expires_at'])
                                    <span class="text-[11px] text-slate-400 font-medium">Expires {{ $item['expires_at'] }}</span>
                                @endif
                            </div>

                            <div>
                                <h3 class="text-sm font-extrabold text-slate-800 dark:text-white line-clamp-2">
                                    {{ $item['title'] }}
                                </h3>
                                @if ($item['provider'])
                                    <p class="text-xs font-semibold text-slate-400 mt-0.5">{{ $item['provider'] }}</p>
                                @endif
                            </div>

                            @if ($item['description'])
                                @php
                                    $descriptionLength = mb_strlen($item['description']);
                                    $isLongDescription = $descriptionLength > 180;
                                @endphp
                                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                    {{ $isLongDescription ? \Illuminate\Support\Str::limit($item['description'], 180) : $item['description'] }}
                                </p>
                                @if ($isLongDescription)
                                    <button
                                        type="button"
                                        x-on:click="openDescription(@js($item['title']), @js($item['description']))"
                                        class="text-[11px] font-bold text-[#7C3AED] hover:underline"
                                    >
                                        Read more
                                    </button>
                                @endif
                            @endif

                            {{-- Type Specific Extra Details --}}
                            @if ($item['type'] === 'Job')
                                <div class="grid grid-cols-2 gap-2 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 text-xs">
                                    @if (!empty($extra['role']))
                                        <div><span class="text-slate-400 block text-[10px]">Role</span> <span class="font-bold text-slate-800 dark:text-slate-200">{{ $extra['role'] }}</span></div>
                                    @endif
                                    @if (!empty($extra['location']))
                                        <div><span class="text-slate-400 block text-[10px]">Location</span> <span class="font-bold text-slate-800 dark:text-slate-200">{{ $extra['location'] }}</span></div>
                                    @endif
                                    @if (!empty($extra['job_mode']))
                                        <div><span class="text-slate-400 block text-[10px]">Mode</span> <span class="font-bold text-slate-800 dark:text-slate-200">{{ $extra['job_mode'] }}</span></div>
                                    @endif
                                    @if (!empty($extra['salary']))
                                        <div><span class="text-slate-400 block text-[10px]">Pay</span> <span class="font-bold text-slate-800 dark:text-slate-200">{{ $extra['salary'] }}</span></div>
                                    @endif
                                </div>
                            @endif

                            @if ($item['type'] === 'Scholarship')
                                <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 space-y-1 text-xs">
                                    @if (!empty($extra['value']))
                                        <p><strong class="text-slate-700 dark:text-slate-300">Value:</strong> <span class="text-slate-500 dark:text-slate-400">{{ $extra['value'] }}</span></p>
                                    @endif
                                    @if (!empty($extra['eligibility']))
                                        <p><strong class="text-slate-700 dark:text-slate-300">Eligibility:</strong> <span class="text-slate-500 dark:text-slate-400">{{ $extra['eligibility'] }}</span></p>
                                    @endif
                                </div>
                            @endif

                            @if ($item['promo_code'])
                                <div class="flex items-center gap-2 p-2 rounded-xl bg-amber-50/60 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800">
                                    <code class="text-xs font-mono font-bold text-amber-800 dark:text-amber-300 flex-1">{{ $item['promo_code'] }}</code>
                                    <button
                                        type="button"
                                        @click="copy(@js($item['promo_code']), {{ $item['id'] }})"
                                        class="px-2.5 py-1 text-xs font-bold rounded-lg bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 shadow-2xs hover:bg-slate-50"
                                    >
                                        <span x-show="copied !== {{ $item['id'] }}">Copy</span>
                                        <span x-show="copied === {{ $item['id'] }}" x-cloak class="text-emerald-600">Copied!</span>
                                    </button>
                                </div>
                            @endif
                        </div>

                        {{-- Card Actions & Reaction Strip --}}
                        <div class="space-y-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                            <div class="flex items-center justify-between gap-2">
                                {{-- Reactions Cluster --}}
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    @foreach ($item['reactions'] as $reaction)
                                        <button
                                            type="button"
                                            wire:click="toggleReaction({{ $item['id'] }}, @js($reaction['emoji']))"
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold border transition-all {{ !empty($reaction['mine']) ? 'bg-purple-50 text-[#7C3AED] border-purple-300 dark:bg-purple-900/40 dark:border-purple-700' : 'bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700' }}"
                                        >
                                            <span>{{ $reaction['emoji'] }}</span>
                                            <span class="text-[10px]">{{ $reaction['count'] }}</span>
                                        </button>
                                    @endforeach

                                    {{-- Emoji Picker Trigger --}}
                                    <div class="relative">
                                        <button
                                            type="button"
                                            x-on:click="emojiOpen = emojiOpen === {{ $item['id'] }} ? null : {{ $item['id'] }}"
                                            class="w-7 h-7 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
                                        </button>
                                        <div
                                            x-show="emojiOpen === {{ $item['id'] }}"
                                            x-cloak
                                            x-transition.opacity
                                            class="absolute bottom-9 left-0 z-40 p-2.5 bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 min-w-[200px]"
                                        >
                                            <div class="flex gap-1.5 flex-wrap mb-2">
                                                @foreach (['🔥','👍','❤️','🎉','👏','😮','💯','🚀','🙌','🥳'] as $quickEmoji)
                                                    <button type="button" wire:click="toggleReaction({{ $item['id'] }}, @js($quickEmoji))" x-on:click="emojiOpen=null" class="text-base hover:scale-125 transition-transform">{{ $quickEmoji }}</button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Share & Comment Actions --}}
                                <div class="flex items-center gap-1 text-slate-400">
                                    <div class="relative">
                                        <button
                                            type="button"
                                            x-on:click="shareOpen = shareOpen === {{ $item['id'] }} ? null : {{ $item['id'] }}"
                                            class="w-7 h-7 rounded-full flex items-center justify-center hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                                        </button>
                                        <div
                                            x-show="shareOpen === {{ $item['id'] }}"
                                            x-cloak
                                            x-transition.opacity
                                            class="absolute bottom-9 right-0 z-40 p-2 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-100 dark:border-slate-700 min-w-[180px] text-xs font-semibold space-y-1"
                                        >
                                            <button type="button" x-on:click="whatsapp(@js($item['link_url']), @js($item['title'])); shareOpen=null" class="w-full text-left px-2.5 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200">WhatsApp</button>
                                            <button type="button" x-on:click="facebook(@js($item['link_url'])); shareOpen=null" class="w-full text-left px-2.5 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200">Facebook</button>
                                            <button type="button" x-on:click="copyLink(@js($item['link_url']), {{ $item['id'] }}); shareOpen=null" class="w-full text-left px-2.5 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200">Copy Link</button>
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        wire:click="toggleComments({{ $item['id'] }})"
                                        class="w-7 h-7 rounded-full flex items-center justify-center hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    </button>
                                </div>
                            </div>

                            @if ($item['link_url'])
                                <a
                                    href="{{ $item['link_url'] }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="w-full inline-flex items-center justify-center gap-1.5 py-2 rounded-full text-xs font-bold text-white bg-[#7C3AED] hover:bg-[#6D28D9] shadow-xs transition-colors"
                                >
                                    <span>{{ $item['type'] === 'Job' ? 'View & Apply' : ($item['type'] === 'Reading Material' ? 'Open Resource' : 'Learn More') }}</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                            @endif
                        </div>

                        @if ($openComments === $item['id'])
                            <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                                @livewire('comment-section', ['type' => 'opportunity', 'id' => $item['id']], key('cs-opp-'.$item['id']))
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Description Dialog Modal --}}
        <div
            x-show="descriptionModalOpen"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
            x-on:click.self="descriptionModalOpen = false"
        >
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 dark:border-slate-800 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 x-text="descriptionModalTitle" class="text-base font-extrabold text-slate-800 dark:text-white"></h3>
                    <button type="button" x-on:click="descriptionModalOpen = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>
                <div class="max-h-[60vh] overflow-y-auto">
                    <p x-text="descriptionModalBody" class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-wrap"></p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
