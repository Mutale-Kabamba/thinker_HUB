<div wire:key="comments-{{ $type }}-{{ $id }}" style="display:flex;flex-direction:column;gap:0.75rem;">

    @php $currentUser = auth()->user(); @endphp

    <h4 style="margin:0;font-size:0.9rem;font-weight:700;color:var(--hub-ink);">
        Comments
        <span style="color:var(--hub-muted);font-weight:500;">({{ $this->comments->count() }})</span>
    </h4>

    {{-- New comment form --}}
    @auth
        <form wire:submit.prevent="addComment" style="display:flex;flex-direction:column;gap:0.35rem;">
            <textarea
                wire:model="body"
                rows="2"
                placeholder="Share your thoughts…"
                class="hub-input"
                style="width:100%;font-size:0.85rem;padding:0.45rem 0.6rem;resize:vertical;"
            ></textarea>
            @error('body') <span style="color:#dc2626;font-size:0.72rem;">{{ $message }}</span> @enderror
            <div style="display:flex;justify-content:flex-end;">
                <button type="submit" class="hub-btn" style="font-size:0.78rem;padding:0.35rem 0.9rem;background:var(--hub-primary,#0d9488);color:#fff;border:none;border-radius:0.45rem;cursor:pointer;">
                    <span wire:loading.remove wire:target="addComment">Post Comment</span>
                    <span wire:loading wire:target="addComment">Posting…</span>
                </button>
            </div>
        </form>
    @endauth

    {{-- Comment list --}}
    <div style="display:flex;flex-direction:column;gap:0.65rem;">
        @forelse ($this->comments as $comment)
            <div style="border:1px solid var(--hub-border);border-radius:0.6rem;padding:0.55rem 0.7rem;">
                <div style="display:flex;justify-content:space-between;gap:0.5rem;align-items:baseline;">
                    <p style="margin:0;font-weight:600;font-size:0.82rem;color:var(--hub-ink);">
                        {{ $comment->user?->name ?? 'Unknown' }}
                        <span style="font-weight:400;color:var(--hub-muted);font-size:0.72rem;">· {{ $comment->created_at?->diffForHumans() }}</span>
                    </p>
                    @if ($comment->canBeDeletedBy($currentUser))
                        <button
                            type="button"
                            wire:click="deleteComment({{ $comment->id }})"
                            wire:confirm="Delete this comment?"
                            style="background:none;border:none;color:#dc2626;font-size:0.72rem;cursor:pointer;padding:0;"
                        >Delete</button>
                    @endif
                </div>
                <p style="margin:0.25rem 0 0;font-size:0.85rem;color:var(--hub-ink);white-space:pre-wrap;">{{ $comment->body }}</p>

                @auth
                    <button
                        type="button"
                        wire:click="startReply({{ $comment->id }})"
                        style="background:none;border:none;color:var(--hub-muted);font-size:0.72rem;cursor:pointer;padding:0.25rem 0 0;"
                    >Reply</button>
                @endauth

                {{-- Reply form --}}
                @if ($replyingTo === $comment->id)
                    <form wire:submit.prevent="addReply" style="display:flex;flex-direction:column;gap:0.3rem;margin-top:0.4rem;padding-left:0.75rem;border-left:2px solid var(--hub-border);">
                        <textarea
                            wire:model="replyBody"
                            rows="2"
                            placeholder="Write a reply…"
                            class="hub-input"
                            style="width:100%;font-size:0.82rem;padding:0.4rem 0.55rem;resize:vertical;"
                        ></textarea>
                        @error('replyBody') <span style="color:#dc2626;font-size:0.72rem;">{{ $message }}</span> @enderror
                        <div style="display:flex;gap:0.4rem;justify-content:flex-end;">
                            <button type="button" wire:click="cancelReply" style="font-size:0.75rem;padding:0.3rem 0.7rem;background:none;border:1px solid var(--hub-border);border-radius:0.4rem;cursor:pointer;color:var(--hub-ink);">Cancel</button>
                            <button type="submit" style="font-size:0.75rem;padding:0.3rem 0.8rem;background:var(--hub-primary,#0d9488);color:#fff;border:none;border-radius:0.4rem;cursor:pointer;">Reply</button>
                        </div>
                    </form>
                @endif

                {{-- Replies --}}
                @if ($comment->replies->isNotEmpty())
                    <div style="display:flex;flex-direction:column;gap:0.45rem;margin-top:0.5rem;padding-left:0.75rem;border-left:2px solid var(--hub-border);">
                        @foreach ($comment->replies as $reply)
                            <div>
                                <div style="display:flex;justify-content:space-between;gap:0.5rem;align-items:baseline;">
                                    <p style="margin:0;font-weight:600;font-size:0.79rem;color:var(--hub-ink);">
                                        {{ $reply->user?->name ?? 'Unknown' }}
                                        <span style="font-weight:400;color:var(--hub-muted);font-size:0.7rem;">· {{ $reply->created_at?->diffForHumans() }}</span>
                                    </p>
                                    @if ($reply->canBeDeletedBy($currentUser))
                                        <button
                                            type="button"
                                            wire:click="deleteComment({{ $reply->id }})"
                                            wire:confirm="Delete this reply?"
                                            style="background:none;border:none;color:#dc2626;font-size:0.7rem;cursor:pointer;padding:0;"
                                        >Delete</button>
                                    @endif
                                </div>
                                <p style="margin:0.2rem 0 0;font-size:0.82rem;color:var(--hub-ink);white-space:pre-wrap;">{{ $reply->body }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <p style="margin:0;font-size:0.82rem;color:var(--hub-muted);">No comments yet. Be the first to comment.</p>
        @endforelse
    </div>
</div>
