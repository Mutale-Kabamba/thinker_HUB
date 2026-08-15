<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClaimItem extends Model
{
    use HasFactory;

    public const CATEGORY_DATA = 'data';
    public const CATEGORY_MERCH = 'merch';
    public const CATEGORY_VOUCHER = 'voucher';
    public const CATEGORY_PERK = 'perk';

    public const CATEGORIES = [
        self::CATEGORY_DATA => 'Data Bundles & Airtime',
        self::CATEGORY_MERCH => 'Merchandise & Swag',
        self::CATEGORY_VOUCHER => 'Gift Vouchers',
        self::CATEGORY_PERK => 'Special Perks & Mentorship',
    ];

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'category',
        'coin_cost',
        'stock_quantity',
        'image_path',
        'is_active',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'course_id' => 'integer',
            'coin_cost' => 'integer',
            'stock_quantity' => 'integer',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function course(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function claimRequests(): HasMany
    {
        return $this->hasMany(ClaimRequest::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeForCourses(Builder $query, array $courseIds): Builder
    {
        return $query->where(function (Builder $q) use ($courseIds) {
            $q->whereNull('course_id');
            if ($courseIds !== []) {
                $q->orWhereIn('course_id', $courseIds);
            }
        });
    }

    public function isUnlimited(): bool
    {
        return $this->stock_quantity < 0;
    }

    public function isInStock(): bool
    {
        return $this->stock_quantity < 0 || $this->stock_quantity > 0;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_quantity === 0;
    }
}
