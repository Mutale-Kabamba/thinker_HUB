<div style="padding:0.85rem 1rem;background:var(--hub-surface);border-bottom:2px solid var(--hub-border);">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
        <h4 style="margin:0;font-size:0.88rem;font-weight:700;color:var(--hub-ink);">
            {{ $assessment['submission_status'] === 'Not submitted' ? 'Submit Work' : 'Resubmit / Update' }} — {{ $assessment['name'] }}
        </h4>
        <button @click="expanded = null; panel = null;" style="background:none;border:none;cursor:pointer;color:var(--hub-muted);font-size:1.1rem;line-height:1;" title="Close">&times;</button>
    </div>
    <div style="display:flex;flex-direction:column;gap:0.45rem;">
        <textarea wire:model.defer="submissionDrafts.{{ $assessment['id'] }}.text" class="hub-textarea" placeholder="Write your assessment response..." style="min-height:80px;font-size:0.82rem;"></textarea>
        <input type="url" wire:model.defer="submissionDrafts.{{ $assessment['id'] }}.link" class="hub-input" placeholder="Paste a link (optional)" style="font-size:0.82rem;" />
        <input type="url" wire:model.defer="submissionDrafts.{{ $assessment['id'] }}.video" class="hub-input" placeholder="Paste a video URL — YouTube, Vimeo, etc. (optional)" style="font-size:0.82rem;" />
        <input type="file" wire:model="submissionDrafts.{{ $assessment['id'] }}.file" class="hub-input" style="font-size:0.82rem;" accept=".pdf,.doc,.docx,.txt,.csv,.mp4,.avi,.mov,.wmv,.jpg,.jpeg,.png,.gif,.pptx,.xlsx" />
    </div>
    <div style="margin-top:0.6rem;display:flex;gap:0.45rem;flex-wrap:wrap;">
        <button type="button" wire:click="submit({{ $assessment['id'] }})" class="hub-btn hub-btn-primary" style="font-size:0.8rem;padding:0.35rem 1rem;">
            {{ $assessment['submission_status'] === 'Not submitted' ? 'Submit' : 'Resubmit' }}
        </button>
        @if ($assessment['submission_status'] !== 'Not submitted')
            <button type="button" wire:click="removeSubmission({{ $assessment['id'] }})" class="hub-btn hub-btn-danger" style="font-size:0.8rem;padding:0.35rem 1rem;">Delete Submission</button>
        @endif
        <button type="button" @click="expanded = null; panel = null;" class="hub-btn hub-btn-secondary" style="font-size:0.8rem;padding:0.35rem 1rem;">Cancel</button>
    </div>
</div>
