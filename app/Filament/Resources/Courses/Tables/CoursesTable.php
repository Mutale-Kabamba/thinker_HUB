<?php

namespace App\Filament\Resources\Courses\Tables;

use App\Models\Course;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;

class CoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->contentGrid([
                'default' => 1,
                'md' => null,
            ])
            ->columns([
                // Mobile Card View Structure (Stacked & Clean)
                Stack::make([
                    Split::make([
                        ImageColumn::make('image_path')
                            ->disk('public')
                            ->circular()
                            ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->title) . '&background=0d9488&color=ffffff')
                            ->grow(false),
                        Stack::make([
                            TextColumn::make('title')
                                ->weight('bold')
                                ->size('sm')
                                ->searchable(),
                            TextColumn::make('code')
                                ->size('xs')
                                ->color('gray')
                                ->searchable(),
                        ]),
                        TextColumn::make('is_active')
                            ->badge()
                            ->state(fn ($record) => $record->is_active ? 'Active' : 'Draft')
                            ->color(fn ($record) => $record->is_active ? 'success' : 'gray')
                            ->grow(false),
                    ]),
                    Split::make([
                        TextColumn::make('offering_mode')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => ($state ?? 'once_off') === 'ongoing' ? 'Ongoing Intakes' : 'Self-Paced')
                            ->color(fn (?string $state): string => ($state ?? 'once_off') === 'ongoing' ? 'info' : 'gray')
                            ->size('xs'),
                        TextColumn::make('is_open_enrollment')
                            ->badge()
                            ->formatStateUsing(fn (?bool $state): string => $state === false ? 'Locked' : 'Open')
                            ->color(fn (?bool $state): string => $state === false ? 'gray' : 'success')
                            ->size('xs'),
                    ])->extraAttributes(['class' => 'pt-2 border-t border-gray-100 dark:border-gray-800']),
                ])
                ->extraAttributes([
                    'class' => 'p-4 bg-white dark:bg-[#111b21] rounded-2xl border border-gray-200/80 dark:border-gray-800/80 shadow-sm space-y-2 md:hidden',
                ]),

                // Desktop Table Columns (Hidden on Mobile)
                ImageColumn::make('image_path')
                    ->label('Image')
                    ->disk('public')
                    ->circular()
                    ->visibleFrom('md'),
                TextColumn::make('title')
                    ->searchable()
                    ->grow()
                    ->wrap()
                    ->weight('bold')
                    ->visibleFrom('md'),
                TextColumn::make('code')
                    ->searchable()
                    ->badge()
                    ->color('gray')
                    ->visibleFrom('md'),
                TextColumn::make('offering_mode')
                    ->label('Structure')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => ($state ?? 'once_off') === 'ongoing' ? 'Ongoing (Intakes)' : 'Once-off')
                    ->color(fn (?string $state): string => ($state ?? 'once_off') === 'ongoing' ? 'info' : 'gray')
                    ->visibleFrom('md'),
                TextColumn::make('activeIntake.name')
                    ->label('Active Intake')
                    ->placeholder('None active')
                    ->badge()
                    ->color('success')
                    ->visibleFrom('lg'),
                TextColumn::make('is_open_enrollment')
                    ->label('Enrollment')
                    ->badge()
                    ->formatStateUsing(fn (?bool $state): string => $state === false ? 'Locked' : 'Open')
                    ->color(fn (?bool $state): string => $state === false ? 'gray' : 'success')
                    ->visibleFrom('md'),
                IconColumn::make('is_active')
                    ->boolean()
                    ->visibleFrom('md'),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->date('M j, Y')
                    ->sortable()
                    ->visibleFrom('xl')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->date('M j, Y')
                    ->sortable()
                    ->visibleFrom('xl')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('offering_mode')
                    ->label('Course Structure')
                    ->options([
                        'once_off' => 'Once-off / Self-Paced',
                        'ongoing' => 'Ongoing / Intakes',
                    ]),
                SelectFilter::make('is_open_enrollment')
                    ->label('Enrollment Mode')
                    ->options([
                        '1' => 'Open',
                        '0' => 'Locked',
                    ])
                    ->query(function ($query, array $data) {
                        $value = $data['value'] ?? null;

                        if ($value === null || $value === '') {
                            return $query;
                        }

                        if ($value === '0') {
                            return $query->where('is_open_enrollment', false);
                        }

                        return $query->where(function ($innerQuery): void {
                            $innerQuery
                                ->where('is_open_enrollment', true)
                                ->orWhereNull('is_open_enrollment');
                        });
                    }),
            ])
            ->recordActions([
                \Filament\Actions\ActionGroup::make([
                    Action::make('viewDetails')
                        ->label('View Details')
                        ->icon('heroicon-m-eye')
                        ->color('info')
                        ->tooltip('Open structured course details')
                        ->modalHeading('Course Details')
                        ->modalWidth('4xl')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close')
                        ->modalContent(fn (Course $record): View => view('filament.partials.course-view-details', [
                            'record' => $record,
                            'feeSections' => self::feeSections($record->fees),
                            'progressionCards' => self::levelProgressionCards($record->level_progression),
                        ])),
                    EditAction::make()->icon('heroicon-m-pencil-square'),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @return array<int, array{key: string, label: string, rows: array<int, array{level: string, amount: string, duration: string}>}>
     */
    private static function feeSections(?string $value): array
    {
        $rows = self::parseFeeRows($value);
        $groups = [
            'one_on_one' => [],
            'group' => [],
            'other' => [],
        ];

        foreach ($rows as $row) {
            $key = $row['mode'];

            if (! array_key_exists($key, $groups)) {
                $key = 'other';
            }

            $groups[$key][] = [
                'level' => $row['level'],
                'amount' => $row['amount'],
                'duration' => $row['duration'],
            ];
        }

        $sections = [];

        foreach (['one_on_one' => 'One-On-One', 'group' => 'Group'] as $key => $label) {
            if ($groups[$key] !== []) {
                $sections[] = [
                    'key' => $key,
                    'label' => $label,
                    'rows' => $groups[$key],
                ];
            }
        }

        if ($sections === [] && $groups['other'] !== []) {
            $sections[] = [
                'key' => 'other',
                'label' => 'Fees',
                'rows' => $groups['other'],
            ];
        }

        return $sections;
    }

    /**
     * @return array<int, array{level: string, amount: string, duration: string, mode: string}>
     */
    private static function parseFeeRows(?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }

        $rows = [];

        foreach (preg_split('/\R+/', $value) ?: [] as $line) {
            $line = trim((string) $line);

            if ($line === '') {
                continue;
            }

            [$mode, $normalizedLine] = self::extractFeeModeAndRemainder($line);
            $compactLine = trim((string) preg_replace('/\s+/', ' ', $normalizedLine));

            $multiMatches = [];
            if (preg_match_all('/\b(Beginner|Intermediate|Advanced)\b\s*[:\-]\s*([^()]+?)\s*(?:\(([^)]+)\))?(?=\s*(?:Beginner|Intermediate|Advanced)\s*[:\-]|$)/i', $compactLine, $multiMatches, PREG_SET_ORDER) === 1 || count($multiMatches) > 1) {
                foreach ($multiMatches as $match) {
                    $rows[] = [
                        'level' => self::cleanLevelText((string) ($match[1] ?? '')),
                        'amount' => self::stripFeeModeText((string) ($match[2] ?? '')),
                        'duration' => trim((string) ($match[3] ?? '')),
                        'mode' => $mode,
                    ];
                }

                continue;
            }

            $level = $normalizedLine;
            $amount = '';
            $duration = '';

            if (preg_match('/^([^:()|]+?)\s*:\s*([^()]+?)\s*(?:\(([^)]+)\))?$/', $normalizedLine, $match)) {
                $level = trim($match[1]);
                $amount = trim($match[2]);
                $duration = trim((string) ($match[3] ?? ''));
            } elseif (preg_match('/^(.+?)\s+-\s+([^()]+?)\s*(?:\(([^)]+)\))?$/', $normalizedLine, $match)) {
                $level = trim($match[1]);
                $amount = trim($match[2]);
                $duration = trim((string) ($match[3] ?? ''));
            } elseif (str_contains($normalizedLine, '|')) {
                $parts = array_values(array_filter(array_map('trim', explode('|', $normalizedLine)), fn (string $part): bool => $part !== ''));
                $level = $parts[0] ?? '';
                $amount = $parts[1] ?? '';
                $duration = $parts[2] ?? '';
            }

            $rows[] = [
                'level' => self::cleanLevelText($level),
                'amount' => self::stripFeeModeText($amount),
                'duration' => $duration,
                'mode' => $mode,
            ];
        }

        return array_values(array_filter($rows, fn (array $row): bool => $row['level'] !== '' || $row['amount'] !== '' || $row['duration'] !== ''));
    }

    /**
     * @return array{0: string, 1: string}
     */
    private static function extractFeeModeAndRemainder(string $line): array
    {
        $mode = self::detectFeeMode($line);
        $normalizedLine = trim((string) preg_replace('/^(one\s*[-\s:]?\s*on\s*[-\s:]?\s*one|1\s*[:x]\s*1|private|group)\s*(?:[:\-|]\s*)?/i', '', $line));

        if ($normalizedLine === '') {
            $normalizedLine = $line;
        }

        return [$mode, $normalizedLine];
    }

    private static function detectFeeMode(string $text): string
    {
        $text = strtolower(trim($text));

        if (preg_match('/one\s*[-:]?\s*on\s*[-:]?\s*one|1\s*[:x]\s*1|private/', $text)) {
            return 'one_on_one';
        }

        if (str_contains($text, 'group')) {
            return 'group';
        }

        return 'other';
    }

    private static function stripFeeModeText(string $value): string
    {
        return trim((string) preg_replace('/\b(one\s*[-:]?\s*on\s*[-:]?\s*one|1\s*[:x]\s*1|private|group)\b\s*[:\-]?\s*/i', '', $value));
    }

    private static function cleanLevelText(string $value): string
    {
        return trim((string) preg_replace('/^(level\s*[:\-]\s*)/i', '', self::stripFeeModeText($value)));
    }

    /**
     * @return array<int, array{level: string, details: string}>
     */
    private static function levelProgressionCards(?string $value): array
    {
        $entries = self::parseLevelProgressions($value);
        $levels = ['Beginner', 'Intermediate', 'Advanced'];
        $sourceText = trim(implode("\n", array_map(
            fn (array $entry): string => trim(($entry['level'] !== '' ? $entry['level'].': ' : '').$entry['details']),
            $entries,
        )));

        return array_map(function (string $level, int $index) use ($entries, $levels, $sourceText): array {
            foreach ($entries as $entry) {
                if (str_contains(strtolower($entry['level']), strtolower($level)) && $entry['details'] !== '') {
                    return ['level' => $level, 'details' => $entry['details']];
                }
            }

            $nextLevel = $levels[$index + 1] ?? null;
            $pattern = $nextLevel !== null
                ? '/'.preg_quote($level, '/').'\s*[:\-]\s*([\s\S]*?)(?='.preg_quote($nextLevel, '/').'\s*[:\-]|$)/i'
                : '/'.preg_quote($level, '/').'\s*[:\-]\s*([\s\S]*?)$/i';

            if ($sourceText !== '' && preg_match($pattern, $sourceText, $match) === 1) {
                $details = trim((string) ($match[1] ?? ''));

                if ($details !== '') {
                    return ['level' => $level, 'details' => $details];
                }
            }

            if (count($entries) === 1 && $index === 0) {
                $fallback = trim(($entries[0]['level'] !== '' ? $entries[0]['level'].': ' : '').$entries[0]['details']);

                if ($fallback !== '') {
                    return ['level' => $level, 'details' => $fallback];
                }
            }

            return ['level' => $level, 'details' => 'Details coming soon.'];
        }, $levels, array_keys($levels));
    }

    /**
     * @return array<int, array{level: string, details: string}>
     */
    private static function parseLevelProgressions(?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }

        $entries = [];

        foreach (preg_split('/\R+/', $value) ?: [] as $line) {
            $line = trim((string) $line);

            if ($line === '') {
                continue;
            }

            if (str_contains($line, ':')) {
                [$level, $details] = array_pad(explode(':', $line, 2), 2, '');
                $entries[] = ['level' => trim($level), 'details' => trim($details)];
                continue;
            }

            $entries[] = ['level' => $line, 'details' => ''];
        }

        return array_values(array_filter($entries, fn (array $entry): bool => $entry['level'] !== '' || $entry['details'] !== ''));
    }
}
