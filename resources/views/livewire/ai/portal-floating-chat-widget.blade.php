@php
    if (! $canAccess) {
        return;
    }

    $theme = match ($portal) {
        'admin' => [
            'name' => 'Admin AI System Co-Pilot',
            'short' => 'Admin AI Co-Pilot',
            'badge' => 'Executive Manager',
            'color' => 'teal',
            'bg_primary' => 'bg-teal-600 hover:bg-teal-700',
            'text_primary' => 'text-teal-600 dark:text-teal-400',
            'bg_light' => 'bg-teal-50 dark:bg-teal-950/60',
            'border_light' => 'border-teal-200 dark:border-teal-800',
            'user_bubble' => 'bg-[#0f766e]',
            'icon' => 'heroicon-o-shield-check',
        ],
        'instructor' => [
            'name' => 'Instructor AI Co-Pilot',
            'short' => 'Teaching Co-Pilot',
            'badge' => 'Teaching & Ops',
            'color' => 'indigo',
            'bg_primary' => 'bg-indigo-600 hover:bg-indigo-700',
            'text_primary' => 'text-indigo-600 dark:text-indigo-400',
            'bg_light' => 'bg-indigo-50 dark:bg-indigo-950/60',
            'border_light' => 'border-indigo-200 dark:border-indigo-800',
            'user_bubble' => 'bg-indigo-700',
            'icon' => 'heroicon-o-cpu-chip',
        ],
        'student' => [
            'name' => 'AI Study Buddy',
            'short' => 'Study Buddy',
            'badge' => 'Academic Tutor',
            'color' => 'teal',
            'bg_primary' => 'bg-teal-600 hover:bg-teal-700',
            'text_primary' => 'text-teal-600 dark:text-teal-400',
            'bg_light' => 'bg-teal-50 dark:bg-teal-950/60',
            'border_light' => 'border-teal-200 dark:border-teal-800',
            'user_bubble' => 'bg-teal-700',
            'icon' => 'heroicon-o-academic-cap',
        ],
        default => [
            'name' => 'AI Co-Pilot',
            'short' => 'AI Assistant',
            'badge' => 'Assistant',
            'color' => 'teal',
            'bg_primary' => 'bg-teal-600 hover:bg-teal-700',
            'text_primary' => 'text-teal-600 dark:text-teal-400',
            'bg_light' => 'bg-teal-50 dark:bg-teal-950/60',
            'border_light' => 'border-teal-200 dark:border-teal-800',
            'user_bubble' => 'bg-teal-700',
            'icon' => 'heroicon-o-sparkles',
        ],
    };
@endphp

<div class="font-sans">
    <style>
        .portal-float-bubble {
            font-size: 0.84rem;
            line-height: 1.55;
            word-break: break-word;
        }
        .portal-float-bubble > *:first-child { margin-top: 0 !important; }
        .portal-float-bubble > *:last-child { margin-bottom: 0 !important; }
        .portal-float-bubble p { margin-top: 0; margin-bottom: 0.5rem; }
        .portal-float-bubble strong, .portal-float-bubble b { font-weight: 700; color: inherit; }
        .portal-float-bubble h1, .portal-float-bubble h2, .portal-float-bubble h3, .portal-float-bubble h4 {
            font-weight: 700; line-height: 1.3; margin-top: 0.75rem; margin-bottom: 0.3rem; color: inherit;
        }
        .portal-float-bubble h1 { font-size: 1.05rem; }
        .portal-float-bubble h2 { font-size: 0.95rem; }
        .portal-float-bubble h3 { font-size: 0.88rem; }
        .portal-float-bubble h4 { font-size: 0.82rem; }
        .portal-float-bubble ul { list-style-type: disc; margin-left: 1.15rem; margin-top: 0.3rem; margin-bottom: 0.5rem; }
        .portal-float-bubble ol { list-style-type: decimal; margin-left: 1.15rem; margin-top: 0.3rem; margin-bottom: 0.5rem; }
        .portal-float-bubble li { margin-top: 0.15rem; margin-bottom: 0.15rem; line-height: 1.45; }
        .portal-float-bubble code:not(pre code) {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.82em;
            padding: 0.15em 0.35em;
            border-radius: 0.35rem;
            background-color: rgba(15, 23, 42, 0.08);
            font-weight: 600;
        }
        .dark .portal-float-bubble code:not(pre code) {
            background-color: rgba(255, 255, 255, 0.12);
        }
        .portal-float-bubble pre {
            background-color: #0f172a;
            color: #f8fafc;
            border-radius: 0.5rem;
            padding: 0.65rem 0.8rem;
            overflow-x: auto;
            font-size: 0.8em;
            margin: 0.45rem 0;
        }
        .portal-float-bubble pre code { background: transparent !important; color: inherit !important; padding: 0 !important; }
        .portal-float-bubble blockquote {
            border-left: 3px solid currentColor;
            padding-left: 0.65rem;
            margin: 0.45rem 0;
            font-style: italic;
            opacity: 0.85;
        }
        .portal-float-bubble a { text-decoration: underline; text-underline-offset: 2px; font-weight: 600; }

        .portal-user-bubble {
            color: #ffffff !important;
            border-radius: 1.25rem 1.25rem 0.25rem 1.25rem !important;
            padding: 0.65rem 0.9rem !important;
            max-width: 85% !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
        }
        .portal-user-bubble p {
            color: #ffffff !important;
            margin: 0 !important;
            line-height: 1.45 !important;
            font-size: 0.84rem !important;
        }
        .portal-user-bubble .portal-bubble-time {
            color: rgba(255, 255, 255, 0.75) !important;
            font-size: 10px !important;
            text-align: right !important;
            margin-top: 0.25rem !important;
            display: block !important;
            font-weight: 500 !important;
        }
    </style>

    @if (! $isOpen)
        {{-- Minimized Floating Trigger Button --}}
        <div class="fixed bottom-5 right-5 z-[9990]">
            <button
                type="button"
                wire:click="toggleOpen"
                class="flex items-center gap-2.5 px-4 py-2.5 rounded-full shadow-xl hover:shadow-2xl transition-all duration-200 cursor-pointer text-xs font-semibold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-800 dark:text-gray-100 group"
                title="Open floating {{ $theme['name'] }}"
                aria-label="Open floating {{ $theme['name'] }}"
            >
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 {{ $portal === 'instructor' ? 'bg-indigo-400' : 'bg-teal-400' }}"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 {{ $portal === 'instructor' ? 'bg-indigo-500' : 'bg-teal-500' }}"></span>
                </span>

                <div class="w-6 h-6 rounded-lg {{ $theme['bg_light'] }} {{ $theme['text_primary'] }} flex items-center justify-center">
                    <x-dynamic-component :component="$theme['icon']" class="w-3.5 h-3.5" />
                </div>

                <span>{{ $theme['short'] }}</span>

                <x-heroicon-o-chevron-up class="w-4 h-4 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition" />
            </button>
        </div>
    @else
        {{-- Expanded Floating Chat Window --}}
        <div class="fixed bottom-4 right-4 z-[9990] w-[calc(100vw-2rem)] sm:w-[440px] h-[580px] max-h-[85vh] rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            {{-- Header --}}
            <div class="p-3.5 bg-gray-50/90 dark:bg-gray-800/80 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center space-x-2 truncate mr-2">
                    <div class="w-7 h-7 rounded-lg {{ $theme['bg_light'] }} {{ $theme['text_primary'] }} flex items-center justify-center flex-shrink-0">
                        <x-dynamic-component :component="$theme['icon']" class="w-4 h-4" />
                    </div>
                    <div class="truncate">
                        <div class="flex items-center gap-1.5">
                            <h3 class="text-xs font-bold text-gray-900 dark:text-white truncate">{{ $theme['short'] }}</h3>
                            <span class="px-1.5 py-0.2 text-[9px] font-semibold rounded {{ $theme['bg_light'] }} {{ $theme['text_primary'] }} border {{ $theme['border_light'] }}">
                                {{ $theme['badge'] }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-1 flex-shrink-0">
                    {{-- Threads Drawer Toggle --}}
                    <button
                        type="button"
                        wire:click="toggleThreads"
                        class="p-1.5 rounded-lg text-xs font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 transition cursor-pointer flex items-center gap-1"
                        title="View Conversation Threads"
                    >
                        <x-heroicon-o-chat-bubble-left-right class="w-3.5 h-3.5" />
                        <span class="text-[11px]">({{ $conversations->count() }})</span>
                    </button>

                    {{-- Pin Back to Full Page --}}
                    <button
                        type="button"
                        wire:click="pinBack"
                        class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-semibold bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-100 transition cursor-pointer shadow-2xs"
                        title="Pin back to full AI page"
                    >
                        <x-heroicon-o-arrows-pointing-in class="w-3.5 h-3.5 text-gray-600 dark:text-gray-300" />
                        <span class="text-[11px]">Pin</span>
                    </button>

                    {{-- Minimize to Bubble --}}
                    <button
                        type="button"
                        wire:click="toggleOpen"
                        class="p-1.5 rounded-lg text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 transition cursor-pointer"
                        title="Minimize"
                    >
                        <x-heroicon-o-minus class="w-4 h-4" />
                    </button>
                </div>
            </div>

            {{-- Optional Threads Overlay Drawer --}}
            @if ($showThreads)
                <div class="p-3 bg-gray-100/95 dark:bg-gray-800/95 border-b border-gray-200 dark:border-gray-700 max-h-52 overflow-y-auto space-y-1.5 flex-shrink-0">
                    <div class="flex items-center justify-between pb-1 mb-1 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-[11px] font-semibold text-gray-700 dark:text-gray-300">Previous Threads</span>
                        <button
                            type="button"
                            wire:click="startNewConversation"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium {{ $theme['bg_light'] }} {{ $theme['text_primary'] }} hover:opacity-80 transition cursor-pointer"
                        >
                            <x-heroicon-o-plus class="w-3 h-3" />
                            <span>New</span>
                        </button>
                    </div>

                    @forelse ($conversations as $conv)
                        <div
                            wire:key="float-conv-{{ $conv->id }}"
                            class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs transition cursor-pointer {{ $conversationId === $conv->id ? $theme['bg_light'] . ' ' . $theme['text_primary'] . ' font-medium border ' . $theme['border_light'] : 'text-gray-700 dark:text-gray-300 hover:bg-gray-200/70 dark:hover:bg-gray-700/70' }}"
                        >
                            <button
                                type="button"
                                wire:click="selectConversation('{{ $conv->id }}')"
                                class="flex-1 text-left truncate mr-2 focus:outline-none cursor-pointer"
                            >
                                <span class="block truncate text-[11px]">{{ $conv->title ?: 'Conversation Thread' }}</span>
                                <span class="block text-[9px] text-gray-400">{{ $conv->updated_at->diffForHumans() }}</span>
                            </button>
                            <button
                                type="button"
                                wire:click.stop="deleteConversation('{{ $conv->id }}')"
                                wire:confirm="Delete this thread?"
                                class="p-1 rounded hover:bg-red-50 dark:hover:bg-red-950 text-gray-400 hover:text-red-500 transition cursor-pointer"
                                title="Delete"
                            >
                                <x-heroicon-o-trash class="w-3 h-3" />
                            </button>
                        </div>
                    @empty
                        <div class="text-center py-4 text-gray-400 text-[11px]">
                            No previous threads.
                        </div>
                    @endforelse
                </div>
            @endif

            {{-- Error Message --}}
            @if ($errorMessage)
                <div class="px-3 py-1.5 bg-red-50 dark:bg-red-950 border-b border-red-200 dark:border-red-900 text-[11px] text-red-700 dark:text-red-300 flex items-center justify-between flex-shrink-0">
                    <span class="truncate mr-2">{{ $errorMessage }}</span>
                    <button type="button" wire:click="$set('errorMessage', null)" class="text-red-500 hover:text-red-700 cursor-pointer">
                        <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
                    </button>
                </div>
            @endif

            {{-- Student Grounding Material Selector --}}
            @if ($portal === 'student' && $materials->isNotEmpty())
                <div class="px-3 py-1.5 bg-gray-50/80 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800 flex items-center space-x-2 flex-shrink-0">
                    <span class="text-[10px] text-gray-500 dark:text-gray-400 whitespace-nowrap">Material:</span>
                    <select
                        wire:model="selectedMaterialId"
                        class="text-[11px] rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white py-1 px-2 flex-1 focus:ring-1 focus:ring-teal-500 truncate"
                    >
                        <option value="">General Course Context</option>
                        @foreach ($materials as $mat)
                            <option value="{{ $mat->id }}">
                                [{{ $mat->course?->code ?? 'Course' }}] {{ Str::limit($mat->title, 30) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Chat Messages Stream --}}
            <div
                x-data="{ scrollToBottom() { $el.scrollTop = $el.scrollHeight } }"
                x-init="scrollToBottom()"
                x-on:message-sent.window="$nextTick(() => scrollToBottom())"
                class="flex-1 min-h-0 overflow-y-auto p-3.5 space-y-3 bg-gray-50/40 dark:bg-gray-950/20"
            >
                @if ($messages->isEmpty())
                    <div class="py-6 text-center max-w-sm mx-auto">
                        <div class="w-10 h-10 mx-auto rounded-xl {{ $theme['bg_light'] }} {{ $theme['text_primary'] }} flex items-center justify-center mb-2.5 border {{ $theme['border_light'] }}">
                            <x-dynamic-component :component="$theme['icon']" class="w-5 h-5" />
                        </div>
                        <h4 class="text-xs font-bold text-gray-900 dark:text-white">{{ $theme['name'] }}</h4>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                            @if ($portal === 'admin')
                                Query platform revenue, audit courses, manage users, or review instructor applications.
                            @elseif ($portal === 'instructor')
                                Mark registers, check session schedules, inspect student risk, or review quizzes.
                            @else
                                Ask for explanations, practice quizzes, or study help with your enrolled tracks.
                            @endif
                        </p>

                        {{-- Quick Action Starter Chips --}}
                        <div class="mt-4 flex flex-wrap justify-center gap-1.5">
                            @if ($portal === 'admin')
                                <button
                                    type="button"
                                    wire:click="sendMessage('Provide a full system overview: user counts, active courses, enrollments, revenue, and pending actions')"
                                    class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[10px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-teal-500 text-gray-700 dark:text-gray-300 transition shadow-2xs cursor-pointer"
                                >
                                    <x-heroicon-o-chart-bar-square class="w-3 h-3 text-teal-600" />
                                    <span>System Vitals</span>
                                </button>
                                <button
                                    type="button"
                                    wire:click="sendMessage('List all pending instructor applications awaiting administrative review')"
                                    class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[10px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-teal-500 text-gray-700 dark:text-gray-300 transition shadow-2xs cursor-pointer"
                                >
                                    <x-heroicon-o-academic-cap class="w-3 h-3 text-teal-600" />
                                    <span>Pending Instructors</span>
                                </button>
                                <button
                                    type="button"
                                    wire:click="sendMessage('Break down platform revenue by payment method, provider, and courses')"
                                    class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[10px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-teal-500 text-gray-700 dark:text-gray-300 transition shadow-2xs cursor-pointer"
                                >
                                    <x-heroicon-o-currency-dollar class="w-3 h-3 text-teal-600" />
                                    <span>Revenue Breakdown</span>
                                </button>
                            @elseif ($portal === 'instructor')
                                <button
                                    type="button"
                                    wire:click="sendMessage('Show my scheduled teaching sessions for today')"
                                    class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[10px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-indigo-500 text-gray-700 dark:text-gray-300 transition shadow-2xs cursor-pointer"
                                >
                                    <x-heroicon-o-calendar class="w-3 h-3 text-indigo-600" />
                                    <span>Today's Sessions</span>
                                </button>
                                <button
                                    type="button"
                                    wire:click="sendMessage('Mark everyone present for today\'s session')"
                                    class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[10px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-indigo-500 text-gray-700 dark:text-gray-300 transition shadow-2xs cursor-pointer"
                                >
                                    <x-heroicon-o-clipboard-document-check class="w-3 h-3 text-indigo-600" />
                                    <span>Mark Register</span>
                                </button>
                                <button
                                    type="button"
                                    wire:click="sendMessage('Identify any at-risk students with low attendance or failing grades in my courses')"
                                    class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[10px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-indigo-500 text-gray-700 dark:text-gray-300 transition shadow-2xs cursor-pointer"
                                >
                                    <x-heroicon-o-user-group class="w-3 h-3 text-indigo-600" />
                                    <span>At-Risk Students</span>
                                </button>
                            @else
                                <button
                                    type="button"
                                    wire:click="sendMessage('Quiz me on my enrolled course topics')"
                                    class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[10px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-teal-500 text-gray-700 dark:text-gray-300 transition shadow-2xs cursor-pointer"
                                >
                                    <x-heroicon-o-question-mark-circle class="w-3 h-3 text-teal-600" />
                                    <span>Quiz Practice</span>
                                </button>
                                <button
                                    type="button"
                                    wire:click="sendMessage('Explain my mistakes from my last completed quiz')"
                                    class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[10px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-teal-500 text-gray-700 dark:text-gray-300 transition shadow-2xs cursor-pointer"
                                >
                                    <x-heroicon-o-document-magnifying-glass class="w-3 h-3 text-teal-600" />
                                    <span>Review Last Quiz</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @else
                    @foreach ($messages as $msg)
                        @if ($msg->role === 'user')
                            <div class="flex justify-end" wire:key="float-msg-{{ $msg->id }}">
                                <div class="portal-user-bubble {{ $theme['user_bubble'] }}">
                                    <p class="whitespace-pre-wrap">{{ $msg->content }}</p>
                                    <span class="portal-bubble-time">{{ $msg->created_at->format('H:i') }}</span>
                                </div>
                            </div>
                        @else
                            <div class="flex justify-start" wire:key="float-msg-{{ $msg->id }}">
                                <div class="rounded-2xl rounded-tl-none px-3.5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 max-w-[90%] shadow-2xs">
                                    <div class="flex items-center space-x-1.5 mb-1.5">
                                        <div class="w-4 h-4 rounded {{ $theme['bg_light'] }} {{ $theme['text_primary'] }} flex items-center justify-center font-bold">
                                            <x-dynamic-component :component="$theme['icon']" class="w-3 h-3" />
                                        </div>
                                        <span class="text-[10px] font-bold text-gray-700 dark:text-gray-300">{{ $theme['short'] }}</span>
                                        <span class="text-[9px] text-gray-400">{{ $msg->created_at->format('H:i') }}</span>
                                    </div>
                                    <div class="portal-float-bubble text-gray-800 dark:text-gray-200">
                                        {!! Str::markdown($msg->content) !!}
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif

                {{-- Loading Indicator --}}
                <div wire:loading wire:target="sendMessage" class="flex justify-start">
                    <div class="rounded-2xl rounded-tl-none px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 text-[11px] flex items-center space-x-2 shadow-2xs">
                        <svg class="w-3.5 h-3.5 animate-spin {{ $theme['text_primary'] }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Thinking & executing...</span>
                    </div>
                </div>
            </div>

            {{-- Input Bar --}}
            <div class="p-2.5 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 flex-shrink-0">
                <form wire:submit.prevent="sendMessage" class="flex items-center space-x-1.5">
                    <input
                        type="text"
                        wire:model="message"
                        wire:loading.attr="disabled"
                        placeholder="Ask {{ $theme['short'] }}..."
                        class="flex-1 px-3 py-2 text-xs rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-teal-500 disabled:opacity-60"
                    />
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="p-2 rounded-xl {{ $theme['bg_primary'] }} disabled:opacity-50 text-white transition flex items-center justify-center cursor-pointer shrink-0"
                        title="Send Message"
                    >
                        <x-heroicon-m-paper-airplane class="w-3.5 h-3.5" />
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>

