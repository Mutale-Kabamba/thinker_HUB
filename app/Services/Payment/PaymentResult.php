<?php

namespace App\Services\Payment;

class PaymentResult
{
    public function __construct(
        public bool $success,
        public string $status,
        public string $reference,
        public ?string $gatewayReference = null,
        public ?string $redirectUrl = null,
        public string $message = '',
        public array $data = [],
    ) {}

    public static function completed(string $reference, string $message = 'Payment completed successfully.', array $data = []): self
    {
        return new self(
            success: true,
            status: 'completed',
            reference: $reference,
            gatewayReference: $data['gateway_reference'] ?? null,
            redirectUrl: null,
            message: $message,
            data: $data,
        );
    }

    public static function pending(string $reference, string $message = 'Payment initiated and awaiting confirmation.', array $data = []): self
    {
        return new self(
            success: true,
            status: 'pending',
            reference: $reference,
            gatewayReference: $data['gateway_reference'] ?? null,
            redirectUrl: null,
            message: $message,
            data: $data,
        );
    }

    public static function redirect(string $reference, string $redirectUrl, string $message = 'Redirecting to payment gateway...', array $data = []): self
    {
        return new self(
            success: true,
            status: 'redirect',
            reference: $reference,
            gatewayReference: $data['gateway_reference'] ?? null,
            redirectUrl: $redirectUrl,
            message: $message,
            data: $data,
        );
    }

    public static function failed(string $reference, string $message = 'Payment transaction failed.', array $data = []): self
    {
        return new self(
            success: false,
            status: 'failed',
            reference: $reference,
            gatewayReference: $data['gateway_reference'] ?? null,
            redirectUrl: null,
            message: $message,
            data: $data,
        );
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'processing'], true);
    }

    public function isRedirect(): bool
    {
        return $this->status === 'redirect' && ! empty($this->redirectUrl);
    }
}
