<?php

namespace App\Filament\Resources\Courses\Schemas;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Course Studio')
                    ->tabs([
                        Tab::make('Overview & Details')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make('Course Identity & Classification')
                                    ->description('Essential identification, catalog code, and offering structure.')
                                    ->icon('heroicon-o-identification')
                                    ->schema([
                                        TextInput::make('title')
                                            ->required()
                                            ->placeholder('e.g. Full-Stack Web Development Mastery')
                                            ->maxLength(255)
                                            ->columnSpan(['default' => 1, 'sm' => 2]),

                                        TextInput::make('code')
                                            ->required()
                                            ->placeholder('e.g. CS-101')
                                            ->maxLength(50)
                                            ->helperText('Unique catalog code or course reference.'),

                                        TextInput::make('course_by')
                                            ->label('Course By')
                                            ->placeholder('think.er HUB / Organization / Individual')
                                            ->maxLength(255),

                                        TextInput::make('timeline')
                                            ->label('Timeline & Pacing')
                                            ->placeholder('e.g. 8 Weeks (approx. 4-5 hours/week)')
                                            ->helperText('Estimated commitment required for student completion.')
                                            ->maxLength(255),

                                        Select::make('offering_mode')
                                            ->label('Offering Structure')
                                            ->options([
                                                'once_off' => 'Once-off (Single Instance / Self-Paced)',
                                                'ongoing' => 'Ongoing (Intakes / Classes / Cohorts)',
                                            ])
                                            ->default('once_off')
                                            ->helperText('Ongoing courses feature scheduled Intakes/Classes with start/end dates.')
                                            ->required(),
                                    ])
                                    ->columns(['default' => 1, 'sm' => 2, 'lg' => 3]),

                                Section::make('Course Media & Visual Cover')
                                    ->description('Cover visual showcased across public cards, course details, and group chats.')
                                    ->icon('heroicon-o-photo')
                                    ->schema([
                                        FileUpload::make('image_path')
                                            ->label('Cover Image')
                                            ->disk('public')
                                            ->directory('course-images')
                                            ->image()
                                            ->imageEditor()
                                            ->maxSize(2048)
                                            ->helperText('Recommended 16:9 ratio. JPG, PNG or WebP up to 2MB.')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Narrative & Learning Outcomes')
                                    ->description('Set clear expectations, prerequisites, and tangible takeaways.')
                                    ->icon('heroicon-o-sparkles')
                                    ->schema([
                                        Textarea::make('overview')
                                            ->label('Course Overview / Teaser')
                                            ->helperText('High-level introduction shown in the course preview modal.')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        Textarea::make('description')
                                            ->label('Detailed Description')
                                            ->rows(4)
                                            ->columnSpanFull(),
                                        Textarea::make('requirements')
                                            ->label('Requirements & Prerequisites')
                                            ->helperText('Add each requirement on a new line.')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        Textarea::make('key_outcome')
                                            ->label('Key Learning Outcomes')
                                            ->helperText('Summarize expected competencies gained after completion.')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Publishing & Enrollment Controls')
                                    ->description('Control live catalog visibility, enrollment gates, and student privacy.')
                                    ->icon('heroicon-o-globe-alt')
                                    ->schema([
                                        Toggle::make('is_active')
                                            ->label('Active / Published')
                                            ->helperText(fn () => auth()->user()?->isAdmin() ? 'Course is active and published to the public catalog.' : 'Requires Admin approval before becoming active and published.')
                                            ->disabled(fn () => ! (bool) auth()->user()?->isAdmin())
                                            ->dehydrated(fn () => (bool) auth()->user()?->isAdmin())
                                            ->default(false),

                                        Toggle::make('is_open_enrollment')
                                            ->label('Open For Public Enrollment')
                                            ->helperText('Turn off to lock enrollment to selected participants only.')
                                            ->default(true)
                                            ->live()
                                            ->required(),

                                        Toggle::make('is_for_kids')
                                            ->label('Designated for Kids (Parent Supervised)')
                                            ->helperText('Protects child privacy: enrolled kids are not visible to the general public community.')
                                            ->default(false),

                                        Select::make('selected_participant_ids')
                                            ->label('Restricted Students')
                                            ->helperText('Only these participants can enroll when public enrollment is locked.')
                                            ->options(fn (): array => User::query()
                                                ->where('role', 'student')
                                                ->orderBy('name')
                                                ->pluck('name', 'id')
                                                ->all())
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->visible(fn (callable $get): bool => ! (bool) $get('is_open_enrollment'))
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(['default' => 1, 'sm' => 3]),
                            ]),

                                    Tab::make('Curriculum & Path')
                                        ->icon('heroicon-o-academic-cap')
                                        ->schema([
                                            Section::make('Sequential Learning Path')
                                                ->description('Organize curriculum modules, sequential sections, and lessons (Videos, Readings, Quizzes, Assignments).')
                                                ->icon('heroicon-o-queue-list')
                                                ->schema([
                                                    Repeater::make('sections')
                                                        ->relationship('sections')
                                                        ->orderColumn('order_column')
                                                        ->label('Curriculum Sections')
                                                        ->helperText('Define sections and ordered lessons (Video, Reading, Quiz, Task/Assignment).')
                                                        ->schema([
                                                            TextInput::make('title')->required()->label('Section Title'),

                                                            Repeater::make('lessons')
                                                                ->relationship('lessons')
                                                                ->orderColumn('order_column')
                                                                ->label('Lessons')
                                                                ->schema([
                                                                    TextInput::make('title')->required()->label('Lesson Title'),

                                                                    Select::make('type')
                                                                        ->options([
                                                                            'video' => 'Video',
                                                                            'reading' => 'Reading Article',
                                                                            'quiz' => 'Quiz',
                                                                            'assignment' => 'Task / Assignment',
                                                                        ])
                                                                        ->reactive()
                                                                        ->required(),

                                                                    // Displayed conditionally for Reading
                                                                    RichEditor::make('reading_body')
                                                                        ->visible(fn ($get) => $get('type') === 'reading')
                                                                        ->columnSpanFull(),

                                                                    // Polymorphic relationship link for quizzes/assignments/videos
                                                                    MorphToSelect::make('content')
                                                                        ->types([
                                                                            MorphToSelect\Type::make(\App\Models\ResourceVideo::class)->titleAttribute('title'),
                                                                            MorphToSelect\Type::make(\App\Models\Quiz::class)->titleAttribute('title'),
                                                                            MorphToSelect\Type::make(\App\Models\Assignment::class)->titleAttribute('name'),
                                                                        ])
                                                                        ->searchable()
                                                                        ->preload()
                                                                        ->visible(fn ($get) => in_array($get('type'), ['video', 'quiz', 'assignment']))
                                                                        ->columnSpanFull(),
                                                                ])
                                                                ->collapsible()
                                                                ->defaultItems(1),
                                                        ])
                                                        ->collapsible()
                                                        ->defaultItems(0)
                                                        ->columnSpanFull(),
                                                ]),
                                        ]),

                                    Tab::make('Tuition & Levels')
                                        ->icon('heroicon-o-banknotes')
                                        ->schema([
                                            Section::make('Tuition & Fee Structure')
                                                ->description('Manage fee tiers across categories (One-On-One, Group, Self-Paced) and proficiency levels.')
                                                ->icon('heroicon-o-currency-dollar')
                                                ->schema([
                                                    Repeater::make('fees')
                                                        ->label('Fees')
                                                        ->helperText('Add fee entries with +. Categories: One-On-One, Group, or Self-Paced; levels: Beginner, Intermediate, Advanced, Advanced (Beginner to Advanced), Advanced (Intermediate to Advanced), or Intermediate (Beginner to Intermediate).')
                                                        ->schema([
                                                            Select::make('category')
                                                                ->options([
                                                                    'one_on_one' => 'One-On-One',
                                                                    'group' => 'Group',
                                                                    'self_paced' => 'Self-Paced',
                                                                ])
                                                                ->required(),
                                                            Select::make('level')
                                                                ->options(self::getLevelOptions())
                                                                ->required(),
                                                            TextInput::make('amount')
                                                                ->required()
                                                                ->placeholder('K450'),
                                                            TextInput::make('duration')
                                                                ->placeholder('6 Weeks'),
                                                        ])
                                                        ->columns(['default' => 1, 'sm' => 2, 'lg' => 4])
                                                        ->itemLabel(fn (array $state): string => trim(sprintf(
                                                            '%s - %s (%s)',
                                                            match ($state['category'] ?? 'one_on_one') {
                                                                'group' => 'Group',
                                                                'self_paced' => 'Self-Paced',
                                                                default => 'One-On-One',
                                                            },
                                                            $state['level'] ?? 'Level',
                                                            $state['amount'] ?? '-',
                                                        )))
                                                        ->compact()
                                                        ->collapsible()
                                                        ->collapsed()
                                                        ->reorderable(false)
                                                        ->cloneable(false)
                                                        ->defaultItems(0)
                                                        ->addAction(fn (Action $action): Action => $action
                                                            ->label('Add fee entry')
                                                            ->icon('heroicon-o-plus')
                                                            ->color('primary')
                                                            ->modalHeading('Add Fee Entry')
                                                            ->modalSubmitActionLabel('Add Entry')
                                                            ->form([
                                                                Select::make('category')
                                                                    ->options([
                                                                        'one_on_one' => 'One-On-One',
                                                                        'group' => 'Group',
                                                                        'self_paced' => 'Self-Paced',
                                                                    ])
                                                                    ->required(),
                                                                Select::make('level')
                                                                    ->options(self::getLevelOptions())
                                                                    ->required(),
                                                                TextInput::make('amount')
                                                                    ->required()
                                                                    ->placeholder('K450'),
                                                                TextInput::make('duration')
                                                                    ->placeholder('6 Weeks'),
                                                            ])
                                                            ->action(function (array $data, Repeater $component): void {
                                                                $items = $component->getState() ?? [];
                                                                $items[(string) Str::uuid()] = [
                                                                    'category' => self::normalizeCategory((string) ($data['category'] ?? 'one_on_one')),
                                                                    'level' => self::normalizeLevel((string) ($data['level'] ?? '')),
                                                                    'amount' => trim((string) ($data['amount'] ?? '')),
                                                                    'duration' => trim((string) ($data['duration'] ?? '')),
                                                                ];

                                                                $component->state($items);
                                                                $component->callAfterStateUpdated();
                                                            }))
                                                        ->afterStateHydrated(function (Repeater $component, mixed $state): void {
                                                            $component->state(self::parseFeesState($state));
                                                        })
                                                        ->columnSpanFull(),
                                                ]),

                                            Section::make('Skill Level Progression Roadmap')
                                                ->description('Define what students learn and master across foundational, intermediate, and advanced progression tracks.')
                                                ->icon('heroicon-o-chart-bar')
                                                ->schema([
                                                    Placeholder::make('roadmap_explanation')
                                                        ->label('')
                                                        ->content(new \Illuminate\Support\HtmlString('
                                                            <div class="rounded-xl border border-teal-200 bg-teal-50/70 p-4 text-xs text-slate-700 space-y-3 dark:border-teal-900/50 dark:bg-teal-950/20 dark:text-slate-200">
                                                                <div class="flex items-center gap-2 font-bold text-teal-900 dark:text-teal-300 text-sm">
                                                                    <svg class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                                    Skill Level Progression Roadmap Framework & Guidelines
                                                                </div>
                                                                <p class="leading-relaxed text-slate-600 dark:text-slate-300">
                                                                    Map out clear milestone competencies, tools, and real-world project outcomes for each level offered in your course. Use the detailed guidelines below:
                                                                </p>
                                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 pt-1">
                                                                    <div class="rounded-lg border border-teal-100 bg-white p-3 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                                                                        <span class="inline-block px-2 py-0.5 text-[11px] font-bold rounded-md bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 mb-1">1. Beginner</span>
                                                                        <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-snug"><strong>Target:</strong> Absolute beginners with zero prior background.<br><strong>Focus:</strong> Core fundamentals, syntax, terminology, setup of tools, and guided basic exercises.</p>
                                                                    </div>
                                                                    <div class="rounded-lg border border-teal-100 bg-white p-3 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                                                                        <span class="inline-block px-2 py-0.5 text-[11px] font-bold rounded-md bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 mb-1">2. Intermediate</span>
                                                                        <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-snug"><strong>Target:</strong> Learners with foundational knowledge.<br><strong>Focus:</strong> Practical problem-solving, modular code, APIs, database integration, and independent mini-projects.</p>
                                                                    </div>
                                                                    <div class="rounded-lg border border-teal-100 bg-white p-3 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                                                                        <span class="inline-block px-2 py-0.5 text-[11px] font-bold rounded-md bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300 mb-1">3. Advanced</span>
                                                                        <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-snug"><strong>Target:</strong> Experienced practitioners.<br><strong>Focus:</strong> Complex architecture, performance optimization, security, CI/CD, and enterprise-grade delivery.</p>
                                                                    </div>
                                                                    <div class="rounded-lg border border-teal-100 bg-white p-3 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                                                                        <span class="inline-block px-2 py-0.5 text-[11px] font-bold rounded-md bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-300 mb-1">4. Advanced (Beginner to Advanced)</span>
                                                                        <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-snug"><strong>Zero-to-Hero Track:</strong> Comprehensive journey taking learners all the way from ground zero through advanced mastery.</p>
                                                                    </div>
                                                                    <div class="rounded-lg border border-teal-100 bg-white p-3 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                                                                        <span class="inline-block px-2 py-0.5 text-[11px] font-bold rounded-md bg-indigo-100 text-indigo-900 dark:bg-indigo-950 dark:text-indigo-300 mb-1">5. Advanced (Intermediate to Advanced)</span>
                                                                        <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-snug"><strong>Specialist Track:</strong> Skips beginner setup to accelerate skilled learners into senior/lead specialist capabilities.</p>
                                                                    </div>
                                                                    <div class="rounded-lg border border-teal-100 bg-white p-3 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                                                                        <span class="inline-block px-2 py-0.5 text-[11px] font-bold rounded-md bg-teal-100 text-teal-900 dark:bg-teal-950 dark:text-teal-300 mb-1">6. Intermediate (Beginner to Intermediate)</span>
                                                                        <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-snug"><strong>Bridge Track:</strong> Guides novices through core principles into confident, autonomous, and job-ready practical execution.</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        '))
                                                        ->columnSpanFull(),

                                                    Repeater::make('level_progression')
                                                        ->label('Level Progression')
                                                        ->helperText('Add each level with + and provide detailed progression milestones, technologies, and outcomes.')
                                                        ->schema([
                                                            Select::make('level')
                                                                ->options(self::getLevelOptions())
                                                                ->required(),
                                                            Textarea::make('details')
                                                                ->rows(3)
                                                                ->placeholder('e.g. Master fundamental syntax, environment setup, and complete 3 guided hands-on mini projects.')
                                                                ->required(),
                                                        ])
                                                        ->columns(['default' => 1, 'md' => 2])
                                                        ->itemLabel(fn (array $state): string => (string) ($state['level'] ?? 'Level'))
                                                        ->compact()
                                                        ->collapsible()
                                                        ->collapsed()
                                                        ->reorderable(false)
                                                        ->cloneable(false)
                                                        ->defaultItems(0)
                                                        ->addAction(fn (Action $action): Action => $action
                                                            ->label('Add level entry')
                                                            ->icon('heroicon-o-plus')
                                                            ->color('primary')
                                                            ->modalHeading('Add Level Progression Entry')
                                                            ->modalSubmitActionLabel('Add Entry')
                                                            ->form([
                                                                Select::make('level')
                                                                    ->options(self::getLevelOptions())
                                                                    ->required(),
                                                                Textarea::make('details')
                                                                    ->rows(3)
                                                                    ->placeholder('e.g. Master fundamental syntax, environment setup, and complete 3 guided hands-on mini projects.')
                                                                    ->required(),
                                                            ])
                                                            ->action(function (array $data, Repeater $component): void {
                                                                $items = $component->getState() ?? [];
                                                                $items[(string) Str::uuid()] = [
                                                                    'level' => self::normalizeLevel((string) ($data['level'] ?? '')),
                                                                    'details' => trim((string) ($data['details'] ?? '')),
                                                                ];

                                                                $component->state($items);
                                                                $component->callAfterStateUpdated();
                                                            }))
                                                        ->afterStateHydrated(function (Repeater $component, mixed $state): void {
                                                            $component->state(self::parseLevelProgressionState($state));
                                                        })
                                                        ->columnSpanFull(),
                                                ]),
                                        ]),
                                ])
                                ->columnSpanFull(),
                        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function prepareDataForFill(array $data): array
    {
        $data['offering_mode'] = $data['offering_mode'] ?? 'once_off';
        $data['fees'] = self::parseFeesState($data['fees'] ?? null);
        $data['level_progression'] = self::parseLevelProgressionState($data['level_progression'] ?? null);

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function prepareDataForSave(array $data): array
    {
        $data['offering_mode'] = $data['offering_mode'] ?? 'once_off';
        $courseBy = trim((string) ($data['course_by'] ?? ''));
        $data['course_by'] = $courseBy !== '' ? $courseBy : null;
        $data['fees'] = self::serializeFeesState($data['fees'] ?? null);
        $data['level_progression'] = self::serializeLevelProgressionState($data['level_progression'] ?? null);

        return $data;
    }

    /**
     * @return array<int, array{category: string, level: string, amount: string, duration: string}>
     */
    private static function parseFeesState(mixed $state): array
    {
        if ($state === null) {
            return [];
        }

        if (is_array($state)) {
            return self::normalizeFeeEntries($state);
        }

        if (! is_string($state)) {
            return [];
        }

        $trimmed = trim($state);

        if ($trimmed === '') {
            return [];
        }

        $decoded = self::decodeJson($trimmed);

        if (is_array($decoded)) {
            return self::normalizeFeeEntries($decoded);
        }

        $entries = [];

        foreach (preg_split('/\R+/', $trimmed) ?: [] as $line) {
            $entries = [...$entries, ...self::parseFeeLineEntries((string) $line)];
        }

        return $entries;
    }

    private static function serializeFeesState(mixed $state): ?string
    {
        if (! is_array($state)) {
            return null;
        }

        $entries = self::normalizeFeeEntries($state);

        if ($entries === []) {
            return null;
        }

        $grouped = [
            'one_on_one' => [],
            'group' => [],
            'self_paced' => [],
        ];

        foreach ($entries as $entry) {
            $category = self::normalizeCategory($entry['category']);

            if (! array_key_exists($category, $grouped)) {
                continue;
            }

            $grouped[$category][] = [
                'level' => $entry['level'],
                'amount' => $entry['amount'],
                'duration' => $entry['duration'],
            ];
        }

        return json_encode($grouped, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: null;
    }

    /**
     * @param array<int|string, mixed> $state
     * @return array<int, array{category: string, level: string, amount: string, duration: string}>
     */
    private static function normalizeFeeEntries(array $state): array
    {
        $entries = [];

        if (array_key_exists('one_on_one', $state) || array_key_exists('group', $state) || array_key_exists('self_paced', $state)) {
            foreach (['one_on_one', 'group', 'self_paced'] as $category) {
                $rows = $state[$category] ?? [];

                if (! is_array($rows)) {
                    continue;
                }

                foreach ($rows as $row) {
                    if (! is_array($row)) {
                        continue;
                    }

                    $entries[] = [
                        'category' => $category,
                        'level' => self::normalizeLevel((string) ($row['level'] ?? '')),
                        'amount' => trim((string) ($row['amount'] ?? '')),
                        'duration' => trim((string) ($row['duration'] ?? '')),
                    ];
                }
            }

            return array_values(array_filter($entries, fn (array $entry): bool => $entry['level'] !== '' || $entry['amount'] !== '' || $entry['duration'] !== ''));
        }

        foreach ($state as $row) {
            if (is_string($row)) {
                $entries = [...$entries, ...self::parseFeeLineEntries($row)];

                continue;
            }

            if (! is_array($row)) {
                continue;
            }

            $entries[] = [
                'category' => self::normalizeCategory((string) ($row['category'] ?? $row['mode'] ?? $row['type'] ?? 'one_on_one')),
                'level' => self::normalizeLevel((string) ($row['level'] ?? '')),
                'amount' => trim((string) ($row['amount'] ?? '')),
                'duration' => trim((string) ($row['duration'] ?? '')),
            ];
        }

        return array_values(array_filter($entries, fn (array $entry): bool => $entry['level'] !== '' || $entry['amount'] !== '' || $entry['duration'] !== ''));
    }

    /**
     * @return array<int, array{category: string, level: string, amount: string, duration: string}>
     */
    private static function parseFeeLineEntries(string $line): array
    {
        $line = trim($line);

        if ($line === '') {
            return [];
        }

        $lowerLine = strtolower($line);
        if (str_contains($lowerLine, 'self') || str_contains($lowerLine, 'paced')) {
            $category = 'self_paced';
        } elseif (str_contains($lowerLine, 'group')) {
            $category = 'group';
        } else {
            $category = 'one_on_one';
        }

        $normalizedLine = trim((string) preg_replace('/^(one\s*[-\s:]?\s*on\s*[-\s:]?\s*one|1\s*[:x]\s*1|private|group|self\s*[-:]?\s*paced)\s*(?:[:\-|]\s*)?/i', '', $line));
        $normalizedLine = $normalizedLine === '' ? $line : $normalizedLine;
        $compactLine = trim((string) preg_replace('/\s+/', ' ', $normalizedLine));

        $levelPattern = 'Advanced\s*\(\s*Beginner\s+to\s+Advanced\s*\)|Advanced\s*\(\s*Intermediate\s+to\s+Advanced\s*\)|Intermediate\s*\(\s*Beginner\s+to\s+Intermediate\s*\)|Beginner|Intermediate|Advanced';

        $multiMatches = [];
        if (preg_match_all('/(' . $levelPattern . ')\s*[:\-]\s*([^()]+?)\s*(?:\(([^)]+)\))?(?=\s*(?:' . $levelPattern . ')\s*[:\-]|$)/i', $compactLine, $multiMatches, PREG_SET_ORDER) > 0) {
            $entries = [];

            foreach ($multiMatches as $match) {
                $entries[] = [
                    'category' => $category,
                    'level' => self::normalizeLevel((string) ($match[1] ?? '')),
                    'amount' => trim((string) ($match[2] ?? '')),
                    'duration' => trim((string) ($match[3] ?? '')),
                ];
            }

            return array_values(array_filter($entries, fn (array $entry): bool => $entry['level'] !== '' || $entry['amount'] !== '' || $entry['duration'] !== ''));
        }

        if (preg_match('/(' . $levelPattern . ')\s*[:\-]\s*([^()]+?)\s*(?:\(([^)]+)\))?$/i', $normalizedLine, $match) === 1) {
            return [[
                'category' => $category,
                'level' => self::normalizeLevel((string) ($match[1] ?? '')),
                'amount' => trim((string) ($match[2] ?? '')),
                'duration' => trim((string) ($match[3] ?? '')),
            ]];
        }

        if (preg_match('/^([^:()|]+?)\s*:\s*([^()]+?)\s*(?:\(([^)]+)\))?$/', $normalizedLine, $match) === 1) {
            return [[
                'category' => $category,
                'level' => self::normalizeLevel((string) ($match[1] ?? '')),
                'amount' => trim((string) ($match[2] ?? '')),
                'duration' => trim((string) ($match[3] ?? '')),
            ]];
        }

        return [[
            'category' => $category,
            'level' => self::normalizeLevel($normalizedLine),
            'amount' => '',
            'duration' => '',
        ]];
    }

    /**
     * @return array<int, array{level: string, details: string}>
     */
    private static function parseLevelProgressionState(mixed $state): array
    {
        if ($state === null) {
            return [];
        }

        if (is_array($state)) {
            return self::normalizeLevelProgressionEntries($state);
        }

        if (! is_string($state)) {
            return [];
        }

        $trimmed = trim($state);

        if ($trimmed === '') {
            return [];
        }

        $decoded = self::decodeJson($trimmed);

        if (is_array($decoded)) {
            return self::normalizeLevelProgressionEntries($decoded);
        }

        $entries = [];
        $defaultLevels = [
            'Beginner',
            'Intermediate',
            'Advanced',
            'Advanced (Beginner to Advanced)',
            'Advanced (Intermediate to Advanced)',
            'Intermediate (Beginner to Intermediate)',
        ];
        $levelPattern = 'Advanced\s*\(\s*Beginner\s+to\s+Advanced\s*\)|Advanced\s*\(\s*Intermediate\s+to\s+Advanced\s*\)|Intermediate\s*\(\s*Beginner\s+to\s+Intermediate\s*\)|Beginner|Intermediate|Advanced';
        $index = 0;

        foreach (preg_split('/\R+/', $trimmed) ?: [] as $line) {
            $line = trim((string) $line);

            if ($line === '') {
                continue;
            }

            $compactLine = trim((string) preg_replace('/\s+/', ' ', $line));
            $multiMatches = [];

            if (preg_match_all('/(' . $levelPattern . ')\s*[:\-]\s*([\s\S]*?)(?=\s*(?:' . $levelPattern . ')\s*[:\-]|$)/i', $compactLine, $multiMatches, PREG_SET_ORDER) > 0) {
                foreach ($multiMatches as $match) {
                    $entries[] = [
                        'level' => self::normalizeLevel((string) ($match[1] ?? '')),
                        'details' => trim((string) ($match[2] ?? '')),
                    ];
                }

                continue;
            }

            if (str_contains($line, ':')) {
                [$level, $details] = array_pad(explode(':', $line, 2), 2, '');
                $entries[] = [
                    'level' => self::normalizeLevel((string) $level) ?: ($defaultLevels[$index] ?? 'Beginner'),
                    'details' => trim((string) $details),
                ];
            } else {
                $entries[] = [
                    'level' => $defaultLevels[$index] ?? 'Beginner',
                    'details' => $line,
                ];
            }

            $index++;
        }

        return $entries;
    }

    private static function serializeLevelProgressionState(mixed $state): ?string
    {
        if (! is_array($state)) {
            return null;
        }

        $entries = self::normalizeLevelProgressionEntries($state);

        if ($entries === []) {
            return null;
        }

        return json_encode($entries, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: null;
    }

    /**
     * @param array<int|string, mixed> $state
     * @return array<int, array{level: string, details: string}>
     */
    private static function normalizeLevelProgressionEntries(array $state): array
    {
        $entries = [];

        foreach ($state as $row) {
            if (is_string($row)) {
                $row = ['details' => $row];
            }

            if (! is_array($row)) {
                continue;
            }

            $entries[] = [
                'level' => self::normalizeLevel((string) ($row['level'] ?? '')),
                'details' => trim((string) ($row['details'] ?? '')),
            ];
        }

        return array_values(array_filter($entries, fn (array $entry): bool => $entry['level'] !== '' || $entry['details'] !== ''));
    }

    private static function normalizeCategory(string $value): string
    {
        $normalized = strtolower(trim($value));

        if (preg_match('/one\s*[-\s:]?\s*on\s*[-\s:]?\s*one|1\s*[:x]\s*1|private/', $normalized) === 1) {
            return 'one_on_one';
        }

        if (str_contains($normalized, 'self') || str_contains($normalized, 'paced')) {
            return 'self_paced';
        }

        if (str_contains($normalized, 'group')) {
            return 'group';
        }

        return 'one_on_one';
    }

    /**
     * @return array<string, string>
     */
    public static function getLevelOptions(): array
    {
        return [
            'Beginner' => 'Beginner',
            'Intermediate' => 'Intermediate',
            'Advanced' => 'Advanced',
            'Advanced (Beginner to Advanced)' => 'Advanced (Beginner to Advanced)',
            'Advanced (Intermediate to Advanced)' => 'Advanced (Intermediate to Advanced)',
            'Intermediate (Beginner to Intermediate)' => 'Intermediate (Beginner to Intermediate)',
        ];
    }

    public static function normalizeLevel(string $value): string
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            return '';
        }

        $options = self::getLevelOptions();
        if (isset($options[$trimmed])) {
            return $trimmed;
        }

        $normalized = strtolower($trimmed);

        if (
            str_contains($normalized, 'beginner to advanced') ||
            str_contains($normalized, 'beginer to advanced') ||
            (str_contains($normalized, 'advanced') && (str_contains($normalized, 'beginner') || str_contains($normalized, 'beginer')))
        ) {
            return 'Advanced (Beginner to Advanced)';
        }

        if (
            str_contains($normalized, 'intermediate to advanced') ||
            (str_contains($normalized, 'advanced') && str_contains($normalized, 'intermediate'))
        ) {
            return 'Advanced (Intermediate to Advanced)';
        }

        if (
            str_contains($normalized, 'beginner to intermediate') ||
            str_contains($normalized, 'beginer to intermediate') ||
            (str_contains($normalized, 'intermediate') && (str_contains($normalized, 'beginner') || str_contains($normalized, 'beginer')))
        ) {
            return 'Intermediate (Beginner to Intermediate)';
        }

        if (str_contains($normalized, 'beginner') || str_contains($normalized, 'beginer')) {
            return 'Beginner';
        }

        if (str_contains($normalized, 'intermediate')) {
            return 'Intermediate';
        }

        if (str_contains($normalized, 'advanced')) {
            return 'Advanced';
        }

        return $trimmed;
    }

    private static function decodeJson(string $value): mixed
    {
        if (! ((str_starts_with($value, '{') && str_ends_with($value, '}')) || (str_starts_with($value, '[') && str_ends_with($value, ']')))) {
            return null;
        }

        try {
            return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }
    }
}
