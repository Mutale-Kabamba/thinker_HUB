<div class="fixed bottom-6 right-6 z-50 font-sans" x-data>
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
                                <div class="max-w-[82%] rounded-2xl rounded-tr-none px-3.5 py-2.5 bg-teal-600 text-white shadow-sm">
                                    <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ $msg['content'] }}</p>
                                    @if (! empty($msg['created_at']))
                                        <span class="block text-[10px] text-teal-200 text-right mt-1">{{ $msg['created_at'] }}</span>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="flex justify-start">
                                <div class="max-w-[85%] rounded-2xl rounded-tl-none px-3.5 py-2.5 bg-white border border-slate-200 text-slate-800 shadow-sm">
                                    <div class="text-xs font-semibold text-teal-700 mb-1 flex items-center space-x-1">
                                        <x-heroicon-o-sparkles class="w-3.5 h-3.5" />
                                        <span>Advisor</span>
                                    </div>
                                    <div class="text-sm leading-relaxed whitespace-pre-wrap prose prose-sm max-w-none text-slate-800">{!! nl2br(e($msg['content'])) !!}</div>
                                    @if (! empty($msg['created_at']))
                                        <span class="block text-[10px] text-slate-400 text-left mt-1">{{ $msg['created_at'] }}</span>
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

