<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'course_id',
        'promo_code_id',
        'enrollment_id',
        'amount',
        'original_amount',
        'discount_amount',
        'currency',
        'payment_method',
        'provider',
        'phone_number',
        'reference',
        'status',
        'metadata',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'original_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'metadata' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function formattedAmount(): string
    {
        return $this->currency . ' ' . number_format((float) $this->amount, 2);
    }

    public function hasDiscount(): bool
    {
        return (float) $this->discount_amount > 0 || ! empty($this->promo_code_id) || ! empty($this->metadata['promo_code']);
    }

    public function formattedOriginalAmount(): string
    {
        $orig = $this->original_amount ?? ($this->amount + $this->discount_amount);
        return $this->currency . ' ' . number_format((float) $orig, 2);
    }

    public function formattedDiscountAmount(): string
    {
        return $this->currency . ' ' . number_format((float) $this->discount_amount, 2);
    }

    public function promoCodeText(): ?string
    {
        return $this->promoCode?->code ?? ($this->metadata['promo_code'] ?? null);
    }
}
