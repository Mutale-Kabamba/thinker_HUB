<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromoCode extends Model
{
    use HasFactory;

    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_FIXED = 'fixed';

    public const TYPES = [
        self::TYPE_PERCENTAGE => 'Percentage (%)',
        self::TYPE_FIXED => 'Fixed Amount (ZMW)',
    ];

    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_value',
        'max_discount_amount',
        'min_order_amount',
        'course_id',
        'max_uses',
        'uses_count',
        'max_uses_per_user',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'max_discount_amount' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'max_uses' => 'integer',
            'uses_count' => 'integer',
            'max_uses_per_user' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Always store promo code uppercase and trimmed.
     */
    public function setCodeAttribute(string $value): void
    {
        $this->attributes['code'] = strtoupper(trim($value));
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(PromoCodeUsage::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function (Builder $q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->where(function (Builder $q) {
                $q->whereNull('max_uses')->orWhereRaw('uses_count < max_uses');
            });
    }

    public function scopeForCourse(Builder $query, Course|int $course): Builder
    {
        $courseId = $course instanceof Course ? $course->id : $course;

        return $query->where(function (Builder $q) use ($courseId) {
            $q->whereNull('course_id')->orWhere('course_id', $courseId);
        });
    }

    /**
     * Calculate discount amount against an original price.
     */
    public function calculateDiscount(float $amount): float
    {
        if ($amount <= 0) {
            return 0.00;
        }

        if ($this->discount_type === self::TYPE_PERCENTAGE) {
            $discount = round(($amount * (float) $this->discount_value) / 100, 2);
            if ($this->max_discount_amount !== null && $this->max_discount_amount > 0) {
                $discount = min($discount, (float) $this->max_discount_amount);
            }
        } else {
            $discount = (float) $this->discount_value;
        }

        return min($amount, max(0.00, $discount));
    }

    /**
     * Calculate final payable amount.
     */
    public function calculateFinalAmount(float $amount): float
    {
        return max(0.00, round($amount - $this->calculateDiscount($amount), 2));
    }

    /**
     * Check if promo code is valid for a course, order amount, and student.
     *
     * @return array{valid: bool, message: ?string}
     */
    public function isValidFor(Course $course, float $amount, ?User $user = null, ?string $email = null): array
    {
        if (! $this->is_active) {
            return ['valid' => false, 'message' => 'This promo code is no longer active.'];
        }

        if ($this->starts_at && now()->lt($this->starts_at)) {
            return ['valid' => false, 'message' => 'This promo code is not valid yet. It starts on ' . $this->starts_at->format('d M Y') . '.'];
        }

        if ($this->expires_at && now()->gt($this->expires_at)) {
            return ['valid' => false, 'message' => 'This promo code expired on ' . $this->expires_at->format('d M Y') . '.'];
        }

        if ($this->course_id !== null && (int) $this->course_id !== (int) $course->id) {
            $assignedCourse = $this->course?->title ?? 'another specific course';
            return ['valid' => false, 'message' => "This promo code is only valid for {$assignedCourse}."];
        }

        if ($this->min_order_amount !== null && $amount < (float) $this->min_order_amount) {
            return ['valid' => false, 'message' => 'This promo code requires a minimum course fee of ZMW ' . number_format((float) $this->min_order_amount, 2) . '.'];
        }

        if ($this->max_uses !== null && $this->uses_count >= $this->max_uses) {
            return ['valid' => false, 'message' => 'This promo code has reached its maximum usage limit.'];
        }

        // Check user redemption limits
        $userUsageCount = 0;
        if ($user) {
            $userUsageCount = $this->usages()->where('user_id', $user->id)->count();
        }

        if ($email && $userUsageCount === 0) {
            $userUsageCount = $this->usages()->where('user_email', strtolower(trim($email)))->count();
        }

        if ($this->max_uses_per_user !== null && $userUsageCount >= $this->max_uses_per_user) {
            return ['valid' => false, 'message' => 'You have already used this promo code the maximum allowed number of times.'];
        }

        return ['valid' => true, 'message' => null];
    }

    public function isPercentage(): bool
    {
        return $this->discount_type === self::TYPE_PERCENTAGE;
    }

    public function formattedValue(): string
    {
        return $this->formattedDiscount();
    }

    /**
     * Human-readable discount string.
     */
    public function formattedDiscount(): string
    {
        if ($this->discount_type === self::TYPE_PERCENTAGE) {
            $val = (float) $this->discount_value;
            $text = rtrim(rtrim(number_format($val, 2), '0'), '.') . '% OFF';
            if ($this->max_discount_amount) {
                $text .= ' (Up to ZMW ' . number_format((float) $this->max_discount_amount, 2) . ')';
            }
            return $text;
        }

        return 'ZMW ' . number_format((float) $this->discount_value, 2) . ' OFF';
    }

    /**
     * Computed status helper.
     */
    public function getStatusLabelAttribute(): string
    {
        if (! $this->is_active) {
            return 'Inactive';
        }
        if ($this->starts_at && now()->lt($this->starts_at)) {
            return 'Scheduled';
        }
        if ($this->expires_at && now()->gt($this->expires_at)) {
            return 'Expired';
        }
        if ($this->max_uses !== null && $this->uses_count >= $this->max_uses) {
            return 'Exhausted';
        }

        return 'Active';
    }
}
