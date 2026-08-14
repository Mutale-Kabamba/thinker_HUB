<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'publish_at',
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
            'publish_at' => 'datetime',
            'time_limit_minutes' => 'integer',
            'pass_percentage' => 'integer',
        ];
    }

    public function scopeReleased(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->where(function (Builder $q): void {
                $q->whereNull('publish_at')
                    ->where('is_active', true);
            })->orWhere(function (Builder $q): void {
                $q->whereNotNull('publish_at')
                    ->where('publish_at', '<=', now());
            });
        });
    }

    /**
     * Determine if this quiz is currently released to students.
     */
    public function isReleased(): bool
    {
        if ($this->publish_at !== null) {
            return $this->publish_at->lte(now());
        }

        return (bool) $this->is_active;
    }

    /**
     * Auto-activate and sync any scheduled quizzes whose publish date/time has passed.
     */
    public static function publishScheduled(): int
    {
        $dueQuizzes = self::query()
            ->whereNotNull('publish_at')
            ->where('publish_at', '<=', now())
            ->where('is_active', false)
            ->get();

        $count = 0;

        foreach ($dueQuizzes as $quiz) {
            $quiz->update(['is_active' => true]);
            $count++;
        }

        return $count;
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        $enrolledCourseIds = $user->courses()->pluck('courses.id');

        return $query->whereIn('course_id', $enrolledCourseIds);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
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

    /**
     * Grade a completed attempt: auto-score MCQ, tally points, calculate percentage.
     */
    public function gradeAttempt(QuizAttempt $attempt): QuizAttempt
    {
        $totalPoints = 0;
        $earnedPoints = 0;

        foreach ($attempt->answers()->with('question', 'option')->get() as $answer) {
            $question = $answer->question;

            if (! $question) {
                continue;
            }

            $totalPoints += $question->points;

            if ($question->isMultipleChoice()) {
                $correct = $answer->option?->is_correct ?? false;
                $points = $correct ? $question->points : 0;

                $answer->update([
                    'is_correct' => $correct,
                    'points_earned' => $points,
                ]);

                $earnedPoints += $points;
            } else {
                // Theory/practical answers keep their manually assigned points
                $earnedPoints += (int) $answer->points_earned;
            }
        }

        $percentage = $totalPoints > 0
            ? (int) round(($earnedPoints / $totalPoints) * 100)
            : 0;

        $attempt->update([
            'score' => $earnedPoints,
            'total_points' => $totalPoints,
            'percentage' => $percentage,
            'passed' => $percentage >= $this->pass_percentage,
            'completed_at' => $attempt->completed_at ?? now(),
        ]);

        return $attempt->refresh();
    }
}
