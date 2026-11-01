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

    public function sendMessage(): void
    {
        $input = trim($this->message);
        if ($input === '') {
            return;
        }

        $ip = request()->ip() ?? '127.0.0.1';
        $executed = RateLimiter::attempt(
            'public-ai:' . $ip,
            10,
            function () use ($input) {
                $this->messages[] = [
                    'role' => 'user',
                    'content' => $input,
                    'created_at' => now()->format('H:i'),
                ];

                $this->message = '';
                $this->errorMessage = null;

                try {
                    $history = array_slice($this->messages, -16);
                    $agent = new PublicCourseAdvisor(history: array_slice($history, 0, -1));
                    $response = $agent->prompt($input);

                    $replyText = trim((string) $response);
                    if ($replyText === '') {
                        $replyText = 'Thank you for your inquiry! Please explore our course catalog or contact our admissions team for more information.';
                    }

                    $this->messages[] = [
                        'role' => 'assistant',
                        'content' => $replyText,
                        'created_at' => now()->format('H:i'),
                    ];

                    $this->messages = array_slice($this->messages, -16);
                    session(['public_ai_messages' => $this->messages]);
                } catch (\Throwable $e) {
                    Log::warning('PublicCourseAdvisor error: ' . $e->getMessage(), ['exception' => $e]);
                    $this->messages[] = [
                        'role' => 'assistant',
                        'content' => 'Candice is currently experiencing high demand. Please try again shortly or contact admissions at support@thinkerhub.net.',
                        'created_at' => now()->format('H:i'),
                    ];
                }
            },
            60
        );

        if (! $executed) {
            $this->errorMessage = 'Too many requests. Please wait a minute before sending another question.';
        }
    }

    public function render()
    {
        return view('livewire.ai.public-chat-widget');
    }
}

