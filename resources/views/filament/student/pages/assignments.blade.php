<x-filament-panels::page>
    <div class="hub-shell">
        <section class="hub-card">
            <p class="hub-eyebrow">Assignment Workspace</p>
            <h2 class="hub-title">Assignments</h2>
            <p class="hub-copy">Write, submit, update, or remove your assignment responses from one place.</p>
        </section>

        <div class="hub-stack">
            @forelse ($assignments as $assignment)
                <article class="hub-card">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:0.75rem;flex-wrap:wrap;">
                        <div>
                            <p class="hub-eyebrow">{{ $assignment['course'] }} | {{ $assignment['scope'] }}</p>
                            <h3 class="hub-title" style="margin-top:0.25rem;">{{ $assignment['name'] }}</h3>
                            <p class="hub-copy">Due: {{ $assignment['due'] }}</p>
                        </div>
                        <div style="text-align:right;">
                            <span class="hub-chip {{ $assignment['status'] === 'Submitted' ? 'hub-chip-green' : 'hub-chip-amber' }}">{{ $assignment['status'] }}</span>
                            <p style="margin-top:0.35rem;color:var(--hub-muted);font-size:0.76rem;">{{ $assignment['submitted_at'] }}</p>
                        </div>
                    </div>

                    <!-- Assignment File View/Download -->
                    @if (!empty($assignment['file_path']))
                        <div style="margin-top:0.5rem;display:flex;gap:0.5rem;align-items:center;">
                            <a href="{{ asset('storage/' . $assignment['file_path']) }}" target="_blank" class="hub-btn hub-btn-secondary">View</a>
                            <a href="{{ asset('storage/' . $assignment['file_path']) }}" download class="hub-btn hub-btn-secondary">Download</a>
                        </div>
                    @endif

                    <!-- Robust Submission Form -->
                    <form wire:submit.prevent="submit({{ $assignment['id'] }})" enctype="multipart/form-data" style="margin-top:0.75rem;">
                        <textarea wire:model.defer="submissionDrafts.{{ $assignment['id'] }}.text" class="hub-textarea" placeholder="Write your submission..."></textarea>
                        <input type="url" wire:model.defer="submissionDrafts.{{ $assignment['id'] }}.link" class="hub-input" placeholder="Paste a link (optional)" style="margin-top:0.5rem;" />
                        <input type="url" wire:model.defer="submissionDrafts.{{ $assignment['id'] }}.video" class="hub-input" placeholder="Paste a video URL (optional)" style="margin-top:0.5rem;" />
                        <input type="file" wire:model.defer="submissionDrafts.{{ $assignment['id'] }}.file" class="hub-input" style="margin-top:0.5rem;" accept=".pdf,.doc,.docx,.txt,.csv,.mp4,.avi,.mov,.wmv,.jpg,.jpeg,.png,.gif" />
                        <div style="margin-top:0.75rem;display:flex;gap:0.55rem;flex-wrap:wrap;">
                            <button type="submit" class="hub-btn hub-btn-primary">Save / Submit</button>
                            <button type="button" wire:click="removeSubmission({{ $assignment['id'] }})" class="hub-btn hub-btn-danger">Delete Submission</button>
                        </div>
                    </form>
                </article>
            @empty
                <section class="hub-card">
                    <p class="hub-copy">No assignments available.</p>
                </section>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
