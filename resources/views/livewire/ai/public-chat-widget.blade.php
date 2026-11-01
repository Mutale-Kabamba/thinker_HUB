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

    <!-- Floating Launcher Button -->
    <div class="fixed bottom-5 right-4 sm:bottom-6 sm:right-6 z-50">
        <button
            type="button"
            @click="toggle()"
            class="flex items-center justify-center w-13 h-13 sm:w-14 sm:h-14 rounded-full bg-teal-600 hover:bg-teal-700 active:scale-95 text-white shadow-xl transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-teal-500/30 cursor-pointer"
            aria-label="Chat with Candice (Course Advisor)"
            title="Chat with Candice"
        >
            <span x-show="!isOpen" class="flex items-center justify-center">
                <x-heroicon-o-chat-bubble-left-right class="w-6 h-6" />
            </span>
            <span x-show="isOpen" x-cloak class="flex items-center justify-center">
                <x-heroicon-o-x-mark class="w-6 h-6" />
            </span>
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
        <div class="flex items-center justify-between px-4 py-3.5 bg-gradient-to-r from-teal-700 via-teal-800 to-slate-900 text-white border-b border-teal-800 flex-shrink-0">
            <div class="flex items-center space-x-2.5">
                <div class="relative">
                    <div class="w-9 h-9 rounded-full bg-white/10 text-teal-200 flex items-center justify-center border border-white/20 shadow-xs">
                        <x-heroicon-o-sparkles class="w-5 h-5 text-teal-300" />
                    </div>
                    <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-400 ring-2 ring-teal-800"></span>
                </div>
                <div>
                    <div class="flex items-center space-x-1.5">
                        <h3 class="text-sm font-bold tracking-tight text-white leading-none">Candice</h3>
                        <span class="px-1.5 py-0.5 text-[10px] font-semibold bg-teal-500/30 text-teal-200 rounded-md border border-teal-400/30">AI</span>
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
                    <div class="w-13 h-13 mx-auto rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center border border-teal-200/70 mb-3 shadow-xs">
                        <x-heroicon-o-academic-cap class="w-7 h-7 text-teal-600" />
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
