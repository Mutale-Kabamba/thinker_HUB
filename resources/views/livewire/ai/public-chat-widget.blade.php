<div class="fixed bottom-6 right-6 z-50 font-sans" x-data>
    <style>
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
    </style>
    <!-- Floating Launcher Button -->
    <button
        type="button"
        wire:click="toggleChat"
        class="flex items-center justify-center w-14 h-14 rounded-full bg-teal-600 hover:bg-teal-700 text-white shadow-xl transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-teal-500/30"
        aria-label="Open Course Advisor"
    >
        @if ($isOpen)
            <x-heroicon-o-x-mark class="w-6 h-6" />
        @else
            <x-heroicon-o-chat-bubble-left-right class="w-6 h-6" />
        @endif
    </button>

    <!-- Chat Drawer -->
    @if ($isOpen)
        <div class="fixed bottom-24 right-6 w-96 max-w-[calc(100vw-2rem)] h-[520px] rounded-2xl bg-white border border-slate-200 shadow-2xl flex flex-col overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-200">
            <!-- Header -->
            <div class="flex items-center justify-between px-4 py-3 bg-slate-900 text-white border-b border-slate-800">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-full bg-teal-500/20 text-teal-400 flex items-center justify-center border border-teal-500/30">
                        <x-heroicon-o-sparkles class="w-4 h-4" />
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold tracking-tight leading-none text-white">Course Advisor</h3>
                        <p class="text-xs text-slate-400 mt-0.5 leading-none">think.er HUB AI Assistant</p>
                    </div>
                </div>
                <div class="flex items-center space-x-1">
                    <button
                        type="button"
                        wire:click="resetChat"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition"
                        title="Reset Chat"
                        aria-label="Reset Chat"
                    >
                        <x-heroicon-o-arrow-path class="w-4 h-4" />
                    </button>
                    <button
                        type="button"
                        wire:click="toggleChat"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition"
                        title="Close Chat"
                        aria-label="Close Chat"
                    >
                        <x-heroicon-o-x-mark class="w-4 h-4" />
                    </button>
                </div>
            </div>

            <!-- Error Notification Banner -->
            @if ($errorMessage)
                <div class="px-3 py-2 bg-rose-50 border-b border-rose-200 text-xs text-rose-700 flex items-center justify-between">
                    <span>{{ $errorMessage }}</span>
                    <button type="button" wire:click="$set('errorMessage', null)" class="text-rose-500 hover:text-rose-700">
                        <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
                    </button>
                </div>
            @endif

            <!-- Messages Stream -->
            <div class="flex-1 p-4 overflow-y-auto space-y-3 bg-slate-50 text-sm">
                @if (empty($messages))
                    <div class="text-center py-6 text-slate-500">
                        <div class="w-12 h-12 mx-auto rounded-full bg-teal-50 text-teal-600 flex items-center justify-center border border-teal-200 mb-3">
                            <x-heroicon-o-academic-cap class="w-6 h-6" />
                        </div>
                        <p class="font-medium text-slate-700">Welcome to think.er HUB</p>
                        <p class="text-xs text-slate-500 mt-1">Ask me about courses, fees, upcoming intakes, or how to get started!</p>
                        <div class="mt-4 flex flex-col gap-1.5 text-xs text-left">
                            <button
                                type="button"
                                wire:click="$set('message', 'What courses are currently available?'); sendMessage()"
                                class="p-2 rounded-lg bg-white border border-slate-200 hover:border-teal-500 text-slate-700 transition"
                            >
                                What courses are currently available?
                            </button>
                            <button
                                type="button"
                                wire:click="$set('message', 'How does enrollment and payment work?'); sendMessage()"
                                class="p-2 rounded-lg bg-white border border-slate-200 hover:border-teal-500 text-slate-700 transition"
                            >
                                How does enrollment and payment work?
                            </button>
                        </div>
                    </div>
                @else
                    @foreach ($messages as $msg)
                        @if ($msg['role'] === 'user')
                            <div class="flex justify-end">
                                <div class="max-w-[85%] rounded-2xl rounded-tr-sm px-4 py-2.5 bg-gradient-to-r from-teal-600 to-teal-700 text-white shadow-sm">
                                    <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ $msg['content'] }}</p>
                                    @if (! empty($msg['created_at']))
                                        <span class="block text-[10px] text-teal-200 text-right mt-1 font-medium">{{ $msg['created_at'] }}</span>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="flex justify-start">
                                <div class="max-w-[88%] rounded-2xl rounded-tl-sm px-4 py-3 bg-white border border-slate-200/90 text-slate-800 shadow-sm">
                                    <div class="text-xs font-semibold text-teal-700 mb-2 pb-1.5 border-b border-slate-100 flex items-center space-x-1.5">
                                        <x-heroicon-s-sparkles class="w-3.5 h-3.5 text-teal-600" />
                                        <span>Course Advisor</span>
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
                        <span>Searching catalog & formulating reply...</span>
                    </div>
                </div>
            </div>

            <!-- Footer / Input Box -->
            <div class="p-3 bg-white border-t border-slate-200">
                <form wire:submit.prevent="sendMessage" class="flex items-center space-x-2">
                    <input
                        type="text"
                        wire:model="message"
                        wire:loading.attr="disabled"
                        placeholder="Ask about courses, fees, intakes..."
                        class="flex-1 px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 disabled:opacity-60 disabled:bg-slate-100"
                    />
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="p-2 rounded-xl bg-teal-600 hover:bg-teal-700 disabled:opacity-50 text-white transition flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-teal-500/50"
                        title="Send Message"
                        aria-label="Send Message"
                    >
                        <x-heroicon-o-paper-airplane class="w-4 h-4 transform rotate-90" />
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>

