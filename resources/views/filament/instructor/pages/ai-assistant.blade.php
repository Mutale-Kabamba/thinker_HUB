<x-filament-panels::page :full-height="true">
    <style>
        /* Lock outer page on desktop to prevent whole-window scrolling */
        @media (min-width: 1024px) {
            html, body {
                height: 100vh !important;
                max-height: 100vh !important;
                overflow: hidden !important;
            }
            .fi-layout, .fi-main-ctn {
                height: 100vh !important;
                max-height: 100vh !important;
                overflow: hidden !important;
            }
            .fi-main {
                height: 100% !important;
                max-height: 100% !important;
                overflow: hidden !important;
                display: flex !important;
                flex-direction: column !important;
                padding-top: 0.75rem !important;
                padding-bottom: 0.75rem !important;
            }
            .fi-page {
                height: 100% !important;
                max-height: 100% !important;
                display: flex !important;
                flex-direction: column !important;
                overflow: hidden !important;
                min-height: 0 !important;
            }
            .fi-page-header-main-ctn {
                height: 100% !important;
                max-height: 100% !important;
                display: flex !important;
                flex-direction: column !important;
                overflow: hidden !important;
                min-height: 0 !important;
                gap: 0.75rem !important;
            }
            .fi-header {
                flex-shrink: 0 !important;
                margin-bottom: 0 !important;
                padding-bottom: 0 !important;
            }
            .fi-page-main, .fi-page-content {
                flex: 1 1 0% !important;
                min-height: 0 !important;
                height: 100% !important;
                display: flex !important;
                flex-direction: column !important;
                overflow: hidden !important;
            }
            .ai-screen-grid {
                flex: 1 1 0% !important;
                min-height: 0 !important;
                height: 100% !important;
                max-height: calc(100vh - 8.5rem) !important;
            }
        }

        .ai-screen-grid {
            height: calc(100vh - 8.5rem);
            max-height: calc(100vh - 8.5rem);
            min-height: 0;
            overflow: hidden;
        }

        .ai-panel-box {
            height: 100% !important;
            max-height: 100% !important;
            min-height: 0 !important;
            overflow: hidden !important;
            display: flex !important;
            flex-direction: column !important;
        }

        .ai-scroll-area {
            flex: 1 1 0% !important;
            min-height: 0 !important;
            overflow-y: auto !important;
            overscroll-behavior: contain !important;
            -webkit-overflow-scrolling: touch;
        }

        /* Sleek custom scrollbars */
        .ai-scroll-area::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        .ai-scroll-area::-webkit-scrollbar-track {
            background: transparent;
        }
        .ai-scroll-area::-webkit-scrollbar-thumb {
            background: rgba(156, 163, 175, 0.4);
            border-radius: 9999px;
        }
        .ai-scroll-area::-webkit-scrollbar-thumb:hover {
            background: rgba(156, 163, 175, 0.6);
        }

        .ai-markdown-bubble {
            font-size: 0.875rem;
            line-height: 1.6;
            word-break: break-word;
        }
        .ai-markdown-bubble > *:first-child { margin-top: 0 !important; }
        .ai-markdown-bubble > *:last-child { margin-bottom: 0 !important; }
        .ai-markdown-bubble p { margin-top: 0; margin-bottom: 0.65rem; }
        .ai-markdown-bubble strong, .ai-markdown-bubble b { font-weight: 700; color: inherit; }
        .ai-markdown-bubble h1, .ai-markdown-bubble h2, .ai-markdown-bubble h3, .ai-markdown-bubble h4 {
            font-weight: 700; line-height: 1.35; margin-top: 0.85rem; margin-bottom: 0.35rem; color: inherit;
        }
        .ai-markdown-bubble h1 { font-size: 1.15rem; }
        .ai-markdown-bubble h2 { font-size: 1.05rem; }
        .ai-markdown-bubble h3 { font-size: 0.95rem; }
        .ai-markdown-bubble h4 { font-size: 0.875rem; }
        .ai-markdown-bubble ul { list-style-type: disc; margin-left: 1.25rem; margin-top: 0.35rem; margin-bottom: 0.65rem; padding-left: 0; }
        .ai-markdown-bubble ol { list-style-type: decimal; margin-left: 1.25rem; margin-top: 0.35rem; margin-bottom: 0.65rem; padding-left: 0; }
        .ai-markdown-bubble li { margin-top: 0.2rem; margin-bottom: 0.2rem; line-height: 1.5; }
        .ai-markdown-bubble li > ul, .ai-markdown-bubble li > ol { margin-top: 0.15rem; margin-bottom: 0.15rem; margin-left: 1rem; }
        .ai-markdown-bubble code:not(pre code) {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.82em;
            padding: 0.15em 0.4em;
            border-radius: 0.35rem;
            background-color: rgba(15, 23, 42, 0.07);
            color: #4f46e5;
            font-weight: 600;
        }
        .dark .ai-markdown-bubble code:not(pre code) {
            background-color: rgba(255, 255, 255, 0.12);
            color: #a5b4fc;
        }
        .ai-markdown-bubble pre {
            background-color: #0f172a; color: #f8fafc; border-radius: 0.5rem; padding: 0.75rem 0.9rem; overflow-x: auto; font-size: 0.82em; margin: 0.5rem 0;
        }
        .ai-markdown-bubble pre code { background: transparent !important; color: inherit !important; padding: 0 !important; }
        .ai-markdown-bubble blockquote { border-left: 3px solid #4f46e5; padding-left: 0.75rem; margin: 0.5rem 0; font-style: italic; opacity: 0.85; }
        .ai-markdown-bubble a { color: #4f46e5; text-decoration: underline; text-underline-offset: 2px; font-weight: 600; }
        .dark .ai-markdown-bubble a { color: #818cf8; }
        .ai-markdown-bubble hr { border: 0; border-top: 1px solid rgba(148, 163, 184, 0.3); margin: 0.75rem 0; }

        .ai-user-bubble {
            background-color: #4338ca !important;
            color: #ffffff !important;
            border-radius: 1.25rem 1.25rem 0.25rem 1.25rem !important;
            padding: 0.75rem 1rem !important;
            max-width: 82% !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12) !important;
        }
        .ai-user-bubble p {
            color: #ffffff !important;
            margin: 0 !important;
            line-height: 1.5 !important;
            font-size: 0.875rem !important;
        }
        .ai-user-bubble .ai-bubble-time {
            color: rgba(255, 255, 255, 0.8) !important;
            font-size: 10px !important;
            text-align: right !important;
            margin-top: 0.35rem !important;
            display: block !important;
            font-weight: 500 !important;
        }
    </style>

    <div class="ai-screen-grid grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Left: Conversations Sidebar (Fixed Container) -->
        <div class="ai-panel-box lg:col-span-1 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <div class="p-4 pb-3 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center space-x-2">
                    <x-heroicon-o-chat-bubble-left-right class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                    <span class="font-semibold text-sm text-gray-900 dark:text-white">Co-Pilot Threads</span>
                </div>
                <button
                    type="button"
                    wire:click="startNewConversation"
                    class="inline-flex items-center space-x-1 px-2.5 py-1 text-xs font-medium rounded-lg bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900 transition"
                    title="Start New Thread"
                >
                    <x-heroicon-o-plus class="w-3.5 h-3.5" />
                    <span>New</span>
                </button>
            </div>

            <!-- Conversations List (Only this scrolls) -->
            <div class="ai-scroll-area p-3 space-y-1.5">
                @forelse ($conversations as $conv)
                    <div
                        wire:key="conv-{{ $conv->id }}"
                        class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs transition cursor-pointer {{ $conversationId === $conv->id ? 'bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 font-medium border border-indigo-500/30' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}"
                    >
                        <button
                            type="button"
                            wire:click="selectConversation('{{ $conv->id }}')"
                            class="flex-1 text-left truncate mr-2 focus:outline-none"
                        >
                            <span class="block truncate">{{ $conv->title ?: 'Operational Task' }}</span>
                            <span class="block text-[10px] text-gray-400 mt-0.5">{{ $conv->updated_at->diffForHumans() }}</span>
                        </button>
                        <button
                            type="button"
                            wire:click.stop="deleteConversation('{{ $conv->id }}')"
                            wire:confirm="Are you sure you want to delete this thread?"
                            class="opacity-0 group-hover:opacity-100 p-1 rounded hover:bg-red-50 dark:hover:bg-red-950 text-gray-400 hover:text-red-600 transition"
                            title="Delete thread"
                        >
                            <x-heroicon-o-trash class="w-3.5 h-3.5" />
                        </button>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-400 text-xs">
                        <x-heroicon-o-cpu-chip class="w-8 h-8 mx-auto text-gray-300 dark:text-gray-700 mb-2" />
                        <p>No operational threads</p>
                        <p class="text-[10px] text-gray-400 mt-1">Start a new request below</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right: Active Chat Area (Fixed Container) -->
        <div class="ai-panel-box lg:col-span-3 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <!-- Top Controls (Fixed) -->
            <div class="p-4 bg-gray-50/75 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-full bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <x-heroicon-o-cpu-chip class="w-4 h-4" />
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Teaching Co-Pilot & Operations</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Mark registers, update schedules, create sessions, and inspect cohort statistics</p>
                    </div>
                </div>
            </div>

            <!-- Error Banner (Fixed if present) -->
            @if ($errorMessage)
                <div class="px-4 py-2.5 bg-red-50 dark:bg-red-950 border-b border-red-200 dark:border-red-900 text-xs text-red-700 dark:text-red-300 flex items-center justify-between flex-shrink-0">
                    <span>{{ $errorMessage }}</span>
                    <button type="button" wire:click="$set('errorMessage', null)" class="text-red-500 hover:text-red-700">
                        <x-heroicon-o-x-mark class="w-4 h-4" />
                    </button>
                </div>
            @endif

            <!-- Messages Stream (Only this scrolls) -->
            <div
                x-data="{ scrollToBottom() { $el.scrollTop = $el.scrollHeight } }"
                x-init="scrollToBottom()"
                x-on:message-sent.window="$nextTick(() => scrollToBottom())"
                class="ai-scroll-area p-5 space-y-4 bg-gray-50/40 dark:bg-gray-950/20"
            >
                @if ($messages->isEmpty())
                    <div class="max-w-xl mx-auto py-10 text-center">
                        <div class="w-14 h-14 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-4 border border-indigo-200 dark:border-indigo-800">
                            <x-heroicon-o-command-line class="w-7 h-7" />
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Instructor Operational Co-Pilot</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">
                            Automate administrative tasks across your assigned courses. Mark registers, reschedule classes, or analyze cohort health.
                        </p>

                        <!-- Quick Action Chips -->
                        <div class="mt-6 flex flex-wrap justify-center gap-2">
                            <button
                                type="button"
                                wire:click="sendMessage('List my sessions scheduled for today')"
                                class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-full text-xs bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-indigo-500 text-gray-700 dark:text-gray-300 transition shadow-sm"
                            >
                                <x-heroicon-o-calendar class="w-3.5 h-3.5 text-indigo-600" />
                                <span>List my sessions scheduled for today</span>
                            </button>
                            <button
                                type="button"
                                wire:click="sendMessage('Mark everyone present for today\'s session')"
                                class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-full text-xs bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-indigo-500 text-gray-700 dark:text-gray-300 transition shadow-sm"
                            >
                                <x-heroicon-o-clipboard-document-check class="w-3.5 h-3.5 text-indigo-600" />
                                <span>Mark everyone present for today's session</span>
                            </button>
                            <button
                                type="button"
                                wire:click="sendMessage('Reschedule an upcoming session')"
                                class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-full text-xs bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-indigo-500 text-gray-700 dark:text-gray-300 transition shadow-sm"
                            >
                                <x-heroicon-o-clock class="w-3.5 h-3.5 text-indigo-600" />
                                <span>Reschedule an upcoming session</span>
                            </button>
                            <button
                                type="button"
                                wire:click="sendMessage('Summarize quiz & attendance stats for my courses')"
                                class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-full text-xs bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-indigo-500 text-gray-700 dark:text-gray-300 transition shadow-sm"
                            >
                                <x-heroicon-o-chart-bar class="w-3.5 h-3.5 text-indigo-600" />
                                <span>Summarize quiz & attendance stats for my courses</span>
                            </button>
                        </div>
                    </div>
                @else
                    @foreach ($messages as $msg)
                        @if ($msg->role === 'user')
                            <div class="flex justify-end" wire:key="msg-{{ $msg->id }}">
                                <div class="ai-user-bubble">
                                    <p class="whitespace-pre-wrap">{{ $msg->content }}</p>
                                    <span class="ai-bubble-time">{{ $msg->created_at->format('H:i') }}</span>
                                </div>
                            </div>
                        @elseif ($msg->role === 'assistant')
                            <div class="flex justify-start" wire:key="msg-{{ $msg->id }}">
                                <div class="max-w-[85%] rounded-2xl rounded-tl-sm px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white shadow-sm">
                                    <div class="flex items-center space-x-1.5 text-xs font-semibold text-indigo-700 dark:text-indigo-400 mb-2 pb-1.5 border-b border-gray-100 dark:border-gray-700/60">
                                        <x-heroicon-s-cpu-chip class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                                        <span>Instructor Co-Pilot</span>
                                    </div>
                                    <div class="ai-markdown-bubble text-gray-800 dark:text-gray-200">
                                        {!! \Illuminate\Support\Str::markdown($msg->content, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                                    </div>
                                    <span class="block text-[10px] text-gray-400 text-left mt-2">{{ $msg->created_at->format('H:i') }}</span>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif

                <!-- Loading State Indicator -->
                <div wire:loading wire:target="sendMessage" class="flex justify-start">
                    <div class="rounded-2xl rounded-tl-none px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 text-xs flex items-center space-x-2 shadow-sm">
                        <x-heroicon-o-arrow-path class="w-4 h-4 animate-spin text-indigo-600" />
                        <span>Executing operations & syncing registers...</span>
                    </div>
                </div>
            </div>

            <!-- Input Bar (Fixed at Bottom) -->
            <div class="p-3 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 flex-shrink-0">
                <form wire:submit.prevent="sendMessage" class="flex items-center space-x-2">
                    <input
                        type="text"
                        wire:model="message"
                        wire:loading.attr="disabled"
                        placeholder="Tell the co-pilot to mark attendance, reschedule a session, or summarize cohort stats..."
                        class="flex-1 px-4 py-2.5 text-sm rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 disabled:opacity-60"
                    />
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="p-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white transition flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-indigo-500/50 shadow-sm shrink-0"
                        title="Execute Instruction"
                        aria-label="Execute Instruction"
                    >
                        <x-heroicon-s-paper-airplane class="w-4 h-4" />
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-filament-panels::page>
