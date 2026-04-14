<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'title',
        'description',
        'time_limit_minutes',
        'shuffle_questions',
        'show_results',
        'pass_percentage',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'shuffle_questions' => 'boolean',
            'show_results' => 'boolean',
            'is_active' => 'boolean',
            'time_limit_minutes' => 'integer',
            'pass_percentage' => 'integer',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function getTotalPointsAttribute(): int
    {
        return $this->questions()->sum('points');
    }

    public function getQuestionCountAttribute(): int
    {
        return $this->questions()->count();
    }
}
