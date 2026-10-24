<?php

namespace App\Filament\Resources\Courses\Schemas;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MorphToSelect;
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
                                                                            MorphToSelect\Type::make(\App\Models\Assignment::class)->titleAttribute('title'),
                                                                        ])
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
                                                        ->helperText('Add fee entries with +. Category is One-On-One, Group, or Self-Paced; level is Beginner, Intermediate, or Advanced.')
                                                        ->schema([
                                                            Select::make('category')
                                                                ->options([
                                                                    'one_on_one' => 'One-On-One',
                                                                    'group' => 'Group',
                                                                    'self_paced' => 'Self-Paced',
                                                                ])
                                                                ->required(),
                                                            Select::make('level')
                                                                ->options([
                                                                    'Beginner' => 'Beginner',
                                                                    'Intermediate' => 'Intermediate',
                                                                    'Advanced' => 'Advanced',
                                                                ])
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
                                                                    ->options([
                                                                        'Beginner' => 'Beginner',
                                                                        'Intermediate' => 'Intermediate',
                                                                        'Advanced' => 'Advanced',
                                                                    ])
                                                                    ->required(),
                                                                TextInput::make('amount')
                                                                    ->required()
                                                                    ->placeholder('K450'),
                                                                TextInput::make('duration')
                                                                    ->placeholder('6 Weeks'),
                                                            ])
                                                            ->action(function (array $data, Repeater $component): void {
                                                                $items = $component->getRawState() ?? [];
                                                                $items[(string) Str::uuid()] = [
                                                                    'category' => self::normalizeCategory((string) ($data['category'] ?? 'one_on_one')),
                                                                    'level' => self::normalizeLevel((string) ($data['level'] ?? '')),
                                                                    'amount' => trim((string) ($data['amount'] ?? '')),
                                                                    'duration' => trim((string) ($data['duration'] ?? '')),
                                                                ];

                                                                $component->rawState($items);
                                                                $component->callAfterStateUpdated();
                                                                $component->partiallyRender();
                                                            }))
                                                        ->afterStateHydrated(function (Repeater $component, mixed $state): void {
                                                            $component->state(self::parseFeesState($state));
                                                        })
                                                        ->columnSpanFull(),
                                                ]),

                                            Section::make('Skill Level Progression Roadmap')
                                                ->description('Define what students learn and master across Beginner, Intermediate, and Advanced tiers.')
                                                ->icon('heroicon-o-chart-bar')
                                                ->schema([
                                                    Repeater::make('level_progression')
                                                        ->label('Level Progression')
                                                        ->helperText('Add each level with + and provide the progression description.')
                                                        ->schema([
                                                            Select::make('level')
                                                                ->options([
                                                                    'Beginner' => 'Beginner',
                                                                    'Intermediate' => 'Intermediate',
                                                                    'Advanced' => 'Advanced',
                                                                ])
                                                                ->required(),
                                                            Textarea::make('details')
                                                                ->rows(2)
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
                                                                    ->options([
                                                                        'Beginner' => 'Beginner',
                                                                        'Intermediate' => 'Intermediate',
                                                                        'Advanced' => 'Advanced',
                                                                    ])
                                                                    ->required(),
                                                                Textarea::make('details')
                                                                    ->rows(3)
                                                                    ->required(),
                                                            ])
                                                            ->action(function (array $data, Repeater $component): void {
                                                                $items = $component->getRawState() ?? [];
                                                                $items[(string) Str::uuid()] = [
                                                                    'level' => self::normalizeLevel((string) ($data['level'] ?? '')),
                                                                    'details' => trim((string) ($data['details'] ?? '')),
                                                                ];

                                                                $component->rawState($items);
                                                                $component->callAfterStateUpdated();
                                                                $component->partiallyRender();
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

        $multiMatches = [];
        if (preg_match_all('/\b(Beginner|Intermediate|Advanced)\b\s*[:\-]\s*([^()]+?)\s*(?:\(([^)]+)\))?(?=\s*(?:Beginner|Intermediate|Advanced)\s*[:\-]|$)/i', $compactLine, $multiMatches, PREG_SET_ORDER) > 0) {
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

        if (preg_match('/\b(Beginner|Intermediate|Advanced)\b\s*[:\-]\s*([^()]+?)\s*(?:\(([^)]+)\))?$/i', $normalizedLine, $match) === 1) {
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
        $defaultLevels = ['Beginner', 'Intermediate', 'Advanced'];
        $index = 0;

        foreach (preg_split('/\R+/', $trimmed) ?: [] as $line) {
            $line = trim((string) $line);

            if ($line === '') {
                continue;
            }

            $compactLine = trim((string) preg_replace('/\s+/', ' ', $line));
            $multiMatches = [];

            if (preg_match_all('/\b(Beginner|Intermediate|Advanced)\b\s*[:\-]\s*([\s\S]*?)(?=\s*(?:Beginner|Intermediate|Advanced)\s*[:\-]|$)/i', $compactLine, $multiMatches, PREG_SET_ORDER) > 0) {
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

    private static function normalizeLevel(string $value): string
    {
        $normalized = strtolower(trim($value));

        if (str_contains($normalized, 'beginner')) {
            return 'Beginner';
        }

        if (str_contains($normalized, 'intermediate')) {
            return 'Intermediate';
        }

        if (str_contains($normalized, 'advanced')) {
            return 'Advanced';
        }

        return '';
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
