<?php

namespace App\Ai\Concerns;

use Illuminate\Http\Client\ConnectionException;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Exceptions\ProviderOverloadedException;
use Laravel\Ai\Exceptions\RateLimitedException;
use Throwable;

trait ResolvesAiProviders
{
    /**
     * Build an ordered failover list of configured AI providers and fallback models.
     *
     * When the primary Gemini model is overloaded (HTTP 503) or rate-limited (HTTP 429),
     * Laravel AI automatically fails over through distinct Gemini fallback models and
     * any additional configured providers (OpenAI, Anthropic, Groq).
     *
     * @return array<int, Lab|string>|Lab|string
     */
    public function provider(): array|Lab|string
    {
        $providers = [];

        if (! empty(config('ai.providers.gemini.key')) || ! empty(config('services.gemini.api_key'))) {
            $seenModels = [];

            foreach (['gemini', 'gemini-fallback', 'gemini-backup'] as $providerName) {
                if (! is_array(config("ai.providers.{$providerName}"))) {
                    continue;
                }

                $model = (string) config("ai.providers.{$providerName}.models.text.default", '');

                if ($model !== '' && in_array($model, $seenModels, true)) {
                    continue;
                }

                if ($model !== '') {
                    $seenModels[] = $model;
                }

                $providers[] = $providerName;
            }
        }

        if (! empty(config('ai.providers.openai.key'))) {
            $providers[] = Lab::OpenAI;
        }

        if (! empty(config('ai.providers.anthropic.key'))) {
            $providers[] = Lab::Anthropic;
        }

        if (! empty(config('ai.providers.groq.key'))) {
            $providers[] = Lab::Groq;
        }

        return $providers !== [] ? $providers : ['gemini', 'gemini-fallback', Lab::OpenAI];
    }

    /**
     * Convert raw AI provider / network exceptions into a clear, user-friendly message.
     */
    public static function formatUserFriendlyError(Throwable $e): string
    {
        $raw = strtolower($e->getMessage());

        if (
            $e instanceof ProviderOverloadedException
            || str_contains($raw, 'overloaded')
            || str_contains($raw, '503')
            || str_contains($raw, 'service unavailable')
        ) {
            return 'The AI service is currently experiencing high traffic. Please wait a few seconds and try sending your message again.';
        }

        if (
            $e instanceof RateLimitedException
            || str_contains($raw, 'rate limit')
            || str_contains($raw, '429')
            || str_contains($raw, 'quota')
        ) {
            return 'The AI assistant has reached its temporary request limit. Please wait a moment and try again.';
        }

        if (
            $e instanceof ConnectionException
            || str_contains($raw, 'timed out')
            || str_contains($raw, 'timeout')
            || str_contains($raw, 'curl error 28')
        ) {
            return 'The AI assistant took too long to respond. Try asking a shorter or more specific question.';
        }

        return 'The AI assistant could not complete your request right now. Please try again in a moment.';
    }
}
