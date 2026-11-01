<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 min-h-[640px]">
        <!-- Left: Conversations Sidebar -->
        <div class="lg:col-span-1 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 flex flex-col shadow-sm">
            <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-800">
                <div class="flex items-center space-x-2">
                    <x-heroicon-o-chat-bubble-left-right class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                    <span class="font-semibold text-sm text-gray-900 dark:text-white">Sessions</span>
                </div>
                <button
                    type="button"
                    wire:click="startNewConversation"
                    class="inline-flex items-center space-x-1 px-2.5 py-1 text-xs font-medium rounded-lg bg-teal-50 dark:bg-teal-950 text-teal-700 dark:text-teal-300 hover:bg-teal-100 dark:hover:bg-teal-900 transition"
                    title="Start New Study Session"
                >
                    <x-heroicon-o-plus class="w-3.5 h-3.5" />
                    <span>New</span>
                </button>
            </div>

            <!-- Conversations List -->
            <div class="flex-1 overflow-y-auto mt-3 space-y-1.5 pr-1">
                @forelse ($conversations as $conv)
                    <div
                        wire:key="conv-{{ $conv->id }}"
                        class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs transition cursor-pointer {{ $conversationId === $conv->id ? 'bg-teal-500/10 text-teal-700 dark:text-teal-300 font-medium border border-teal-500/30' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}"
                    >
                        <button
                            type="button"
                            wire:click="selectConversation('{{ $conv->id }}')"
                            class="flex-1 text-left truncate mr-2 focus:outline-none"
                        >
                            <span class="block truncate">{{ $conv->title ?: 'Study Session' }}</span>
                            <span class="block text-[10px] text-gray-400 mt-0.5">{{ $conv->updated_at->diffForHumans() }}</span>
                        </button>
                        <button
                            type="button"
                            wire:click.stop="deleteConversation('{{ $conv->id }}')"
                            wire:confirm="Are you sure you want to delete this study session?"
                            class="opacity-0 group-hover:opacity-100 p-1 rounded hover:bg-red-50 dark:hover:bg-red-950 text-gray-400 hover:text-red-600 transition"
                            title="Delete session"
                        >
                            <x-heroicon-o-trash class="w-3.5 h-3.5" />
                        </button>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-400 text-xs">
                        <x-heroicon-o-chat-bubble-bottom-center-text class="w-8 h-8 mx-auto text-gray-300 dark:text-gray-700 mb-2" />
                        <p>No past study sessions</p>
                        <p class="text-[10px] text-gray-400 mt-1">Start a new thread below</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right: Active Chat Area -->
        <div class="lg:col-span-3 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 flex flex-col shadow-sm overflow-hidden min-h-[580px]">
            <!-- Top Controls: Material Attachment -->
            <div class="p-4 bg-gray-50/75 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-full bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center">
                        <x-heroicon-o-sparkles class="w-4 h-4" />
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Socratic Study Companion</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Contextually aware of your enrolled courses, schedule, and quiz reviews</p>
                    </div>
                </div>

                <!-- Document Attachment Select -->
                <div class="flex items-center space-x-2">
                    <label for="material-select" class="text-xs text-gray-500 dark:text-gray-400 flex items-center space-x-1">
                        <x-heroicon-o-paper-clip class="w-3.5 h-3.5" />
                        <span>Grounding Material:</span>
                    </label>
                    <select
                        id="material-select"
                        wire:model="selectedMaterialId"
                        class="text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-teal-500 focus:border-teal-500 py-1.5 pl-2.5 pr-8"
                    >
                        <option value="">None (Use general course context)</option>
                        @foreach ($materials as $mat)
                            <option value="{{ $mat->id }}">
                                [{{ $mat->course?->code ?? 'Course' }}] {{ Str::limit($mat->title, 35) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Error Banner -->
            @if ($errorMessage)
                <div class="px-4 py-2.5 bg-red-50 dark:bg-red-950 border-b border-red-200 dark:border-red-900 text-xs text-red-700 dark:text-red-300 flex items-center justify-between">
                    <span>{{ $errorMessage }}</span>
                    <button type="button" wire:click="$set('errorMessage', null)" class="text-red-500 hover:text-red-700">
                        <x-heroicon-o-x-mark class="w-4 h-4" />
                    </button>
                </div>
            @endif

            <!-- Messages Stream -->
            <div class="flex-1 p-5 overflow-y-auto space-y-4 bg-gray-50/40 dark:bg-gray-950/20">
                @if ($messages->isEmpty())
                    <div class="max-w-xl mx-auto py-10 text-center">
                        <div class="w-14 h-14 mx-auto rounded-2xl bg-teal-50 dark:bg-teal-950 text-teal-600 dark:text-teal-400 flex items-center justify-center mb-4 border border-teal-200 dark:border-teal-800">
                            <x-heroicon-o-academic-cap class="w-7 h-7" />
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">How can I help you study today?</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">
                            I can quiz you on key topics, break down programming concepts, explain your mistakes from past quizzes, or look up your upcoming lecture schedule.
                        </p>

                        <!-- Quick Starter Chips -->
                        <div class="mt-6 flex flex-wrap justify-center gap-2">
                            <button
                                type="button"
                                wire:click="sendMessage('Quiz me on my enrolled course topics')"
                                class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-full text-xs bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-teal-500 text-gray-700 dark:text-gray-300 transition shadow-sm"
                            >
                                <x-heroicon-o-question-mark-circle class="w-3.5 h-3.5 text-teal-600" />
                                <span>Quiz me on my enrolled course topics</span>
                            </button>
                            <button
                                type="button"
                                wire:click="sendMessage('Explain my mistakes from my last completed quiz')"
                                class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-full text-xs bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-teal-500 text-gray-700 dark:text-gray-300 transition shadow-sm"
                            >
                                <x-heroicon-o-document-magnifying-glass class="w-3.5 h-3.5 text-teal-600" />
                                <span>Explain my mistakes from my last completed quiz</span>
                            </button>
                            <button
                                type="button"
                                wire:click="sendMessage('What classes do I have coming up?')"
                                class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-full text-xs bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-teal-500 text-gray-700 dark:text-gray-300 transition shadow-sm"
                            >
                                <x-heroicon-o-calendar-days class="w-3.5 h-3.5 text-teal-600" />
                                <span>What classes do I have coming up?</span>
                            </button>
                        </div>
                    </div>
                @else
                    @foreach ($messages as $msg)
                        @if ($msg->role === 'user')
                            <div class="flex justify-end" wire:key="msg-{{ $msg->id }}">
                                <div class="max-w-[80%] rounded-2xl rounded-tr-none px-4 py-3 bg-teal-600 text-white shadow-sm">
                                    <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ $msg->content }}</p>
                                    <span class="block text-[10px] text-teal-200 text-right mt-1.5">{{ $msg->created_at->format('H:i') }}</span>
                                </div>
                            </div>
                        @elseif ($msg->role === 'assistant')
                            <div class="flex justify-start" wire:key="msg-{{ $msg->id }}">
                                <div class="max-w-[85%] rounded-2xl rounded-tl-none px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white shadow-sm">
                                    <div class="flex items-center space-x-1.5 text-xs font-semibold text-teal-700 dark:text-teal-400 mb-1.5">
                                        <x-heroicon-o-sparkles class="w-4 h-4" />
                                        <span>Study Buddy</span>
                                    </div>
                                    <div class="text-sm leading-relaxed whitespace-pre-wrap prose prose-sm dark:prose-invert max-w-none text-gray-800 dark:text-gray-200">
                                        {!! nl2br(e($msg->content)) !!}
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
                        <x-heroicon-o-arrow-path class="w-4 h-4 animate-spin text-teal-600" />
                        <span>Consulting course notes, schedule, and quiz rubrics...</span>
                    </div>
                </div>
            </div>

            <!-- Input Bar -->
            <div class="p-3 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800">
                <form wire:submit.prevent="sendMessage" class="flex items-center space-x-2">
                    <input
                        type="text"
                        wire:model="message"
                        wire:loading.attr="disabled"
                        placeholder="Ask a question, request code feedback, or review a topic..."
                        class="flex-1 px-4 py-2.5 text-sm rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 disabled:opacity-60"
                    />
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-medium text-xs transition flex items-center space-x-1.5 focus:outline-none focus:ring-2 focus:ring-teal-500/50 disabled:opacity-50"
                        title="Send Message"
                    >
                        <span>Send</span>
                        <x-heroicon-o-paper-airplane class="w-3.5 h-3.5 transform rotate-90" />
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-filament-panels::page>

