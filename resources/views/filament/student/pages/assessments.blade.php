<x-filament-panels::page>
    <div class="hub-shell">
        <section class="hub-card">
            <p class="hub-eyebrow">Assessment Workspace</p>
            <h2 class="hub-title">Assessments</h2>
            <p class="hub-copy">Track your assessments and submit your responses with clear feedback.</p>
        </section>

        <div class="hub-stack">
            @forelse ($assessments as $assessment)
                <article class="hub-card">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:0.75rem;flex-wrap:wrap;">
                        <div>
                            <h3 class="hub-title">{{ $assessment['name'] ?? 'Assessment' }}</h3>
                            <p class="hub-copy">Course: {{ $assessment['course'] }} | Due: {{ $assessment['due_date'] ?? '-' }} | Score: {{ $assessment['score'] }}</p>
                        </div>
                        <span class="hub-chip {{ $assessment['submission_status'] === 'Submitted' ? 'hub-chip-green' : 'hub-chip-amber' }}">{{ $assessment['submission_status'] }}</span>
                    </div>

                    <div style="margin-top:0.75rem;">
                        <textarea wire:model="submissionDrafts.{{ $assessment['id'] }}" class="hub-textarea" placeholder="Write your assessment response..."></textarea>
                    </div>

                    <div style="margin-top:0.75rem;display:flex;gap:0.55rem;flex-wrap:wrap;">
                        <button type="button" wire:click="submit({{ $assessment['id'] }})" class="hub-btn hub-btn-primary">Save / Submit</button>
                        <button type="button" wire:click="removeSubmission({{ $assessment['id'] }})" class="hub-btn hub-btn-danger">Delete Submission</button>
                    </div>
                </article>
            @empty
                <section class="hub-card">
                    <p class="hub-copy">No assessments available.</p>
                </section>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
