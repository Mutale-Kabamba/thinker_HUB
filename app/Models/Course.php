<?php

namespace App\Models;

use App\Traits\HasReviews;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

class Course extends Model
{
    use HasFactory;
    use HasReviews;

    protected $attributes = [
        'offering_mode' => 'once_off',
    ];

    protected $fillable = [
        'title',
        'code',
        'offering_mode',
        'course_by',
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
        'gamification_settings',
        'average_rating',
        'review_count',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_open_enrollment' => 'boolean',
            'gamification_settings' => 'array',
            'average_rating' => 'decimal:2',
            'review_count' => 'integer',
        ];
    }

    public function getTimelineAttribute($value): ?string
    {
        if (! $value) {
            return $value;
        }

        return trim(preg_replace('/\s*\(.*\)/', '', $value));
    }

    public function getCourseOwnerLabelAttribute(): string
    {
        $courseBy = trim((string) $this->course_by);

        if ($courseBy !== '') {
            return $courseBy;
        }

        return trim((string) config('app.name'));
    }

    public function getInstructorLabelAttribute(): string
    {
        if (! $this->relationLoaded('instructors')) {
            return 'TBA';
        }

        /** @var Collection<int, string> $names */
        $names = $this->instructors
            ->pluck('name')
            ->filter(static fn ($name) => trim((string) $name) !== '')
            ->values();

        return $names->isNotEmpty()
            ? $names->implode(' / ')
            : 'TBA';
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

    public function intakes(): HasMany
    {
        return $this->hasMany(CourseIntake::class);
    }

    public function activeIntake(): HasOne
    {
        return $this->hasOne(CourseIntake::class)
            ->where(function ($query) {
                $query->where('is_active', true)
                    ->orWhere('status', CourseIntake::STATUS_ACTIVE);
            })
            ->where('status', '!=', CourseIntake::STATUS_ARCHIVED)
            ->latest('start_date');
    }

    public function upcomingIntakes(): HasMany
    {
        return $this->hasMany(CourseIntake::class)
            ->where('status', CourseIntake::STATUS_UPCOMING)
            ->where('is_active', false)
            ->orderBy('start_date');
    }

    public function archivedIntakes(): HasMany
    {
        return $this->hasMany(CourseIntake::class)
            ->where(function ($query) {
                $query->where('status', CourseIntake::STATUS_ARCHIVED)
                    ->orWhereNotNull('archived_at');
            })
            ->latest('archived_at');
    }

    public function isOngoing(): bool
    {
        return ($this->offering_mode ?? 'once_off') === 'ongoing';
    }

    public function isOnceOff(): bool
    {
        return ! $this->isOngoing();
    }

    public function getActiveOrNextIntake(): ?CourseIntake
    {
        if (! $this->relationLoaded('intakes')) {
            $active = $this->activeIntake;
            if ($active) {
                return $active;
            }

            return $this->upcomingIntakes()->first();
        }

        $active = $this->intakes->first(fn (CourseIntake $i) => $i->is_active || $i->status === CourseIntake::STATUS_ACTIVE);
        if ($active && ! $active->isArchived()) {
            return $active;
        }

        return $this->intakes->where('status', CourseIntake::STATUS_UPCOMING)->sortBy('start_date')->first();
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(CourseRating::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getNumericFee(): float
    {
        $fees = trim((string) $this->fees);

        if ($fees === '') {
            return 0.0;
        }

        if (preg_match_all('/\d+(?:[.,]\d+)?/', $fees, $matches) !== false) {
            foreach ($matches[0] ?? [] as $rawAmount) {
                $normalized = (float) str_replace(',', '', (string) $rawAmount);
                if ($normalized > 0) {
                    return $normalized;
                }
            }
        }

        return 0.0;
    }

    public function getNumericFeeForLevel(?string $level = null): float
    {
        $defaultFee = $this->getNumericFee();

        if (! $level) {
            return $defaultFee;
        }

        $fees = $this->fees;

        if (empty($fees)) {
            return $defaultFee;
        }

        // If fees is a JSON string or array
        $parsed = is_string($fees) ? json_decode($fees, true) : $fees;

        if (is_array($parsed)) {
            // Check flat level map: ['Beginner' => 1200, ...]
            foreach ($parsed as $k => $v) {
                if (is_string($k) && strcasecmp(trim($k), trim($level)) === 0) {
                    $amount = is_numeric($v) ? (float) $v : (float) str_replace(',', '', (string) preg_replace('/[^\d.,]/', '', (string) $v));
                    if ($amount > 0) {
                        return $amount;
                    }
                }
            }

            // Check nested sections: ['group' => [['level' => 'Beginner', 'amount' => 600]], ...]
            foreach (['group', 'one_on_one', 'fees', 'levels'] as $section) {
                if (isset($parsed[$section]) && is_array($parsed[$section])) {
                    foreach ($parsed[$section] as $entry) {
                        if (is_array($entry) && isset($entry['level']) && strcasecmp(trim((string) $entry['level']), trim($level)) === 0) {
                            $rawAmount = $entry['amount'] ?? $entry['fee'] ?? null;
                            if ($rawAmount !== null) {
                                $amount = (float) str_replace(',', '', (string) preg_replace('/[^\d.,]/', '', (string) $rawAmount));
                                if ($amount > 0) {
                                    return $amount;
                                }
                            }
                        }
                    }
                }
            }

            // Check if sequential list of items: [['level' => 'Beginner', 'amount' => 1200]]
            foreach ($parsed as $entry) {
                if (is_array($entry) && isset($entry['level']) && strcasecmp(trim((string) $entry['level']), trim($level)) === 0) {
                    $rawAmount = $entry['amount'] ?? $entry['fee'] ?? null;
                    if ($rawAmount !== null) {
                        $amount = (float) str_replace(',', '', (string) preg_replace('/[^\d.,]/', '', (string) $rawAmount));
                        if ($amount > 0) {
                            return $amount;
                        }
                    }
                }
            }
        }

        // If fees is a string with lines like "Beginner: 1200" or "Beginner - K1,500"
        if (is_string($fees)) {
            $escapedLevel = preg_quote(trim($level), '/');
            if (preg_match('/(?:^|[\r\n;,|])\s*(?:level\s*[:\-]\s*)?' . $escapedLevel . '\s*[:\-]\s*(?:ZMW|K|USD|\$)?\s*([\d,]+(?:\.\d+)?)/i', $fees, $matches) === 1) {
                $amount = (float) str_replace(',', '', (string) $matches[1]);
                if ($amount > 0) {
                    return $amount;
                }
            }
        }

        return $defaultFee;
    }

    public function isPayable(?string $level = null): bool
    {
        return $this->getNumericFeeForLevel($level) > 0 || $this->getNumericFee() > 0;
    }

    public function getLevelFees(): array
    {
        $levels = ['Beginner', 'Intermediate', 'Advanced'];
        $result = [];
        $defaultFee = $this->getNumericFee();

        foreach ($levels as $lvl) {
            $fee = $this->getNumericFeeForLevel($lvl);
            $result[$lvl] = $fee > 0 ? $fee : ($defaultFee > 0 ? $defaultFee : 1500.00);
        }

        return $result;
    }

    public function averageRating(): float
    {
        if (array_key_exists('ratings_avg_rating', $this->getAttributes())) {
            return round((float) $this->ratings_avg_rating, 1);
        }

        return round((float) $this->ratings()->avg('rating'), 1);
    }

    public function ratingsCount(): int
    {
        if (array_key_exists('ratings_count', $this->getAttributes())) {
            return (int) $this->ratings_count;
        }

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

    public function claimItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ClaimItem::class);
    }

    public function courseGamificationRule(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CourseGamificationRule::class);
    }

    /**
     * Retrieve a course-specific gamification point or coin rule with fallback.
     */
    public function gamificationRule(string $key, int $default = 0): int
    {
        // 1. Dedicated CourseGamificationRule table
        $hasCustomRuleSet = CourseGamificationRule::query()->where('course_id', $this->id)->where('is_active', true)->exists();
        if ($hasCustomRuleSet) {
            $rule = CourseGamificationRule::getRuleForCourse($this, $key);
            if (! empty($rule['enabled'])) {
                if (str_ends_with($key, '_xp') && isset($rule['xp']) && $rule['xp'] > 0) {
                    return (int) $rule['xp'];
                }
                if (str_ends_with($key, '_coins') && isset($rule['coins']) && $rule['coins'] > 0) {
                    return (int) $rule['coins'];
                }
                if (! empty($rule['xp'])) {
                    return (int) $rule['xp'];
                }
            } elseif (isset($rule['enabled']) && ! $rule['enabled']) {
                return 0;
            }
        }

        // 2. Course JSON settings
        $settings = $this->gamification_settings;
        if (is_array($settings) && isset($settings[$key]) && is_numeric($settings[$key])) {
            return (int) $settings[$key];
        }

        // 3. Fallback check on Global / Default Matrix
        $rule = CourseGamificationRule::getRuleForCourse(null, $key);
        if (! empty($rule['enabled'])) {
            if (str_ends_with($key, '_xp') && isset($rule['xp']) && $rule['xp'] > 0) {
                return (int) $rule['xp'];
            }
            if (str_ends_with($key, '_coins') && isset($rule['coins']) && $rule['coins'] > 0) {
                return (int) $rule['coins'];
            }
            if (! empty($rule['xp'])) {
                return (int) $rule['xp'];
            }
        }

        return $default > 0 ? $default : 0;
    }
}
