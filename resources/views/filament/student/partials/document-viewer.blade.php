{{--
    Shared in-app document viewer for student pages.

    Usage: spread the factory into the page's Alpine component and include
    this partial INSIDE that component's root element:

        <div x-data="{ ...window.documentViewer(), expanded: null, ... }">
            ...
            @include('filament.student.partials.document-viewer')
        </div>

    Provides: PDF (native iframe + blob fallback), DOCX (mammoth.js client
    side with sanitizer + loading/download fallback), ppt/pptx/doc/xls/xlsx
    (download/open card), image/video/text branches, and near-fullscreen
    mobile CSS. No Google Docs dependency; files stream same-origin through
    the auth-guarded file.view route each page already uses.
--}}

<script src="https://cdn.jsdelivr.net/npm/mammoth@1.6.0/mammoth.browser.min.js" defer onerror="window.__mammothFailed = true"></script>
<script>
    window.documentViewer = function () {
        return {
            viewerOpen: false,
            viewerUrl: '',
            viewerRawUrl: '',
            viewerObjectUrl: '',
            viewerName: '',
            viewerType: '',
            viewerDocxHtml: '',
            viewerLoading: false,
            async buildObjectUrl(url) {
                const resp = await fetch(url, { credentials: 'same-origin' });
                if (!resp.ok) throw new Error('Failed to fetch file for preview');
                const blob = await resp.blob();
                return URL.createObjectURL(blob);
            },
            sanitizeDocHtml(html) {
                // mammoth output is semantic HTML from the docx itself, but
                // the source file is user-supplied — strip anything executable.
                const doc = new DOMParser().parseFromString(html, 'text/html');
                doc.querySelectorAll('script,style,iframe,object,embed,form,link,meta,input,button,textarea,select').forEach(el => el.remove());
                doc.querySelectorAll('*').forEach(el => {
                    [...el.attributes].forEach(attr => {
                        const n = attr.name.toLowerCase();
                        if (n.startsWith('on') || n === 'style') { el.removeAttribute(attr.name); return; }
                        if (n === 'src' || n === 'href') {
                            const v = (attr.value || '').trim().toLowerCase();
                            const ok = v.startsWith('data:image/') || v.startsWith('https://') || v.startsWith('http://') || v.startsWith('#') || v.startsWith('/');
                            if (!ok) el.removeAttribute(attr.name);
                        }
                    });
                });
                return doc.body.innerHTML;
            },
            async openViewer(url, name) {
                this.viewerRawUrl = url;
                this.viewerUrl = url;
                this.viewerName = name;
                this.viewerDocxHtml = '';
                this.viewerLoading = false;
                if (this.viewerObjectUrl) {
                    URL.revokeObjectURL(this.viewerObjectUrl);
                    this.viewerObjectUrl = '';
                }
                const ext = name.split('.').pop().toLowerCase();
                if (ext === 'pdf') {
                    this.viewerType = 'pdf';
                    try {
                        this.viewerObjectUrl = await this.buildObjectUrl(url);
                        this.viewerUrl = this.viewerObjectUrl;
                    } catch (e) {
                        // Keep original URL as fallback when blob preview fails.
                        this.viewerUrl = this.viewerRawUrl;
                    }
                }
                else if (['jpg','jpeg','png','gif','webp','svg','bmp'].includes(ext)) this.viewerType = 'image';
                else if (['mp4','webm','ogg'].includes(ext)) this.viewerType = 'video';
                else if (ext === 'docx') {
                    // In-app rendering via mammoth.js (client-side docx → HTML).
                    // Falls back to the download card when the CDN script
                    // failed or conversion errors.
                    if (typeof mammoth === 'undefined' || window.__mammothFailed) {
                        this.viewerType = 'other';
                    } else {
                        this.viewerType = 'docx';
                        this.viewerLoading = true;
                        try {
                            const resp = await fetch(url, { credentials: 'same-origin' });
                            if (!resp.ok) throw new Error('Failed to fetch document');
                            const buf = await resp.arrayBuffer();
                            const result = await mammoth.convertToHtml({ arrayBuffer: buf });
                            this.viewerDocxHtml = this.sanitizeDocHtml(result.value);
                            this.viewerLoading = false;
                        } catch (e) {
                            this.viewerLoading = false;
                            this.viewerType = 'other';
                        }
                    }
                }
                else if (['ppt','pptx'].includes(ext)) this.viewerType = 'presentation';
                else if (['doc','xls','xlsx'].includes(ext)) this.viewerType = 'other';
                else if (['txt','csv'].includes(ext)) this.viewerType = 'text';
                else this.viewerType = 'other';
                this.viewerOpen = true;
            },
            closeViewer() {
                this.viewerOpen = false;
                this.viewerUrl = '';
                this.viewerRawUrl = '';
                this.viewerDocxHtml = '';
                this.viewerLoading = false;
                if (this.viewerObjectUrl) {
                    URL.revokeObjectURL(this.viewerObjectUrl);
                    this.viewerObjectUrl = '';
                }
            },
        };
    };
</script>

<style>
    .doc-reader { max-width:680px; margin:0 auto; padding:1rem 1.1rem 2.5rem; font-size:1rem; line-height:1.65; color:#1f2937; word-wrap:break-word; }
    .doc-reader h1, .doc-reader h2, .doc-reader h3, .doc-reader h4 { line-height:1.3; margin:1.1em 0 0.45em; color:#111827; }
    .doc-reader p { margin:0 0 0.75em; }
    .doc-reader img { max-width:100%; height:auto; border-radius:6px; }
    .doc-reader ul, .doc-reader ol { padding-left:1.4rem; margin:0 0 0.75em; }
    .doc-reader table { border-collapse:collapse; width:100%; font-size:0.88rem; margin:0 0 0.9em; }
    .doc-reader td, .doc-reader th { border:1px solid #e5e7eb; padding:0.35rem 0.5rem; text-align:left; }
    .doc-reader a { color:#0e7490; }

    /* Mobile-first: the viewer goes near-fullscreen on small screens. */
    @media (max-width: 768px) {
        .doc-viewer-overlay { padding:0 !important; }
        .doc-viewer-panel { width:100vw !important; max-width:100vw !important; height:100dvh; max-height:100dvh !important; border-radius:0 !important; }
        .doc-viewer-body { padding:0 !important; }
        .doc-viewer-frame { height:100% !important; border-radius:0 !important; }
        .doc-reader { font-size:1.02rem; padding:0.9rem 1rem 3rem; }
    }
</style>

{{-- File Viewer Modal --}}
<div x-show="viewerOpen" x-cloak x-transition.opacity class="doc-viewer-overlay" style="position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;padding:0.5rem;background:rgba(0,0,0,0.7);" @keydown.escape.window="closeViewer()">
    <div @click.away="closeViewer()" class="doc-viewer-panel" style="background:#fff;border-radius:12px;width:95vw;max-width:900px;max-height:92vh;margin:auto;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:0.65rem 1rem;border-bottom:1px solid #e5e7eb;gap:0.5rem;">
            <p style="margin:0;font-size:0.85rem;font-weight:600;color:#1f2937;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0;flex:1;" x-text="viewerName"></p>
            <template x-if="viewerType === 'pdf' && viewerRawUrl.includes('/file/view/material/')">
                <a :href="'/materials/' + viewerRawUrl.split('/').pop() + '/read'" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-sm transition" style="text-decoration:none;">
                    <i class="fa-solid fa-book-open"></i>
                    <span>Reader (+XP)</span>
                </a>
            </template>
            <a :href="viewerRawUrl" target="_blank" rel="noopener" title="Open in new tab"
                style="flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;width:2rem;height:2rem;border:1px solid #e5e7eb;border-radius:999px;color:#475569;text-decoration:none;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            </a>
            <a :href="viewerRawUrl" download title="Download"
                style="flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;width:2rem;height:2rem;border:1px solid #e5e7eb;border-radius:999px;color:#475569;text-decoration:none;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            </a>
            <button @click="closeViewer()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:#6b7280;line-height:1;flex-shrink:0;" title="Close">&times;</button>
        </div>
        <div class="doc-viewer-body" style="flex:1;overflow:auto;padding:0.75rem;display:flex;align-items:stretch;justify-content:center;min-height:300px;">
            <template x-if="viewerType === 'pdf'">
                <iframe :src="viewerUrl" class="doc-viewer-frame" style="width:100%;height:75vh;border:none;"></iframe>
            </template>
            <template x-if="viewerType === 'image'">
                <img :src="viewerUrl" style="max-width:100%;max-height:75vh;object-fit:contain;align-self:center;" />
            </template>
            <template x-if="viewerType === 'video'">
                <video :src="viewerUrl" controls style="max-width:100%;max-height:75vh;align-self:center;"></video>
            </template>
            <template x-if="viewerType === 'docx'">
                <div style="width:100%;height:75vh;overflow:auto;background:#fff;border-radius:8px;" class="doc-viewer-frame">
                    <div x-show="viewerLoading" style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;gap:0.6rem;color:#6b7280;">
                        <x-heroicon-o-document-text style="width:2rem;height:2rem;color:#6b7280;" />
                        <p style="margin:0;font-size:0.85rem;">Loading document…</p>
                    </div>
                    <article x-show="!viewerLoading" class="doc-reader" x-html="viewerDocxHtml"></article>
                </div>
            </template>
            <template x-if="viewerType === 'presentation'">
                <div style="text-align:center;padding:2.5rem 1.5rem;align-self:center;">
                    <div style="display:flex;justify-content:center;margin-bottom:0.5rem;">
                        <x-heroicon-o-presentation-chart-bar style="width:3rem;height:3rem;color:#0d9488;" />
                    </div>
                    <p style="font-size:1rem;font-weight:700;color:#1f2937;margin:0.6rem 0 0.2rem;">Presentation</p>
                    <p style="font-size:0.82rem;color:#6b7280;margin:0 0 1.1rem;word-break:break-word;" x-text="viewerName"></p>
                    <div style="display:flex;gap:0.5rem;justify-content:center;flex-wrap:wrap;">
                        <a :href="viewerRawUrl" download class="hub-btn hub-btn-primary" style="font-size:0.85rem;">Download</a>
                        <a :href="viewerRawUrl" target="_blank" rel="noopener" class="hub-btn" style="font-size:0.85rem;border:1px solid var(--hub-border,#e5e7eb);">Open in new tab</a>
                    </div>
                    <p style="font-size:0.74rem;color:#9ca3af;margin:1rem 0 0;">Presentations open in your device's native viewer (PowerPoint, Keynote, or Google Slides).</p>
                </div>
            </template>
            <template x-if="viewerType === 'text'">
                <iframe :src="viewerUrl" class="doc-viewer-frame" style="width:100%;height:75vh;border:none;"></iframe>
            </template>
            <template x-if="viewerType === 'other'">
                <div style="text-align:center;padding:2rem;align-self:center;">
                    <p style="font-size:1rem;color:#6b7280;margin:0 0 1rem;">Preview is not available for this file type.</p>
                    <a :href="viewerUrl" download class="hub-btn hub-btn-primary" style="font-size:0.85rem;">Download File</a>
                </div>
            </template>
        </div>
    </div>
</div>
