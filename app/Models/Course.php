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

    public function students(): BelongsToMany
    {
        return $this->enrolledUsers();
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

    public function learningMaterials(): HasMany
    {
        return $this->materials();
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

    /**
     * Parse and extract all available fee options (mode/category, level, amount, duration).
     *
     * @return array<int, array{
     *     id: string,
     *     category: string,
     *     mode: string,
     *     mode_label: string,
     *     mode_badge: string,
     *     level: string,
     *     amount: float,
     *     formatted_amount: string,
     *     duration: ?string,
     *     highlight: string
     * }>
     */
    public function getFeeOptions(): array
    {
        $fees = $this->fees;
        $defaultDuration = $this->timeline ?: null;
        $parsed = is_string($fees) ? json_decode($fees, true) : $fees;
        $options = [];

        $normalizeCategory = static function (string $raw): array {
            $cat = strtolower(trim(str_replace(['-', ' '], '_', $raw)));
            if (in_array($cat, ['one_on_one', 'one2one', 'one_to_one', 'private', 'private_class', '1_1'], true)) {
                return ['one_on_one', 'One-on-One', '1:1 Focus', 'Personalized 1:1 mentorship & dedicated project guidance'];
            }
            if (in_array($cat, ['group', 'group_class', 'group_classes', 'class_group'], true)) {
                return ['group', 'Group Class', 'Best Value', 'Interactive group cohorts & collaborative exercises'];
            }
            return ['group', 'Group Class', 'Standard', 'Comprehensive curriculum & hands-on practical training'];
        };

        $normalizeLevel = static function (string $raw): string {
            $lvl = strtolower(trim($raw));
            if (str_contains($lvl, 'beginner')) return 'Beginner';
            if (str_contains($lvl, 'intermediate')) return 'Intermediate';
            if (str_contains($lvl, 'advanced')) return 'Advanced';
            return trim($raw) !== '' ? ucwords(trim($raw)) : 'Beginner';
        };

        $parseAmount = static function (mixed $raw): float {
            if (is_numeric($raw)) return (float) $raw;
            if (is_string($raw)) {
                $clean = (float) str_replace(',', '', (string) preg_replace('/[^\d.,]/', '', $raw));
                return $clean > 0 ? $clean : 0.0;
            }
            return 0.0;
        };

        if (is_array($parsed)) {
            // 1. Check categorized dictionary: ['group' => [...], 'one_on_one' => [...]]
            foreach (['group', 'one_on_one'] as $sectionKey) {
                if (isset($parsed[$sectionKey]) && is_array($parsed[$sectionKey])) {
                    [$catKey, $modeLabel, $modeBadge, $highlight] = $normalizeCategory($sectionKey);
                    foreach ($parsed[$sectionKey] as $entry) {
                        if (is_array($entry)) {
                            $level = $normalizeLevel((string) ($entry['level'] ?? 'Beginner'));
                            $amount = $parseAmount($entry['amount'] ?? $entry['fee'] ?? null);
                            $duration = trim((string) ($entry['duration'] ?? $defaultDuration));
                            if ($amount > 0) {
                                $options[] = [
                                    'id' => $catKey . '_' . strtolower($level),
                                    'category' => $catKey,
                                    'mode' => $catKey,
                                    'mode_label' => $modeLabel,
                                    'mode_badge' => $modeBadge,
                                    'level' => $level,
                                    'amount' => $amount,
                                    'formatted_amount' => 'ZMW ' . number_format($amount, 2),
                                    'duration' => $duration ?: null,
                                    'highlight' => $highlight,
                                ];
                            }
                        }
                    }
                }
            }

            // 2. Sequential list of items: [['category' => 'Group', 'level' => 'Beginner', 'amount' => 'K350']]
            if (empty($options) && array_is_list($parsed)) {
                foreach ($parsed as $entry) {
                    if (is_array($entry)) {
                        $rawCat = (string) ($entry['category'] ?? $entry['mode'] ?? $entry['type'] ?? 'group');
                        [$catKey, $modeLabel, $modeBadge, $highlight] = $normalizeCategory($rawCat);
                        $level = $normalizeLevel((string) ($entry['level'] ?? 'Beginner'));
                        $amount = $parseAmount($entry['amount'] ?? $entry['fee'] ?? null);
                        $duration = trim((string) ($entry['duration'] ?? $defaultDuration));
                        if ($amount > 0) {
                            $options[] = [
                                'id' => $catKey . '_' . strtolower($level),
                                'category' => $catKey,
                                'mode' => $catKey,
                                'mode_label' => $modeLabel,
                                'mode_badge' => $modeBadge,
                                'level' => $level,
                                'amount' => $amount,
                                'formatted_amount' => 'ZMW ' . number_format($amount, 2),
                                'duration' => $duration ?: null,
                                'highlight' => $highlight,
                            ];
                        }
                    }
                }
            }

            // 3. Flat map: ['Beginner' => 1200, 'Intermediate' => 1800]
            if (empty($options)) {
                foreach ($parsed as $k => $v) {
                    if (is_string($k) && ! in_array($k, ['group', 'one_on_one'], true)) {
                        $amount = $parseAmount($v);
                        if ($amount > 0) {
                            $level = $normalizeLevel($k);
                            [$catKey, $modeLabel, $modeBadge, $highlight] = $normalizeCategory('group');
                            $options[] = [
                                'id' => 'group_' . strtolower($level),
                                'category' => 'group',
                                'mode' => 'group',
                                'mode_label' => $modeLabel,
                                'mode_badge' => $modeBadge,
                                'level' => $level,
                                'amount' => $amount,
                                'formatted_amount' => 'ZMW ' . number_format($amount, 2),
                                'duration' => $defaultDuration,
                                'highlight' => $highlight,
                            ];
                        }
                    }
                }
            }
        }

        // 4. If fees is a string with lines
        if (empty($options) && is_string($fees) && trim($fees) !== '') {
            $lines = preg_split('/\R+/', trim($fees)) ?: [];
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') continue;

                $rawCat = str_contains(strtolower($line), 'one') || str_contains(strtolower($line), 'private') ? 'one_on_one' : 'group';
                [$catKey, $modeLabel, $modeBadge, $highlight] = $normalizeCategory($rawCat);

                if (preg_match_all('/\b(Beginner|Intermediate|Advanced)\b\s*[:\-]\s*([^()]+?)\s*(?:\(([^)]+)\))?(?=\s*(?:Beginner|Intermediate|Advanced)\s*[:\-]|$)/i', $line, $matches, PREG_SET_ORDER) > 0) {
                    foreach ($matches as $match) {
                        $level = $normalizeLevel((string) ($match[1] ?? ''));
                        $amount = $parseAmount($match[2] ?? 0);
                        $dur = trim((string) ($match[3] ?? $defaultDuration));
                        if ($amount > 0) {
                            $options[] = [
                                'id' => $catKey . '_' . strtolower($level),
                                'category' => $catKey,
                                'mode' => $catKey,
                                'mode_label' => $modeLabel,
                                'mode_badge' => $modeBadge,
                                'level' => $level,
                                'amount' => $amount,
                                'formatted_amount' => 'ZMW ' . number_format($amount, 2),
                                'duration' => $dur ?: null,
                                'highlight' => $highlight,
                            ];
                        }
                    }
                    continue;
                }

                if (preg_match('/\b(Beginner|Intermediate|Advanced)\b\s*[:\-]\s*(?:ZMW|K|USD|\$)?\s*([\d,]+(?:\.\d+)?)/i', $line, $match) === 1) {
                    $level = $normalizeLevel((string) $match[1]);
                    $amount = $parseAmount($match[2]);
                    if ($amount > 0) {
                        $options[] = [
                            'id' => $catKey . '_' . strtolower($level),
                            'category' => $catKey,
                            'mode' => $catKey,
                            'mode_label' => $modeLabel,
                            'mode_badge' => $modeBadge,
                            'level' => $level,
                            'amount' => $amount,
                            'formatted_amount' => 'ZMW ' . number_format($amount, 2),
                            'duration' => $defaultDuration,
                            'highlight' => $highlight,
                        ];
                    }
                }
            }
        }

        // 5. Fallback single flat fee
        if (empty($options)) {
            $defaultFee = $this->getNumericFee();
            if ($defaultFee > 0) {
                $options[] = [
                    'id' => 'group_beginner',
                    'category' => 'group',
                    'mode' => 'group',
                    'mode_label' => 'Standard',
                    'mode_badge' => 'Self Paced',
                    'level' => 'Beginner',
                    'amount' => $defaultFee,
                    'formatted_amount' => 'ZMW ' . number_format($defaultFee, 2),
                    'duration' => $defaultDuration,
                    'highlight' => 'Comprehensive curriculum & hands-on practical training',
                ];
            }
        }

        return $options;
    }

    /**
     * Check if course has multiple level and/or mode options.
     */
    public function hasMultipleFeeOptions(): bool
    {
        return count($this->getFeeOptions()) > 1;
    }

    /**
     * Get numeric fee for exact level and mode combination.
     */
    public function isFreeCourse(): bool
    {
        $rawFees = strtolower(trim((string) $this->fees));

        return $rawFees === '0' || $rawFees === '0.00' || $rawFees === '0.0' || $rawFees === 'free';
    }

    public function getNumericFeeForOption(?string $level = null, ?string $mode = null): float
    {
        if ($this->isFreeCourse()) {
            return 0.0;
        }

        $options = $this->getFeeOptions();

        if (empty($options)) {
            $fee = $this->getNumericFee();
            return $fee > 0 ? $fee : 1500.00;
        }

        $normMode = $mode ? strtolower(trim(str_replace(['-', ' '], '_', $mode))) : null;
        if ($normMode && in_array($normMode, ['one_on_one', 'one2one', '1_1', 'private'], true)) {
            $normMode = 'one_on_one';
        } elseif ($normMode && in_array($normMode, ['group', 'class'], true)) {
            $normMode = 'group';
        }

        $normLevel = $level ? strtolower(trim($level)) : null;

        // 1. Exact match on both Mode & Level
        if ($normMode && $normLevel) {
            foreach ($options as $opt) {
                if ($opt['category'] === $normMode && strtolower($opt['level']) === $normLevel) {
                    return (float) $opt['amount'];
                }
            }
        }

        // 2. Match on Mode only (if level not given or not found in that mode)
        if ($normMode) {
            foreach ($options as $opt) {
                if ($opt['category'] === $normMode) {
                    if ($normLevel && strtolower($opt['level']) === $normLevel) {
                        return (float) $opt['amount'];
                    }
                }
            }
            foreach ($options as $opt) {
                if ($opt['category'] === $normMode) {
                    return (float) $opt['amount'];
                }
            }
        }

        // 3. Match on Level only
        if ($normLevel) {
            foreach ($options as $opt) {
                if (strtolower($opt['level']) === $normLevel) {
                    return (float) $opt['amount'];
                }
            }
        }

        // 4. Default to first option amount or numeric fee
        return (float) ($options[0]['amount'] ?? ($this->getNumericFee() ?: 1500.00));
    }

    public function getNumericFeeForLevel(?string $level = null, ?string $mode = null): float
    {
        return $this->getNumericFeeForOption($level, $mode);
    }

    public function isPayable(?string $level = null, ?string $mode = null): bool
    {
        if ($this->isFreeCourse()) {
            return false;
        }

        return $this->getNumericFeeForOption($level, $mode) > 0 || $this->getNumericFee() > 0;
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
