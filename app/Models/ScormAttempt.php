<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScormAttempt extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'scorm_package_id',
        'user_id',
        'lesson_id',
        'tenant_id',
        'attempt_number',
        'lesson_status',
        'score_raw',
        'score_min',
        'score_max',
        'score_scaled',
        'lesson_location',
        'suspend_data',
        'session_time',
        'total_time',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'attempt_number' => 'integer',
            'score_raw' => 'decimal:2',
            'score_min' => 'decimal:2',
            'score_max' => 'decimal:2',
            'score_scaled' => 'decimal:4',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(ScormPackage::class, 'scorm_package_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function trackingEntries(): HasMany
    {
        return $this->hasMany(ScormTracking::class);
    }

    public function isCompletedOrPassed(): bool
    {
        return in_array(strtolower((string) $this->lesson_status), ['completed', 'passed'], true);
    }
}
