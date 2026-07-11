<?php

namespace App\Livewire;

use App\Models\LearningMaterial;
use App\Models\Opportunity;
use App\Models\ResourceComment;
use App\Models\ResourceVideo;
use Illuminate\Support\Collection;
use Livewire\Attributes\Validate;
use Livewire\Component;

class CommentSection extends Component
{
    /**
     * Whitelist of allowed commentable aliases -> model classes.
     * Never resolve a model class directly from client input.
     *
     * @var array<string, class-string<\Illuminate\Database\Eloquent\Model>>
     */
    private const TYPE_MAP = [
        'video' => ResourceVideo::class,
        'lesson' => LearningMaterial::class,
        'opportunity' => Opportunity::class,
    ];

    public string $type;

    public int $id;

    #[Validate('required|string|min:1|max:1000')]
    public string $body = '';

    public ?int $replyingTo = null;

    #[Validate('required|string|min:1|max:1000')]
    public string $replyBody = '';

    public function mount(string $type, int $id): void
    {
        abort_unless(array_key_exists($type, self::TYPE_MAP), 404);

        $this->type = $type;
        $this->id = $id;
    }

    private function modelClass(): string
    {
        return self::TYPE_MAP[$this->type];
    }

    public function getCommentsProperty(): Collection
    {
        $modelClass = $this->modelClass();

        return ResourceComment::query()
            ->where('commentable_type', (new $modelClass)->getMorphClass())
            ->where('commentable_id', $this->id)
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->latest()
            ->get();
    }

    public function addComment(): void
    {
        $user = auth()->user();
        abort_unless((bool) $user, 403);

        $this->validateOnly('body');

        $modelClass = $this->modelClass();
        $commentable = $modelClass::findOrFail($this->id);

        $commentable->comments()->create([
            'user_id' => $user->id,
            'body' => trim($this->body),
        ]);

        $this->body = '';
    }

    public function startReply(int $parentId): void
    {
        $this->replyingTo = $parentId;
        $this->replyBody = '';
    }

    public function cancelReply(): void
    {
        $this->replyingTo = null;
        $this->replyBody = '';
    }

    public function addReply(): void
    {
        $user = auth()->user();
        abort_unless((bool) $user, 403);

        if ($this->replyingTo === null) {
            return;
        }

        $this->validateOnly('replyBody');

        $modelClass = $this->modelClass();
        $morphClass = (new $modelClass)->getMorphClass();

        // Ensure the parent belongs to this same commentable.
        $parent = ResourceComment::query()
            ->where('id', $this->replyingTo)
            ->where('commentable_type', $morphClass)
            ->where('commentable_id', $this->id)
            ->whereNull('parent_id')
            ->firstOrFail();

        $commentable = $modelClass::findOrFail($this->id);

        $commentable->comments()->create([
            'user_id' => $user->id,
            'parent_id' => $parent->id,
            'body' => trim($this->replyBody),
        ]);

        $this->cancelReply();
    }

    public function deleteComment(int $commentId): void
    {
        $comment = ResourceComment::find($commentId);

        if (! $comment) {
            return;
        }

        abort_unless($comment->canBeDeletedBy(auth()->user()), 403);

        $comment->delete();
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.comment-section');
    }
}
