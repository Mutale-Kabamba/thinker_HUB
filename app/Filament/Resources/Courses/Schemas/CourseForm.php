<?php

namespace App\Filament\Resources\Courses\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('code')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Textarea::make('overview')
                    ->helperText('High-level introduction shown in the course details modal.')
                    ->rows(4)
                    ->columnSpanFull(),
                TextInput::make('timeline')
                    ->helperText('Example: 4 Weeks (approx. 4-5 hours per week)')
                    ->columnSpanFull(),
                Repeater::make('fees')
                    ->label('Fees')
                    ->helperText('Add fee entries with +. Category is One-On-One or Group; level is Beginner, Intermediate, or Advanced.')
                    ->schema([
                        Select::make('category')
                            ->options([
                                'one_on_one' => 'One-On-One',
                                'group' => 'Group',
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
                    ->columns(4)
                    ->addActionLabel('Add fee entry')
                    ->defaultItems(1)
                    ->formatStateUsing(fn (mixed $state): array => self::parseFeesState($state))
                    ->dehydrateStateUsing(fn (mixed $state): ?string => self::serializeFeesState($state))
                    ->columnSpanFull(),
                Textarea::make('requirements')
                    ->helperText('Add each requirement on a new line.')
                    ->rows(4)
                    ->columnSpanFull(),
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
                    ->columns(2)
                    ->addActionLabel('Add level entry')
                    ->defaultItems(3)
                    ->formatStateUsing(fn (mixed $state): array => self::parseLevelProgressionState($state))
                    ->dehydrateStateUsing(fn (mixed $state): ?string => self::serializeLevelProgressionState($state))
                    ->columnSpanFull(),
                Textarea::make('key_outcome')
                    ->helperText('Summarize expected learning outcome after completion.')
                    ->rows(3)
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->required(),
            ]);
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
            $parsed = self::parseFeeLine((string) $line);

            if ($parsed !== null) {
                $entries[] = $parsed;
            }
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

        if (array_key_exists('one_on_one', $state) || array_key_exists('group', $state)) {
            foreach (['one_on_one', 'group'] as $category) {
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
                $parsed = self::parseFeeLine($row);

                if ($parsed !== null) {
                    $entries[] = $parsed;
                }

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
     * @return array{category: string, level: string, amount: string, duration: string}|null
     */
    private static function parseFeeLine(string $line): ?array
    {
        $line = trim($line);

        if ($line === '') {
            return null;
        }

        $category = str_contains(strtolower($line), 'group') ? 'group' : 'one_on_one';
        $normalizedLine = trim((string) preg_replace('/^(one\s*[-\s:]?\s*on\s*[-\s:]?\s*one|1\s*[:x]\s*1|private|group)\s*(?:[:\-|]\s*)?/i', '', $line));
        $normalizedLine = $normalizedLine === '' ? $line : $normalizedLine;

        if (preg_match('/\b(Beginner|Intermediate|Advanced)\b\s*[:\-]\s*([^()]+?)\s*(?:\(([^)]+)\))?$/i', $normalizedLine, $match) === 1) {
            return [
                'category' => $category,
                'level' => self::normalizeLevel((string) ($match[1] ?? '')),
                'amount' => trim((string) ($match[2] ?? '')),
                'duration' => trim((string) ($match[3] ?? '')),
            ];
        }

        if (preg_match('/^([^:()|]+?)\s*:\s*([^()]+?)\s*(?:\(([^)]+)\))?$/', $normalizedLine, $match) === 1) {
            return [
                'category' => $category,
                'level' => self::normalizeLevel((string) ($match[1] ?? '')),
                'amount' => trim((string) ($match[2] ?? '')),
                'duration' => trim((string) ($match[3] ?? '')),
            ];
        }

        return [
            'category' => $category,
            'level' => self::normalizeLevel($normalizedLine),
            'amount' => '',
            'duration' => '',
        ];
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
