<?php

namespace App\Livewire\Ai;

use App\Ai\Agents\PublicCourseAdvisor;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class PublicChatWidget extends Component
{
    public bool $isOpen = false;
    public string $message = '';
    public array $messages = [];
    public ?string $errorMessage = null;

    public function mount(): void
    {
        $this->messages = session('public_ai_messages', []);
    }

    public function toggleChat(): void
    {
        $this->isOpen = ! $this->isOpen;
        $this->errorMessage = null;
    }

    public function resetChat(): void
    {
        $this->messages = [];
        session()->forget('public_ai_messages');
        $this->errorMessage = null;
    }

    public function sendMessage(?string $quickPrompt = null): void
    {
        $input = trim($quickPrompt ?: $this->message);
        if ($input === '') {
            return;
        }

        if (! app()->runningInConsole()) {
            @set_time_limit(25);
        }

        $ip = request()->ip() ?? '127.0.0.1';
        $executed = RateLimiter::attempt(
            'public-ai:' . $ip,
            12,
            function () use ($input) {
                $this->messages[] = [
                    'role' => 'user',
                    'content' => $input,
                    'created_at' => now()->format('H:i'),
                ];

                $this->message = '';
                $this->errorMessage = null;

                try {
                    $history = array_slice($this->messages, -10);
                    $agent = new PublicCourseAdvisor(history: array_slice($history, 0, -1));
                    $response = $agent->prompt($input);

                    $replyText = trim((string) $response);
                    if ($replyText === '') {
                        $replyText = 'Thank you for reaching out! Please explore our course catalog or contact our admissions team at support@thinkerhub.net.';
                    }

                    $this->messages[] = [
                        'role' => 'assistant',
                        'content' => $replyText,
                        'created_at' => now()->format('H:i'),
                    ];

                    $this->messages = array_slice($this->messages, -12);
                    session(['public_ai_messages' => $this->messages]);
                } catch (\Throwable $e) {
                    Log::warning('PublicCourseAdvisor timeout or error: ' . $e->getMessage(), ['exception' => $e]);
                    $this->messages[] = [
                        'role' => 'assistant',
                        'content' => "I'm experiencing a brief network delay fetching live catalog data. You can explore our [Course Catalog](/courses) directly, or feel free to ask me again in just a moment!",
                        'created_at' => now()->format('H:i'),
                    ];
                }
            },
            60
        );

        if (! $executed) {
            $this->errorMessage = 'You have asked several questions in a short period. Please wait a moment before sending another.';
        }
    }

    public function render()
    {
        return view('livewire.ai.public-chat-widget');
    }
}

