<?php

namespace App\Filament\Resources\Courses\Tables;

use App\Models\Course;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;

class CoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('code')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('viewDetails')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
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
                EditAction::make(),
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
