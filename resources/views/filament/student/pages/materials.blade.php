<x-filament-panels::page>

    <div x-data="{
        viewerOpen: false,
        viewerUrl: '',
        viewerName: '',
        viewerType: '',
        expanded: null,
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
        toggle(id) {
            this.expanded = this.expanded === id ? null : id;
        }
    }">
    <div class="hub-shell">
        <section class="hub-card" style="padding:0.75rem 1rem;">
            <p class="hub-eyebrow">Resource Library</p>
            <h2 class="hub-title" style="font-size:1.05rem;">Materials</h2>
            <p class="hub-copy" style="margin-top:0.2rem;">Browse materials visible to your role, course, and level. View, download, or open resources.</p>
        </section>

        {{-- ======================== FILTERS ======================== --}}
        <section class="hub-card" style="padding:0.65rem 1rem;">
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center;">
                <select wire:model.live="filterCategory" class="hub-input" style="max-width:180px;font-size:0.8rem;padding:0.3rem 0.5rem;">
                    <option value="">All Categories</option>
                    <option value="Curriculum">Curriculum</option>
                    <option value="Study Material">Study Material</option>
                    <option value="Rules">Rules</option>
                    <option value="General Notices">General Notices</option>
                </select>
                <select wire:model.live="filterType" class="hub-input" style="max-width:160px;font-size:0.8rem;padding:0.3rem 0.5rem;">
                    <option value="">All Types</option>
                    <option value="Document">Document</option>
                    <option value="Image">Image</option>
                    <option value="Video">Video</option>
                    <option value="Link">Link</option>
                </select>
                <select wire:model.live="filterCourse" class="hub-input" style="max-width:200px;font-size:0.8rem;padding:0.3rem 0.5rem;">
                    <option value="">All Courses</option>
                    @foreach ($availableCourses as $course)
                        <option value="{{ $course['id'] }}">{{ $course['title'] }}</option>
                    @endforeach
                </select>
            </div>
        </section>

        {{-- ======================== DESKTOP TABLE ======================== --}}
        <div class="hub-card hub-desktop-only" style="padding:0;overflow:hidden;">
            <table style="width:100%;border-collapse:collapse;font-size:0.82rem;">
                <thead>
                    <tr style="background:var(--hub-surface);border-bottom:2px solid var(--hub-border);">
                        <th style="padding:0.6rem 0.75rem;text-align:left;font-weight:700;color:var(--hub-ink);font-size:0.74rem;text-transform:uppercase;letter-spacing:0.04em;">Material</th>
                        <th style="padding:0.6rem 0.5rem;text-align:left;font-weight:700;color:var(--hub-ink);font-size:0.74rem;text-transform:uppercase;letter-spacing:0.04em;">Category</th>
                        <th style="padding:0.6rem 0.5rem;text-align:center;font-weight:700;color:var(--hub-ink);font-size:0.74rem;text-transform:uppercase;letter-spacing:0.04em;">Type</th>
                        <th style="padding:0.6rem 0.5rem;text-align:center;font-weight:700;color:var(--hub-ink);font-size:0.74rem;text-transform:uppercase;letter-spacing:0.04em;">Scope</th>
                        <th style="padding:0.6rem 0.5rem;text-align:left;font-weight:700;color:var(--hub-ink);font-size:0.74rem;text-transform:uppercase;letter-spacing:0.04em;">Date</th>
                        <th style="padding:0.6rem 0.75rem;text-align:right;font-weight:700;color:var(--hub-ink);font-size:0.74rem;text-transform:uppercase;letter-spacing:0.04em;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($materials as $material)
                        {{-- Table Row --}}
                        <tr style="border-bottom:1px solid var(--hub-border);transition:background 0.1s;" onmouseover="this.style.background='var(--hub-surface)'" onmouseout="this.style.background=''">
                            <td style="padding:0.55rem 0.75rem;">
                                <p style="margin:0;font-weight:600;color:var(--hub-ink);">{{ $material['title'] }}</p>
                                <p style="margin:0.15rem 0 0;font-size:0.74rem;color:var(--hub-muted);">{{ $material['course'] }}</p>
                            </td>
                            <td style="padding:0.55rem 0.5rem;">
                                <span class="hub-chip {{ $material['category'] === 'Curriculum' ? 'hub-chip-primary' : ($material['category'] === 'Rules' ? 'hub-chip-danger' : 'hub-chip-amber') }}" style="font-size:0.7rem;">{{ $material['category'] }}</span>
                            </td>
                            <td style="padding:0.55rem 0.5rem;text-align:center;">
                                <span class="hub-chip hub-chip-blue" style="font-size:0.7rem;">{{ $material['type'] }}</span>
                            </td>
                            <td style="padding:0.55rem 0.5rem;text-align:center;">
                                <span style="font-size:0.78rem;color:var(--hub-muted);">{{ $material['scope'] }}</span>
                            </td>
                            <td style="padding:0.55rem 0.5rem;color:var(--hub-muted);white-space:nowrap;font-size:0.78rem;">{{ $material['created_at'] }}</td>
                            <td style="padding:0.55rem 0.75rem;text-align:right;">
                                <div style="display:flex;gap:0.35rem;justify-content:flex-end;flex-wrap:wrap;">
                                    @if (!empty($material['file_path']))
                                        <button type="button" @click="openViewer(@js(route('file.view', ['type' => 'material', 'id' => $material['id']])), @js($material['title'] . '.' . pathinfo($material['file_path'], PATHINFO_EXTENSION)))" style="background:none;border:1px solid var(--hub-border);border-radius:6px;padding:0.25rem 0.5rem;font-size:0.72rem;cursor:pointer;color:#0e7490;font-weight:600;transition:background 0.15s;" onmouseover="this.style.background='#ecfeff'" onmouseout="this.style.background='none'" title="View file">View</button>
                                        <button type="button" wire:click="downloadFile({{ $material['id'] }})" style="background:none;border:1px solid var(--hub-border);border-radius:6px;padding:0.25rem 0.5rem;font-size:0.72rem;cursor:pointer;color:#6d28d9;font-weight:600;transition:background 0.15s;" onmouseover="this.style.background='#f5f3ff'" onmouseout="this.style.background='none'" title="Download file">Download</button>
                                    @endif
                                    @if (!empty($material['link_url']))
                                        <a href="{{ $material['link_url'] }}" target="_blank" rel="noopener" style="background:none;border:1px solid var(--hub-border);border-radius:6px;padding:0.25rem 0.5rem;font-size:0.72rem;cursor:pointer;color:#0d9488;font-weight:600;text-decoration:none;transition:background 0.15s;" onmouseover="this.style.background='#f0fdfa'" onmouseout="this.style.background='none'" title="Open link">Open Link</a>
                                    @endif
                                    @if (!empty($material['video_url']))
                                        <a href="{{ $material['video_url'] }}" target="_blank" rel="noopener" style="background:none;border:1px solid var(--hub-border);border-radius:6px;padding:0.25rem 0.5rem;font-size:0.72rem;cursor:pointer;color:#7c3aed;font-weight:600;text-decoration:none;transition:background 0.15s;" onmouseover="this.style.background='#f5f3ff'" onmouseout="this.style.background='none'" title="Watch video">Watch</a>
                                    @endif
                                    @if (!empty($material['description']))
                                        <button type="button" @click="toggle({{ $material['id'] }})" :style="expanded === {{ $material['id'] }} ? 'background:#475569;color:#fff;border-color:#475569;' : ''" style="background:none;border:1px solid var(--hub-border);border-radius:6px;padding:0.25rem 0.5rem;font-size:0.72rem;cursor:pointer;color:#475569;font-weight:600;transition:all 0.15s;" title="View details">Details</button>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        {{-- Expandable Details Panel --}}
                        @if (!empty($material['description']))
                        <tr x-show="expanded === {{ $material['id'] }}" x-cloak x-collapse>
                            <td colspan="6" style="padding:0;">
                                <div style="padding:0.85rem 1rem;background:var(--hub-surface);border-bottom:2px solid var(--hub-border);">
                                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
                                        <h4 style="margin:0;font-size:0.88rem;font-weight:700;color:var(--hub-ink);">{{ $material['title'] }}</h4>
                                        <button @click="expanded = null;" style="background:none;border:none;cursor:pointer;color:var(--hub-muted);font-size:1.1rem;line-height:1;" title="Close">&times;</button>
                                    </div>
                                    <div style="padding:0.55rem 0.75rem;background:#fff;border:1px solid var(--hub-border);border-radius:8px;">
                                        <p style="margin:0;font-size:0.76rem;font-weight:600;color:var(--hub-ink);text-transform:uppercase;letter-spacing:0.03em;">Description</p>
                                        <p style="margin:0.25rem 0 0;font-size:0.82rem;color:var(--hub-muted);white-space:pre-line;">{{ $material['description'] }}</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" style="padding:1.5rem;text-align:center;">
                                <p class="hub-copy">No materials available.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ======================== MOBILE CARDS ======================== --}}
        <div class="hub-mobile-only">
            @forelse ($materials as $material)
                <div class="hub-card" style="padding:0.75rem 1rem;margin-bottom:0.65rem;">
                    {{-- Header: title + type chip --}}
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:0.5rem;">
                        <div style="min-width:0;flex:1;">
                            <p style="margin:0;font-weight:600;color:var(--hub-ink);font-size:0.88rem;">{{ $material['title'] }}</p>
                            <p style="margin:0.15rem 0 0;font-size:0.74rem;color:var(--hub-muted);">{{ $material['course'] }}</p>
                        </div>
                        <span class="hub-chip hub-chip-blue" style="font-size:0.7rem;flex-shrink:0;">{{ $material['type'] }}</span>
                    </div>

                    {{-- Meta: category + scope + date --}}
                    <div style="display:flex;gap:0.5rem;margin-top:0.45rem;font-size:0.74rem;flex-wrap:wrap;align-items:center;">
                        <span class="hub-chip {{ $material['category'] === 'Curriculum' ? 'hub-chip-primary' : ($material['category'] === 'Rules' ? 'hub-chip-danger' : 'hub-chip-amber') }}" style="font-size:0.68rem;">{{ $material['category'] }}</span>
                        <span style="color:var(--hub-muted);">{{ $material['scope'] }}</span>
                        <span style="color:var(--hub-muted);">{{ $material['created_at'] }}</span>
                    </div>

                    {{-- Action buttons --}}
                    <div style="display:flex;gap:0.35rem;margin-top:0.55rem;flex-wrap:wrap;">
                        @if (!empty($material['file_path']))
                            <button type="button" @click="openViewer(@js(route('file.view', ['type' => 'material', 'id' => $material['id']])), @js($material['title'] . '.' . pathinfo($material['file_path'], PATHINFO_EXTENSION)))" style="background:none;border:1px solid var(--hub-border);border-radius:6px;padding:0.3rem 0.65rem;font-size:0.75rem;cursor:pointer;color:#0e7490;font-weight:600;">View</button>
                            <button type="button" wire:click="downloadFile({{ $material['id'] }})" style="background:none;border:1px solid var(--hub-border);border-radius:6px;padding:0.3rem 0.65rem;font-size:0.75rem;cursor:pointer;color:#6d28d9;font-weight:600;">Download</button>
                        @endif
                        @if (!empty($material['link_url']))
                            <a href="{{ $material['link_url'] }}" target="_blank" rel="noopener" style="background:none;border:1px solid var(--hub-border);border-radius:6px;padding:0.3rem 0.65rem;font-size:0.75rem;cursor:pointer;color:#0d9488;font-weight:600;text-decoration:none;">Open Link</a>
                        @endif
                        @if (!empty($material['video_url']))
                            <a href="{{ $material['video_url'] }}" target="_blank" rel="noopener" style="background:none;border:1px solid var(--hub-border);border-radius:6px;padding:0.3rem 0.65rem;font-size:0.75rem;cursor:pointer;color:#7c3aed;font-weight:600;text-decoration:none;">Watch</a>
                        @endif
                        @if (!empty($material['description']))
                            <button type="button" @click="toggle({{ $material['id'] }})" :style="expanded === {{ $material['id'] }} ? 'background:#475569;color:#fff;border-color:#475569;' : ''" style="background:none;border:1px solid var(--hub-border);border-radius:6px;padding:0.3rem 0.65rem;font-size:0.75rem;cursor:pointer;color:#475569;font-weight:600;">Details</button>
                        @endif
                    </div>

                    {{-- Expandable Details --}}
                    @if (!empty($material['description']))
                    <div x-show="expanded === {{ $material['id'] }}" x-cloak x-collapse>
                        <div style="margin-top:0.6rem;padding:0.75rem;background:var(--hub-surface);border-radius:8px;border:1px solid var(--hub-border);">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
                                <h4 style="margin:0;font-size:0.85rem;font-weight:700;color:var(--hub-ink);">Details</h4>
                                <button @click="expanded = null;" style="background:none;border:none;cursor:pointer;color:var(--hub-muted);font-size:1.1rem;line-height:1;" title="Close">&times;</button>
                            </div>
                            <div style="padding:0.5rem 0.65rem;background:#fff;border:1px solid var(--hub-border);border-radius:8px;">
                                <p style="margin:0;font-size:0.74rem;font-weight:600;color:var(--hub-ink);text-transform:uppercase;">Description</p>
                                <p style="margin:0.2rem 0 0;font-size:0.8rem;color:var(--hub-muted);white-space:pre-line;">{{ $material['description'] }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            @empty
                <div class="hub-card" style="padding:1.5rem;text-align:center;">
                    <p class="hub-copy">No materials available.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- File Viewer Modal --}}
    <div x-show="viewerOpen" x-cloak x-transition.opacity style="position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;padding:0.5rem;background:rgba(0,0,0,0.7);" @keydown.escape.window="closeViewer()">
        <div @click.away="closeViewer()" style="background:#fff;border-radius:12px;width:95vw;max-width:900px;max-height:92vh;margin:auto;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:0.65rem 1rem;border-bottom:1px solid #e5e7eb;gap:0.5rem;">
                <p style="margin:0;font-size:0.85rem;font-weight:600;color:#1f2937;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0;" x-text="viewerName"></p>
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
