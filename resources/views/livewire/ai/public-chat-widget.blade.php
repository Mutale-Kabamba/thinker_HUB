<div
    x-data="{
        isOpen: @entangle('isOpen').live,
        isScrolling: false,
        scrollTimeout: null,
        messagesCount: {{ count($messages) }},
        toggle() {
            this.isOpen = !this.isOpen;
        },
        close() {
            this.isOpen = false;
        },
        init() {
            this.$watch('$wire.messages', (val) => {
                this.messagesCount = Array.isArray(val) ? val.length : 0;
            });
            window.addEventListener('scroll', () => {
                if (!this.isOpen || this.messagesCount === 0) return;
                this.isScrolling = true;
                clearTimeout(this.scrollTimeout);
                this.scrollTimeout = setTimeout(() => {
                    this.isScrolling = false;
                }, 260);
            }, { passive: true });
        }
    }"
    class="font-sans"
>
    <style>
        [x-cloak] { display: none !important; }

        @keyframes candice-wave {
            0%, 100% {
                transform: rotate(0deg);
            }
            15% {
                transform: rotate(15deg);
            }
            30% {
                transform: rotate(-10deg);
            }
            45% {
                transform: rotate(14deg);
            }
            60% {
                transform: rotate(-6deg);
            }
            75% {
                transform: rotate(10deg);
            }
        }

        .candice-waving-hand {
            transform-origin: 70px 78px;
            animation: candice-wave 2.2s infinite ease-in-out;
        }

        .ai-markdown-bubble {
            font-size: 0.84rem;
            line-height: 1.55;
            word-break: break-word;
        }
        .ai-markdown-bubble > *:first-child { margin-top: 0 !important; }
        .ai-markdown-bubble > *:last-child { margin-bottom: 0 !important; }
        .ai-markdown-bubble p { margin-top: 0; margin-bottom: 0.55rem; }
        .ai-markdown-bubble strong, .ai-markdown-bubble b { font-weight: 700; color: #0f172a; }
        .ai-markdown-bubble h1, .ai-markdown-bubble h2, .ai-markdown-bubble h3, .ai-markdown-bubble h4 {
            font-weight: 700; line-height: 1.35; margin-top: 0.75rem; margin-bottom: 0.35rem; color: #0f172a;
        }
        .ai-markdown-bubble h1 { font-size: 1.05rem; }
        .ai-markdown-bubble h2 { font-size: 0.98rem; }
        .ai-markdown-bubble h3 { font-size: 0.92rem; }
        .ai-markdown-bubble h4 { font-size: 0.875rem; }
        .ai-markdown-bubble ul { list-style-type: disc; margin-left: 1.25rem; margin-top: 0.35rem; margin-bottom: 0.55rem; padding-left: 0; }
        .ai-markdown-bubble ol { list-style-type: decimal; margin-left: 1.25rem; margin-top: 0.35rem; margin-bottom: 0.55rem; padding-left: 0; }
        .ai-markdown-bubble li { margin-top: 0.2rem; margin-bottom: 0.2rem; line-height: 1.5; }
        .ai-markdown-bubble li > ul, .ai-markdown-bubble li > ol { margin-top: 0.15rem; margin-bottom: 0.15rem; margin-left: 1rem; }
        .ai-markdown-bubble code:not(pre code) {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.82em;
            padding: 0.15em 0.4em;
            border-radius: 0.35rem;
            background-color: rgba(15, 23, 42, 0.07);
            color: #0f766e;
            font-weight: 600;
        }
        .ai-markdown-bubble pre {
            background-color: #0f172a; color: #f8fafc; border-radius: 0.5rem; padding: 0.65rem 0.8rem; overflow-x: auto; font-size: 0.8em; margin: 0.5rem 0;
        }
        .ai-markdown-bubble pre code { background: transparent !important; color: inherit !important; padding: 0 !important; }
        .ai-markdown-bubble blockquote { border-left: 3px solid #0d9488; padding-left: 0.75rem; margin: 0.5rem 0; font-style: italic; color: #475569; }
        .ai-markdown-bubble a { color: #0d9488; text-decoration: underline; text-underline-offset: 2px; font-weight: 600; }
        .ai-markdown-bubble hr { border: 0; border-top: 1px solid #e2e8f0; margin: 0.75rem 0; }

        .ai-user-bubble {
            background-color: #0f766e !important;
            color: #ffffff !important;
            border-radius: 1rem 1rem 0.25rem 1rem !important;
            padding: 0.65rem 0.9rem !important;
            max-width: 85% !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1) !important;
        }
        .ai-user-bubble p {
            color: #ffffff !important;
            margin: 0 !important;
            line-height: 1.45 !important;
            font-size: 0.85rem !important;
        }
        .ai-user-bubble .ai-bubble-time {
            color: rgba(255, 255, 255, 0.8) !important;
            font-size: 10px !important;
            text-align: right !important;
            margin-top: 0.25rem !important;
            display: block !important;
            font-weight: 500 !important;
        }
    </style>

    <!-- Floating Launcher Button with Waving Lady AI Bot (Candice) -->
    <div class="fixed bottom-5 right-4 sm:bottom-6 sm:right-6 z-50 flex items-center">
        <!-- Friendly Tooltip Pill (Desktop only, visible when closed) -->
        <div
            x-show="!isOpen"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 translate-y-2 scale-90"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            class="absolute bottom-full right-0 mb-3.5 hidden sm:flex items-center space-x-2 px-4 py-2 rounded-full bg-white text-slate-900 text-xs font-bold shadow-xl border border-teal-300 whitespace-nowrap pointer-events-none select-none ring-2 ring-teal-500/10 z-10"
        >
            <span class="relative flex h-2.5 w-2.5 shrink-0">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
            </span>
            <span class="text-slate-900 font-bold text-xs tracking-tight">Ask Candice!</span>
            <!-- Speech bubble tail positioned strictly behind the pill -->
            <span class="absolute -bottom-1.5 right-7 w-3 h-3 bg-white border-r border-b border-teal-300 transform rotate-45 -z-10"></span>
        </div>

        <button
            type="button"
            @click="toggle()"
            class="relative flex items-center justify-center w-14 h-14 sm:w-16 sm:h-16 rounded-full shadow-2xl transition-all duration-300 transform hover:scale-110 active:scale-95 focus:outline-none focus:ring-4 focus:ring-teal-500/40 cursor-pointer"
            aria-label="Chat with Candice (Course Advisor)"
            title="Chat with Candice"
        >
            <!-- Animated Ambient Pulse Ring -->
            <span class="absolute -inset-1 rounded-full bg-teal-400/30 animate-pulse pointer-events-none"></span>

            <!-- Candice: Lady Animated AI Bot (Waves when closed) -->
            <div x-show="!isOpen" class="relative w-full h-full rounded-full overflow-hidden shadow-lg border-2 border-teal-300/80 bg-teal-800">
                <svg viewBox="0 0 100 100" class="w-full h-full" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="candice-bg-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#0f766e" />
                            <stop offset="60%" stop-color="#0d9488" />
                            <stop offset="100%" stop-color="#042f2e" />
                        </linearGradient>
                        <linearGradient id="candice-hair-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#1e1b4b" />
                            <stop offset="100%" stop-color="#312e81" />
                        </linearGradient>
                        <linearGradient id="candice-suit-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#0f766e" />
                            <stop offset="100%" stop-color="#115e59" />
                        </linearGradient>
                        <clipPath id="candice-avatar-clip">
                            <circle cx="50" cy="50" r="48" />
                        </clipPath>
                    </defs>

                    <!-- Background -->
                    <circle cx="50" cy="50" r="48" fill="url(#candice-bg-grad)" />

                    <g clip-path="url(#candice-avatar-clip)">
                        <!-- Ambient Light Halo -->
                        <circle cx="50" cy="42" r="28" fill="#2dd4bf" opacity="0.25" />

                        <!-- Shoulders & Tech Suit -->
                        <path d="M22 96 C24 76 34 70 50 70 C66 70 76 76 78 96 Z" fill="url(#candice-suit-grad)" />
                        <path d="M43 70 L50 82 L57 70 Z" fill="#ccfbf1" />
                        <path d="M46 62 H54 V71 H46 Z" fill="#fed7aa" />

                        <!-- Hair Back -->
                        <path d="M31 46 C31 23 69 23 69 46 C69 58 65 66 65 66 C65 66 61 54 50 54 C39 54 35 66 35 66 C35 66 31 58 31 46 Z" fill="url(#candice-hair-grad)" />

                        <!-- Head & Face -->
                        <ellipse cx="50" cy="46" rx="16" ry="18" fill="#ffedd5" />

                        <!-- Bangs / Hairstyle -->
                        <path d="M34 38 C38 27 58 24 66 34 C63 32 56 31 48 34 C41 37 36 43 34 38 Z" fill="url(#candice-hair-grad)" />
                        <path d="M32 40 C34 46 36 52 38 54 C36 48 34 44 32 40 Z" fill="url(#candice-hair-grad)" />

                        <!-- Cheeks -->
                        <ellipse cx="40" cy="51" rx="2.8" ry="1.8" fill="#f43f5e" opacity="0.35" />
                        <ellipse cx="60" cy="51" rx="2.8" ry="1.8" fill="#f43f5e" opacity="0.35" />

                        <!-- Eyes -->
                        <ellipse cx="43" cy="44" rx="2.4" ry="3.2" fill="#0f172a" />
                        <circle cx="44" cy="43" r="1.1" fill="#ffffff" />
                        <ellipse cx="57" cy="44" rx="2.4" ry="3.2" fill="#0f172a" />
                        <circle cx="58" cy="43" r="1.1" fill="#ffffff" />

                        <!-- Eyebrows -->
                        <path d="M39 39 Q43 37 47 39" stroke="#1e1b4b" stroke-width="1.2" stroke-linecap="round" fill="none" />
                        <path d="M53 39 Q57 37 61 39" stroke="#1e1b4b" stroke-width="1.2" stroke-linecap="round" fill="none" />

                        <!-- Friendly Smile -->
                        <path d="M46 52 Q50 56 54 52" stroke="#e11d48" stroke-width="1.4" stroke-linecap="round" fill="none" />

                        <!-- Glowing AI Headset & Mic -->
                        <path d="M34 44 C33 33 42 27 50 27 C58 27 67 33 66 44" stroke="#5eead4" stroke-width="1.8" stroke-linecap="round" fill="none" opacity="0.9" />
                        <rect x="31" y="42" width="4.5" height="7" rx="2.25" fill="#14b8a6" stroke="#99f6e4" stroke-width="1" />
                        <path d="M33 49 Q36 57 44 56" stroke="#5eead4" stroke-width="1.3" stroke-linecap="round" fill="none" />
                        <circle cx="44" cy="56" r="1.6" fill="#2dd4bf" />

                        <!-- Animated Waving Arm and Hand -->
                        <g class="candice-waving-hand">
                            <path d="M68 76 Q77 64 80 50 L87 53 Q83 70 73 82 Z" fill="#0f766e" />
                            <path d="M78 51 L81 44 C82 41 85 41 86 43 C87 41 90 42 90 44 C91 43 94 44 93 47 C93 49 91 52 87 55 L82 55 Z" fill="#fed7aa" />
                            <path d="M91 36 C93 38 94 41 94 43" stroke="#5eead4" stroke-width="1.4" stroke-linecap="round" opacity="0.75" />
                            <path d="M86 33 C89 35 91 38 91 40" stroke="#5eead4" stroke-width="1.2" stroke-linecap="round" opacity="0.6" />
                        </g>
                    </g>
                </svg>
                <!-- Online Status Dot -->
                <span class="absolute bottom-1 right-1 w-3 h-3 rounded-full bg-emerald-400 border-2 border-white ring-1 ring-teal-800 shadow-sm"></span>
            </div>

            <!-- Close State Icon (When drawer is open) -->
            <div x-show="isOpen" x-cloak class="w-full h-full rounded-full bg-slate-900 text-white flex items-center justify-center shadow-inner border-2 border-slate-700">
                <x-heroicon-o-x-mark class="w-7 h-7 text-white" />
            </div>
        </button>
    </div>

    <!-- Chat Drawer (Pops upwards from bottom on both mobile & desktop) -->
    <div
        x-show="isOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-200 transform"
        x-transition:enter-start="opacity-0 translate-y-6 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150 transform"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-6 scale-95"
        x-bind:class="{
            'opacity-0 pointer-events-none translate-y-8 scale-95': isScrolling && messagesCount > 0,
            'opacity-100 translate-y-0 scale-100': !isScrolling || messagesCount === 0
        }"
        class="fixed inset-x-3 bottom-[4.75rem] sm:inset-auto sm:bottom-24 sm:right-6 w-auto sm:w-[390px] max-w-[calc(100vw-1.5rem)] sm:max-w-[calc(100vw-2rem)] h-[calc(100dvh-6rem)] sm:h-[520px] max-h-[580px] sm:max-h-[calc(100vh-7.5rem)] rounded-2xl bg-white border border-slate-200/90 shadow-2xl flex flex-col overflow-hidden transition-all duration-300 ease-out z-50"
    >
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 bg-gradient-to-r from-teal-700 via-teal-800 to-slate-900 text-white border-b border-teal-800 flex-shrink-0">
            <div class="flex items-center space-x-2.5">
                <div class="relative w-9 h-9 rounded-full overflow-hidden border border-teal-400/40 shadow-xs bg-teal-800 flex-shrink-0">
                    <svg viewBox="0 0 100 100" class="w-full h-full" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="50" cy="50" r="48" fill="#0d9488" />
                        <ellipse cx="50" cy="46" rx="16" ry="18" fill="#ffedd5" />
                        <path d="M31 46 C31 23 69 23 69 46 C69 58 65 66 65 66 C65 66 61 54 50 54 C39 54 35 66 35 66 C35 66 31 58 31 46 Z" fill="#1e1b4b" />
                        <path d="M34 38 C38 27 58 24 66 34 C63 32 56 31 48 34 C41 37 36 43 34 38 Z" fill="#1e1b4b" />
                        <ellipse cx="43" cy="44" rx="2.4" ry="3.2" fill="#0f172a" />
                        <circle cx="44" cy="43" r="1.1" fill="#ffffff" />
                        <ellipse cx="57" cy="44" rx="2.4" ry="3.2" fill="#0f172a" />
                        <circle cx="58" cy="43" r="1.1" fill="#ffffff" />
                        <path d="M46 52 Q50 56 54 52" stroke="#e11d48" stroke-width="1.4" stroke-linecap="round" fill="none" />
                        <path d="M34 44 C33 33 42 27 50 27 C58 27 67 33 66 44" stroke="#5eead4" stroke-width="1.8" stroke-linecap="round" fill="none" />
                    </svg>
                    <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-400 ring-1 ring-white"></span>
                </div>
                <div>
                    <div class="flex items-center space-x-1.5">
                        <h3 class="text-sm font-bold tracking-tight text-white leading-none">Candice</h3>
                        <span class="px-1.5 py-0.5 text-[10px] font-semibold bg-teal-500/30 text-teal-200 rounded-md border border-teal-400/30">AI Bot</span>
                    </div>
                    <p class="text-[11px] text-teal-100/80 mt-1 leading-none">Course Advisor • think.er HUB</p>
                </div>
            </div>
            <div class="flex items-center space-x-1">
                <button
                    type="button"
                    wire:click="resetChat"
                    class="p-1.5 rounded-lg text-teal-200 hover:text-white hover:bg-white/10 transition cursor-pointer"
                    title="Reset Chat"
                    aria-label="Reset Chat"
                >
                    <x-heroicon-o-arrow-path class="w-4 h-4" />
                </button>
                <button
                    type="button"
                    @click="close()"
                    class="p-1.5 rounded-lg text-teal-200 hover:text-white hover:bg-white/10 transition cursor-pointer"
                    title="Close Chat"
                    aria-label="Close Chat"
                >
                    <x-heroicon-o-x-mark class="w-4 h-4" />
                </button>
            </div>
        </div>

        <!-- Error Notification Banner -->
        @if ($errorMessage)
            <div class="px-3.5 py-2 bg-rose-50 border-b border-rose-200 text-xs text-rose-700 flex items-center justify-between flex-shrink-0">
                <span>{{ $errorMessage }}</span>
                <button type="button" wire:click="$set('errorMessage', null)" class="text-rose-500 hover:text-rose-700 cursor-pointer">
                    <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
                </button>
            </div>
        @endif

        <!-- Messages Stream -->
        <div
            x-ref="messageContainer"
            x-init="if (messagesCount > 0) { $el.scrollTop = $el.scrollHeight; }"
            x-on:message-sent.window="messagesCount++; $nextTick(() => { $el.scrollTop = $el.scrollHeight; })"
            class="flex-1 min-h-0 p-4 overflow-y-auto space-y-3 bg-slate-50 text-sm overscroll-contain"
        >
            @if (empty($messages))
                <div class="py-5 px-1 text-center text-slate-500">
                    <div class="w-14 h-14 mx-auto rounded-full overflow-hidden border-2 border-teal-300 shadow-md mb-3 bg-teal-800">
                        <svg viewBox="0 0 100 100" class="w-full h-full" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="50" cy="50" r="48" fill="#0d9488" />
                            <ellipse cx="50" cy="46" rx="16" ry="18" fill="#ffedd5" />
                            <path d="M31 46 C31 23 69 23 69 46 C69 58 65 66 65 66 C65 66 61 54 50 54 C39 54 35 66 35 66 C35 66 31 58 31 46 Z" fill="#1e1b4b" />
                            <path d="M34 38 C38 27 58 24 66 34 C63 32 56 31 48 34 C41 37 36 43 34 38 Z" fill="#1e1b4b" />
                            <ellipse cx="40" cy="51" rx="2.8" ry="1.8" fill="#f43f5e" opacity="0.35" />
                            <ellipse cx="60" cy="51" rx="2.8" ry="1.8" fill="#f43f5e" opacity="0.35" />
                            <ellipse cx="43" cy="44" rx="2.4" ry="3.2" fill="#0f172a" />
                            <circle cx="44" cy="43" r="1.1" fill="#ffffff" />
                            <ellipse cx="57" cy="44" rx="2.4" ry="3.2" fill="#0f172a" />
                            <circle cx="58" cy="43" r="1.1" fill="#ffffff" />
                            <path d="M46 52 Q50 56 54 52" stroke="#e11d48" stroke-width="1.4" stroke-linecap="round" fill="none" />
                            <path d="M34 44 C33 33 42 27 50 27 C58 27 67 33 66 44" stroke="#5eead4" stroke-width="1.8" stroke-linecap="round" fill="none" />
                        </svg>
                    </div>
                    <h4 class="font-bold text-slate-900 text-base">Hi, I'm Candice!</h4>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed max-w-[280px] mx-auto">
                        Your think.er HUB Course Advisor. Ask me anything about our courses, fees, upcoming intakes, or admission steps.
                    </p>

                    <div class="mt-4 flex flex-col gap-2 text-xs text-left">
                        <button
                            type="button"
                            wire:click="$set('message', 'What courses are currently available?'); sendMessage()"
                            class="group p-2.5 rounded-xl bg-white border border-slate-200 hover:border-teal-500 hover:bg-teal-50/30 text-slate-700 hover:text-teal-900 transition flex items-center justify-between shadow-xs cursor-pointer"
                        >
                            <div class="flex items-center space-x-2">
                                <x-heroicon-o-book-open class="w-4 h-4 text-teal-600 group-hover:scale-110 transition-transform" />
                                <span class="font-medium">What courses are currently available?</span>
                            </div>
                            <x-heroicon-o-chevron-right class="w-3.5 h-3.5 text-slate-400 group-hover:text-teal-600 transition" />
                        </button>
                        <button
                            type="button"
                            wire:click="$set('message', 'How does enrollment and payment work?'); sendMessage()"
                            class="group p-2.5 rounded-xl bg-white border border-slate-200 hover:border-teal-500 hover:bg-teal-50/30 text-slate-700 hover:text-teal-900 transition flex items-center justify-between shadow-xs cursor-pointer"
                        >
                            <div class="flex items-center space-x-2">
                                <x-heroicon-o-credit-card class="w-4 h-4 text-teal-600 group-hover:scale-110 transition-transform" />
                                <span class="font-medium">How does enrollment and payment work?</span>
                            </div>
                            <x-heroicon-o-chevron-right class="w-3.5 h-3.5 text-slate-400 group-hover:text-teal-600 transition" />
                        </button>
                        <button
                            type="button"
                            wire:click="$set('message', 'What delivery modes do you support?'); sendMessage()"
                            class="group p-2.5 rounded-xl bg-white border border-slate-200 hover:border-teal-500 hover:bg-teal-50/30 text-slate-700 hover:text-teal-900 transition flex items-center justify-between shadow-xs cursor-pointer"
                        >
                            <div class="flex items-center space-x-2">
                                <x-heroicon-o-tv class="w-4 h-4 text-teal-600 group-hover:scale-110 transition-transform" />
                                <span class="font-medium">What delivery modes do you support?</span>
                            </div>
                            <x-heroicon-o-chevron-right class="w-3.5 h-3.5 text-slate-400 group-hover:text-teal-600 transition" />
                        </button>
                    </div>
                </div>
            @else
                @foreach ($messages as $msg)
                    @if ($msg['role'] === 'user')
                        <div class="flex justify-end">
                            <div class="ai-user-bubble">
                                <p class="whitespace-pre-wrap">{{ $msg['content'] }}</p>
                                @if (! empty($msg['created_at']))
                                    <span class="ai-bubble-time">{{ $msg['created_at'] }}</span>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="flex justify-start">
                            <div class="max-w-[88%] rounded-2xl rounded-tl-sm px-4 py-3 bg-white border border-slate-200/90 text-slate-800 shadow-sm">
                                <div class="text-xs font-semibold text-teal-700 mb-2 pb-1.5 border-b border-slate-100 flex items-center space-x-1.5">
                                    <x-heroicon-s-sparkles class="w-3.5 h-3.5 text-teal-600" />
                                    <span>Candice</span>
                                </div>
                                <div class="ai-markdown-bubble text-slate-800">
                                    {!! \Illuminate\Support\Str::markdown($msg['content'], ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                                </div>
                                @if (! empty($msg['created_at']))
                                    <span class="block text-[10px] text-slate-400 text-left mt-2">{{ $msg['created_at'] }}</span>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif

            <!-- Loading State Indicator -->
            <div wire:loading wire:target="sendMessage" class="flex justify-start">
                <div class="rounded-2xl rounded-tl-none px-3 py-2 bg-white border border-slate-200 text-slate-500 text-xs flex items-center space-x-2 shadow-sm">
                    <x-heroicon-o-arrow-path class="w-4 h-4 animate-spin text-teal-600" />
                    <span>Candice is searching the catalog & formulating reply...</span>
                </div>
            </div>
        </div>

        <!-- Footer / Input Box -->
        <div class="p-3 bg-white border-t border-slate-200 flex-shrink-0">
            <form wire:submit.prevent="sendMessage" class="flex items-center space-x-2">
                <input
                    type="text"
                    wire:model="message"
                    wire:loading.attr="disabled"
                    placeholder="Ask Candice about courses, fees, intakes..."
                    class="flex-1 px-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 disabled:opacity-60 disabled:bg-slate-50 text-slate-900 placeholder:text-slate-400"
                />
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="p-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 disabled:opacity-50 text-white transition flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-teal-500/50 shadow-sm shrink-0 cursor-pointer"
                    title="Send Message"
                    aria-label="Send Message"
                >
                    <x-heroicon-s-paper-airplane class="w-4 h-4" />
                </button>
            </form>
        </div>
    </div>
</div>
