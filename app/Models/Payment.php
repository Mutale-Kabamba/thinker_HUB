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

    public const CHANNEL_DIRECT_CHECKOUT = 'direct_checkout';
    public const CHANNEL_ADMIN_MANUAL = 'admin_manual';
    public const CHANNEL_INSTRUCTOR_MANUAL = 'instructor_manual';

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
        'registered_by_user_id',
        'registration_type',
        'is_free',
        'payment_notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'original_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'metadata' => 'array',
            'paid_at' => 'datetime',
            'is_free' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by_user_id');
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

    public function isDirectCheckout(): bool
    {
        return ($this->registration_type ?? self::CHANNEL_DIRECT_CHECKOUT) === self::CHANNEL_DIRECT_CHECKOUT;
    }

    public function isManualRegistration(): bool
    {
        return in_array($this->registration_type, [self::CHANNEL_ADMIN_MANUAL, self::CHANNEL_INSTRUCTOR_MANUAL], true);
    }

    public function isFree(): bool
    {
        return (bool) ($this->is_free || (float) $this->amount <= 0.0 || $this->payment_method === 'free_registration');
    }

    public function registrationChannelLabel(): string
    {
        return match ($this->registration_type) {
            self::CHANNEL_ADMIN_MANUAL => 'Admin Added',
            self::CHANNEL_INSTRUCTOR_MANUAL => 'Instructor Added',
            self::CHANNEL_DIRECT_CHECKOUT => 'Direct Checkout',
            default => 'Unspecified (Legacy)',
        };
    }

    public function paymentMethodLabel(): string
    {
        if ($this->isFree()) {
            return 'Free / Sponsored';
        }

        return match ($this->payment_method) {
            'mobile_money' => 'Mobile Money' . ($this->provider ? ' (' . ucfirst($this->provider) . ')' : ''),
            'card' => 'Credit / Debit Card',
            'cash' => 'Cash Payment',
            'bank_transfer' => 'Bank Transfer / Deposit',
            'cheque' => 'Cheque',
            'promo_code' => 'Promo Code Waiver',
            default => ucwords(str_replace('_', ' ', (string) ($this->payment_method ?? 'Payment'))),
        };
    }
}
