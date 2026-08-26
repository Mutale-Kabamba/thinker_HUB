<x-filament-panels::page>
    <div class="space-y-6 font-sans">
        {{-- Header Card --}}
        <div class="edtech-card bg-white dark:bg-[#102028] p-6 rounded-2xl border border-slate-100 dark:border-[#233842] shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-300 border border-purple-200/60 dark:border-purple-800">
                        Communications Center
                    </span>
                </div>
                <h1 class="text-xl md:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    Cohort Broadcasts & Announcements
                </h1>
                <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 font-medium">
                    Send email announcements and in-app dashboard notifications directly to all students enrolled in your course.
                </p>
            </div>
        </div>

        {{-- Compose Broadcast Card --}}
        <div class="edtech-card bg-white dark:bg-[#102028] rounded-2xl border border-slate-100 dark:border-[#233842] shadow-sm overflow-hidden p-6 space-y-5">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-[#233842]">
                <div class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 flex items-center justify-center font-bold">
                    <x-heroicon-o-megaphone class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                        Compose Broadcast
                    </h2>
                    <p class="text-xs text-slate-400 dark:text-slate-500">
                        Delivered simultaneously via email and student workspace alerts.
                    </p>
                </div>
            </div>

            @if (count($courseOptions) === 0)
                <div class="py-8 text-center bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-dashed border-slate-200 dark:border-[#233842] text-slate-400 text-xs font-semibold">
                    You have no active courses assigned to broadcast to.
                </div>
            @else
                <div class="space-y-4 max-w-3xl">
                    {{-- Target Course Selection --}}
                    <div class="space-y-1.5">
                        <label for="broadcast-course" class="block text-xs font-bold text-slate-700 dark:text-slate-200">
                            Target Course <span class="text-rose-500">*</span>
                        </label>
                        <select 
                            id="broadcast-course" 
                            wire:model.live="courseId" 
                            class="w-full text-xs font-medium rounded-xl border border-slate-300 dark:border-[#233842] bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2.5 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition"
                        >
                            <option value="">Select a course…</option>
                            @foreach ($courseOptions as $option)
                                <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </select>

                        @if ($courseId)
                            <div class="pt-1 text-xs">
                                @if ($this->enrolledCount > 0)
                                    <span class="inline-flex items-center gap-1.5 font-bold text-emerald-600 dark:text-emerald-400">
                                        <x-heroicon-s-user-group class="w-4 h-4" />
                                        {{ $this->enrolledCount }} enrolled student{{ $this->enrolledCount === 1 ? '' : 's' }} will receive this broadcast
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 font-bold text-amber-600 dark:text-amber-400">
                                        <x-heroicon-s-exclamation-triangle class="w-4 h-4" />
                                        No enrolled students with email found in this course.
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Subject --}}
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label for="broadcast-subject" class="block text-xs font-bold text-slate-700 dark:text-slate-200">
                                Subject <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[11px] font-semibold text-slate-400">
                                {{ mb_strlen($subject) }}/255
                            </span>
                        </div>
                        <input 
                            id="broadcast-subject" 
                            type="text" 
                            wire:model="subject" 
                            maxlength="255" 
                            placeholder="e.g. Project Phase 2 Guidelines & Live Q&A Session"
                            class="w-full text-xs font-medium rounded-xl border border-slate-300 dark:border-[#233842] bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2.5 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition"
                        />
                    </div>

                    {{-- Message Body --}}
                    <div class="space-y-1.5">
                        <label for="broadcast-message" class="block text-xs font-bold text-slate-700 dark:text-slate-200">
                            Announcement Message <span class="text-rose-500">*</span>
                        </label>
                        <textarea 
                            id="broadcast-message" 
                            wire:model="message" 
                            rows="6" 
                            placeholder="Write your announcement to the class… Students will receive this formatted in their email inbox and in-app dashboard."
                            class="w-full text-xs font-medium rounded-xl border border-slate-300 dark:border-[#233842] bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-3 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition"
                        ></textarea>
                    </div>

                    {{-- Attachment --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200">
                            Media Attachment <span class="font-normal text-slate-400">(Optional &bull; PDF, Image, Video, Zip, Docs &bull; Max 25MB)</span>
                        </label>

                        @if ($attachment)
                            <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-teal-50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="text-lg">📎</span>
                                    <div class="min-w-0">
                                        <p class="text-xs font-extrabold text-teal-900 dark:text-teal-200 truncate">
                                            {{ $attachment->getClientOriginalName() }}
                                        </p>
                                        <p class="text-[10px] text-teal-600 dark:text-teal-400">
                                            {{ round($attachment->getSize() / 1024, 1) }} KB &bull; Ready to send
                                        </p>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    wire:click="removeAttachment"
                                    class="inline-flex items-center gap-1 text-xs font-bold text-rose-600 hover:text-rose-700"
                                >
                                    <x-heroicon-s-x-circle class="w-4 h-4" />
                                    <span>Remove</span>
                                </button>
                            </div>
                        @else
                            <div class="border-2 border-dashed border-slate-200 dark:border-[#233842] rounded-xl p-5 text-center bg-slate-50/50 dark:bg-slate-800/30 hover:border-teal-500 transition cursor-pointer relative">
                                <input
                                    type="file"
                                    wire:model="attachment"
                                    id="broadcast-attachment-input"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                />
                                <div wire:loading.remove wire:target="attachment" class="space-y-1">
                                    <x-heroicon-o-paper-clip class="w-6 h-6 mx-auto text-slate-400" />
                                    <p class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                        Click or drag file to attach media
                                    </p>
                                    <p class="text-[11px] text-slate-400">
                                        Images, PDFs, Slides, Videos, or Archives up to 25MB
                                    </p>
                                </div>
                                <div wire:loading wire:target="attachment" class="text-xs font-bold text-teal-600 dark:text-teal-400 flex items-center justify-center gap-2">
                                    <x-heroicon-m-arrow-path class="w-4 h-4 animate-spin" />
                                    <span>Uploading attachment…</span>
                                </div>
                            </div>
                        @endif
                        @error('attachment')
                            <p class="text-xs font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-2">
                        <button
                            type="button"
                            wire:click="send"
                            wire:confirm="Send this broadcast to all enrolled students in the selected course?"
                            wire:loading.attr="disabled"
                            wire:target="send,attachment"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 shadow-sm transition disabled:opacity-50 transform hover:-translate-y-0.5"
                        >
                            <span wire:loading.remove wire:target="send" class="inline-flex items-center gap-2">
                                <x-heroicon-m-paper-airplane class="w-4 h-4" />
                                <span>Send Broadcast Now</span>
                            </span>
                            <span wire:loading wire:target="send" class="inline-flex items-center gap-2">
                                <x-heroicon-m-arrow-path class="w-4 h-4 animate-spin" />
                                <span>Sending to Students…</span>
                            </span>
                        </button>
                    </div>
                </div>
            @endif
        </div>

        {{-- Broadcast History Feed Card --}}
        <div class="edtech-card bg-white dark:bg-[#102028] rounded-2xl border border-slate-100 dark:border-[#233842] shadow-sm overflow-hidden p-6 space-y-4">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-[#233842]">
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center font-bold">
                    <x-heroicon-o-clock class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                        Broadcast History
                    </h2>
                    <p class="text-xs text-slate-400 dark:text-slate-500">
                        Past cohort communications and delivery logs.
                    </p>
                </div>
            </div>

            @if (count($history) === 0)
                <div class="py-8 text-center text-slate-400 dark:text-slate-500 text-xs font-semibold">
                    No broadcasts sent yet.
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($history as $item)
                        <div x-data="{ expanded: false }" class="p-4 rounded-xl border border-slate-200/80 dark:border-[#233842] bg-slate-50/50 dark:bg-slate-800/40 space-y-3">
                            <div class="flex items-start justify-between gap-4 flex-wrap">
                                <div class="min-w-0 flex-1 space-y-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h4 class="font-extrabold text-xs text-slate-900 dark:text-white">
                                            {{ $item['subject'] }}
                                        </h4>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                            {{ $item['course'] }}
                                        </span>
                                        @if (!empty($item['attachment_name']))
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">
                                                📎 {{ $item['attachment_name'] }} ({{ $item['attachment_size'] }})
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-slate-400">
                                        Sent: {{ $item['sent_at'] }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                                        <x-heroicon-s-check-circle class="w-3.5 h-3.5" />
                                        {{ $item['recipients_count'] }} {{ Str::plural('recipient', $item['recipients_count']) }}
                                    </span>
                                    @if ($item['failed_count'] > 0)
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300">
                                            {{ $item['failed_count'] }} failed
                                        </span>
                                    @endif
                                    <button
                                        type="button"
                                        @click="expanded = !expanded"
                                        class="text-xs font-bold text-teal-600 dark:text-teal-400 hover:text-teal-700 px-2 py-1"
                                    >
                                        <span x-show="!expanded">View Details ↓</span>
                                        <span x-show="expanded">Hide ↑</span>
                                    </button>
                                </div>
                            </div>

                            <div x-show="expanded" x-cloak class="mt-2 pt-3 border-t border-slate-200/60 dark:border-[#233842] text-xs text-slate-700 dark:text-slate-300 space-y-3 leading-relaxed whitespace-pre-line bg-white dark:bg-slate-900/60 p-3 rounded-lg">
                                <div>
                                    {{ $item['body'] }}
                                </div>
                                @if (!empty($item['attachment_path']))
                                    <div class="pt-2 border-t border-dashed border-slate-200 dark:border-[#233842] flex items-center justify-between">
                                        <span class="text-[11px] font-bold text-slate-500">
                                            Attachment: {{ $item['attachment_name'] }} ({{ $item['attachment_size'] }})
                                        </span>
                                        <a
                                            href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item['attachment_path']) }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 text-xs font-bold text-teal-600 dark:text-teal-400 hover:underline"
                                        >
                                            <span>Download Attachment</span>
                                            <x-heroicon-m-arrow-top-right-on-square class="w-3.5 h-3.5" />
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
