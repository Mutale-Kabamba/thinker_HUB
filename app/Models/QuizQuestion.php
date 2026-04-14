<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'type',
        'question',
        'explanation',
        'points',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuizOption::class)->orderBy('sort_order');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }

    public function isMultipleChoice(): bool
    {
        return $this->type === 'multiple_choice';
    }

    public function isTheory(): bool
    {
        return $this->type === 'theory';
    }

    public function isPractical(): bool
    {
        return $this->type === 'practical';
    }

    public function getCorrectOption(): ?QuizOption
    {
        return $this->options()->where('is_correct', true)->first();
    }
}
