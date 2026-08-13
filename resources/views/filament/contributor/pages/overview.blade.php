<x-filament-panels::page>
    <div class="hub-shell">

        {{-- Welcome Header Banner --}}
        <section class="hub-card hub-card-dark" style="padding:1.25rem; background:#0a2d27; border-color:#0a2d27;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.75rem;">
                <div>
                    <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.4rem;">
                        <span class="hub-chip" style="background:rgba(255,255,255,0.15); color:#fff; border-color:rgba(255,255,255,0.3); font-size:0.68rem; text-transform:uppercase;">
                            {{ auth()->user()->role ? ucfirst(auth()->user()->role) : 'Contributor' }}
                        </span>
                        @if (! $isActive)
                            <span class="hub-chip hub-chip-amber">Pending Admin Approval</span>
                        @else
                            <span class="hub-chip hub-chip-green">Active Contributor</span>
                        @endif
                    </div>
                    <h2 class="hub-title" style="color:#ffffff; font-size:1.4rem; margin-bottom:0.25rem;">
                        {{ $roleTitle }}
                    </h2>
                    <p class="hub-copy" style="color:#d1d5db; margin:0; font-size:0.85rem;">
                        {{ $roleSubtitle }}
                    </p>
                </div>

                <div>
                    <a
                        href="{{ route('filament.contributor.resources.hub-posts.create') }}"
                        class="hub-btn hub-btn-primary"
                        style="padding:0.55rem 1rem; font-size:0.8rem; background:#0d9488; color:#ffffff; border-color:#0d9488; border-radius:999px; text-decoration:none;"
                    >
                        <i class="fa-solid fa-circle-plus"></i> Create New {{ ucfirst(str_replace('_', ' ', $allowedPostType)) }}
                    </a>
                </div>
            </div>
        </section>

        {{-- Pending Approval Alert --}}
        @if (! $isActive)
            <section class="hub-card" style="border-color:#fcd34d; background:#fffbeb; padding:0.85rem;">
                <div style="display:flex; align-items:flex-start; gap:0.6rem;">
                    <i class="fa-solid fa-triangle-exclamation" style="color:#d97706; font-size:1.1rem; margin-top:0.1rem;"></i>
                    <div>
                        <h4 style="margin:0; font-size:0.85rem; font-weight:700; color:#92400e;">Account Verification Notice</h4>
                        <p style="margin:0.2rem 0 0 0; font-size:0.78rem; color:#b45309;">
                            Your contributor account is currently pending Administrator review. You can create and draft your posts, and they will be visible to the public once approved.
                        </p>
                    </div>
                </div>
            </section>
        @endif

        {{-- Stats Grid --}}
        <div class="hub-grid hub-grid-4">
            <section class="hub-card">
                <p class="hub-eyebrow">Total Created</p>
                <p class="hub-metric">{{ number_format($totalPosts) }}</p>
                <p class="hub-copy">Submitted items</p>
            </section>
            <section class="hub-card">
                <p class="hub-eyebrow">Published</p>
                <p class="hub-metric" style="color:#059669;">{{ number_format($publishedPosts) }}</p>
                <p class="hub-copy">Live on Knowledge Hub</p>
            </section>
            <section class="hub-card">
                <p class="hub-eyebrow">Pending Review</p>
                <p class="hub-metric" style="color:#d97706;">{{ number_format($pendingPosts) }}</p>
                <p class="hub-copy">Drafts or in queue</p>
            </section>
            <section class="hub-card">
                <p class="hub-eyebrow">Total Views</p>
                <p class="hub-metric" style="color:#0284c7;">{{ number_format($totalViews) }}</p>
                <p class="hub-copy">Community reads</p>
            </section>
        </div>

        {{-- Recent Contributions Table --}}
        <section class="hub-card" style="padding:1rem;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem; flex-wrap:wrap; gap:0.4rem;">
                <div>
                    <h3 class="hub-title">My Contributions</h3>
                    <p class="hub-copy" style="margin-top:0.1rem;">Recent posts and resources submitted by you.</p>
                </div>
                <a href="{{ route('filament.contributor.resources.hub-posts.index') }}" class="hub-btn hub-btn-muted" style="font-size:0.72rem; padding:0.3rem 0.6rem; text-decoration:none;">View All &rarr;</a>
            </div>

            @if (count($recentPosts) > 0)
                <div style="overflow-x:auto;">
                    <table class="hub-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Views</th>
                                <th>Date</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentPosts as $post)
                                <tr>
                                    <td style="font-weight:700;">
                                        <a href="{{ route('hub.show', $post['slug']) }}" target="_blank" style="color:var(--hub-ink); text-decoration:none;">
                                            {{ $post['title'] }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="hub-chip hub-chip-gray">
                                            {{ ucfirst(str_replace('_', ' ', $post['type'])) }}
                                        </span>
                                    </td>
                                    <td style="color:var(--hub-muted);">
                                        {{ $post['category'] }}
                                    </td>
                                    <td>
                                        @if ($post['is_published'])
                                            <span class="hub-chip hub-chip-green">Published</span>
                                        @else
                                            <span class="hub-chip hub-chip-amber">Pending Review</span>
                                        @endif
                                    </td>
                                    <td style="font-weight:700;">
                                        {{ number_format($post['views_count']) }}
                                    </td>
                                    <td style="color:var(--hub-muted);">
                                        {{ $post['created_at'] }}
                                    </td>
                                    <td style="text-align:right;">
                                        <a href="{{ route('filament.contributor.resources.hub-posts.edit', ['record' => $post['id']]) }}" class="hub-btn hub-btn-muted" style="font-size:0.7rem; padding:0.2rem 0.4rem; text-decoration:none;">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="text-align:center; padding:2rem 1rem;">
                    <i class="fa-solid fa-file-circle-plus" style="font-size:2rem; color:var(--hub-muted); display:block; margin-bottom:0.5rem;"></i>
                    <h4 class="hub-title">No contributions yet</h4>
                    <p class="hub-copy">Get started by creating your first resource or post.</p>
                    <div style="margin-top:0.8rem;">
                        <a href="{{ route('filament.contributor.resources.hub-posts.create') }}" class="hub-btn hub-btn-primary">
                            <i class="fa-solid fa-circle-plus"></i> Create Contribution
                        </a>
                    </div>
                </div>
            @endif
        </section>

    </div>
</x-filament-panels::page>
