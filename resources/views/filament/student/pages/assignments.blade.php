<x-filament-panels::page>
    <div x-data="{
        viewerOpen: false,
        viewerUrl: '',
        viewerName: '',
        viewerType: '',
        expanded: null,
        panel: null,
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
        closeViewer() { this.viewerOpen = false; this.viewerUrl = ''; },
        toggle(id, p) {
            if (this.expanded === id && this.panel === p) { this.expanded = null; this.panel = null; }
            else { this.expanded = id; this.panel = p; }
        }
    }">
    <div class="hub-shell">
        <section class="hub-card" style="padding:0.75rem 1rem;">
            <p class="hub-eyebrow">Assignment Workspace</p>
            <h2 class="hub-title" style="font-size:1.05rem;">Assignments</h2>
            <p class="hub-copy" style="margin-top:0.2rem;">View details, download files, submit work, and check grades.</p>
        </section>

        {{-- Compact Table --}}
        <div class="hub-card" style="padding:0;overflow:hidden;">
            <table style="width:100%;border-collapse:collapse;font-size:0.82rem;">
                <thead>
                    <tr style="background:var(--hub-surface);border-bottom:2px solid var(--hub-border);">
                        <th style="padding:0.6rem 0.75rem;text-align:left;font-weight:700;color:var(--hub-ink);font-size:0.74rem;text-transform:uppercase;letter-spacing:0.04em;">Assignment</th>
                        <th style="padding:0.6rem 0.5rem;text-align:left;font-weight:700;color:var(--hub-ink);font-size:0.74rem;text-transform:uppercase;letter-spacing:0.04em;">Due</th>
                        <th style="padding:0.6rem 0.5rem;text-align:center;font-weight:700;color:var(--hub-ink);font-size:0.74rem;text-transform:uppercase;letter-spacing:0.04em;">Status</th>
                        <th style="padding:0.6rem 0.5rem;text-align:center;font-weight:700;color:var(--hub-ink);font-size:0.74rem;text-transform:uppercase;letter-spacing:0.04em;">Grade</th>
                        <th style="padding:0.6rem 0.75rem;text-align:right;font-weight:700;color:var(--hub-ink);font-size:0.74rem;text-transform:uppercase;letter-spacing:0.04em;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assignments as $assignment)
                        {{-- Table Row --}}
                        <tr style="border-bottom:1px solid var(--hub-border);transition:background 0.1s;" onmouseover="this.style.background='var(--hub-surface)'" onmouseout="this.style.background=''">
                            <td style="padding:0.55rem 0.75rem;">
                                <p style="margin:0;font-weight:600;color:var(--hub-ink);">{{ $assignment['name'] }}</p>
                                <p style="margin:0.15rem 0 0;font-size:0.74rem;color:var(--hub-muted);">{{ $assignment['course'] }} &middot; {{ $assignment['scope'] }}</p>
                            </td>
                            <td style="padding:0.55rem 0.5rem;color:var(--hub-muted);white-space:nowrap;">{{ $assignment['due'] }}</td>
                            <td style="padding:0.55rem 0.5rem;text-align:center;">
                                <span class="hub-chip {{ in_array($assignment['status'], ['Graded','Checked']) ? 'hub-chip-green' : ($assignment['status'] === 'Submitted' ? 'hub-chip-blue' : 'hub-chip-amber') }}" style="font-size:0.7rem;">{{ $assignment['status'] }}</span>
                            </td>
                            <td style="padding:0.55rem 0.5rem;text-align:center;font-weight:700;color:{{ $assignment['grade'] !== null ? '#15803d' : 'var(--hub-muted)' }};">{{ $assignment['grade'] !== null ? $assignment['grade'] . '%' : '-' }}</td>
                            <td style="padding:0.55rem 0.75rem;text-align:right;">
                                <div style="display:flex;gap:0.35rem;justify-content:flex-end;flex-wrap:wrap;">
                                    @if (!empty($assignment['file_path']))
                                        <button type="button" @click="openViewer(@js('/storage/' . $assignment['file_path']), @js($assignment['name'] . '.' . pathinfo($assignment['file_path'], PATHINFO_EXTENSION)))" style="background:none;border:1px solid var(--hub-border);border-radius:6px;padding:0.25rem 0.5rem;font-size:0.72rem;cursor:pointer;color:#0e7490;font-weight:600;transition:background 0.15s;" onmouseover="this.style.background='#ecfeff'" onmouseout="this.style.background='none'" title="View file">View</button>
                                        <button type="button" wire:click="downloadFile({{ $assignment['id'] }})" style="background:none;border:1px solid var(--hub-border);border-radius:6px;padding:0.25rem 0.5rem;font-size:0.72rem;cursor:pointer;color:#6d28d9;font-weight:600;transition:background 0.15s;" onmouseover="this.style.background='#f5f3ff'" onmouseout="this.style.background='none'" title="Download file">Download</button>
                                    @endif
                                    <button type="button" @click="toggle({{ $assignment['id'] }}, 'submit')" :style="expanded === {{ $assignment['id'] }} && panel === 'submit' ? 'background:#0d9488;color:#fff;border-color:#0d9488;' : ''" style="background:none;border:1px solid var(--hub-border);border-radius:6px;padding:0.25rem 0.5rem;font-size:0.72rem;cursor:pointer;color:#0d9488;font-weight:600;transition:all 0.15s;" title="Submit work">Submit</button>
                                    <button type="button" @click="toggle({{ $assignment['id'] }}, 'details')" :style="expanded === {{ $assignment['id'] }} && panel === 'details' ? 'background:#475569;color:#fff;border-color:#475569;' : ''" style="background:none;border:1px solid var(--hub-border);border-radius:6px;padding:0.25rem 0.5rem;font-size:0.72rem;cursor:pointer;color:#475569;font-weight:600;transition:all 0.15s;" title="View details">Details</button>
                                </div>
                            </td>
                        </tr>

                        {{-- Expandable Details Panel --}}
                        <tr x-show="expanded === {{ $assignment['id'] }} && panel === 'details'" x-cloak x-collapse>
                            <td colspan="5" style="padding:0;">
                                <div style="padding:0.85rem 1rem;background:var(--hub-surface);border-bottom:2px solid var(--hub-border);">
                                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
                                        <h4 style="margin:0;font-size:0.88rem;font-weight:700;color:var(--hub-ink);">{{ $assignment['name'] }}</h4>
                                        <button @click="expanded = null; panel = null;" style="background:none;border:none;cursor:pointer;color:var(--hub-muted);font-size:1.1rem;line-height:1;" title="Close">&times;</button>
                                    </div>

                                    @if (!empty($assignment['description']))
                                        <div style="padding:0.55rem 0.75rem;background:#fff;border:1px solid var(--hub-border);border-radius:8px;margin-bottom:0.5rem;">
                                            <p style="margin:0;font-size:0.76rem;font-weight:600;color:var(--hub-ink);text-transform:uppercase;letter-spacing:0.03em;">Description</p>
                                            <p style="margin:0.25rem 0 0;font-size:0.82rem;color:var(--hub-muted);white-space:pre-line;">{{ $assignment['description'] }}</p>
                                        </div>
                                    @endif

                                    {{-- Grade & Feedback --}}
                                    @if (in_array($assignment['status'], ['Graded', 'Checked']))
                                        <div style="padding:0.55rem 0.75rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;margin-bottom:0.5rem;">
                                            <div style="display:flex;gap:1.2rem;align-items:center;flex-wrap:wrap;">
                                                @if ($assignment['grade'] !== null)
                                                    <div>
                                                        <p style="margin:0;font-size:0.7rem;font-weight:600;color:#166534;text-transform:uppercase;">Grade</p>
                                                        <p style="margin:0;font-size:1.15rem;font-weight:800;color:#15803d;">{{ $assignment['grade'] }}%</p>
                                                    </div>
                                                @endif
                                                <div>
                                                    <p style="margin:0;font-size:0.7rem;font-weight:600;color:#166534;text-transform:uppercase;">Status</p>
                                                    <p style="margin:0;font-size:0.84rem;font-weight:600;color:#15803d;">{{ $assignment['status'] }}</p>
                                                </div>
                                            </div>
                                            @if (!empty($assignment['feedback']))
                                                <div style="margin-top:0.45rem;border-top:1px solid #bbf7d0;padding-top:0.45rem;">
                                                    <p style="margin:0;font-size:0.7rem;font-weight:600;color:#166534;text-transform:uppercase;">Feedback</p>
                                                    <p style="margin:0.2rem 0 0;font-size:0.82rem;color:#14532d;white-space:pre-line;">{{ $assignment['feedback'] }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Submitted Attachments --}}
                                    @if ($assignment['status'] !== 'Not submitted')
                                        @if (!empty($assignment['submission']['file']) || !empty($assignment['submission']['link']) || !empty($assignment['submission']['video']))
                                            <div style="padding:0.55rem 0.75rem;background:#fff;border:1px solid var(--hub-border);border-radius:8px;margin-bottom:0.5rem;">
                                                <p style="margin:0 0 0.3rem;font-size:0.76rem;font-weight:600;color:var(--hub-ink);text-transform:uppercase;letter-spacing:0.03em;">Your Submission</p>
                                                @if (!empty($assignment['submission']['file']))
                                                    <p style="margin:0.15rem 0;font-size:0.8rem;">📄 <a href="#" @click.prevent="openViewer(@js('/storage/' . $assignment['submission']['file']), @js('Submission.' . pathinfo($assignment['submission']['file'], PATHINFO_EXTENSION)))" style="color:#0e7490;text-decoration:underline;cursor:pointer;">View uploaded file</a></p>
                                                @endif
                                                @if (!empty($assignment['submission']['link']))
                                                    <p style="margin:0.15rem 0;font-size:0.8rem;">🔗 <a href="{{ $assignment['submission']['link'] }}" target="_blank" rel="noopener" style="color:#0e7490;text-decoration:underline;">{{ Str::limit($assignment['submission']['link'], 60) }}</a></p>
                                                @endif
                                                @if (!empty($assignment['submission']['video']))
                                                    <p style="margin:0.15rem 0;font-size:0.8rem;">🎬 <a href="{{ $assignment['submission']['video'] }}" target="_blank" rel="noopener" style="color:#0e7490;text-decoration:underline;">{{ Str::limit($assignment['submission']['video'], 60) }}</a></p>
                                                @endif
                                            </div>
                                        @endif
                                    @endif

                                    <p style="margin:0;font-size:0.74rem;color:var(--hub-muted);">Submitted: {{ $assignment['submitted_at'] ?: 'Not yet' }}</p>
                                </div>
                            </td>
                        </tr>

                        {{-- Expandable Submit Panel --}}
                        <tr x-show="expanded === {{ $assignment['id'] }} && panel === 'submit'" x-cloak x-collapse>
                            <td colspan="5" style="padding:0;">
                                <div style="padding:0.85rem 1rem;background:var(--hub-surface);border-bottom:2px solid var(--hub-border);">
                                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
                                        <h4 style="margin:0;font-size:0.88rem;font-weight:700;color:var(--hub-ink);">
                                            {{ $assignment['status'] === 'Not submitted' ? 'Submit Work' : 'Resubmit / Update' }} — {{ $assignment['name'] }}
                                        </h4>
                                        <button @click="expanded = null; panel = null;" style="background:none;border:none;cursor:pointer;color:var(--hub-muted);font-size:1.1rem;line-height:1;" title="Close">&times;</button>
                                    </div>
                                    <div style="display:flex;flex-direction:column;gap:0.45rem;">
                                        <textarea wire:model.defer="submissionDrafts.{{ $assignment['id'] }}.text" class="hub-textarea" placeholder="Write your response here..." style="min-height:80px;font-size:0.82rem;"></textarea>
                                        <input type="url" wire:model.defer="submissionDrafts.{{ $assignment['id'] }}.link" class="hub-input" placeholder="Paste a link (optional)" style="font-size:0.82rem;" />
                                        <input type="url" wire:model.defer="submissionDrafts.{{ $assignment['id'] }}.video" class="hub-input" placeholder="Paste a video URL — YouTube, Vimeo, etc. (optional)" style="font-size:0.82rem;" />
                                        <input type="file" wire:model="submissionDrafts.{{ $assignment['id'] }}.file" class="hub-input" style="font-size:0.82rem;" accept=".pdf,.doc,.docx,.txt,.csv,.mp4,.avi,.mov,.wmv,.jpg,.jpeg,.png,.gif,.pptx,.xlsx" />
                                    </div>
                                    <div style="margin-top:0.6rem;display:flex;gap:0.45rem;flex-wrap:wrap;">
                                        <button type="button" wire:click="submit({{ $assignment['id'] }})" class="hub-btn hub-btn-primary" style="font-size:0.8rem;padding:0.35rem 1rem;">
                                            {{ $assignment['status'] === 'Not submitted' ? 'Submit' : 'Resubmit' }}
                                        </button>
                                        @if ($assignment['status'] !== 'Not submitted')
                                            <button type="button" wire:click="removeSubmission({{ $assignment['id'] }})" class="hub-btn hub-btn-danger" style="font-size:0.8rem;padding:0.35rem 1rem;">Delete Submission</button>
                                        @endif
                                        <button type="button" @click="expanded = null; panel = null;" class="hub-btn hub-btn-secondary" style="font-size:0.8rem;padding:0.35rem 1rem;">Cancel</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding:1.5rem;text-align:center;">
                                <p class="hub-copy">No assignments available.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- File Viewer Modal --}}
    <div x-show="viewerOpen" x-cloak x-transition.opacity style="position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;padding:1rem;background:rgba(0,0,0,0.7);" @keydown.escape.window="closeViewer()">
        <div @click.away="closeViewer()" style="background:#fff;border-radius:12px;width:90vw;max-width:900px;max-height:90vh;margin:auto;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
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
