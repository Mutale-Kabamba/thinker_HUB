<x-filament-panels::page>
    <div class="hub-shell">
        <section class="hub-card">
            <p class="hub-eyebrow">Assessment Workspace</p>
            <h2 class="hub-title">Assessments</h2>
            <p class="hub-copy">View assessment details, download files, submit your responses, and review grades &amp; feedback.</p>
        </section>

        <div class="hub-stack">
            @forelse ($assessments as $assessment)
                <article class="hub-card">
                    {{-- Header --}}
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:0.75rem;flex-wrap:wrap;">
                        <div>
                            <h3 class="hub-title">{{ $assessment['name'] }}</h3>
                            <p class="hub-copy">Course: {{ $assessment['course'] }} | Due: {{ $assessment['due_date'] }}</p>
                        </div>
                        <div style="text-align:right;">
                            <span class="hub-chip
                                @if(in_array($assessment['submission_status'], ['Graded','Checked'])) hub-chip-green
                                @elseif($assessment['submission_status'] === 'Submitted') hub-chip-blue
                                @else hub-chip-amber @endif
                            ">{{ $assessment['submission_status'] }}</span>
                        </div>
                    </div>

                    {{-- Description --}}
                    @if (!empty($assessment['description']))
                        <div style="margin-top:0.65rem;padding:0.65rem 0.85rem;background:var(--hub-surface);border:1px solid var(--hub-border);border-radius:10px;">
                            <p style="margin:0;font-size:0.84rem;font-weight:600;color:var(--hub-ink);">Description</p>
                            <p style="margin:0.3rem 0 0;font-size:0.82rem;color:var(--hub-muted);white-space:pre-line;">{{ $assessment['description'] }}</p>
                        </div>
                    @endif

                    {{-- Assessment File: View / Download --}}
                    @if (!empty($assessment['file_path']))
                        <div style="margin-top:0.65rem;padding:0.65rem 0.85rem;background:var(--hub-surface);border:1px solid var(--hub-border);border-radius:10px;display:flex;align-items:center;gap:0.65rem;flex-wrap:wrap;">
                            <span style="font-size:0.82rem;font-weight:600;color:var(--hub-ink);">📎 Assessment File:</span>
                            <a href="{{ asset('storage/' . $assessment['file_path']) }}" target="_blank" class="hub-btn hub-btn-secondary" style="font-size:0.78rem;padding:0.3rem 0.75rem;">View</a>
                            <a href="{{ asset('storage/' . $assessment['file_path']) }}" download class="hub-btn hub-btn-secondary" style="font-size:0.78rem;padding:0.3rem 0.75rem;">Download</a>
                        </div>
                    @endif

                    {{-- Score & Feedback --}}
                    @if (in_array($assessment['submission_status'], ['Graded', 'Checked']))
                        <div style="margin-top:0.65rem;padding:0.75rem 0.85rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;">
                            <div style="display:flex;gap:1.5rem;align-items:center;flex-wrap:wrap;">
                                @if ($assessment['score'] !== null && $assessment['score'] !== '-')
                                    <div>
                                        <p style="margin:0;font-size:0.76rem;font-weight:600;color:#166534;text-transform:uppercase;letter-spacing:0.05em;">Score</p>
                                        <p style="margin:0.15rem 0 0;font-size:1.3rem;font-weight:800;color:#15803d;">{{ $assessment['score'] }}%</p>
                                    </div>
                                @endif
                                <div>
                                    <p style="margin:0;font-size:0.76rem;font-weight:600;color:#166534;text-transform:uppercase;letter-spacing:0.05em;">Status</p>
                                    <p style="margin:0.15rem 0 0;font-size:0.9rem;font-weight:600;color:#15803d;">{{ $assessment['submission_status'] }}</p>
                                </div>
                            </div>
                            @if (!empty($assessment['feedback']))
                                <div style="margin-top:0.6rem;border-top:1px solid #bbf7d0;padding-top:0.6rem;">
                                    <p style="margin:0;font-size:0.76rem;font-weight:600;color:#166534;text-transform:uppercase;letter-spacing:0.05em;">Instructor Feedback</p>
                                    <p style="margin:0.3rem 0 0;font-size:0.84rem;color:#14532d;white-space:pre-line;">{{ $assessment['feedback'] }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Previous Submission Info --}}
                    @if ($assessment['submission_status'] !== 'Not submitted')
                        @if (!empty($assessment['submission']['file']) || !empty($assessment['submission']['link']) || !empty($assessment['submission']['video']))
                            <div style="margin-top:0.65rem;padding:0.65rem 0.85rem;background:var(--hub-surface);border:1px solid var(--hub-border);border-radius:10px;">
                                <p style="margin:0 0 0.35rem;font-size:0.8rem;font-weight:600;color:var(--hub-ink);">Your Submitted Attachments</p>
                                @if (!empty($assessment['submission']['file']))
                                    <p style="margin:0.2rem 0;font-size:0.8rem;">📄 <a href="{{ asset('storage/' . $assessment['submission']['file']) }}" target="_blank" style="color:#0e7490;text-decoration:underline;">View uploaded file</a></p>
                                @endif
                                @if (!empty($assessment['submission']['link']))
                                    <p style="margin:0.2rem 0;font-size:0.8rem;">🔗 <a href="{{ $assessment['submission']['link'] }}" target="_blank" rel="noopener" style="color:#0e7490;text-decoration:underline;">{{ Str::limit($assessment['submission']['link'], 60) }}</a></p>
                                @endif
                                @if (!empty($assessment['submission']['video']))
                                    <p style="margin:0.2rem 0;font-size:0.8rem;">🎬 <a href="{{ $assessment['submission']['video'] }}" target="_blank" rel="noopener" style="color:#0e7490;text-decoration:underline;">{{ Str::limit($assessment['submission']['video'], 60) }}</a></p>
                                @endif
                            </div>
                        @endif
                    @endif

                    {{-- Submission Form (submit / resubmit) --}}
                    <div style="margin-top:0.75rem;border-top:1px solid var(--hub-border);padding-top:0.75rem;">
                        <p style="margin:0 0 0.5rem;font-size:0.82rem;font-weight:700;color:var(--hub-ink);">
                            {{ $assessment['submission_status'] === 'Not submitted' ? 'Submit Your Work' : 'Resubmit / Update' }}
                        </p>
                        <textarea wire:model.defer="submissionDrafts.{{ $assessment['id'] }}.text" class="hub-textarea" placeholder="Write your assessment response..."></textarea>
                        <input type="url" wire:model.defer="submissionDrafts.{{ $assessment['id'] }}.link" class="hub-input" placeholder="Paste a link (optional)" style="margin-top:0.5rem;" />
                        <input type="url" wire:model.defer="submissionDrafts.{{ $assessment['id'] }}.video" class="hub-input" placeholder="Paste a video URL — YouTube, Vimeo, etc. (optional)" style="margin-top:0.5rem;" />
                        <input type="file" wire:model="submissionDrafts.{{ $assessment['id'] }}.file" class="hub-input" style="margin-top:0.5rem;" accept=".pdf,.doc,.docx,.txt,.csv,.mp4,.avi,.mov,.wmv,.jpg,.jpeg,.png,.gif,.pptx,.xlsx" />
                        <div style="margin-top:0.75rem;display:flex;gap:0.55rem;flex-wrap:wrap;">
                            <button type="button" wire:click="submit({{ $assessment['id'] }})" class="hub-btn hub-btn-primary">
                                {{ $assessment['submission_status'] === 'Not submitted' ? 'Submit' : 'Resubmit' }}
                            </button>
                            @if ($assessment['submission_status'] !== 'Not submitted')
                                <button type="button" wire:click="removeSubmission({{ $assessment['id'] }})" class="hub-btn hub-btn-danger">Delete Submission</button>
                            @endif
                        </div>
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
