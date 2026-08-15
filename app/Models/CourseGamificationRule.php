<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseGamificationRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'name',
        'rules',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'course_id' => 'integer',
            'rules' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function setRulesAttribute($value): void
    {
        if (is_array($value)) {
            if (array_is_list($value)) {
                $value = array_map(function ($row) {
                    if (is_array($row) && isset($row['xp'])) {
                        $row['coins'] = (int) round(((float) $row['xp']) * 0.30);
                    }
                    return $row;
                }, $value);
            } else {
                foreach ($value as $key => $item) {
                    if (is_array($item) && isset($item['xp'])) {
                        $value[$key]['coins'] = (int) round(((float) $item['xp']) * 0.30);
                    }
                }
            }
        }

        $this->attributes['rules'] = is_array($value) ? json_encode($value) : $value;
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Complete Point Earning Matrix matching the specification.
     * Spendable Coins (TC) default to 30% of XP Earned.
     *
     * @return array<string, array{category: string, label: string, xp: int, coins: int, limit: string, enabled: bool}>
     */
    public static function getDefaultMatrix(): array
    {
        return [
            // 1. Daily Login & Streak
            'daily_login' => [
                'category' => 'Daily Login & Streak',
                'label' => 'Daily Platform Login',
                'xp' => 5,
                'coins' => 2, // 30% of 5 = 1.5 -> 2
                'limit' => 'Once per calendar day',
                'enabled' => true,
            ],
            'streak_7' => [
                'category' => 'Daily Login & Streak',
                'label' => '7-Day Streak Bonus',
                'xp' => 50,
                'coins' => 15, // 30% of 50 = 15
                'limit' => 'Triggers every 7 consecutive days',
                'enabled' => true,
            ],
            'streak_30' => [
                'category' => 'Daily Login & Streak',
                'label' => '30-Day Streak Bonus',
                'xp' => 250,
                'coins' => 75, // 30% of 250 = 75
                'limit' => 'Triggers every 30 consecutive days',
                'enabled' => true,
            ],

            // 2. Course & Learning Material
            'video_completed' => [
                'category' => 'Course & Learning Material',
                'label' => 'Completing a Lesson/Session Video',
                'xp' => 10,
                'coins' => 3, // 30% of 10 = 3
                'limit' => 'Max 5 videos/day eligible for rewards',
                'enabled' => true,
            ],
            'material_read' => [
                'category' => 'Course & Learning Material',
                'label' => 'Reading Learning Material',
                'xp' => 5,
                'coins' => 2, // 30% of 5 = 1.5 -> 2
                'limit' => 'Must spend at least 3 minutes on page',
                'enabled' => true,
            ],
            'course_completion' => [
                'category' => 'Course & Learning Material',
                'label' => 'Course Completion (100%)',
                'xp' => 200,
                'coins' => 60, // 30% of 200 = 60
                'limit' => 'One-time per course',
                'enabled' => true,
            ],

            // 3. Quizzes & Assessments
            'quiz_attempt' => [
                'category' => 'Quizzes & Assessments',
                'label' => 'Attempting a Quiz',
                'xp' => 5,
                'coins' => 2, // 30% of 5 = 1.5 -> 2
                'limit' => 'Max 3 quiz attempts rewarded/day',
                'enabled' => true,
            ],
            'quiz_score_80' => [
                'category' => 'Quizzes & Assessments',
                'label' => 'Quiz Score 80%+',
                'xp' => 25,
                'coins' => 8, // 30% of 25 = 7.5 -> 8
                'limit' => 'First passing attempt only',
                'enabled' => true,
            ],
            'quiz_score_100' => [
                'category' => 'Quizzes & Assessments',
                'label' => 'Perfect Quiz Score (100%)',
                'xp' => 50,
                'coins' => 15, // 30% of 50 = 15
                'limit' => 'First perfect attempt only',
                'enabled' => true,
            ],
            'assessment_passed' => [
                'category' => 'Quizzes & Assessments',
                'label' => 'Passing Course Assessment',
                'xp' => 100,
                'coins' => 30, // 30% of 100 = 30
                'limit' => 'Verified by Instructor/System',
                'enabled' => true,
            ],

            // 4. Assignments
            'assignment_ontime' => [
                'category' => 'Assignments',
                'label' => 'Submitting Assignment On-time',
                'xp' => 30,
                'coins' => 9, // 30% of 30 = 9
                'limit' => 'Per valid assignment',
                'enabled' => true,
            ],
            'assignment_grade_a' => [
                'category' => 'Assignments',
                'label' => 'High Grade (Grade A / 90%+)',
                'xp' => 70,
                'coins' => 21, // 30% of 70 = 21
                'limit' => 'Awarded upon instructor grading',
                'enabled' => true,
            ],

            // 5. Community & Peer Engagement
            'hub_post_published' => [
                'category' => 'Community & Peer Engagement',
                'label' => 'Publishing Hub Post / Tutorial',
                'xp' => 15,
                'coins' => 5, // 30% of 15 = 4.5 -> 5
                'limit' => 'Subject to approval or min 150 words',
                'enabled' => true,
            ],
            'best_answer' => [
                'category' => 'Community & Peer Engagement',
                'label' => 'Answer Selected as "Best Answer"',
                'xp' => 30,
                'coins' => 9, // 30% of 30 = 9
                'limit' => 'Awarded when marked by post author',
                'enabled' => true,
            ],
            'reactions_10' => [
                'category' => 'Community & Peer Engagement',
                'label' => 'Receiving 10 Upvotes / Reactions',
                'xp' => 10,
                'coins' => 3, // 30% of 10 = 3
                'limit' => 'Cap at 50 TC per day from engagement',
                'enabled' => true,
            ],

            // 6. Feedback & Platform Support
            'course_rating' => [
                'category' => 'Feedback & Platform Support',
                'label' => 'Rating/Reviewing a Course',
                'xp' => 10,
                'coins' => 3, // 30% of 10 = 3
                'limit' => 'Requires minimum 50% course completion',
                'enabled' => true,
            ],
            'feedback_bug_report' => [
                'category' => 'Feedback & Platform Support',
                'label' => 'Helpful Feedback / Bug Report',
                'xp' => 50,
                'coins' => 15, // 30% of 50 = 15
                'limit' => 'Verified by Admin',
                'enabled' => true,
            ],
        ];
    }

    /**
     * Get effective rule matrix list for a course.
     *
     * @return array<int, array{activity_key: string, activity_name: string, category: string, xp: int, coins: int, limit: string, enabled: bool}>
     */
    public static function getEffectiveMatrixForCourse(?Course $course): array
    {
        if ($course) {
            $ruleSet = self::query()->where('course_id', $course->id)->where('is_active', true)->first();
            if ($ruleSet && is_array($ruleSet->rules) && ! empty($ruleSet->rules)) {
                $normalized = self::normalizeRulesForRepeater($ruleSet->rules);
                if (! empty($normalized)) {
                    return $normalized;
                }
            }
        }

        // Global Default
        $globalRuleSet = self::query()->whereNull('course_id')->where('is_active', true)->first();
        if ($globalRuleSet && is_array($globalRuleSet->rules) && ! empty($globalRuleSet->rules)) {
            $normalized = self::normalizeRulesForRepeater($globalRuleSet->rules);
            if (! empty($normalized)) {
                return $normalized;
            }
        }

        return self::getDefaultRepeaterRows();
    }

    /**
     * Get default repeater rows for the CRUD table.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getDefaultRepeaterRows(): array
    {
        $matrix = self::getDefaultMatrix();
        $rows = [];

        foreach ($matrix as $key => $meta) {
            $xp = (int) $meta['xp'];
            $coins = (int) round(((float) $xp) * 0.30);

            $rows[] = [
                'activity_key' => $key,
                'activity_name' => $meta['label'],
                'category' => $meta['category'],
                'xp' => $xp,
                'coins' => $coins,
                'limit' => $meta['limit'] ?? '',
                'enabled' => $meta['enabled'] ?? true,
            ];
        }

        return $rows;
    }

    /**
     * Normalize rules array into list of repeater rows.
     */
    public static function normalizeRulesForRepeater(?array $rules): array
    {
        if (empty($rules)) {
            return [];
        }

        if (array_is_list($rules)) {
            return array_map(function ($row) {
                if (! is_array($row)) {
                    return $row;
                }
                if (! isset($row['coins']) && isset($row['xp'])) {
                    $row['coins'] = (int) round(((float) $row['xp']) * 0.30);
                }
                return $row;
            }, $rules);
        }

        $matrix = self::getDefaultMatrix();
        $rows = [];
        foreach ($rules as $key => $item) {
            if (! is_array($item)) {
                continue;
            }
            $meta = $matrix[$key] ?? [];
            $xp = isset($item['xp']) ? (int) $item['xp'] : (int) ($meta['xp'] ?? 0);
            $coins = isset($item['coins']) ? (int) $item['coins'] : (int) round(((float) $xp) * 0.30);

            $rows[] = [
                'activity_key' => $key,
                'activity_name' => $item['activity_name'] ?? ($item['label'] ?? ($meta['label'] ?? ucfirst(str_replace('_', ' ', $key)))),
                'category' => $item['category'] ?? ($meta['category'] ?? 'Custom Actions'),
                'xp' => $xp,
                'coins' => $coins,
                'limit' => (string) ($item['limit'] ?? ($meta['limit'] ?? '')),
                'enabled' => isset($item['enabled']) ? (bool) $item['enabled'] : (bool) ($meta['enabled'] ?? true),
            ];
        }

        return $rows;
    }

    /**
     * Helper to find rule definition within raw rules data (supports repeater list or associative map).
     */
    public static function findRuleInArray(array $rulesData, string $key, string $normalizedKey): ?array
    {
        // 1. Direct associative check
        if (isset($rulesData[$normalizedKey]) && is_array($rulesData[$normalizedKey])) {
            return $rulesData[$normalizedKey];
        }
        if (isset($rulesData[$key]) && is_array($rulesData[$key])) {
            return $rulesData[$key];
        }

        // 2. Repeater list check
        foreach ($rulesData as $row) {
            if (! is_array($row)) {
                continue;
            }

            $rowKey = $row['activity_key'] ?? $row['key'] ?? null;
            $rowName = strtolower(trim((string) ($row['activity_name'] ?? $row['label'] ?? '')));

            if ($rowKey === $normalizedKey || $rowKey === $key) {
                return $row;
            }

            if ($rowName && (
                $rowName === strtolower($key) ||
                $rowName === strtolower($normalizedKey) ||
                $rowName === strtolower(str_replace('_', ' ', $key))
            )) {
                return $row;
            }
        }

        return null;
    }

    /**
     * Get effective rule values (XP, Coins, Enabled) for a given course and activity.
     *
     * @return array{xp: int, coins: int, enabled: bool, limit: string}
     */
    public static function getRuleForCourse(?Course $course, string $activityKey): array
    {
        $defaultMatrix = self::getDefaultMatrix();

        // Map common aliases
        $normalizedKey = match ($activityKey) {
            'quiz', 'quiz_xp', 'quiz_coins', 'quiz_passed' => 'quiz_score_80',
            'quiz_perfect' => 'quiz_score_100',
            'assignment', 'assignment_xp', 'assignment_coins' => 'assignment_ontime',
            'assessment', 'assessment_xp', 'assessment_coins' => 'assessment_passed',
            'course_completion_xp', 'course_completion_coins' => 'course_completion',
            'video_watched' => 'video_completed',
            'material_viewed' => 'material_read',
            default => $activityKey,
        };

        $fallback = $defaultMatrix[$normalizedKey] ?? $defaultMatrix[$activityKey] ?? null;

        if ($course) {
            $ruleSet = self::query()->where('course_id', $course->id)->where('is_active', true)->first();
            if ($ruleSet && is_array($ruleSet->rules)) {
                $custom = self::findRuleInArray($ruleSet->rules, $activityKey, $normalizedKey);
                if ($custom) {
                    $xp = isset($custom['xp']) && is_numeric($custom['xp']) ? (int) $custom['xp'] : (int) ($fallback['xp'] ?? 0);
                    $coins = isset($custom['coins']) && is_numeric($custom['coins']) ? (int) $custom['coins'] : (int) round(((float) $xp) * 0.30);
                    return [
                        'xp' => $xp,
                        'coins' => $coins,
                        'enabled' => isset($custom['enabled']) ? (bool) $custom['enabled'] : (bool) ($fallback['enabled'] ?? true),
                        'limit' => (string) ($custom['limit'] ?? ($fallback['limit'] ?? '')),
                    ];
                }
            }

            // Also check course->gamification_settings if set
            $settings = $course->gamification_settings;
            if (is_array($settings)) {
                $customXp = $settings[$activityKey] ?? $settings[$normalizedKey.'_xp'] ?? ($settings['quiz_xp'] ?? null);
                $customCoins = $settings[$activityKey] ?? $settings[$normalizedKey.'_coins'] ?? ($settings['quiz_coins'] ?? null);

                if (str_ends_with($activityKey, '_xp') && isset($settings[$activityKey])) {
                    $customXp = $settings[$activityKey];
                }
                if (str_ends_with($activityKey, '_coins') && isset($settings[$activityKey])) {
                    $customCoins = $settings[$activityKey];
                }

                if ($customXp !== null || $customCoins !== null) {
                    $xp = is_numeric($customXp) ? (int) $customXp : (int) ($fallback['xp'] ?? 0);
                    $coins = is_numeric($customCoins) ? (int) $customCoins : (int) round(((float) $xp) * 0.30);
                    return [
                        'xp' => $xp,
                        'coins' => $coins,
                        'enabled' => true,
                        'limit' => (string) ($fallback['limit'] ?? ''),
                    ];
                }
            }
        }

        // Check Global Default rule set
        $globalRuleSet = self::query()->whereNull('course_id')->where('is_active', true)->first();
        if ($globalRuleSet && is_array($globalRuleSet->rules)) {
            $custom = self::findRuleInArray($globalRuleSet->rules, $activityKey, $normalizedKey);
            if ($custom) {
                $xp = isset($custom['xp']) && is_numeric($custom['xp']) ? (int) $custom['xp'] : (int) ($fallback['xp'] ?? 0);
                $coins = isset($custom['coins']) && is_numeric($custom['coins']) ? (int) $custom['coins'] : (int) round(((float) $xp) * 0.30);
                return [
                    'xp' => $xp,
                    'coins' => $coins,
                    'enabled' => isset($custom['enabled']) ? (bool) $custom['enabled'] : (bool) ($fallback['enabled'] ?? true),
                    'limit' => (string) ($custom['limit'] ?? ($fallback['limit'] ?? '')),
                ];
            }
        }

        if ($fallback) {
            $xp = (int) $fallback['xp'];
            $coins = isset($fallback['coins']) ? (int) $fallback['coins'] : (int) round(((float) $xp) * 0.30);
            return [
                'xp' => $xp,
                'coins' => $coins,
                'enabled' => (bool) ($fallback['enabled'] ?? true),
                'limit' => (string) ($fallback['limit'] ?? ''),
            ];
        }

        return [
            'xp' => 0,
            'coins' => 0,
            'enabled' => true,
            'limit' => '',
        ];
    }
}
