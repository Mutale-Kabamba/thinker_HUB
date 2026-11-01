<x-filament-panels::page>
    <div 
        x-data="{
            scrollToBottom() {
                this.$nextTick(() => {
                    const el = this.$refs.messagesContainer;
                    if (el) { el.scrollTop = el.scrollHeight; }
                });
            }
        }"
        x-init="scrollToBottom()"
        @chat-room-selected.window="scrollToBottom()"
        @chat-message-sent.window="scrollToBottom()"
        class="space-y-4"
    >
        {{-- Privacy Notice Banner --}}
        <div class="rounded-2xl bg-gradient-to-r from-teal-900/90 to-emerald-900/90 text-white p-4 shadow-sm border border-teal-800/40 flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-teal-500/20 border border-teal-400/30 flex items-center justify-center text-teal-300 shrink-0">
                    <x-heroicon-o-shield-check class="w-5 h-5" />
                </div>
                <div>
                    <h4 class="font-bold text-sm text-white flex items-center gap-2">
                        <span>Mutual Parents Community Privacy</span>
                        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-teal-500/20 text-teal-200 border border-teal-400/30">Verified</span>
                    </h4>
                    <p class="text-xs text-teal-100/80 leading-relaxed mt-0.5">
                        In accordance with thinker HUB safety standards, you can only see, interact with, and message fellow parents whose children are enrolled in the same course(s) as your child.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button 
                    wire:click="$set('showNewChatModal', true)" 
                    type="button" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-teal-400 hover:bg-teal-300 text-teal-950 shadow-xs transition cursor-pointer"
                >
                    <x-heroicon-o-paper-airplane class="w-4 h-4" />
                    <span>New Direct Message</span>
                </button>
            </div>
        </div>

        {{-- Main Chat Layout Card --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden grid grid-cols-1 md:grid-cols-12 min-h-[620px] max-h-[750px]">
            
            {{-- ── Left Column: Rooms & Direct Chats List ── --}}
            <div class="md:col-span-4 border-r border-slate-200 dark:border-slate-800 flex flex-col h-full bg-slate-50/50 dark:bg-slate-900/50">
                
                {{-- Search & Filter Header --}}
                <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 space-y-2.5">
                    <div class="relative">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                        <input 
                            wire:model.live.debounce.300ms="search" 
                            type="text" 
                            placeholder="Filter forums or parents..." 
                            class="w-full text-xs pl-9 pr-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-hidden focus:border-teal-500"
                        />
                    </div>

                    <div class="flex items-center gap-1">
                        <button 
                            wire:click="$set('filter', 'all')" 
                            type="button" 
                            class="flex-1 py-1 px-2 rounded-lg text-[11px] font-bold text-center transition {{ $filter === 'all' ? 'bg-teal-600 text-white shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                        >
                            All
                        </button>
                        <button 
                            wire:click="$set('filter', 'forums')" 
                            type="button" 
                            class="flex-1 py-1 px-2 rounded-lg text-[11px] font-bold text-center transition {{ $filter === 'forums' ? 'bg-teal-600 text-white shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                        >
                            Course Forums
                        </button>
                        <button 
                            wire:click="$set('filter', 'direct')" 
                            type="button" 
                            class="flex-1 py-1 px-2 rounded-lg text-[11px] font-bold text-center transition {{ $filter === 'direct' ? 'bg-teal-600 text-white shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                        >
                            Direct Chats
                        </button>
                    </div>
                </div>

                {{-- Rooms List --}}
                <div class="flex-1 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800/60 p-2 space-y-1">
                    
                    {{-- 1. Course Forums Section --}}
                    @if($filter === 'all' || $filter === 'forums')
                        <div class="pt-1 pb-1 px-2">
                            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-1">
                                Course Parent Forums
                            </span>
                        </div>

                        @forelse($forumRooms as $fRoom)
                            @php
                                $matchesSearch = empty($search) || str_contains(mb_strtolower($fRoom['name']), mb_strtolower($search)) || str_contains(mb_strtolower($fRoom['course_title'] ?? ''), mb_strtolower($search));
                            @endphp
                            @if($matchesSearch)
                            <div 
                                wire:click="selectRoom({{ $fRoom['id'] }})"
                                class="p-3 rounded-xl cursor-pointer transition-all flex items-start gap-3 {{ $selectedRoomId === $fRoom['id'] ? 'bg-teal-50 dark:bg-teal-950/50 border border-teal-200 dark:border-teal-800 shadow-2xs' : 'hover:bg-white dark:hover:bg-slate-800/60' }}"
                            >
                                <div class="w-10 h-10 rounded-xl bg-teal-100 dark:bg-teal-900/60 text-teal-700 dark:text-teal-300 flex items-center justify-center shrink-0 border border-teal-200/60">
                                    <x-heroicon-o-academic-cap class="w-5 h-5" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1">
                                        <h5 class="text-xs font-bold text-slate-900 dark:text-white truncate">
                                            {{ $fRoom['name'] }}
                                        </h5>
                                        @if($fRoom['latest_time'])
                                            <span class="text-[10px] text-slate-400 shrink-0">{{ $fRoom['latest_time'] }}</span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                        {{ $fRoom['latest_message'] ?: 'No posts yet. Start discussion!' }}
                                    </p>
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded-sm bg-teal-100/80 dark:bg-teal-950 text-teal-800 dark:text-teal-300">
                                            Cohort Forum
                                        </span>
                                        @if($fRoom['unread'])
                                            <span class="h-2 w-2 rounded-full bg-teal-500"></span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                        @empty
                            <div class="text-xs text-slate-400 italic p-3 text-center">
                                No course forums available.
                            </div>
                        @endforelse
                    @endif

                    {{-- 2. Direct Messages Section --}}
                    @if($filter === 'all' || $filter === 'direct')
                        <div class="pt-3 pb-1 px-2">
                            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-1">
                                Mutual Parents Direct Messages
                            </span>
                        </div>

                        @forelse($directRooms as $dRoom)
                            @php
                                $matchesSearch = empty($search) || str_contains(mb_strtolower($dRoom['name']), mb_strtolower($search));
                            @endphp
                            @if($matchesSearch)
                            <div 
                                wire:click="selectRoom({{ $dRoom['id'] }})"
                                class="p-3 rounded-xl cursor-pointer transition-all flex items-start gap-3 {{ $selectedRoomId === $dRoom['id'] ? 'bg-teal-50 dark:bg-teal-950/50 border border-teal-200 dark:border-teal-800 shadow-2xs' : 'hover:bg-white dark:hover:bg-slate-800/60' }}"
                            >
                                <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center font-bold text-xs text-slate-700 dark:text-slate-200 shrink-0 overflow-hidden border border-slate-300 dark:border-slate-600">
                                    @if(!empty($dRoom['avatar']))
                                        <img src="{{ $dRoom['avatar'] }}" alt="{{ $dRoom['name'] }}" class="w-full h-full object-cover">
                                    @else
                                        <span>{{ strtoupper(substr($dRoom['name'], 0, 1)) }}</span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1">
                                        <h5 class="text-xs font-bold text-slate-900 dark:text-white truncate">
                                            {{ $dRoom['name'] }}
                                        </h5>
                                        @if($dRoom['latest_time'])
                                            <span class="text-[10px] text-slate-400 shrink-0">{{ $dRoom['latest_time'] }}</span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                        {{ $dRoom['latest_message'] ?: 'Conversation initiated.' }}
                                    </p>
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <span class="text-[9px] font-bold text-slate-500 dark:text-slate-400">
                                            Course Parent
                                        </span>
                                        @if($dRoom['unread'])
                                            <span class="h-2 w-2 rounded-full bg-teal-500"></span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                        @empty
                            <div class="text-xs text-slate-400 italic p-3 text-center">
                                No direct chats yet. Click "New Direct Message" to connect!
                            </div>
                        @endforelse
                    @endif

                </div>
            </div>

            {{-- ── Right Column: Chat Room Messages & Composer ── --}}
            <div class="md:col-span-8 flex flex-col h-full bg-white dark:bg-slate-900">
                @if($this->activeRoom)
                    
                    {{-- Room Header --}}
                    <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-white dark:bg-slate-900">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-teal-100 dark:bg-teal-900/60 text-teal-700 dark:text-teal-300 flex items-center justify-center shrink-0 border border-teal-200/60 font-bold text-xs">
                                @if($this->activeRoom->type === 'course_parents')
                                    <x-heroicon-o-user-group class="w-5 h-5" />
                                @else
                                    <span>{{ strtoupper(substr($this->activeRoom->displayNameFor(auth()->user()), 0, 1)) }}</span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-sm font-bold text-slate-900 dark:text-white truncate">
                                    {{ $this->activeRoom->displayNameFor(auth()->user()) }}
                                </h4>
                                <div class="flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400">
                                    <span class="flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-semibold">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Mutual Parents Only</span>
                                    </span>
                                    <span>&bull;</span>
                                    <span>{{ $this->activeRoom->type === 'course_parents' ? 'Course Cohort Discussion' : 'Direct Parent Chat' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a 
                                href="{{ route('filament.parent.pages.course-parents') }}" 
                                class="text-xs font-bold text-teal-600 dark:text-teal-400 hover:underline inline-flex items-center gap-1"
                                title="View all verified parents in your child's courses"
                            >
                                <x-heroicon-o-users class="w-3.5 h-3.5" />
                                <span class="hidden sm:inline">Course Parents Circle</span>
                            </a>
                        </div>
                    </div>

                    {{-- Messages Stream Container --}}
                    <div 
                        x-ref="messagesContainer"
                        wire:poll.5s
                        class="flex-1 overflow-y-auto p-5 space-y-4 bg-slate-50/40 dark:bg-slate-950/40"
                    >
                        @forelse($this->messages as $msg)
                            @php
                                $isMine = (int) $msg->user_id === (int) auth()->id();
                                $sender = $msg->user;
                                $childNames = $sender ? $sender->kids->pluck('name')->map(fn ($n) => explode(' ', trim($n))[0])->join(', ') : '';
                            @endphp

                            <div class="flex items-end gap-2.5 {{ $isMine ? 'justify-end' : 'justify-start' }}">
                                @if(!$isMine)
                                    <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center font-bold text-[11px] text-slate-700 dark:text-slate-200 shrink-0 overflow-hidden mb-1">
                                        @if($sender?->profile_photo_path)
                                            <img src="{{ $sender->getFilamentAvatarUrl() }}" alt="{{ $sender->name }}" class="w-full h-full object-cover">
                                        @else
                                            <span>{{ strtoupper(substr($sender?->name ?? 'P', 0, 1)) }}</span>
                                        @endif
                                    </div>
                                @endif

                                <div class="max-w-[75%] sm:max-w-[65%] space-y-1">
                                    @if(!$isMine)
                                        <div class="flex items-center gap-1.5 pl-1">
                                            <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300">{{ $sender?->name ?? 'Parent' }}</span>
                                            @if($childNames)
                                                <span class="text-[10px] text-teal-600 dark:text-teal-400 font-medium">({{ $childNames }}'s Parent)</span>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="p-3.5 rounded-2xl text-xs leading-relaxed {{ $isMine ? 'bg-teal-600 text-white rounded-br-xs shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-bl-xs border border-slate-200 dark:border-slate-700 shadow-2xs' }}">
                                        <p class="whitespace-pre-line">{{ $msg->body }}</p>
                                    </div>

                                    <div class="text-[10px] text-slate-400 dark:text-slate-500 {{ $isMine ? 'text-right pr-1' : 'pl-1' }}">
                                        {{ $msg->created_at?->format('H:i') }} &bull; {{ $msg->created_at?->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="h-full flex flex-col items-center justify-center text-center p-8 text-slate-400">
                                <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3 text-slate-400">
                                    <x-heroicon-o-chat-bubble-bottom-center-text class="w-6 h-6" />
                                </div>
                                <h5 class="font-bold text-slate-700 dark:text-slate-300 text-sm">Welcome to the Parents Forum</h5>
                                <p class="text-xs max-w-sm mt-1">Be the first to say hello, ask questions, or share encouragement with fellow parents in this course!</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- Message Composer --}}
                    <div class="p-3.5 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                        <form wire:submit.prevent="sendMessage" class="flex items-center gap-2">
                            <input 
                                wire:model="newMessage" 
                                type="text" 
                                placeholder="Write a message to mutual parents in this forum..." 
                                class="flex-1 text-xs py-2.5 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-hidden focus:border-teal-500 focus:bg-white dark:focus:bg-slate-800"
                                required
                            />
                            <button 
                                type="submit" 
                                class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-xs transition cursor-pointer shrink-0"
                            >
                                <x-heroicon-o-paper-airplane class="w-4 h-4" />
                                <span class="hidden sm:inline">Send</span>
                            </button>
                        </form>
                    </div>

                @else
                    <div class="h-full flex flex-col items-center justify-center text-center p-8 text-slate-400">
                        <div class="w-14 h-14 rounded-full bg-teal-50 dark:bg-teal-950 flex items-center justify-center text-teal-600 mb-3">
                            <x-heroicon-o-chat-bubble-left-right class="w-7 h-7" />
                        </div>
                        <h4 class="font-bold text-slate-800 dark:text-slate-200 text-base">Select a Forum or Conversation</h4>
                        <p class="text-xs max-w-sm mt-1 text-slate-500">Choose a course parents forum from the left to engage with parents whose children share the same course.</p>
                    </div>
                @endif
            </div>

        </div>

        {{-- ── Modal: New Direct Message with Mutual Parents ── --}}
        @if($showNewChatModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
                <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">
                                Direct Message Mutual Parent
                            </h3>
                            <p class="text-xs text-slate-500">Only parents with children in your child's courses</p>
                        </div>
                        <button 
                            wire:click="$set('showNewChatModal', false)" 
                            type="button" 
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                        >
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="space-y-2 max-h-72 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($mutualParents as $mp)
                            <div class="pt-2 pb-2 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center font-bold text-xs text-slate-700 dark:text-slate-200 shrink-0">
                                        @if(!empty($mp['avatar']))
                                            <img src="{{ $mp['avatar'] }}" alt="{{ $mp['name'] }}" class="w-full h-full object-cover rounded-full">
                                        @else
                                            <span>{{ strtoupper(substr($mp['name'], 0, 1)) }}</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $mp['name'] }}</p>
                                        <p class="text-[11px] text-teal-600 dark:text-teal-400 font-medium">{{ $mp['children'] }}</p>
                                    </div>
                                </div>
                                <button 
                                    wire:click="startDirectChat({{ $mp['id'] }})" 
                                    type="button" 
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-teal-600 hover:bg-teal-700 text-white shadow-2xs transition"
                                >
                                    <span>Chat</span>
                                    <x-heroicon-o-chevron-right class="w-3 h-3" />
                                </button>
                            </div>
                        @empty
                            <div class="text-xs text-slate-400 italic p-6 text-center">
                                No other mutual parents are currently enrolled in your child's courses. Once another child is registered, their parent will appear here!
                            </div>
                        @endforelse
                    </div>

                    <div class="pt-2 text-right">
                        <button 
                            wire:click="$set('showNewChatModal', false)" 
                            type="button" 
                            class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
