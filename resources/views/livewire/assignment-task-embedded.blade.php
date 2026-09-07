<div>
    @if($assignment)
        <div class="space-y-6">
            <div class="border-b border-slate-800 pb-4">
                <h3 class="text-xl font-bold text-white">{{ $assignment->name }}</h3>
                @if($assignment->description)
                    <div class="text-sm text-slate-300 mt-2 whitespace-pre-line leading-relaxed">
                        {{ $assignment->description }}
                    </div>
                @endif
                @if($assignment->file_path)
                    <div class="mt-4">
                        <a 
                            href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($assignment->file_path) }}" 
                            target="_blank" 
                            class="inline-flex items-center space-x-2 text-xs text-indigo-400 hover:text-indigo-300 bg-indigo-950/40 border border-indigo-500/30 px-3 py-1.5 rounded-lg transition"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Download Resource Materials</span>
                        </a>
                    </div>
                @endif
            </div>

            @if($isSubmitted)
                <div class="p-6 rounded-xl border bg-emerald-950/40 border-emerald-500/50 space-y-3">
                    <div class="flex items-center space-x-3 text-emerald-400">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span class="font-semibold text-base">Assignment Submitted</span>
                    </div>
                    <p class="text-xs text-slate-300">
                        Your submission has been recorded. This lesson is verified and marked complete.
                    </p>
                    @if($submission && $submission->grade)
                        <div class="mt-2 text-sm bg-slate-900/80 p-3 rounded-lg border border-slate-800">
                            <span class="text-slate-400">Grade:</span> <strong class="text-emerald-400">{{ $submission->grade }}</strong>
                            @if($submission->feedback)
                                <p class="text-slate-300 text-xs mt-1">{{ $submission->feedback }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            @else
                <form wire:submit.prevent="submitTask" class="space-y-4 bg-slate-950/60 p-6 rounded-xl border border-slate-800">
                    <h4 class="text-sm font-semibold text-slate-200">Submit Your Solution</h4>

                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Notes / Solution Details</label>
                        <textarea 
                            wire:model="content" 
                            rows="4" 
                            placeholder="Explain your approach, provide summary or answers..." 
                            class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-sm text-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 placeholder-slate-500"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Project Link (GitHub, Google Docs, Figma, etc.)</label>
                        <input 
                            type="url" 
                            wire:model="link" 
                            placeholder="https://..." 
                            class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-sm text-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 placeholder-slate-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">File Attachment (Optional, max 20MB)</label>
                        <input 
                            type="file" 
                            wire:model="file" 
                            class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-200 hover:file:bg-slate-700 cursor-pointer"
                        />
                        <div wire:loading wire:target="file" class="text-xs text-indigo-400 mt-1">Uploading file...</div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button 
                            type="submit" 
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 font-medium rounded-lg text-white shadow-lg transition flex items-center space-x-2 text-sm"
                        >
                            <span>Submit Task & Unlock Next</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    @else
        <p class="text-slate-400">Assignment not found.</p>
    @endif
</div>
