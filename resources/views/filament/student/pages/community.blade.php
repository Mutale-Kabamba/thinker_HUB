<x-filament-panels::page>

    <style>
        .fi-header,
        header.fi-header,
        .fi-page-header {
            display: none !important;
        }
        .fi-main-ctn,
        .fi-page,
        .fi-main {
            padding: 0 !important;
            max-width: 100% !important;
        }
        .hub-shell {
            padding: 0 !important;
            margin: 0 !important;
        }
    </style>

    <div class="w-full h-[calc(100dvh-64px)] flex flex-col overflow-hidden bg-slate-50 dark:bg-[#0b141a] text-gray-900 dark:text-gray-100">

        {{-- 1. FIXED TOP HEADER & GLOBAL TAB BAR (DOES NOT SCROLL) --}}
        @if (! $selectedRoomId)
            <header class="flex-shrink-0 px-4 pt-3 pb-2 space-y-3 bg-slate-50 dark:bg-[#0b141a] border-b border-gray-200/80 dark:border-gray-800/80 z-20">
                <!-- Header Title & XP Badge -->
                <div class="flex items-center justify-between">
                    <h1 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">Community</h1>
                    <button type="button" wire:click="setTab('leaderboard')" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white dark:bg-[#111b21] border border-gray-200 dark:border-gray-800 shadow-xs text-xs font-semibold text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-800 transition cursor-pointer" title="View XP & Badges Leaderboard">
                        <span class="text-amber-500">⚡</span>
                        <span>{{ number_format($this->myXp['xp'] ?? 0) }} XP</span>
                        <span class="text-gray-300 dark:text-gray-700">•</span>
                        <span class="text-amber-500">⭐</span>
                        <span>{{ $this->myXp['badge_count'] ?? 0 }} badges</span>
                        @if (count($this->myXp['badge_icons'] ?? []) > 0)
                            <span>{{ implode('', $this->myXp['badge_icons']) }}</span>
                        @else
                            <span>💯</span>
                        @endif
                    </button>
                </div>

                <!-- Unified Pill Tabs (4-Column Fit Grid, Zero Horizontal Scrolling on Mobile) -->
                <div class="grid grid-cols-4 gap-1 xs:gap-1.5 sm:gap-2.5 py-1 w-full max-w-xl mx-auto">
                    @php $isChats = ($tab === 'chats'); @endphp
                    <button 
                        type="button" 
                        wire:click="setTab('chats')" 
                        class="w-full flex items-center justify-center gap-1 sm:gap-1.5 px-1 xs:px-2 sm:px-3 py-1.5 sm:py-2 rounded-full text-[11px] xs:text-xs sm:text-sm font-bold transition-all cursor-pointer {{ $isChats ? 'bg-[#008069] text-white shadow-xs border border-[#008069]' : 'bg-white dark:bg-[#111b21] text-gray-700 dark:text-gray-200 border border-gray-200/90 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/80 shadow-2xs' }}"
                        @if($isChats) style="background-color: #008069 !important; color: #ffffff !important; border-color: #008069 !important;" @endif
                    >
                        <x-heroicon-s-chat-bubble-left-right class="w-3.5 h-3.5 shrink-0 {{ $isChats ? 'text-white' : 'text-emerald-600 dark:text-emerald-400' }}" />
                        <span class="truncate">Chats</span>
                    </button>

                    @php $isResults = ($tab === 'results' || $tab === 'scores'); @endphp
                    <button 
                        type="button" 
                        wire:click="setTab('results')" 
                        class="w-full flex items-center justify-center gap-1 sm:gap-1.5 px-1 xs:px-2 sm:px-3 py-1.5 sm:py-2 rounded-full text-[11px] xs:text-xs sm:text-sm font-bold transition-all cursor-pointer {{ $isResults ? 'bg-[#008069] text-white shadow-xs border border-[#008069]' : 'bg-white dark:bg-[#111b21] text-gray-700 dark:text-gray-200 border border-gray-200/90 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/80 shadow-2xs' }}"
                        @if($isResults) style="background-color: #008069 !important; color: #ffffff !important; border-color: #008069 !important;" @endif
                    >
                        <x-heroicon-s-chart-bar class="w-3.5 h-3.5 shrink-0 {{ $isResults ? 'text-white' : 'text-teal-600 dark:text-teal-400' }}" />
                        <span class="truncate">Score Board</span>
                    </button>

                    @php $isFriends = ($tab === 'friends'); @endphp
                    <button 
                        type="button" 
                        wire:click="setTab('friends')" 
                        class="w-full flex items-center justify-center gap-1 sm:gap-1.5 px-1 xs:px-2 sm:px-3 py-1.5 sm:py-2 rounded-full text-[11px] xs:text-xs sm:text-sm font-bold transition-all cursor-pointer {{ $isFriends ? 'bg-[#008069] text-white shadow-xs border border-[#008069]' : 'bg-white dark:bg-[#111b21] text-gray-700 dark:text-gray-200 border border-gray-200/90 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/80 shadow-2xs' }}"
                        @if($isFriends) style="background-color: #008069 !important; color: #ffffff !important; border-color: #008069 !important;" @endif
                    >
                        <x-heroicon-s-user-group class="w-3.5 h-3.5 shrink-0 {{ $isFriends ? 'text-white' : 'text-sky-600 dark:text-sky-400' }}" />
                        <span class="truncate">Friends</span>
                        @if ($this->pendingRequests->count() > 0)
                            <span class="bg-rose-600 text-white rounded-full text-[8px] xs:text-[9px] px-1 py-0.2 leading-none shrink-0 font-extrabold">{{ $this->pendingRequests->count() }}</span>
                        @endif
                    </button>

                    @php $isLeaderboard = ($tab === 'leaderboard' || $tab === 'ranks'); @endphp
                    <button 
                        type="button" 
                        wire:click="setTab('leaderboard')" 
                        class="w-full flex items-center justify-center gap-1 sm:gap-1.5 px-1 xs:px-2 sm:px-3 py-1.5 sm:py-2 rounded-full text-[11px] xs:text-xs sm:text-sm font-bold transition-all cursor-pointer {{ $isLeaderboard ? 'bg-[#008069] text-white shadow-xs border border-[#008069]' : 'bg-white dark:bg-[#111b21] text-gray-700 dark:text-gray-200 border border-gray-200/90 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/80 shadow-2xs' }}"
                        @if($isLeaderboard) style="background-color: #008069 !important; color: #ffffff !important; border-color: #008069 !important;" @endif
                    >
                        <x-heroicon-s-trophy class="w-3.5 h-3.5 shrink-0 {{ $isLeaderboard ? 'text-white' : 'text-amber-600 dark:text-amber-400' }}" />
                        <span class="truncate">Ranks</span>
                    </button>
                </div>
            </header>
        @endif

        {{-- 2. SCROLLABLE TAB CONTENTS (ONLY THIS CONTENT AREA SCROLLS) --}}
        <div class="flex-1 overflow-hidden relative flex flex-col">

            {{-- ===================== TAB 1: CHATS ===================== --}}
            @if ($tab === 'chats')
                @php
                    $allRooms = $this->rooms;
                    $activeRoom = $this->activeRoom;
                @endphp

                @if ($selectedRoomId && $activeRoom)
                    {{-- 1. FULLSCREEN LOCKED CHAT ROOM (ZERO PAGE SCROLLING, WHATSAPP IMMERSIVE) --}}
                    <div class="fixed inset-0 top-0 sm:top-[64px] z-50 flex flex-col bg-[#efeae2] dark:bg-[#0b141a] overflow-hidden text-gray-900 dark:text-gray-100">

                        {{-- FIXED TOP CHAT HEADER --}}
                        <header class="h-14 bg-white dark:bg-[#202c33] px-3 sm:px-4 flex items-center justify-between border-b border-gray-200 dark:border-gray-700/60 flex-shrink-0 z-10 shadow-xs">
                            <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                                <button type="button" wire:click="closeRoom" class="p-1.5 -ml-1 text-gray-600 dark:text-gray-300 hover:text-black dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition shrink-0" title="Back to chat list">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>
                                
                                {{-- Avatar with Online Indicator --}}
                                <div class="relative shrink-0">
                                    @if ($activeRoom->type === 'course')
                                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-[#00a884] to-[#008069] text-white font-bold flex items-center justify-center text-sm shadow-xs">
                                            <x-heroicon-s-academic-cap class="w-5 h-5" />
                                        </div>
                                    @else
                                        @php
                                            $otherUser = $activeRoom->members->firstWhere('id', '!=', auth()->id());
                                            $avatar = $otherUser?->getFilamentAvatarUrl();
                                            $initials = strtoupper(substr($activeRoom->displayNameFor(auth()->user()), 0, 2));
                                        @endphp
                                        @if ($avatar)
                                            <img src="{{ $avatar }}" alt="{{ $activeRoom->displayNameFor(auth()->user()) }}" class="w-9 h-9 rounded-full object-cover border border-gray-200 dark:border-gray-700">
                                        @else
                                            <div class="w-9 h-9 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center text-sm shadow-xs">
                                                {{ $initials }}
                                            </div>
                                        @endif
                                    @endif
                                    <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-500 border-2 border-white dark:border-[#202c33] rounded-full"></span>
                                </div>

                                {{-- Name & Subtitle --}}
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate leading-tight">
                                        {{ $activeRoom->displayNameFor(auth()->user()) }}
                                    </h3>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">
                                        @if ($activeRoom->type === 'course')
                                            {{ $activeRoom->members->count() }} cohort members
                                        @else
                                            Direct Message
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-1 shrink-0 text-gray-600 dark:text-gray-300">
                                <button type="button" wire:click="$refresh" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition" title="Refresh messages">
                                    <x-heroicon-o-arrow-path class="w-5 h-5" />
                                </button>
                            </div>
                        </header>

                        {{-- SCROLLABLE MESSAGES STREAM (ONLY THIS SCROLLS) --}}
                        <main 
                            class="flex-1 overflow-y-auto p-2 sm:p-3 space-y-1.5 bg-repeat" 
                            style="background-image: radial-gradient(rgba(0,0,0,0.04) 1px, transparent 0); background-size: 16px 16px;"
                            x-data="{
                                scrollToBottom() {
                                    $el.scrollTop = $el.scrollHeight;
                                }
                            }"
                            x-init="$nextTick(() => scrollToBottom())"
                            x-on:message-sent.window="$nextTick(() => scrollToBottom())"
                        >
                            @if ($this->hasMoreMessages)
                                <div class="text-center my-1">
                                    <button type="button" wire:click="loadMoreMessages" class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 shadow-xs hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                        Load earlier messages
                                    </button>
                                </div>
                            @endif

                            @forelse ($this->messages as $msg)
                                @php
                                    $isMe = $msg->user_id === auth()->id();
                                    $groupedReactions = $msg->getGroupedReactions(auth()->id());
                                    $author = $msg->user;
                                    $authorName = $author ? ($author->first_name ?: $author->name) : 'Participant';
                                    $palette = $author?->chatColorPalette() ?? ['name_color' => '#0d9488'];
                                @endphp
                                <div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }} w-full" wire:key="msg-{{ $msg->id }}">
                                    <div 
                                        class="group relative max-w-[85%] sm:max-w-[70%] rounded-xl px-2.5 py-1 shadow-xs text-xs sm:text-sm {{ 
                                            $isMe 
                                                ? 'bg-[#d9fdd3] dark:bg-[#005c4b] text-gray-900 dark:text-gray-100 rounded-tr-none' 
                                                : 'bg-white dark:bg-[#202c33] text-gray-900 dark:text-gray-100 rounded-tl-none' 
                                        }}"
                                        x-data="{ showMenu: false, copied: false }"
                                    >
                                        {{-- Compact Sender Name (Both Sent & Received) --}}
                                        <div class="flex items-center justify-between gap-2 mb-0.5">
                                            <span class="text-[11px] font-bold leading-none {{ $isMe ? 'text-emerald-800 dark:text-emerald-300' : 'text-teal-600 dark:text-teal-400' }}" style="{{ ! $isMe && isset($palette['name_color']) ? 'color:' . $palette['name_color'] . ';' : '' }}">
                                                {{ $isMe ? 'You' : $authorName }}
                                            </span>
                                        </div>

                                        {{-- Replying to Quote Preview --}}
                                        @if ($msg->replyTo)
                                            @php
                                                $repUser = $msg->replyTo->user;
                                                $repMine = $msg->replyTo->user_id === auth()->id();
                                            @endphp
                                            <div class="p-1 px-1.5 mb-1 rounded bg-black/5 dark:bg-white/10 border-l-2 text-[11px] space-y-0.2" style="border-left-color:#00a884;">
                                                <span class="font-bold text-[#00a884] block leading-tight">
                                                    {{ $repMine ? 'You' : ($repUser?->first_name ?? 'Participant') }}
                                                </span>
                                                <p class="text-gray-600 dark:text-gray-300 truncate leading-tight">
                                                    {{ $msg->replyTo->body ?: 'Attachment' }}
                                                </p>
                                            </div>
                                        @endif

                                        {{-- Attachments & Files --}}
                                        @php
                                            $rawAtts = $msg->attachments;
                                            $attachmentsList = is_array($rawAtts) ? $rawAtts : (is_string($rawAtts) ? json_decode($rawAtts, true) : []);
                                            if (empty($attachmentsList) && $msg->attachment_path) {
                                                $attachmentsList = [[
                                                    'path' => $msg->attachment_path,
                                                    'name' => $msg->attachment_name ?: 'File',
                                                    'type' => $msg->attachment_type ?: 'file',
                                                ]];
                                            }
                                        @endphp
                                        @if (!empty($attachmentsList))
                                            <div class="space-y-1 my-1">
                                                @foreach ($attachmentsList as $att)
                                                    @php
                                                        $url = \Illuminate\Support\Facades\Storage::disk('public')->url($att['path'] ?? '');
                                                        $isImage = ($att['type'] ?? '') === 'image' || preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $att['path'] ?? '');
                                                    @endphp
                                                    @if ($isImage)
                                                        <a href="{{ $url }}" target="_blank" class="block rounded-lg overflow-hidden border border-black/10 dark:border-white/10">
                                                            <img src="{{ $url }}" alt="{{ $att['name'] ?? 'Image' }}" class="max-h-52 w-full object-cover">
                                                        </a>
                                                    @else
                                                        <div class="p-1.5 rounded-lg bg-black/5 dark:bg-black/20 flex items-center justify-between gap-2 text-xs border border-black/5 dark:border-white/5">
                                                            <div class="flex items-center gap-1.5 min-w-0 flex-1">
                                                                <span class="text-sm">📄</span>
                                                                <span class="truncate font-medium text-[12px] text-gray-700 dark:text-gray-200">{{ $att['name'] ?? basename($att['path'] ?? 'Document') }}</span>
                                                            </div>
                                                            <a href="{{ $url }}" download class="text-emerald-600 dark:text-emerald-400 hover:opacity-80 p-0.5 shrink-0" title="Download">
                                                                <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                                                            </a>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif

                                        {{-- Message Body Text with Tight Leading --}}
                                        @if ($msg->body)
                                            <p class="leading-snug text-gray-800 dark:text-gray-100 break-words whitespace-pre-line text-[13px] sm:text-sm">
                                                {{ $msg->body }}
                                            </p>
                                        @endif

                                        {{-- Inline Timestamp & Delivery Checks --}}
                                        <div class="flex items-center justify-end gap-1 mt-0.5 text-[10px] text-gray-500 dark:text-gray-400 select-none leading-none">
                                            <span>{{ $msg->created_at?->format('H:i') }}</span>
                                            @if ($isMe)
                                                <span class="text-sky-500 font-bold text-[10px]" title="Delivered">✓✓</span>
                                            @endif
                                        </div>

                                        {{-- Quick Action Hover Menu --}}
                                        <div class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-0.5 bg-white/90 dark:bg-[#111b21]/90 backdrop-blur-md rounded-full px-1 py-0.5 shadow-xs border border-gray-200 dark:border-gray-700">
                                            <button type="button" wire:click="setReplyTo({{ $msg->id }})" class="text-gray-500 hover:text-gray-800 dark:hover:text-white p-0.5 transition" title="Reply">
                                                <x-heroicon-m-arrow-uturn-left class="w-3 h-3" />
                                            </button>
                                            <button type="button" wire:click="toggleReaction({{ $msg->id }}, '👍')" class="text-gray-500 hover:text-gray-800 dark:hover:text-white p-0.5 transition text-[10px]" title="Like">
                                                👍
                                            </button>
                                            <div class="relative">
                                                <button type="button" @click="showMenu = !showMenu" class="text-gray-500 hover:text-gray-800 dark:hover:text-white p-0.5 transition">
                                                    <x-heroicon-m-chevron-down class="w-3 h-3" />
                                                </button>
                                                <div x-show="showMenu" @click.away="showMenu = false" x-transition class="absolute right-0 top-full mt-1 w-28 bg-white dark:bg-[#202c33] rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-0.5 z-30 text-xs">
                                                    <button type="button" wire:click="setReplyTo({{ $msg->id }})" @click="showMenu = false" class="w-full text-left px-2.5 py-1 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between text-gray-700 dark:text-gray-200 text-[11px]">
                                                        <span>Reply</span>
                                                        <x-heroicon-m-arrow-uturn-left class="w-3 h-3" />
                                                    </button>
                                                    <button type="button" @click="navigator.clipboard.writeText(@js($msg->body ?? '')); copied = true; setTimeout(() => { copied = false; showMenu = false; }, 900);" class="w-full text-left px-2.5 py-1 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between text-gray-700 dark:text-gray-200 text-[11px]">
                                                        <span x-text="copied ? 'Copied!' : 'Copy'">Copy</span>
                                                        <x-heroicon-m-clipboard class="w-3 h-3" />
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Emoji Reactions Row --}}
                                @if (count($groupedReactions) > 0)
                                    <div class="flex flex-wrap gap-1 -mt-0.5 {{ $isMe ? 'justify-end' : 'justify-start' }}">
                                        @foreach ($groupedReactions as $reaction)
                                            <button 
                                                type="button" 
                                                wire:click="toggleReaction({{ $msg->id }}, '{{ $reaction['emoji'] }}')" 
                                                title="{{ implode(', ', $reaction['names']) }}" 
                                                class="inline-flex items-center gap-0.5 px-1.5 py-0.2 rounded-full text-[10px] border transition {{ 
                                                    $reaction['reacted_by_me'] 
                                                        ? 'bg-[#00a884]/15 border-[#00a884] text-[#008069] dark:text-emerald-300 font-bold' 
                                                        : 'bg-white/80 dark:bg-gray-800/80 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300' 
                                                }}"
                                            >
                                                <span>{{ $reaction['emoji'] }}</span>
                                                <span class="text-[9px] font-bold">{{ $reaction['count'] }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            @empty
                                <div class="m-auto text-center p-6 text-gray-400 dark:text-gray-500 text-xs">
                                    <p>No messages yet in this chat. Start the conversation!</p>
                                </div>
                            @endforelse
                        </main>

                        {{-- Replying-To Active Banner --}}
                        @if ($this->replyingToMessage)
                            @php
                                $repUser = $this->replyingToMessage->user;
                                $repMine = $this->replyingToMessage->user_id === auth()->id();
                            @endphp
                            <div class="bg-gray-50 dark:bg-[#202c33] px-3.5 py-2 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between gap-2 shrink-0">
                                <div class="border-l-3 border-[#00a884] pl-2 min-w-0 flex-1">
                                    <span class="text-[11px] font-bold text-[#00a884] block">
                                        Replying to {{ $repMine ? 'yourself' : ($repUser?->first_name ?? 'Participant') }}
                                    </span>
                                    <p class="text-xs text-gray-600 dark:text-gray-300 truncate">
                                        {{ $this->replyingToMessage->body ?: 'Attachment' }}
                                    </p>
                                </div>
                                <button type="button" wire:click="cancelReply" class="text-gray-400 hover:text-rose-500 text-lg leading-none p-1">
                                    &times;
                                </button>
                            </div>
                        @endif

                        {{-- FIXED BOTTOM INPUT BAR --}}
                        <footer class="p-2 sm:p-3 bg-white dark:bg-[#202c33] flex items-center gap-2 flex-shrink-0 border-t border-gray-200 dark:border-gray-700/60 pb-[calc(env(safe-area-inset-bottom,0.75rem)+0.25rem)] z-10">
                            {{-- Attachment Trigger --}}
                            <div class="relative shrink-0" x-data="{ openAttach: false }">
                                <button type="button" @click="openAttach = !openAttach" class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                    <x-heroicon-o-paper-clip class="w-5 h-5" />
                                </button>
                                <div x-show="openAttach" @click.away="openAttach = false" x-transition class="absolute bottom-full left-0 mb-2 bg-white dark:bg-[#202c33] rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-2 z-20 w-44 space-y-1">
                                    <label class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl cursor-pointer">
                                        <x-heroicon-s-photo class="w-4 h-4 text-emerald-500" />
                                        <span>Photo & Video</span>
                                        <input type="file" wire:model="attachment" accept="image/*,video/*" class="hidden" @change="openAttach = false">
                                    </label>
                                    <label class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl cursor-pointer">
                                        <x-heroicon-s-document-text class="w-4 h-4 text-indigo-500" />
                                        <span>Document</span>
                                        <input type="file" wire:model="attachment" accept=".pdf,.doc,.docx,.zip,.txt" class="hidden" @change="openAttach = false">
                                    </label>
                                </div>
                            </div>

                            {{-- Attachment Preview Chip if Uploaded --}}
                            @if ($attachment)
                                <div class="flex items-center gap-1.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-700 rounded-full px-2.5 py-1 text-xs text-emerald-800 dark:text-emerald-300 max-w-[160px] truncate shrink-0">
                                    <span class="truncate">{{ $attachment->getClientOriginalName() }}</span>
                                    <button type="button" wire:click="$set('attachment', null)" class="text-rose-500 font-bold hover:opacity-80">&times;</button>
                                </div>
                            @endif

                            {{-- Message Input Box --}}
                            <input 
                                type="text" 
                                wire:model="messageBody" 
                                wire:keydown.enter="sendMessage" 
                                placeholder="Type a message..." 
                                class="flex-1 bg-gray-100 dark:bg-[#2a3942] border-0 rounded-full px-4 py-2 text-sm text-gray-900 dark:text-white placeholder-gray-500 focus:ring-1 focus:ring-[#00a884] outline-none"
                            >

                            {{-- Circular Emerald Send Button --}}
                            <button 
                                type="button" 
                                wire:click="sendMessage" 
                                wire:loading.attr="disabled"
                                class="w-10 h-10 rounded-full bg-[#00a884] hover:bg-[#008f6f] text-white flex items-center justify-center flex-shrink-0 shadow-xs transition-colors disabled:opacity-50"
                                title="Send message"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                            </button>
                        </footer>

                    </div>
                @else
                    {{-- 2. COMMUNITY CHAT LIST (FIXED STICKY HEADER, INDEPENDENTLY SCROLLING CONVERSATION LIST) --}}
                    <div class="flex-1 flex flex-col overflow-hidden">

                        <!-- Pinned Subheader & Search & Filter Pills -->
                        <div class="flex-shrink-0 px-4 py-3 bg-white dark:bg-[#111b21] border-b border-gray-100 dark:border-gray-800 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">
                                        💬
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-gray-900 dark:text-white leading-tight">Community Chats</h3>
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Thinker HUB</p>
                                    </div>
                                </div>
                                <button type="button" wire:click="setTab('friends')" class="p-1.5 text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition" title="Friends & Directory">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                </button>
                            </div>

                            <!-- Search Field -->
                            <div class="relative">
                                <input 
                                    type="search" 
                                    wire:model.live.debounce.300ms="chatSearch" 
                                    placeholder="Search or start new chat..." 
                                    class="w-full bg-gray-100 dark:bg-[#202c33] border-0 rounded-xl pl-9 pr-3.5 py-2 text-xs sm:text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-1 focus:ring-emerald-500 outline-none"
                                >
                                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>

                            <!-- Filter Pills -->
                            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
                                <button type="button" wire:click="$set('chatFilter', 'all')" class="px-3 py-1 rounded-full text-xs font-semibold transition-colors shrink-0 {{ $chatFilter === 'all' ? 'bg-[#008069] text-white shadow-xs' : 'bg-gray-100 dark:bg-[#202c33] text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">All</button>
                                <button type="button" wire:click="$set('chatFilter', 'groups')" class="px-3 py-1 rounded-full text-xs font-semibold transition-colors shrink-0 {{ $chatFilter === 'groups' ? 'bg-[#008069] text-white shadow-xs' : 'bg-gray-100 dark:bg-[#202c33] text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">Cohorts / Groups</button>
                                <button type="button" wire:click="$set('chatFilter', 'direct')" class="px-3 py-1 rounded-full text-xs font-semibold transition-colors shrink-0 {{ $chatFilter === 'direct' ? 'bg-[#008069] text-white shadow-xs' : 'bg-gray-100 dark:bg-[#202c33] text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">Direct DMs</button>
                            </div>
                        </div>

                        <!-- Scrollable Chats Feed -->
                        <div class="flex-1 overflow-y-auto overscroll-contain bg-white dark:bg-[#111b21] divide-y divide-gray-100 dark:divide-gray-800/60 pb-[env(safe-area-inset-bottom,1.5rem)]">
                            @forelse($allRooms as $chat)
                                @php
                                    $isCourse = $chat->type === 'course';
                                    $displayName = $chat->displayNameFor(auth()->user());
                                    $lastMsg = $chat->latestMessage;
                                    $otherUser = $chat->members->firstWhere('id', '!=', auth()->id());
                                    $avatar = $isCourse ? null : $otherUser?->getFilamentAvatarUrl();
                                    $initials = strtoupper(substr($displayName, 0, 2));
                                    $lastSender = $lastMsg ? ($lastMsg->user_id === auth()->id() ? 'You' : ($lastMsg->user?->first_name ?? 'Member')) : null;
                                    $lastMsgPreview = $lastMsg ? ($lastMsg->body ?: ($lastMsg->attachment_name ? '📎 '.$lastMsg->attachment_name : 'Sent an attachment')) : 'No messages yet';
                                    $badgeLabel = $isCourse ? ($chat->course?->code ?? 'Cohort') : null;
                                @endphp
                                <div 
                                    wire:click="selectRoom({{ $chat->id }})"
                                    class="flex items-center gap-3.5 px-4 py-3 hover:bg-gray-50 dark:hover:bg-[#202c33]/70 active:bg-gray-100 dark:active:bg-[#202c33] cursor-pointer transition-colors"
                                    wire:key="chat-room-{{ $chat->id }}"
                                >
                                    <!-- Avatar with Online Dot -->
                                    <div class="relative flex-shrink-0">
                                        @if ($isCourse)
                                            <div class="w-11 h-11 rounded-full bg-gradient-to-br from-[#00a884] to-[#008069] text-white font-bold flex items-center justify-center text-sm shadow-xs">
                                                <x-heroicon-s-academic-cap class="w-5 h-5" />
                                            </div>
                                        @elseif ($avatar)
                                            <img src="{{ $avatar }}" alt="{{ $displayName }}" class="w-11 h-11 rounded-full object-cover border border-gray-200 dark:border-gray-700">
                                        @else
                                            <div class="w-11 h-11 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center text-sm shadow-xs">
                                                {{ $initials }}
                                            </div>
                                        @endif
                                        @if(!$isCourse)
                                            <span class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-white dark:border-[#111b21] rounded-full"></span>
                                        @endif
                                    </div>

                                    <!-- Chat Info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between mb-0.5">
                                            <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ $displayName }}</h4>
                                            @if ($lastMsg)
                                                <span class="text-[11px] text-gray-400 font-medium shrink-0">{{ $lastMsg->created_at?->format('H:i') }}</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                @if ($lastSender)
                                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $lastSender }}:</span>
                                                @endif
                                                {{ $lastMsgPreview }}
                                            </p>
                                            @if($badgeLabel)
                                                <span class="text-[10px] font-bold px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded shrink-0">
                                                    {{ $badgeLabel }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="py-12 text-center text-xs text-gray-400">
                                    No active chats found.
                                </div>
                            @endforelse
                        </div>

                    </div>
                @endif
            @endif

            {{-- ===================== TAB 2: SCORES (RESULTS BOARD) ===================== --}}
            @if ($tab === 'results' || $tab === 'scores')
                @php
                    $resultsData = $this->results;
                    $resStats = $resultsData['stats'];
                    $resItems = $resultsData['items'];
                    $resTasks = $resultsData['tasks'] ?? collect();
                    $activeTask = $resultsData['active_task'] ?? $resTasks->first();
                @endphp
                <div class="flex-1 overflow-y-auto overscroll-contain p-3 sm:p-4 space-y-3 bg-slate-50 dark:bg-[#0b141a] pb-[env(safe-area-inset-bottom,1.5rem)]" x-data="{ showMyRecords: false }">
                    
                    {{-- Search & Filter Controls --}}
                    <div class="bg-white dark:bg-[#111b21] p-3.5 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-xs space-y-2.5">
                        <div class="relative">
                            <input 
                                type="search" 
                                wire:model.live.debounce.300ms="resultsSearch" 
                                placeholder="Search test or task title..." 
                                class="w-full bg-gray-100 dark:bg-[#202c33] border-0 rounded-xl pl-9 pr-3.5 py-2 text-xs sm:text-sm text-gray-900 dark:text-white placeholder-gray-500 focus:ring-1 focus:ring-emerald-500 outline-none"
                            >
                            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        
                        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
                            <button type="button" wire:click="$set('resultsFilter', 'all')" class="px-3 py-1 rounded-full text-xs font-semibold transition-colors shrink-0 {{ $resultsFilter === 'all' ? 'bg-[#008069] text-white shadow-xs' : 'bg-gray-100 dark:bg-[#202c33] text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">All</button>
                            <button type="button" wire:click="$set('resultsFilter', 'quiz')" class="px-3 py-1 rounded-full text-xs font-semibold transition-colors shrink-0 {{ $resultsFilter === 'quiz' ? 'bg-[#008069] text-white shadow-xs' : 'bg-gray-100 dark:bg-[#202c33] text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">Quizzes</button>
                            <button type="button" wire:click="$set('resultsFilter', 'assignment')" class="px-3 py-1 rounded-full text-xs font-semibold transition-colors shrink-0 {{ $resultsFilter === 'assignment' ? 'bg-[#008069] text-white shadow-xs' : 'bg-gray-100 dark:bg-[#202c33] text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">Assignments</button>
                            <button type="button" wire:click="$set('resultsFilter', 'assessment')" class="px-3 py-1 rounded-full text-xs font-semibold transition-colors shrink-0 {{ $resultsFilter === 'assessment' ? 'bg-[#008069] text-white shadow-xs' : 'bg-gray-100 dark:bg-[#202c33] text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">Assessments</button>
                        </div>
                    </div>

                    {{-- Graded Task Selector --}}
                    @if ($resTasks->isNotEmpty())
                        <div class="p-3 sm:p-4 rounded-2xl bg-white dark:bg-[#111b21] border border-gray-200 dark:border-gray-800 shadow-xs">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-500/20">
                                    <x-heroicon-s-funnel class="w-4 h-4" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <label for="taskFilterDropdown" class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-0.5">
                                        Recent Graded Tasks
                                    </label>
                                    <select 
                                        id="taskFilterDropdown"
                                        wire:change="selectTask($event.target.value)"
                                        class="w-full text-xs font-bold rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/80 text-gray-800 dark:text-white py-2 pl-3 pr-8 focus:ring-1 focus:ring-emerald-500 outline-none transition cursor-pointer">
                                        @foreach ($resTasks as $task)
                                            <option value="{{ $task['id'] }}" @selected(($activeTask['id'] ?? '') === $task['id'])>
                                                {{ $task['short_title'] }}: {{ $task['title'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Active Graded Task Score Board Card & Candidates List --}}
                        @if ($activeTask)
                            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-[#111b21] shadow-xs overflow-hidden">
                                {{-- Task Header Banner --}}
                                <div class="p-3 sm:p-4 bg-gray-50 dark:bg-[#111b21] border-b border-gray-200/80 dark:border-gray-800/80 flex flex-col xs:flex-row xs:items-center justify-between gap-2">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-1.5 mb-1 flex-wrap">
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold uppercase {{
                                                $activeTask['type'] === 'quiz' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300' :
                                                 ($activeTask['type'] === 'assignment' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/60 dark:text-amber-300')
                                            }}">
                                                {{ $activeTask['short_title'] }}
                                            </span>
                                            <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 truncate">{{ $activeTask['course'] }}</span>
                                        </div>
                                        <h3 class="text-xs sm:text-sm md:text-base font-black text-gray-900 dark:text-white leading-snug break-words">
                                            {{ $activeTask['title'] }}
                                        </h3>
                                    </div>

                                    <div class="flex items-center gap-3 shrink-0 pt-1 xs:pt-0 border-t xs:border-t-0 border-gray-100 dark:border-gray-800/60 text-right">
                                        <div>
                                            <span class="text-[8px] xs:text-[9px] font-bold uppercase tracking-wider text-gray-400 block">Candidates</span>
                                            <span class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">{{ $activeTask['candidates_count'] }} graded</span>
                                        </div>
                                        <div class="border-l border-gray-200 dark:border-gray-700 pl-2.5">
                                            <span class="text-[8px] xs:text-[9px] font-bold uppercase tracking-wider text-gray-400 block">Class Avg</span>
                                            <span class="text-xs sm:text-sm font-black text-emerald-600 dark:text-emerald-400">{{ $activeTask['average_score'] }}%</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Candidates Score List (Mobile-safe fit, no clipping) --}}
                                <div class="p-1.5 sm:p-3 divide-y divide-gray-100 dark:divide-gray-800/60">
                                    @if (!empty($activeTask['candidates']) && count($activeTask['candidates']) > 0)
                                        @foreach ($activeTask['candidates'] as $candidate)
                                            <div class="py-2 px-1.5 sm:px-2.5 flex items-center justify-between gap-1.5 xs:gap-2 hover:bg-gray-50/70 dark:hover:bg-gray-800/40 rounded-xl transition {{ $candidate['is_self'] ? 'bg-emerald-50/30 dark:bg-emerald-950/20' : '' }}">
                                                <div class="flex items-center gap-1.5 xs:gap-2 min-w-0 flex-1">
                                                    {{-- Candidate Rank Badge --}}
                                                    <div class="w-5 h-5 xs:w-6 xs:h-6 rounded-md xs:rounded-lg shrink-0 flex items-center justify-center font-black text-[10px] xs:text-[11px] {{
                                                        $candidate['rank'] === 1 ? 'bg-amber-400 text-white shadow-xs' :
                                                        ($candidate['rank'] === 2 ? 'bg-slate-300 dark:bg-slate-600 text-slate-800 dark:text-white' :
                                                        ($candidate['rank'] === 3 ? 'bg-amber-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400'))
                                                    }}">
                                                        #{{ $candidate['rank'] }}
                                                    </div>

                                                    <img src="{{ $candidate['candidate_avatar'] }}" alt="{{ $candidate['candidate_name'] }}" class="w-6 h-6 xs:w-7 xs:h-7 sm:w-8 sm:h-8 rounded-full object-cover shrink-0 border border-gray-200 dark:border-gray-700" />

                                                    <div class="min-w-0 flex-1">
                                                        <div class="flex items-center gap-1 flex-wrap">
                                                            <span class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white break-words leading-tight">
                                                                {{ $candidate['candidate_name'] }}
                                                            </span>
                                                            @if ($candidate['is_self'])
                                                                <span class="px-1 py-0.1 rounded text-[8px] xs:text-[9px] font-extrabold bg-[#008069] text-white shrink-0">YOU</span>
                                                            @endif
                                                        </div>
                                                        <span class="text-[9px] xs:text-[10px] text-gray-400 dark:text-gray-500 block truncate">Graded {{ $candidate['graded_at'] }}</span>
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-1 xs:gap-1.5 shrink-0 text-right">
                                                    <span class="px-1 xs:px-1.5 py-0.5 rounded text-[8px] xs:text-[9px] sm:text-[10px] font-bold {{ 
                                                        $candidate['status_color'] === 'success' 
                                                            ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300' 
                                                            : ($candidate['status_color'] === 'warning' 
                                                                ? 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300' 
                                                                : 'bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300') 
                                                    }}">
                                                        {{ $candidate['status'] }}
                                                    </span>
                                                    <div class="text-xs xs:text-sm sm:text-base font-black text-emerald-600 dark:text-emerald-400 min-w-[34px] xs:min-w-[40px] text-right">
                                                        {{ $candidate['score'] }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="p-6 text-center text-xs text-gray-500 dark:text-gray-400">
                                            No candidates found for this task.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="p-8 text-center rounded-2xl bg-white dark:bg-[#111b21] border border-gray-200 dark:border-gray-800">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 mx-auto flex items-center justify-center mb-3">
                                <x-heroicon-o-document-magnifying-glass class="w-6 h-6" />
                            </div>
                            <h4 class="font-bold text-gray-800 dark:text-gray-200 text-sm">No recent graded tasks found</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Graded quizzes, assignments, and assessments will appear here.</p>
                        </div>
                    @endif

                    {{-- Personal Evaluation Records Collapsible Section --}}
                    @if ($resItems->isNotEmpty())
                        <div class="bg-white dark:bg-[#111b21] p-3.5 sm:p-4 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-xs" x-data="{ showPersonalRecords: false }">
                            <div @click="showPersonalRecords = !showPersonalRecords" class="flex items-center justify-between cursor-pointer select-none gap-2">
                                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                    <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-[#008069] dark:text-emerald-400 flex items-center justify-center shrink-0">
                                        <x-heroicon-s-clipboard-document-check class="w-4.5 h-4.5" />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white flex items-center gap-1.5 flex-wrap">
                                            <span>Personal Evaluation Records</span>
                                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                                {{ $resItems->count() }} records
                                            </span>
                                        </h3>
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                            Your individual completed grades and feedback across evaluations.
                                        </p>
                                    </div>
                                </div>
                                <button type="button" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-transform duration-200" :class="showPersonalRecords ? 'rotate-180' : ''">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </div>

                            {{-- Collapsible Content --}}
                            <div x-show="showPersonalRecords" x-collapse class="mt-3.5 pt-3.5 border-t border-gray-100 dark:border-gray-800 space-y-3">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach ($resItems as $item)
                                        <div class="p-3.5 rounded-2xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/80 shadow-2xs flex flex-col justify-between gap-2.5">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="flex items-start gap-2.5 min-w-0 flex-1">
                                                    <div class="w-8 h-8 rounded-xl shrink-0 flex items-center justify-center font-black text-xs {{ 
                                                        $item['type'] === 'quiz' 
                                                            ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/50' 
                                                            : ($item['type'] === 'assignment' 
                                                                ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50' 
                                                                : 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50') 
                                                    }}">
                                                        {{ $item['type_badge'] }}
                                                    </div>

                                                    <div class="min-w-0 flex-1">
                                                        <div class="flex items-center gap-1.5 flex-wrap">
                                                            <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.2 rounded {{ 
                                                                $item['type'] === 'quiz' 
                                                                    ? 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' 
                                                                    : ($item['type'] === 'assignment' 
                                                                        ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300' 
                                                                        : 'bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300') 
                                                            }}">
                                                                {{ $item['type_label'] }}
                                                            </span>
                                                            <span class="text-[10px] text-gray-400 dark:text-gray-500 truncate">{{ $item['date_formatted'] }}</span>
                                                        </div>

                                                        <h4 class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white leading-snug break-words mt-1">
                                                            {{ $item['title'] }}
                                                        </h4>
                                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">{{ $item['course'] }}</p>
                                                    </div>
                                                </div>

                                                <div class="flex flex-col items-end shrink-0 gap-1 text-right">
                                                    <div class="text-sm sm:text-base font-black text-emerald-600 dark:text-emerald-400">
                                                        {{ $item['score_display'] }}
                                                    </div>
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold {{ 
                                                        $item['status_color'] === 'success' 
                                                            ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300' 
                                                            : ($item['status_color'] === 'warning' 
                                                                ? 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300' 
                                                                : 'bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300') 
                                                    }}">
                                                        {{ $item['status'] }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- ===================== TAB 3: FRIENDS (STUDENT DIRECTORY) ===================== --}}
            @if ($tab === 'friends')
                @php $directory = $this->directory; @endphp
                <div class="flex-1 overflow-y-auto overscroll-contain p-3 sm:p-4 space-y-3 bg-slate-50 dark:bg-[#0b141a] pb-[env(safe-area-inset-bottom,1.5rem)]">
                    
                    {{-- Student Directory Section --}}
                    <div class="bg-white dark:bg-[#111b21] p-3.5 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-xs space-y-3">
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 px-0.5">
                            <span class="font-bold uppercase tracking-wider text-gray-900 dark:text-white">Student Directory</span>
                            <span>Showing {{ $directory['shown'] }} of {{ $directory['total'] }}</span>
                        </div>
                        
                        <div class="relative">
                            <input 
                                type="search" 
                                wire:model.live.debounce.300ms="directorySearch" 
                                placeholder="Filter by name or keyword..." 
                                class="w-full bg-gray-100 dark:bg-[#202c33] border-0 rounded-xl pl-9 pr-3.5 py-2 text-xs sm:text-sm text-gray-900 dark:text-white placeholder-gray-500 focus:ring-1 focus:ring-emerald-500 outline-none"
                            >
                            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>

                        @if ($directory['rows']->count() > 0)
                            <div class="space-y-2">
                                @foreach ($directory['rows'] as $person)
                                    <div class="p-3 rounded-xl border border-gray-100 dark:border-gray-800/80 bg-gray-50/50 dark:bg-gray-900/40 flex items-center justify-between gap-3 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                                        <div class="min-w-0 flex-1">
                                            <button 
                                                type="button" 
                                                wire:click="showProfile({{ $person['id'] }})"
                                                class="font-bold text-sm text-gray-900 dark:text-white hover:text-[#008069] dark:hover:text-emerald-400 transition text-left truncate block max-w-full cursor-pointer"
                                                title="View learner profile"
                                            >
                                                {{ $person['name'] }}
                                            </button>
                                            
                                            <div class="flex items-center gap-1.5 mt-1 text-xs flex-wrap">
                                                @if (!empty($person['course_intake_labels']))
                                                    @foreach ($person['course_intake_labels'] as $cil)
                                                        <span class="px-2 py-0.5 rounded-md {{ $cil['color']['badge_bg'] ?? 'bg-purple-100 text-purple-700' }} font-bold text-[10px] inline-flex items-center gap-1">
                                                            <x-heroicon-o-academic-cap class="w-3 h-3" />
                                                            <span>{{ $cil['label'] }}</span>
                                                        </span>
                                                    @endforeach
                                                @endif
                                                @if ($person['shared_count'] > 0)
                                                    <span class="px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-semibold text-[10px] inline-flex items-center gap-1">
                                                        <x-heroicon-o-book-open class="w-3 h-3" />
                                                        <span>{{ $person['shared_count'] }} in common</span>
                                                    </span>
                                                @endif
                                                <span class="inline-flex items-center gap-1 text-amber-500 font-bold text-[10px]">
                                                    <x-heroicon-s-bolt class="w-3 h-3 text-amber-500" />
                                                    <span>{{ number_format($person['xp']) }} XP</span>
                                                </span>
                                                @if (count($person['badge_icons']) > 0)
                                                    <span class="text-[11px] tracking-wider">{{ implode('', $person['badge_icons']) }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-1.5 shrink-0">
                                            @if ($person['friendship']['state'] === 'friends')
                                                <span class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 inline-flex items-center gap-1">
                                                    <x-heroicon-s-check-circle class="w-3.5 h-3.5" />
                                                    <span>Friends</span>
                                                </span>
                                            @elseif ($person['friendship']['state'] === 'sent')
                                                <div class="flex items-center gap-1">
                                                    <span class="text-xs text-gray-400 font-medium">Requested</span>
                                                    <button type="button" wire:click="removeFriend({{ $person['id'] }})" class="px-2.5 py-1 rounded-lg text-xs font-medium border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer">
                                                        Cancel
                                                    </button>
                                                </div>
                                            @elseif ($person['friendship']['state'] === 'incoming')
                                                <div class="flex items-center gap-1">
                                                    <button type="button" wire:click="acceptRequest({{ $person['friendship']['friendship_id'] }})" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#008069] text-white hover:bg-[#006e5a] transition cursor-pointer">
                                                        Accept
                                                    </button>
                                                    <button type="button" wire:click="declineRequest({{ $person['friendship']['friendship_id'] }})" class="px-2.5 py-1.5 rounded-lg text-xs font-medium border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer">
                                                        Decline
                                                    </button>
                                                </div>
                                            @else
                                                <button type="button" wire:click="sendRequest({{ $person['id'] }})" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#008069] text-white hover:bg-[#006e5a] transition inline-flex items-center gap-1 cursor-pointer">
                                                    <x-heroicon-s-user-plus class="w-3.5 h-3.5" />
                                                    <span>Add friend</span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-6 text-center text-xs text-gray-400">
                                No students match your search criteria.
                            </div>
                        @endif
                    </div>

                    {{-- Pending Friend Requests Banner --}}
                    @if ($this->pendingRequests->count() > 0)
                        <div class="bg-white dark:bg-[#111b21] p-3.5 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-xs space-y-2.5">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-rose-600 animate-pulse"></span>
                                <span>Friend Requests ({{ $this->pendingRequests->count() }})</span>
                            </h3>
                            <div class="space-y-2">
                                @foreach ($this->pendingRequests as $req)
                                    @php
                                        $reqLabels = $req->requester ? $req->requester->getCourseIntakeLabels() : [];
                                    @endphp
                                    <div class="p-2.5 rounded-xl border border-gray-100 dark:border-gray-800 flex items-center justify-between gap-3">
                                        <div class="min-w-0 flex-1">
                                            <button type="button" wire:click="showProfile({{ $req->requester?->id ?? 0 }})" class="font-bold text-xs text-gray-900 dark:text-white truncate text-left cursor-pointer block">
                                                {{ $req->requester?->name ?? 'Unknown' }}
                                            </button>
                                            @if (!empty($reqLabels))
                                                <div class="flex items-center gap-1 mt-0.5 flex-wrap">
                                                    @foreach ($reqLabels as $rl)
                                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold {{ $rl['color']['badge_bg'] ?? 'bg-purple-100 text-purple-700' }}">{{ $rl['label'] }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-1.5 shrink-0">
                                            <button type="button" wire:click="acceptRequest({{ $req->id }})" class="px-3 py-1 rounded-lg text-xs font-semibold bg-[#008069] text-white hover:bg-[#006e5a] cursor-pointer">
                                                Accept
                                            </button>
                                            <button type="button" wire:click="declineRequest({{ $req->id }})" class="px-2.5 py-1 rounded-lg text-xs font-medium border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer">
                                                Decline
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- My Friends List --}}
                    <div class="bg-white dark:bg-[#111b21] p-3.5 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-xs space-y-3">
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 px-0.5">
                            <span class="font-bold uppercase tracking-wider text-gray-900 dark:text-white">My Friends ({{ $this->friends->count() }})</span>
                        </div>
                        
                        @if ($this->friends->count() === 0)
                            <div class="p-6 text-center text-xs text-gray-400">
                                No friends yet. Connect with classmates using the directory above!
                            </div>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5">
                                @foreach ($this->friends as $friend)
                                    @php
                                        $friendAvatar = $friend->getFilamentAvatarUrl();
                                        $friendInitial = strtoupper(substr($friend->name, 0, 1));
                                        $friendLabels = $friend->getCourseIntakeLabels();
                                        $friendLevel = $friend->proficiency ?: $friend->track;
                                    @endphp
                                    <div class="p-3 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/30 flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                            @if ($friendAvatar)
                                                <img src="{{ $friendAvatar }}" alt="{{ $friend->name }}" class="w-9 h-9 rounded-full object-cover border border-gray-200 dark:border-gray-700 shrink-0">
                                            @else
                                                <span class="w-9 h-9 rounded-full bg-emerald-600 text-white font-bold text-xs flex items-center justify-center shrink-0">
                                                    {{ $friendInitial }}
                                                </span>
                                            @endif
                                            <div class="min-w-0 flex-1">
                                                <button type="button" wire:click="showProfile({{ $friend->id }})" class="font-bold text-xs text-gray-900 dark:text-white truncate block text-left cursor-pointer hover:text-[#008069] transition">
                                                    {{ $friend->name }}
                                                </button>
                                                @if (!empty($friendLabels))
                                                    <div class="flex items-center gap-1 mt-0.5 flex-wrap">
                                                        @foreach ($friendLabels as $fl)
                                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold {{ $fl['color']['badge_bg'] ?? 'bg-purple-100 text-purple-700' }}">{{ $fl['label'] }}</span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <p class="text-[10px] text-gray-400 truncate mt-0.5">
                                                        Enrolled Student {{ $friendLevel ? '• '.$friendLevel : '' }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-1.5 shrink-0">
                                            <button type="button" wire:click="openDirect({{ $friend->id }})" title="Message friend" class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/20 transition cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                            </button>
                                            <button type="button" wire:click="removeFriend({{ $friend->id }})" wire:confirm="Remove this friend?" title="Remove friend" class="p-1.5 rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 hover:bg-rose-500/20 transition cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                </div>
            @endif

            {{-- ===================== TAB 4: RANKS (LEADERBOARD) ===================== --}}
            @if ($tab === 'leaderboard' || $tab === 'ranks')
                @php
                    $leaderboard = $this->leaderboard;
                    $allRows = $leaderboard['rows'];
                    $top5 = $allRows->take(5);
                    $remaining = $allRows->slice(5);
                    $userInTop5 = $top5->contains('user_id', auth()->id());
                    $myRowInList = $allRows->firstWhere('user_id', auth()->id());
                    $xpBreakdown = $this->myXpBreakdown;
                @endphp
                <div class="flex-1 overflow-y-auto overscroll-contain p-3 sm:p-4 space-y-3 bg-slate-50 dark:bg-[#0b141a] pb-[env(safe-area-inset-bottom,1.5rem)]">
                    
                    {{-- 1. TOP STUDENTS LEADERBOARD (TOP 5 WITH EXPAND/COLLAPSE) --}}
                    <div class="bg-white dark:bg-[#111b21] p-3.5 sm:p-4 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-xs" x-data="{ showAllLeaderboard: false }">
                        <div class="flex items-center justify-between gap-2 mb-2 flex-wrap">
                            <div class="flex items-center gap-2">
                                <x-heroicon-s-trophy class="w-5 h-5 text-amber-500" />
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Leaderboard</h3>
                            </div>
                            @if ($allRows->count() > 0)
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                                    Top {{ $allRows->count() }} Students
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Ranked by lifetime XP earned from quizzes, attendance, streaks, and course completions.</p>

                        @if ($allRows->count() === 0)
                            <div class="p-6 text-center text-xs text-gray-400">
                                No XP earned yet. Pass a quiz or complete a lesson to get on the board!
                            </div>
                        @else
                            <div class="space-y-1.5">
                                {{-- Top 5 Rows --}}
                                @foreach ($top5 as $row)
                                    @php $isMe = $row['user_id'] === auth()->id(); @endphp
                                    <div class="flex items-center justify-between gap-2 p-2.5 rounded-xl border {{ $isMe ? 'border-emerald-500/40 bg-emerald-50/40 dark:bg-emerald-950/20' : 'border-gray-100 dark:border-gray-800/80 bg-gray-50/50 dark:bg-gray-900/30' }} transition">
                                        <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                            <span class="w-6 text-center font-black text-xs {{ $row['rank'] <= 3 ? 'text-amber-500 font-extrabold' : 'text-gray-400' }}">
                                                #{{ $row['rank'] }}
                                            </span>
                                            
                                            <button 
                                                type="button" 
                                                wire:click="showProfile({{ $row['user_id'] }})"
                                                class="font-bold text-xs sm:text-sm text-left truncate flex items-center gap-1 {{ $isMe ? 'text-[#008069] dark:text-emerald-400' : 'text-gray-900 dark:text-white' }} hover:underline cursor-pointer"
                                                title="View learner profile"
                                            >
                                                <span class="truncate">{{ $row['name'] }}{{ $isMe ? ' (you)' : '' }}</span>
                                                <x-heroicon-m-sparkles class="w-3.5 h-3.5 text-amber-500 shrink-0" />
                                            </button>
                                        </div>

                                        <div class="flex items-center gap-3 shrink-0 text-right">
                                            {{-- Badges Count --}}
                                            <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                                <x-heroicon-s-trophy class="w-3.5 h-3.5 text-amber-500" />
                                                <span class="font-semibold">{{ $row['badge_count'] }}</span>
                                            </div>

                                            {{-- Lifetime XP --}}
                                            <div class="flex items-center gap-1 text-xs sm:text-sm font-black text-emerald-600 dark:text-emerald-400 min-w-[65px] justify-end">
                                                <x-heroicon-s-bolt class="w-3.5 h-3.5 text-amber-500" />
                                                <span>{{ number_format($row['xp']) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                {{-- Remaining Rows (Collapsible) --}}
                                @if ($remaining->isNotEmpty())
                                    <div x-show="showAllLeaderboard" x-collapse class="space-y-1.5">
                                        @foreach ($remaining as $row)
                                            @php $isMe = $row['user_id'] === auth()->id(); @endphp
                                            <div class="flex items-center justify-between gap-2 p-2.5 rounded-xl border {{ $isMe ? 'border-emerald-500/40 bg-emerald-50/40 dark:bg-emerald-950/20' : 'border-gray-100 dark:border-gray-800/80 bg-gray-50/50 dark:bg-gray-900/30' }} transition">
                                                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                                    <span class="w-6 text-center font-bold text-xs text-gray-400">
                                                        #{{ $row['rank'] }}
                                                    </span>
                                                    
                                                    <button 
                                                        type="button" 
                                                        wire:click="showProfile({{ $row['user_id'] }})"
                                                        class="font-bold text-xs sm:text-sm text-left truncate flex items-center gap-1 {{ $isMe ? 'text-[#008069] dark:text-emerald-400' : 'text-gray-900 dark:text-white' }} hover:underline cursor-pointer"
                                                        title="View learner profile"
                                                    >
                                                        <span class="truncate">{{ $row['name'] }}{{ $isMe ? ' (you)' : '' }}</span>
                                                        <x-heroicon-m-sparkles class="w-3.5 h-3.5 text-amber-500 shrink-0" />
                                                    </button>
                                                </div>

                                                <div class="flex items-center gap-3 shrink-0 text-right">
                                                    <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                                        <x-heroicon-s-trophy class="w-3.5 h-3.5 text-amber-500" />
                                                        <span class="font-semibold">{{ $row['badge_count'] }}</span>
                                                    </div>

                                                    <div class="flex items-center gap-1 text-xs sm:text-sm font-black text-emerald-600 dark:text-emerald-400 min-w-[65px] justify-end">
                                                        <x-heroicon-s-bolt class="w-3.5 h-3.5 text-amber-500" />
                                                        <span>{{ number_format($row['xp']) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="text-center pt-2">
                                        <button 
                                            type="button" 
                                            @click="showAllLeaderboard = !showAllLeaderboard" 
                                            class="px-4 py-1.5 rounded-full text-xs font-bold text-[#008069] dark:text-emerald-400 bg-emerald-500/10 hover:bg-emerald-500/20 transition cursor-pointer"
                                        >
                                            <span x-show="!showAllLeaderboard">View More (Rank 6–{{ $allRows->count() }})</span>
                                            <span x-show="showAllLeaderboard" style="display: none;">Collapse to Top 5</span>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- 2. COLLAPSIBLE XP EARNED & BADGES BREAKDOWN --}}
                    <div class="bg-white dark:bg-[#111b21] p-3.5 sm:p-4 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-xs" x-data="{ showXpEarned: false }">
                        <div @click="showXpEarned = !showXpEarned" class="flex items-center justify-between cursor-pointer user-select-none gap-2">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center shrink-0">
                                    <x-heroicon-s-bolt class="w-4 h-4" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                        <span>XP Earned & Badges</span>
                                        <span class="text-[10px] font-extrabold px-1.5 py-0.2 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">+{{ number_format($xpBreakdown['total_xp']) }} XP</span>
                                    </h3>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                        Tier: <strong>{{ $xpBreakdown['rank']['rank_name'] }}</strong> ({{ $xpBreakdown['rank']['multiplier'] }}x) • <strong>{{ number_format($xpBreakdown['total_coins']) }}</strong> Coins
                                    </p>
                                </div>
                            </div>
                            <button type="button" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                <svg class="w-4 h-4 transition-transform duration-200" :class="showXpEarned ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </div>

                        {{-- Collapsible Content --}}
                        <div x-show="showXpEarned" x-collapse class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800 space-y-3">
                            {{-- Metrics Grid --}}
                            <div class="grid grid-cols-4 gap-2 text-center">
                                <div class="p-2 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800">
                                    <span class="text-[10px] text-gray-400 block font-semibold">XP</span>
                                    <span class="text-xs sm:text-sm font-extrabold text-emerald-600 dark:text-emerald-400">+{{ number_format($xpBreakdown['total_xp']) }}</span>
                                </div>
                                <div class="p-2 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800">
                                    <span class="text-[10px] text-gray-400 block font-semibold">Coins</span>
                                    <span class="text-xs sm:text-sm font-extrabold text-amber-500">{{ number_format($xpBreakdown['total_coins']) }}</span>
                                </div>
                                <div class="p-2 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800">
                                    <span class="text-[10px] text-gray-400 block font-semibold">Streak</span>
                                    <span class="text-xs sm:text-sm font-extrabold text-orange-500">{{ $xpBreakdown['streak'] }}d</span>
                                </div>
                                <div class="p-2 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800">
                                    <span class="text-[10px] text-gray-400 block font-semibold">Rank</span>
                                    <span class="text-xs sm:text-sm font-extrabold text-purple-500 truncate block">{{ $xpBreakdown['rank']['rank_name'] }}</span>
                                </div>
                            </div>

                            {{-- Badges Earned Showcase --}}
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                    <span class="font-bold text-gray-900 dark:text-white">Unlocked Badges</span>
                                    <span>{{ $xpBreakdown['earned_badges']->count() }} of {{ $xpBreakdown['total_available_badges'] }}</span>
                                </div>

                                @if ($xpBreakdown['earned_badges']->isEmpty())
                                    <div class="p-3 text-center text-xs text-gray-400 bg-gray-50 dark:bg-gray-800/40 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                                        No badges unlocked yet. Complete courses and quizzes to earn your first badge!
                                    </div>
                                @else
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($xpBreakdown['earned_badges'] as $badge)
                                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-700 dark:text-amber-300 font-semibold" title="{{ $badge->description }}">
                                                <x-heroicon-s-trophy class="w-3.5 h-3.5 text-amber-500" />
                                                <span>{{ $badge->name }}</span>
                                                @if ($badge->xp_reward > 0)
                                                    <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold">+{{ $badge->xp_reward }} XP</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- Recent Point Activity --}}
                            @if (!empty($xpBreakdown['transactions']) && $xpBreakdown['transactions']->isNotEmpty())
                                <div class="space-y-1.5 pt-2 border-t border-gray-100 dark:border-gray-800">
                                    <h4 class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1">
                                        <x-heroicon-s-clock class="w-3.5 h-3.5 text-gray-400" />
                                        <span>Recent Point Activity</span>
                                    </h4>
                                    <div class="space-y-1">
                                        @foreach ($xpBreakdown['transactions'] as $tx)
                                            <div class="p-2 rounded-lg bg-gray-50 dark:bg-gray-800/40 flex items-center justify-between text-xs">
                                                <span class="font-semibold text-gray-900 dark:text-white">{{ $tx->description }}</span>
                                                <span class="text-emerald-600 font-bold">+{{ number_format($tx->amount_xp ?: $tx->points) }} XP</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            @endif

        </div>

        {{-- ===================== STUDENT XP & BADGE PROFILE MODAL ===================== --}}
        @if ($profileUser)
            <div 
                wire:click="closeProfile"
                @keydown.escape.window="$wire.closeProfile()"
                class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto"
            >
                <div 
                    wire:click.stop
                    class="w-full max-w-lg max-h-[90vh] bg-white dark:bg-[#111b21] rounded-2xl border border-gray-200 dark:border-gray-800 shadow-2xl overflow-hidden flex flex-col my-auto"
                >
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between p-4 border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-[#111b21]">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-amber-500/10 text-amber-500 flex items-center justify-center">
                                <x-heroicon-s-trophy class="w-4 h-4" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white leading-tight">{{ 'XP & Badge Earnings' }}</h3>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">{{ 'Learner Performance & Achievements' }}</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeProfile" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-lg leading-none p-1 cursor-pointer">
                            &times;
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-4 overflow-y-auto space-y-4 max-h-[calc(90vh-4.5rem)]">
                        {{-- Hero Card --}}
                        <div class="flex items-center gap-3.5 p-3.5 rounded-xl bg-gray-50 dark:bg-[#1b2730] border border-gray-100 dark:border-gray-800">
                            @if ($profileUser['avatar'])
                                <img src="{{ $profileUser['avatar'] }}" alt="{{ $profileUser['name'] }}" class="w-14 h-14 rounded-full object-cover border-2 border-[#008069]">
                            @else
                                <div class="w-14 h-14 rounded-full bg-[#008069] text-white font-black text-lg flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($profileUser['name'], 0, 1)) }}
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <h4 class="text-base font-bold text-gray-900 dark:text-white leading-tight">{{ $profileUser['name'] }}</h4>
                                    @if ($profileUser['is_self'])
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-[#008069] text-white">You</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 mt-1 text-xs">
                                    <span class="text-[#008069] dark:text-emerald-400 font-semibold">{{ $profileUser['rank_tier']['rank_name'] ?? 'Scholar' }}</span>
                                    @if (($profileUser['streak'] ?? 0) > 0)
                                        <span class="text-orange-500 font-semibold inline-flex items-center gap-0.5">
                                            <x-heroicon-s-fire class="w-3.5 h-3.5" />
                                            <span>{{ $profileUser['streak'] }}-Day Streak</span>
                                        </span>
                                    @endif
                                </div>
                                @if (!empty($profileUser['course_intake_labels']))
                                    <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                                        @foreach ($profileUser['course_intake_labels'] as $cil)
                                            <span class="px-2 py-0.5 rounded-md {{ $cil['color']['badge_bg'] ?? 'bg-purple-100 text-purple-700' }} font-bold text-[10px] inline-flex items-center gap-1">
                                                <x-heroicon-o-academic-cap class="w-3 h-3" />
                                                <span>{{ $cil['label'] }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Bio if available --}}
                        @if (filled($profileUser['bio']))
                            <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800 text-xs text-gray-700 dark:text-gray-300 leading-relaxed">
                                {{ $profileUser['bio'] }}
                            </div>
                        @endif

                        {{-- Stats Grid --}}
                        <div class="grid grid-cols-4 gap-2 text-center">
                            <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800">
                                <x-heroicon-s-bolt class="w-4 h-4 text-amber-500 mx-auto mb-0.5" />
                                <span class="text-xs sm:text-sm font-black text-gray-900 dark:text-white block">{{ number_format($profileUser['xp']) }}</span>
                                <span class="text-[9px] text-gray-400 font-semibold uppercase">Lifetime XP</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800">
                                <x-heroicon-s-circle-stack class="w-4 h-4 text-amber-600 mx-auto mb-0.5" />
                                <span class="text-xs sm:text-sm font-black text-gray-900 dark:text-white block">{{ number_format($profileUser['coins']) }}</span>
                                <span class="text-[9px] text-gray-400 font-semibold uppercase">Coins</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800">
                                <x-heroicon-s-trophy class="w-4 h-4 text-amber-500 mx-auto mb-0.5" />
                                <span class="text-xs sm:text-sm font-black text-gray-900 dark:text-white block">{{ $profileUser['badge_count'] }}</span>
                                <span class="text-[9px] text-gray-400 font-semibold uppercase">Badges</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800">
                                <x-heroicon-o-book-open class="w-4 h-4 text-[#008069] mx-auto mb-0.5" />
                                <span class="text-xs sm:text-sm font-black text-gray-900 dark:text-white block">{{ $profileUser['courses_count'] }}</span>
                                <span class="text-[9px] text-gray-400 font-semibold uppercase">Courses</span>
                            </div>
                        </div>

                        {{-- Unlocked Badges --}}
                        <div class="space-y-2">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white flex items-center gap-1">
                                <x-heroicon-s-trophy class="w-3.5 h-3.5 text-amber-500" />
                                <span>{{ 'Earned Badges & Accolades' }} ({{ count($profileUser['badges']) }})</span>
                            </h4>
                            @if (count($profileUser['badges']) > 0)
                                <div class="space-y-1.5">
                                    @foreach ($profileUser['badges'] as $badge)
                                        <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800 flex items-center justify-between gap-2 text-xs">
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-1.5 font-bold text-gray-900 dark:text-white">
                                                    <span>⭐</span>
                                                    <span>{{ $badge['name'] }}</span>
                                                </div>
                                                @if (!empty($badge['description']))
                                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">{{ $badge['description'] }}</p>
                                                @endif
                                            </div>
                                            @if (($badge['xp_reward'] ?? 0) > 0)
                                                <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 shrink-0">+{{ $badge['xp_reward'] }} XP</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs text-gray-400 italic">No badges unlocked yet.</p>
                            @endif
                        </div>

                        {{-- Recent XP Activity --}}
                        @if (!empty($profileUser['recent_transactions']))
                            <div class="space-y-2">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white flex items-center gap-1">
                                    <x-heroicon-s-clock class="w-3.5 h-3.5 text-gray-400" />
                                    <span>{{ 'Recent XP & Point Earnings' }}</span>
                                </h4>
                                <div class="space-y-1.5">
                                    @foreach ($profileUser['recent_transactions'] as $tx)
                                        <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800 flex items-center justify-between gap-2 text-xs">
                                            <div class="min-w-0 flex-1">
                                                <span class="font-bold text-gray-900 dark:text-white block truncate">{{ $tx['description'] }}</span>
                                                <span class="text-[10px] text-gray-400">{{ $tx['created_at'] }}</span>
                                            </div>
                                            @if (($tx['amount_xp'] ?? 0) > 0)
                                                <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 shrink-0">+{{ number_format($tx['amount_xp']) }} XP</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Friendship Actions if not self --}}
                        @if (! $profileUser['is_self'])
                            <div class="pt-2 border-t border-gray-100 dark:border-gray-800 flex justify-center">
                                @if ($profileUser['friendship']['state'] === 'friends')
                                    <span class="text-xs font-bold text-[#008069] dark:text-emerald-400 inline-flex items-center gap-1">
                                        <x-heroicon-s-check-circle class="w-4 h-4" />
                                        <span>Connected Friends</span>
                                    </span>
                                @elseif ($profileUser['friendship']['state'] === 'sent')
                                    <button type="button" wire:click="removeFriend({{ $profileUser['id'] }})" class="px-4 py-1.5 rounded-lg text-xs font-semibold border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer">
                                        Cancel Friend Request
                                    </button>
                                @elseif ($profileUser['friendship']['state'] === 'incoming')
                                    <div class="flex items-center gap-2">
                                        <button type="button" wire:click="acceptRequest({{ $profileUser['friendship']['friendship_id'] }})" class="px-4 py-1.5 rounded-lg text-xs font-semibold bg-[#008069] text-white hover:bg-[#006e5a] cursor-pointer">
                                            Accept Request
                                        </button>
                                        <button type="button" wire:click="declineRequest({{ $profileUser['friendship']['friendship_id'] }})" class="px-3 py-1.5 rounded-lg text-xs font-medium border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer">
                                            Decline
                                        </button>
                                    </div>
                                @else
                                    <button type="button" wire:click="sendRequest({{ $profileUser['id'] }})" class="px-4 py-1.5 rounded-lg text-xs font-semibold bg-[#008069] text-white hover:bg-[#006e5a] inline-flex items-center gap-1 cursor-pointer">
                                        <x-heroicon-s-user-plus class="w-4 h-4" />
                                        <span>Add Friend</span>
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
