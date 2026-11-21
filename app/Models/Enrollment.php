<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Enrollment extends Model
{
    use HasFactory;

    public const STATUS_PAID = 'paid';
    public const STATUS_FREE = 'free';
    public const STATUS_PENDING = 'pending';

    public const CHANNEL_DIRECT_CHECKOUT = 'direct_checkout';
    public const CHANNEL_ADMIN_MANUAL = 'admin_manual';
    public const CHANNEL_INSTRUCTOR_MANUAL = 'instructor_manual';

    protected $fillable = [
        'user_id',
        'course_id',
        'course_intake_id',
        'progress_percentage',
        'completed_at',
        'completed_by_user_id',
        'payment_status',
        'is_free',
        'fee_amount',
        'payment_method',
        'transaction_reference',
        'payment_notes',
        'registered_by_user_id',
        'registration_type',
        'paid_at',
    ];

    protected $casts = [
        'progress_percentage' => 'integer',
        'completed_at' => 'datetime',
        'is_free' => 'boolean',
        'fee_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function intake(): BelongsTo
    {
        return $this->belongsTo(CourseIntake::class, 'course_intake_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by_user_id');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function isFree(): bool
    {
        if ($this->is_free || $this->payment_status === self::STATUS_FREE) {
            return true;
        }

        if ($this->payment_status === self::STATUS_PAID) {
            return false;
        }

        return (float) ($this->fee_amount ?? 0) <= 0.0;
    }

    public function isPaid(): bool
    {
        return ! $this->isFree();
    }

    public function formattedFee(): string
    {
        if ($this->isFree()) {
            return 'Free';
        }

        return 'ZMW ' . number_format((float) ($this->fee_amount ?? 0), 2);
    }

    public function registrationChannelLabel(): string
    {
        return match ($this->registration_type) {
            self::CHANNEL_ADMIN_MANUAL => 'Admin Manual Registration',
            self::CHANNEL_INSTRUCTOR_MANUAL => 'Instructor Manual Registration',
            self::CHANNEL_DIRECT_CHECKOUT => 'Direct Online Checkout',
            default => 'Unspecified (Legacy)',
        };
    }

    public function paymentMethodLabel(): string
    {
        if ($this->isFree()) {
            return 'Free / Sponsored';
        }

        return match ($this->payment_method) {
            'cash' => 'Cash Payment',
            'bank_transfer' => 'Bank Transfer / Deposit',
            'mobile_money' => 'Mobile Money',
            'card' => 'Credit / Debit Card',
            'promo_code' => 'Promo Code Waiver',
            default => ucwords(str_replace('_', ' ', (string) ($this->payment_method ?? 'Payment'))),
        };
    }

    public function markAsCompleted(?User $instructor = null): void
    {
        $this->update([
            'completed_at' => now(),
            'completed_by_user_id' => $instructor?->id,
        ]);
    }

    public function markAsIncomplete(): void
    {
        $this->update([
            'completed_at' => null,
            'completed_by_user_id' => null,
        ]);
    }
}
