<x-filament-panels::page>
    <div x-data="{
        viewerOpen: false,
        viewerUrl: '',
        viewerName: '',
        viewerType: '',
        openViewer(url, name) {
            this.viewerUrl = url;
            this.viewerName = name;
            const ext = name.split('.').pop().toLowerCase();
            if (ext === 'pdf') this.viewerType = 'pdf';
            else if (['jpg','jpeg','png','gif','webp','svg','bmp'].includes(ext)) this.viewerType = 'image';
            else if (['mp4','webm','ogg'].includes(ext)) this.viewerType = 'video';
            else this.viewerType = 'other';
            this.viewerOpen = true;
        },
        closeViewer() {
            this.viewerOpen = false;
            this.viewerUrl = '';
        }
    }">
    <div class="hub-shell">
        <section class="hub-card">
            <p class="hub-eyebrow">Assignment Workspace</p>
            <h2 class="hub-title">Assignments</h2>
            <p class="hub-copy">View assignment details, download files, submit your work, and check grades &amp; feedback.</p>
        </section>

        <div class="hub-stack">
            @forelse ($assignments as $assignment)
                <article class="hub-card">
                    {{-- Header --}}
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:0.75rem;flex-wrap:wrap;">
                        <div>
                            <p class="hub-eyebrow">{{ $assignment['course'] }} | {{ $assignment['scope'] }}</p>
                            <h3 class="hub-title" style="margin-top:0.25rem;">{{ $assignment['name'] }}</h3>
                            <p class="hub-copy">Due: {{ $assignment['due'] }}</p>
                        </div>
                        <div style="text-align:right;">
                            <span class="hub-chip
                                @if(in_array($assignment['status'], ['Graded','Checked'])) hub-chip-green
                                @elseif($assignment['status'] === 'Submitted') hub-chip-blue
                                @else hub-chip-amber @endif
                            ">{{ $assignment['status'] }}</span>
                            <p style="margin-top:0.35rem;color:var(--hub-muted);font-size:0.76rem;">{{ $assignment['submitted_at'] }}</p>
                        </div>
                    </div>

                    {{-- Description --}}
                    @if (!empty($assignment['description']))
                        <div style="margin-top:0.65rem;padding:0.65rem 0.85rem;background:var(--hub-surface);border:1px solid var(--hub-border);border-radius:10px;">
                            <p style="margin:0;font-size:0.84rem;font-weight:600;color:var(--hub-ink);">Description</p>
                            <p style="margin:0.3rem 0 0;font-size:0.82rem;color:var(--hub-muted);white-space:pre-line;">{{ $assignment['description'] }}</p>
                        </div>
                    @endif

                    {{-- Assignment File: View / Download --}}
                    @if (!empty($assignment['file_path']))
                        <div style="margin-top:0.65rem;padding:0.65rem 0.85rem;background:var(--hub-surface);border:1px solid var(--hub-border);border-radius:10px;display:flex;align-items:center;gap:0.65rem;flex-wrap:wrap;">
                            <span style="font-size:0.82rem;font-weight:600;color:var(--hub-ink);">📎 Assignment File:</span>
                            <button type="button" @click="openViewer(@js('/storage/' . $assignment['file_path']), @js($assignment['name'] . '.' . pathinfo($assignment['file_path'], PATHINFO_EXTENSION)))" class="hub-btn hub-btn-secondary" style="font-size:0.78rem;padding:0.3rem 0.75rem;">View</button>
                            <button type="button" wire:click="downloadFile({{ $assignment['id'] }})" class="hub-btn hub-btn-secondary" style="font-size:0.78rem;padding:0.3rem 0.75rem;">Download</button>
                        </div>
                    @endif

                    {{-- Grade & Feedback --}}
                    @if (in_array($assignment['status'], ['Graded', 'Checked']))
                        <div style="margin-top:0.65rem;padding:0.75rem 0.85rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;">
                            <div style="display:flex;gap:1.5rem;align-items:center;flex-wrap:wrap;">
                                @if ($assignment['grade'] !== null)
                                    <div>
                                        <p style="margin:0;font-size:0.76rem;font-weight:600;color:#166534;text-transform:uppercase;letter-spacing:0.05em;">Grade</p>
                                        <p style="margin:0.15rem 0 0;font-size:1.3rem;font-weight:800;color:#15803d;">{{ $assignment['grade'] }}%</p>
                                    </div>
                                @endif
                                <div>
                                    <p style="margin:0;font-size:0.76rem;font-weight:600;color:#166534;text-transform:uppercase;letter-spacing:0.05em;">Status</p>
                                    <p style="margin:0.15rem 0 0;font-size:0.9rem;font-weight:600;color:#15803d;">{{ $assignment['status'] }}</p>
                                </div>
                            </div>
                            @if (!empty($assignment['feedback']))
                                <div style="margin-top:0.6rem;border-top:1px solid #bbf7d0;padding-top:0.6rem;">
                                    <p style="margin:0;font-size:0.76rem;font-weight:600;color:#166534;text-transform:uppercase;letter-spacing:0.05em;">Instructor Feedback</p>
                                    <p style="margin:0.3rem 0 0;font-size:0.84rem;color:#14532d;white-space:pre-line;">{{ $assignment['feedback'] }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Previous Submission Info --}}
                    @if ($assignment['status'] === 'Submitted' || in_array($assignment['status'], ['Graded', 'Checked']))
                        @if (!empty($assignment['submission']['file']) || !empty($assignment['submission']['link']) || !empty($assignment['submission']['video']))
                            <div style="margin-top:0.65rem;padding:0.65rem 0.85rem;background:var(--hub-surface);border:1px solid var(--hub-border);border-radius:10px;">
                                <p style="margin:0 0 0.35rem;font-size:0.8rem;font-weight:600;color:var(--hub-ink);">Your Submitted Attachments</p>
                                @if (!empty($assignment['submission']['file']))
                                    <p style="margin:0.2rem 0;font-size:0.8rem;">📄 <a href="#" @click.prevent="openViewer(@js('/storage/' . $assignment['submission']['file']), @js('Submission.' . pathinfo($assignment['submission']['file'], PATHINFO_EXTENSION)))" style="color:#0e7490;text-decoration:underline;cursor:pointer;">View uploaded file</a></p>
                                @endif
                                @if (!empty($assignment['submission']['link']))
                                    <p style="margin:0.2rem 0;font-size:0.8rem;">🔗 <a href="{{ $assignment['submission']['link'] }}" target="_blank" rel="noopener" style="color:#0e7490;text-decoration:underline;">{{ Str::limit($assignment['submission']['link'], 60) }}</a></p>
                                @endif
                                @if (!empty($assignment['submission']['video']))
                                    <p style="margin:0.2rem 0;font-size:0.8rem;">🎬 <a href="{{ $assignment['submission']['video'] }}" target="_blank" rel="noopener" style="color:#0e7490;text-decoration:underline;">{{ Str::limit($assignment['submission']['video'], 60) }}</a></p>
                                @endif
                            </div>
                        @endif
                    @endif

                    {{-- Submission Form (submit / resubmit) --}}
                    <div style="margin-top:0.75rem;border-top:1px solid var(--hub-border);padding-top:0.75rem;">
                        <p style="margin:0 0 0.5rem;font-size:0.82rem;font-weight:700;color:var(--hub-ink);">
                            {{ $assignment['status'] === 'Not submitted' ? 'Submit Your Work' : 'Resubmit / Update' }}
                        </p>
                        <textarea wire:model.defer="submissionDrafts.{{ $assignment['id'] }}.text" class="hub-textarea" placeholder="Write your response here..."></textarea>
                        <input type="url" wire:model.defer="submissionDrafts.{{ $assignment['id'] }}.link" class="hub-input" placeholder="Paste a link (optional)" style="margin-top:0.5rem;" />
                        <input type="url" wire:model.defer="submissionDrafts.{{ $assignment['id'] }}.video" class="hub-input" placeholder="Paste a video URL — YouTube, Vimeo, etc. (optional)" style="margin-top:0.5rem;" />
                        <input type="file" wire:model="submissionDrafts.{{ $assignment['id'] }}.file" class="hub-input" style="margin-top:0.5rem;" accept=".pdf,.doc,.docx,.txt,.csv,.mp4,.avi,.mov,.wmv,.jpg,.jpeg,.png,.gif,.pptx,.xlsx" />
                        <div style="margin-top:0.75rem;display:flex;gap:0.55rem;flex-wrap:wrap;">
                            <button type="button" wire:click="submit({{ $assignment['id'] }})" class="hub-btn hub-btn-primary">
                                {{ $assignment['status'] === 'Not submitted' ? 'Submit' : 'Resubmit' }}
                            </button>
                            @if ($assignment['status'] !== 'Not submitted')
                                <button type="button" wire:click="removeSubmission({{ $assignment['id'] }})" class="hub-btn hub-btn-danger">Delete Submission</button>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <section class="hub-card">
                    <p class="hub-copy">No assignments available.</p>
                </section>
            @endforelse
        </div>
    </div>

    {{-- File Viewer Modal --}}
    <div x-show="viewerOpen" x-cloak x-transition.opacity style="position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.7);" @keydown.escape.window="closeViewer()">
        <div @click.away="closeViewer()" style="background:#fff;border-radius:12px;width:90vw;max-width:900px;max-height:90vh;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem 1rem;border-bottom:1px solid #e5e7eb;">
                <p style="margin:0;font-size:0.9rem;font-weight:600;color:#1f2937;" x-text="viewerName"></p>
                <button @click="closeViewer()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:#6b7280;line-height:1;" title="Close">&times;</button>
            </div>
            <div style="flex:1;overflow:auto;padding:1rem;display:flex;align-items:center;justify-content:center;min-height:400px;">
                <template x-if="viewerType === 'pdf'">
                    <iframe :src="viewerUrl" style="width:100%;height:75vh;border:none;"></iframe>
                </template>
                <template x-if="viewerType === 'image'">
                    <img :src="viewerUrl" style="max-width:100%;max-height:75vh;object-fit:contain;" />
                </template>
                <template x-if="viewerType === 'video'">
                    <video :src="viewerUrl" controls style="max-width:100%;max-height:75vh;"></video>
                </template>
                <template x-if="viewerType === 'other'">
                    <div style="text-align:center;padding:2rem;">
                        <p style="font-size:1rem;color:#6b7280;margin:0 0 1rem;">Preview is not available for this file type.</p>
                        <a :href="viewerUrl" download class="hub-btn hub-btn-primary" style="font-size:0.85rem;">Download File</a>
                    </div>
                </template>
            </div>
        </div>
    </div>
    </div>
</x-filament-panels::page>
