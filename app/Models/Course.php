<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'code',
        'image_path',
        'description',
        'overview',
        'timeline',
        'fees',
        'requirements',
        'key_outcome',
        'level_progression',
        'is_open_enrollment',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_open_enrollment' => 'boolean',
        ];
    }

    public function getTimelineAttribute($value): ?string
    {
        if (! $value) {
            return $value;
        }

        return trim(preg_replace('/\s*\(.*\)/', '', $value));
    }

    public function enrolledUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments')->withTimestamps();
    }

    public function selectedParticipants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_selected_participants')->withTimestamps();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(LearningMaterial::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_instructor')->withTimestamps();
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(CourseSession::class);
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(CourseRating::class);
    }

    public function averageRating(): float
    {
        return round((float) $this->ratings()->avg('rating'), 1);
    }

    public function ratingsCount(): int
    {
        return (int) $this->ratings()->count();
    }

    public function requiresPaymentApproval(): bool
    {
        $fees = trim((string) $this->fees);

        if ($fees === '') {
            return false;
        }

        if (preg_match_all('/\d+(?:[.,]\d+)?/', $fees, $matches) !== false) {
            foreach ($matches[0] ?? [] as $rawAmount) {
                $normalized = str_replace(',', '', (string) $rawAmount);

                if ((float) $normalized > 0) {
                    return true;
                }
            }
        }

        return false;
    }
}
