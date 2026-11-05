<?php

namespace App\Livewire\Ai;

use App\Ai\Agents\AdminSystemCopilot;
use App\Ai\Agents\InstructorAssistant;
use App\Ai\Agents\StudentStudyBuddy;
use App\Filament\Instructor\Concerns\ScopedToInstructor;
use App\Models\LearningMaterial;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Files\Document;
use Laravel\Ai\Models\Conversation;
use Laravel\Ai\Models\ConversationMessage;
use Livewire\Attributes\On;
use Livewire\Component;

class PortalFloatingChatWidget extends Component
{
    use ScopedToInstructor;

    public string $portal = 'admin'; // 'admin', 'instructor', 'student'

    public bool $isOpen = true;

    public ?string $conversationId = null;

    public string $message = '';

    public ?string $errorMessage = null;

    public ?int $selectedMaterialId = null;

    public bool $showThreads = false;

    public function mount(string $portal = 'admin'): void
    {
        $this->portal = in_array($portal, ['admin', 'instructor', 'student'], true) ? $portal : 'admin';

        $this->isOpen = (bool) session('ai_floating_open_' . $this->portal, true);
        $savedConvId = session('ai_conversation_' . $this->portal);

        $user = auth()->user();
        if ($savedConvId && $user) {
            $exists = Conversation::query()
                ->where('id', $savedConvId)
                ->where('participant_type', Conversation::participantType($user))
                ->where('participant_id', Conversation::participantKey($user))
                ->exists();

            if ($exists) {
                $this->conversationId = $savedConvId;
            }
        }
    }

    #[On('toggle-portal-ai-floating')]
    public function toggleOpen(): void
    {
        $this->isOpen = ! $this->isOpen;
        session(['ai_floating_open_' . $this->portal => $this->isOpen]);
    }

    #[On('open-portal-ai-floating')]
    public function openFloating(): void
    {
        $this->isOpen = true;
        session(['ai_floating_open_' . $this->portal => true]);
    }

    public function pinBack()
    {
        session([
            'ai_unpinned_' . $this->portal => false,
            'ai_floating_open_' . $this->portal => false,
            'ai_conversation_' . $this->portal => $this->conversationId,
        ]);

        $fullPageUrl = match ($this->portal) {
            'admin' => url('/manage/ai-copilot'),
            'instructor' => url('/teach/ai-assistant'),
            'student' => url('/learn/study-buddy'),
            default => url('/manage/ai-copilot'),
        };

        if ($this->conversationId) {
            $fullPageUrl .= '?c=' . urlencode($this->conversationId);
        }

        return redirect()->to($fullPageUrl);
    }

    public function toggleThreads(): void
    {
        $this->showThreads = ! $this->showThreads;
    }

    public function startNewConversation(): void
    {
        $this->conversationId = null;
        $this->message = '';
        $this->errorMessage = null;
        $this->showThreads = false;
        session(['ai_conversation_' . $this->portal => null]);
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
            $this->showThreads = false;
            session(['ai_conversation_' . $this->portal => $id]);
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
                session(['ai_conversation_' . $this->portal => null]);
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
        $this->showThreads = false;

        $attachments = [];

        if ($this->portal === 'student' && ! empty($this->selectedMaterialId)) {
            $material = LearningMaterial::query()
                ->visibleTo($user)
                ->where('id', $this->selectedMaterialId)
                ->first();

            if ($material && $material->file_path && Storage::disk('public')->exists($material->file_path)) {
                $attachments[] = Document::fromStorage($material->file_path, disk: 'public');
            }
        }

        try {
            $agent = match ($this->portal) {
                'admin' => new AdminSystemCopilot($user),
                'instructor' => new InstructorAssistant($user, static::instructorCourseIds()),
                'student' => new StudentStudyBuddy($user),
                default => new AdminSystemCopilot($user),
            };

            if ($this->conversationId) {
                $agent->continue($this->conversationId, as: $user);
            } else {
                $agent->forUser($user);
            }

            $agent->prompt($input, attachments: $attachments, timeout: 25);

            $this->conversationId = $agent->currentConversation();
            session(['ai_conversation_' . $this->portal => $this->conversationId]);

            $this->dispatch('message-sent');
        } catch (\Throwable $e) {
            Log::error("PortalFloatingChatWidget [{$this->portal}] error: " . $e->getMessage(), ['exception' => $e]);
            $this->errorMessage = 'AI Co-Pilot error: ' . $e->getMessage();

            Notification::make()
                ->title('AI response failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        $user = auth()->user();

        $canAccess = match ($this->portal) {
            'admin' => (bool) $user?->isAdmin(),
            'instructor' => (bool) ($user && ($user->isInstructor() || $user->isAdmin())),
            'student' => (bool) ($user && ($user->isStudent() || $user->isAdmin())),
            default => false,
        };

        if (! $canAccess) {
            return view('livewire.ai.portal-floating-chat-widget', [
                'canAccess' => false,
                'conversations' => collect(),
                'messages' => collect(),
                'materials' => collect(),
            ]);
        }

        $agentClass = match ($this->portal) {
            'admin' => AdminSystemCopilot::class,
            'instructor' => InstructorAssistant::class,
            'student' => StudentStudyBuddy::class,
            default => AdminSystemCopilot::class,
        };

        $participantType = Conversation::participantType($user);
        $participantId = Conversation::participantKey($user);

        $conversations = Conversation::query()
            ->where('participant_type', $participantType)
            ->where('participant_id', $participantId)
            ->whereHas('messages', fn ($q) => $q->where('agent', $agentClass))
            ->orderByDesc('updated_at')
            ->get();

        $messages = $this->conversationId
            ? ConversationMessage::query()
                ->where('conversation_id', $this->conversationId)
                ->orderBy('created_at')
                ->get()
            : collect();

        $materials = ($this->portal === 'student')
            ? LearningMaterial::query()->visibleTo($user)->with('course')->get()
            : collect();

        return view('livewire.ai.portal-floating-chat-widget', [
            'canAccess' => true,
            'conversations' => $conversations,
            'messages' => $messages,
            'materials' => $materials,
        ]);
    }
}

