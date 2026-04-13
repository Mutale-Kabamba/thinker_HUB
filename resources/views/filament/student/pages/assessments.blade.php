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
            <p class="hub-eyebrow">Assessment Workspace</p>
            <h2 class="hub-title" style="font-size:1.05rem;">Assessments</h2>
            <p class="hub-copy" style="margin-top:0.2rem;">View details, download files, submit responses, and review grades.</p>
        </section>

        {{-- ======================== DESKTOP TABLE ======================== --}}
        <div class="hub-card hub-desktop-only" style="padding:0;overflow:hidden;">
            <table style="width:100%;border-collapse:collapse;font-size:0.82rem;">
                <thead>
                    <tr style="background:var(--hub-surface);border-bottom:2px solid var(--hub-border);">
                        <th style="padding:0.6rem 0.75rem;text-align:left;font-weight:700;color:var(--hub-ink);font-size:0.74rem;text-transform:uppercase;letter-spacing:0.04em;">Assessment</th>
                        <th style="padding:0.6rem 0.5rem;text-align:left;font-weight:700;color:var(--hub-ink);font-size:0.74rem;text-transform:uppercase;letter-spacing:0.04em;">Due</th>
                        <th style="padding:0.6rem 0.5rem;text-align:center;font-weight:700;color:var(--hub-ink);font-size:0.74rem;text-transform:uppercase;letter-spacing:0.04em;">Status</th>
                        <th style="padding:0.6rem 0.5rem;text-align:center;font-weight:700;color:var(--hub-ink);font-size:0.74rem;text-transform:uppercase;letter-spacing:0.04em;">Score</th>
                        <th style="padding:0.6rem 0.75rem;text-align:right;font-weight:700;color:var(--hub-ink);font-size:0.74rem;text-transform:uppercase;letter-spacing:0.04em;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assessments as $assessment)
                        <tr style="border-bottom:1px solid var(--hub-border);transition:background 0.1s;" onmouseover="this.style.background='var(--hub-surface)'" onmouseout="this.style.background=''">
                            <td style="padding:0.55rem 0.75rem;">
                                <p style="margin:0;font-weight:600;color:var(--hub-ink);">{{ $assessment['name'] }}</p>
                                <p style="margin:0.15rem 0 0;font-size:0.74rem;color:var(--hub-muted);">{{ $assessment['course'] }}</p>
                            </td>
                            <td style="padding:0.55rem 0.5rem;color:var(--hub-muted);white-space:nowrap;">{{ $assessment['due_date'] }}</td>
                            <td style="padding:0.55rem 0.5rem;text-align:center;">
                                <span class="hub-chip {{ in_array($assessment['submission_status'], ['Graded','Checked']) ? 'hub-chip-green' : ($assessment['submission_status'] === 'Submitted' ? 'hub-chip-primary' : 'hub-chip-amber') }}" style="font-size:0.7rem;">{{ $assessment['submission_status'] }}</span>
                            </td>
                            <td style="padding:0.55rem 0.5rem;text-align:center;font-weight:700;color:{{ ($assessment['score'] !== null && $assessment['score'] !== '-') ? '#15803d' : 'var(--hub-muted)' }};">{{ ($assessment['score'] !== null && $assessment['score'] !== '-') ? $assessment['score'] . '%' : '-' }}</td>
                            <td style="padding:0.55rem 0.75rem;text-align:right;">
                                <div style="display:flex;gap:0.35rem;justify-content:flex-end;flex-wrap:wrap;">
                                    @if (!empty($assessment['file_path']))
                                        <button type="button" @click="openViewer(@js(route('file.view', ['type' => 'assessment', 'id' => $assessment['id']])), @js($assessment['name'] . '.' . pathinfo($assessment['file_path'], PATHINFO_EXTENSION)))" style="background:none;border:1px solid var(--hub-border);border-radius:6px;padding:0.25rem 0.5rem;font-size:0.72rem;cursor:pointer;color:#0e7490;font-weight:600;transition:background 0.15s;" onmouseover="this.style.background='#ecfeff'" onmouseout="this.style.background='none'" title="View file">View</button>
                                        <button type="button" wire:click="downloadFile({{ $assessment['id'] }})" style="background:none;border:1px solid var(--hub-border);border-radius:6px;padding:0.25rem 0.5rem;font-size:0.72rem;cursor:pointer;color:#6d28d9;font-weight:600;transition:background 0.15s;" onmouseover="this.style.background='#f5f3ff'" onmouseout="this.style.background='none'" title="Download file">Download</button>
                                    @endif
                                    <button type="button" @click="toggle({{ $assessment['id'] }}, 'submit')" :style="expanded === {{ $assessment['id'] }} && panel === 'submit' ? 'background:#0d9488;color:#fff;border-color:#0d9488;' : ''" style="background:none;border:1px solid var(--hub-border);border-radius:6px;padding:0.25rem 0.5rem;font-size:0.72rem;cursor:pointer;color:#0d9488;font-weight:600;transition:all 0.15s;" title="Submit work">Submit</button>
                                    <button type="button" @click="toggle({{ $assessment['id'] }}, 'details')" :style="expanded === {{ $assessment['id'] }} && panel === 'details' ? 'background:#475569;color:#fff;border-color:#475569;' : ''" style="background:none;border:1px solid var(--hub-border);border-radius:6px;padding:0.25rem 0.5rem;font-size:0.72rem;cursor:pointer;color:#475569;font-weight:600;transition:all 0.15s;" title="View details">Details</button>
                                </div>
                            </td>
                        </tr>

                        {{-- Expandable Details Panel --}}
                        <tr x-show="expanded === {{ $assessment['id'] }} && panel === 'details'" x-cloak x-collapse>
                            <td colspan="5" style="padding:0;">
                                @include('filament.student.pages.partials.assess-details', ['assessment' => $assessment])
                            </td>
                        </tr>

                        {{-- Expandable Submit Panel --}}
                        <tr x-show="expanded === {{ $assessment['id'] }} && panel === 'submit'" x-cloak x-collapse>
                            <td colspan="5" style="padding:0;">
                                @include('filament.student.pages.partials.assess-submit', ['assessment' => $assessment])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding:1.5rem;text-align:center;">
                                <p class="hub-copy">No assessments available.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ======================== MOBILE CARDS ======================== --}}
        <div class="hub-mobile-only">
            @forelse ($assessments as $assessment)
                <div class="hub-mobile-card">
                    {{-- Header: Name + Status --}}
                    <div class="hub-mobile-card-row">
                        <div style="flex:1;min-width:0;">
                            <p style="margin:0;font-weight:700;color:var(--hub-ink);font-size:0.88rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $assessment['name'] }}</p>
                            <p style="margin:0.1rem 0 0;font-size:0.74rem;color:var(--hub-muted);">{{ $assessment['course'] }}</p>
                        </div>
                        <span class="hub-chip {{ in_array($assessment['submission_status'], ['Graded','Checked']) ? 'hub-chip-green' : ($assessment['submission_status'] === 'Submitted' ? 'hub-chip-primary' : 'hub-chip-amber') }}" style="font-size:0.68rem;flex-shrink:0;">{{ $assessment['submission_status'] }}</span>
                    </div>

                    {{-- Meta row --}}
                    <div class="hub-mobile-card-meta">
                        <span style="color:var(--hub-muted);"><strong>Due:</strong> {{ $assessment['due_date'] }}</span>
                        <span style="font-weight:700;color:{{ ($assessment['score'] !== null && $assessment['score'] !== '-') ? '#15803d' : 'var(--hub-muted)' }};"><strong>Score:</strong> {{ ($assessment['score'] !== null && $assessment['score'] !== '-') ? $assessment['score'] . '%' : '-' }}</span>
                    </div>

                    {{-- Action buttons --}}
                    <div class="hub-mobile-card-actions">
                        @if (!empty($assessment['file_path']))
                            <button type="button" @click="openViewer(@js(route('file.view', ['type' => 'assessment', 'id' => $assessment['id']])), @js($assessment['name'] . '.' . pathinfo($assessment['file_path'], PATHINFO_EXTENSION)))" class="hub-action-btn" style="color:#0e7490;">View</button>
                            <button type="button" wire:click="downloadFile({{ $assessment['id'] }})" class="hub-action-btn" style="color:#6d28d9;">Download</button>
                        @endif
                        <button type="button" @click="toggle({{ $assessment['id'] }}, 'submit')" class="hub-action-btn" :style="expanded === {{ $assessment['id'] }} && panel === 'submit' ? 'background:#0d9488;color:#fff;border-color:#0d9488;' : ''" style="color:#0d9488;">Submit</button>
                        <button type="button" @click="toggle({{ $assessment['id'] }}, 'details')" class="hub-action-btn" :style="expanded === {{ $assessment['id'] }} && panel === 'details' ? 'background:#475569;color:#fff;border-color:#475569;' : ''" style="color:#475569;">Details</button>
                    </div>

                    {{-- Expandable Details --}}
                    <div x-show="expanded === {{ $assessment['id'] }} && panel === 'details'" x-cloak x-collapse style="margin-top:0.5rem;">
                        @include('filament.student.pages.partials.assess-details', ['assessment' => $assessment])
                    </div>

                    {{-- Expandable Submit --}}
                    <div x-show="expanded === {{ $assessment['id'] }} && panel === 'submit'" x-cloak x-collapse style="margin-top:0.5rem;">
                        @include('filament.student.pages.partials.assess-submit', ['assessment' => $assessment])
                    </div>
                </div>
            @empty
                <div class="hub-mobile-card">
                    <p class="hub-copy" style="text-align:center;">No assessments available.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- File Viewer Modal --}}
    <div x-show="viewerOpen" x-cloak x-transition.opacity style="position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;padding:0.5rem;background:rgba(0,0,0,0.7);" @keydown.escape.window="closeViewer()">
        <div @click.away="closeViewer()" style="background:#fff;border-radius:12px;width:95vw;max-width:900px;max-height:90vh;margin:auto;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:0.6rem 0.85rem;border-bottom:1px solid #e5e7eb;gap:0.5rem;">
                <p style="margin:0;font-size:0.85rem;font-weight:600;color:#1f2937;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" x-text="viewerName"></p>
                <button @click="closeViewer()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:#6b7280;line-height:1;flex-shrink:0;" title="Close">&times;</button>
            </div>
            <div style="flex:1;overflow:auto;padding:0.75rem;display:flex;align-items:center;justify-content:center;min-height:300px;">
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
