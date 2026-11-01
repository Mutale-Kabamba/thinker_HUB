<?php

namespace App\Filament\Student\Pages;

use App\Ai\Agents\StudentStudyBuddy;
use App\Models\LearningMaterial;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Files\Document;
use Laravel\Ai\Models\Conversation;
use Laravel\Ai\Models\ConversationMessage;
use Livewire\Attributes\Url;

class StudyBuddy extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static string|\UnitEnum|null $navigationGroup = 'LEARNING';

    protected static ?string $navigationLabel = 'AI Study Buddy';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.student.pages.study-buddy';

    #[Url(as: 'c')]
    public ?string $conversationId = null;

    public string $message = '';

    public ?int $selectedMaterialId = null;

    public ?string $errorMessage = null;

    public function getTitle(): string|Htmlable
    {
        return 'AI Study Buddy';
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return (bool) ($user && ($user->isStudent() || $user->isAdmin()));
    }

    public function mount(): void
    {
        $user = auth()->user();

        if ($this->conversationId && $user) {
            $exists = Conversation::query()
                ->where('id', $this->conversationId)
                ->where('participant_type', Conversation::participantType($user))
                ->where('participant_id', Conversation::participantKey($user))
                ->exists();

            if (! $exists) {
                $this->conversationId = null;
            }
        }
    }

    public function startNewConversation(): void
    {
        $this->conversationId = null;
        $this->selectedMaterialId = null;
        $this->message = '';
        $this->errorMessage = null;
    }

    public function selectConversation(string $id): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $exists = Conversation::query()
            ->where('id', $id)
            ->where('participant_type', Conversation::participantType($user))
            ->where('participant_id', Conversation::participantKey($user))
            ->exists();

        if ($exists) {
            $this->conversationId = $id;
            $this->errorMessage = null;
        }
    }

    public function deleteConversation(string $id): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $conversation = Conversation::query()
            ->where('id', $id)
            ->where('participant_type', Conversation::participantType($user))
            ->where('participant_id', Conversation::participantKey($user))
            ->first();

        if ($conversation) {
            $conversation->messages()->delete();
            $conversation->delete();

            if ($this->conversationId === $id) {
                $this->conversationId = null;
            }

            Notification::make()
                ->title('Conversation deleted')
                ->success()
                ->send();
        }
    }

    public function sendMessage(?string $quickPrompt = null): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $input = trim($quickPrompt ?: $this->message);

        if ($input === '') {
            return;
        }

        $this->message = '';
        $this->errorMessage = null;

        $attachments = [];

        if (! empty($this->selectedMaterialId)) {
            $material = LearningMaterial::query()
                ->visibleTo($user)
                ->where('id', $this->selectedMaterialId)
                ->first();

            if ($material && $material->file_path && Storage::disk('public')->exists($material->file_path)) {
                $attachments[] = Document::fromStorage($material->file_path, disk: 'public');
            }
        }

        try {
            $agent = new StudentStudyBuddy($user);

            if ($this->conversationId) {
                $agent->continue($this->conversationId, as: $user);
            } else {
                $agent->forUser($user);
            }

            $agent->prompt($input, attachments: $attachments);

            $this->conversationId = $agent->currentConversation();
        } catch (\Throwable $e) {
            Log::error('StudentStudyBuddy error: ' . $e->getMessage(), ['exception' => $e]);
            $this->errorMessage = 'AI study companion error: ' . $e->getMessage();

            Notification::make()
                ->title('AI response failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getViewData(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [
                'conversations' => collect(),
                'messages' => collect(),
                'materials' => collect(),
            ];
        }

        $participantType = Conversation::participantType($user);
        $participantId = Conversation::participantKey($user);

        $conversations = Conversation::query()
            ->where('participant_type', $participantType)
            ->where('participant_id', $participantId)
            ->whereHas('messages', fn ($q) => $q->where('agent', StudentStudyBuddy::class))
            ->orderByDesc('updated_at')
            ->get();

        $messages = $this->conversationId
            ? ConversationMessage::query()
                ->where('conversation_id', $this->conversationId)
                ->orderBy('created_at')
                ->get()
            : collect();

        $materials = LearningMaterial::query()
            ->visibleTo($user)
            ->whereNotNull('file_path')
            ->with(['course'])
            ->orderBy('title')
            ->get();

        return [
            'conversations' => $conversations,
            'messages' => $messages,
            'materials' => $materials,
        ];
    }
}

