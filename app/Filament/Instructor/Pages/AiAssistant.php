<?php

namespace App\Filament\Instructor\Pages;

use App\Ai\Agents\InstructorAssistant;
use App\Filament\Instructor\Concerns\ScopedToInstructor;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Models\Conversation;
use Laravel\Ai\Models\ConversationMessage;
use Livewire\Attributes\Url;

class AiAssistant extends Page
{
    use ScopedToInstructor;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cpu-chip';

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static ?string $navigationLabel = 'AI Co-Pilot';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.instructor.pages.ai-assistant';

    #[Url(as: 'c')]
    public ?string $conversationId = null;

    public string $message = '';

    public ?string $errorMessage = null;

    public function getTitle(): string|Htmlable
    {
        return 'Instructor AI Co-Pilot';
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

        try {
            $courseIds = static::instructorCourseIds();
            $agent = new InstructorAssistant($user, $courseIds);

            if ($this->conversationId) {
                $agent->continue($this->conversationId, as: $user);
            } else {
                $agent->forUser($user);
            }

            $agent->prompt($input);

            $this->conversationId = $agent->currentConversation();
        } catch (\Throwable $e) {
            Log::error('InstructorAssistant error: ' . $e->getMessage(), ['exception' => $e]);
            $this->errorMessage = 'AI execution error: ' . $e->getMessage();

            Notification::make()
                ->title('AI execution failed')
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
            ];
        }

        $participantType = Conversation::participantType($user);
        $participantId = Conversation::participantKey($user);

        $conversations = Conversation::query()
            ->where('participant_type', $participantType)
            ->where('participant_id', $participantId)
            ->whereHas('messages', fn ($q) => $q->where('agent', InstructorAssistant::class))
            ->orderByDesc('updated_at')
            ->get();

        $messages = $this->conversationId
            ? ConversationMessage::query()
                ->where('conversation_id', $this->conversationId)
                ->orderBy('created_at')
                ->get()
            : collect();

        return [
            'conversations' => $conversations,
            'messages' => $messages,
        ];
    }
}

