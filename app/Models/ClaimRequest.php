<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_FULFILLED = 'fulfilled';
    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_FULFILLED => 'Fulfilled',
        self::STATUS_REJECTED => 'Rejected',
    ];

    protected $fillable = [
        'user_id',
        'claim_item_id',
        'coins_spent',
        'status',
        'phone_number',
        'delivery_notes',
        'admin_remarks',
        'fulfilled_at',
    ];

    protected function casts(): array
    {
        return [
            'coins_spent' => 'integer',
            'fulfilled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function claimItem(): BelongsTo
    {
        return $this->belongsTo(ClaimItem::class);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeFulfilled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FULFILLED);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }
}
