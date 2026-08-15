<?php

namespace App\Filament\Resources\CourseGamificationRules\Schemas;

use App\Models\Course;
use App\Models\CourseGamificationRule;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CourseGamificationRuleForm
{
    public static function configure(Schema $schema, bool $isInstructor = false, array $instructorCourseIds = []): Schema
    {
        return $schema
            ->components([
                // 1. Rule Set Scope & Configuration Card (Top, Full Width & Compact)
                Section::make('Rule Set Scope & Configuration')
                    ->description('Define course assignment and activation status for this point rule matrix.')
                    ->columnSpanFull()
                    ->compact()
                    ->columns(2)
                    ->schema([
                        Select::make('course_id')
                            ->label('Course Scope')
                            ->placeholder($isInstructor ? 'Select Course' : '🌟 Global Platform Default (Applies to all courses without custom rules)')
                            ->options(function () use ($isInstructor, $instructorCourseIds): array {
                                $query = Course::query();
                                if ($isInstructor) {
                                    $query->whereIn('id', $instructorCourseIds);
                                }
                                return $query->orderBy('title')->pluck('title', 'id')->all();
                            })
                            ->searchable()
                            ->preload()
                            ->nullable(! $isInstructor)
                            ->required($isInstructor)
                            ->helperText($isInstructor ? 'Select which course to customize point rules for.' : 'Leave empty to set the Global Platform Default rule matrix.'),

                        TextInput::make('name')
                            ->label('Rule Set Name / Memo')
                            ->placeholder('e.g. Standard Point Matrix / AI Bootcamp High-XP Rules')
                            ->maxLength(255),

                        Toggle::make('is_active')
                            ->label('Active & Applied to Students')
                            ->default(true)
                            ->inline(false)
                            ->columnSpanFull(),
                    ]),

                // 2. Point & Reward Rules Matrix (CRUD Table) - Below Scope Card, Compact & Single Row
                Section::make('Point & Reward Rules Matrix (CRUD Table)')
                    ->description('Add specific activities to customize XP earned and spendable Thinker Coins (automatically calculated as 30% of XP). Each entry is arranged as a horizontal row.')
                    ->columnSpanFull()
                    ->collapsible()
                    ->compact()
                    ->schema([
                        Repeater::make('rules')
                            ->label('Configured Activity Rules')
                            ->addActionLabel('➕ Add Action / Activity Rule')
                            ->default([])
                            ->defaultItems(0)
                            ->compact()
                            ->formatStateUsing(fn ($state) => CourseGamificationRule::normalizeRulesForRepeater($state))
                            ->reorderable()
                            ->cloneable()
                            ->deletable()
                            ->collapsible()
                            ->collapseAllAction(fn ($action) => $action->label('Collapse All'))
                            ->expandAllAction(fn ($action) => $action->label('Expand All'))
                            ->itemLabel(function (array $state): ?string {
                                $name = $state['activity_name'] ?? null;
                                if (! $name && isset($state['activity_key'])) {
                                    $defaults = CourseGamificationRule::getDefaultMatrix();
                                    $name = $defaults[$state['activity_key']]['label'] ?? $state['activity_key'];
                                }
                                $category = $state['category'] ?? null;
                                $xp = isset($state['xp']) ? "+{$state['xp']} XP" : null;
                                $coins = isset($state['coins']) ? "🪙 {$state['coins']} TC" : null;

                                return collect([$name, $category, $xp, $coins])->filter()->implode(' • ');
                            })
                            ->columns([
                                'default' => 1,
                                'sm' => 2,
                                'md' => 4,
                                'lg' => 16,
                            ])
                            ->schema([
                                Select::make('activity_key')
                                    ->label('Action / Activity')
                                    ->placeholder('Select activity...')
                                    ->options(self::getActivityOptions())
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $defaults = CourseGamificationRule::getDefaultMatrix();
                                        if ($state && isset($defaults[$state])) {
                                            $def = $defaults[$state];
                                            $set('activity_name', $def['label']);
                                            $set('category', $def['category']);
                                            $set('xp', $def['xp']);
                                            $set('coins', (int) round(((float) $def['xp']) * 0.30));
                                            $set('limit', $def['limit'] ?? '');
                                            $set('enabled', $def['enabled'] ?? true);
                                        } elseif ($state === 'custom') {
                                            if (! $get('activity_name')) {
                                                $set('activity_name', 'Custom Activity');
                                            }
                                            if (! $get('category')) {
                                                $set('category', 'Custom Actions');
                                            }
                                            $xp = (int) ($get('xp') ?: 10);
                                            $set('xp', $xp);
                                            $set('coins', (int) round($xp * 0.30));
                                        }
                                    })
                                    ->columnSpan(['default' => 1, 'sm' => 2, 'lg' => 4]),

                                TextInput::make('activity_name')
                                    ->label('Activity Label')
                                    ->placeholder('Name of activity')
                                    ->required()
                                    ->columnSpan(['default' => 1, 'sm' => 2, 'lg' => 3]),

                                Select::make('category')
                                    ->label('Category')
                                    ->options([
                                        'Daily Login & Streak' => 'Daily Login & Streak',
                                        'Course & Learning Material' => 'Course & Learning Material',
                                        'Quizzes & Assessments' => 'Quizzes & Assessments',
                                        'Assignments' => 'Assignments',
                                        'Community & Peer Engagement' => 'Community & Peer Engagement',
                                        'Feedback & Platform Support' => 'Feedback & Platform Support',
                                        'Custom Actions' => 'Custom Actions',
                                    ])
                                    ->default('Daily Login & Streak')
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(['default' => 1, 'sm' => 2, 'lg' => 2]),

                                TextInput::make('xp')
                                    ->label('XP Earned')
                                    ->numeric()
                                    ->minValue(0)
                                    ->suffix('XP')
                                    ->required()
                                    ->live(debounce: 250)
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $xp = (float) ($state ?? 0);
                                        $set('coins', (int) round($xp * 0.30));
                                    })
                                    ->columnSpan(['default' => 1, 'sm' => 1, 'lg' => 2]),

                                TextInput::make('coins')
                                    ->label('Coins (30%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('🪙')
                                    ->required()
                                    ->readOnly()
                                    ->dehydrated()
                                    ->extraInputAttributes([
                                        'class' => 'font-semibold text-amber-600 dark:text-amber-400 bg-gray-50 dark:bg-gray-800 cursor-not-allowed text-center',
                                    ])
                                    ->columnSpan(['default' => 1, 'sm' => 1, 'lg' => 2]),

                                TextInput::make('limit')
                                    ->label('Constraints')
                                    ->placeholder('e.g. Once/day')
                                    ->columnSpan(['default' => 1, 'sm' => 2, 'lg' => 2]),

                                Toggle::make('enabled')
                                    ->label('Active')
                                    ->default(true)
                                    ->inline(false)
                                    ->columnSpan(['default' => 1, 'sm' => 1, 'lg' => 1]),
                            ]),
                    ]),
            ]);
    }

    public static function getActivityOptions(): array
    {
        return [
            'Daily Login & Streak' => [
                'daily_login' => 'Daily Platform Login',
                'streak_7' => '7-Day Streak Bonus',
                'streak_30' => '30-Day Streak Bonus',
            ],
            'Course & Learning Material' => [
                'video_completed' => 'Completing a Lesson/Session Video',
                'material_read' => 'Reading Learning Material',
                'course_completion' => 'Course Completion (100%)',
            ],
            'Quizzes & Assessments' => [
                'quiz_attempt' => 'Attempting a Quiz',
                'quiz_score_80' => 'Quiz Score 80%+',
                'quiz_score_100' => 'Perfect Quiz Score (100%)',
                'assessment_passed' => 'Passing Course Assessment',
            ],
            'Assignments' => [
                'assignment_ontime' => 'Submitting Assignment On-time',
                'assignment_grade_a' => 'High Grade (Grade A / 90%+)',
            ],
            'Community & Peer Engagement' => [
                'hub_post_published' => 'Publishing Hub Post / Tutorial',
                'best_answer' => 'Answer Selected as "Best Answer"',
                'reactions_10' => 'Receiving 10 Upvotes / Reactions',
            ],
            'Feedback & Platform Support' => [
                'course_rating' => 'Rating/Reviewing a Course',
                'feedback_bug_report' => 'Helpful Feedback / Bug Report',
            ],
            'Custom Actions' => [
                'custom' => '➕ Add Custom Action / Activity',
            ],
        ];
    }
}
