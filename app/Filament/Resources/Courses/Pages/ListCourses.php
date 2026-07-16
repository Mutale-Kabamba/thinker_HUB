<?php

namespace App\Filament\Resources\Courses\Pages;

use App\Filament\Resources\Courses\CourseResource;
use App\Models\Course;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ListCourses extends ListRecords
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('bulkImportJson')
                ->label('Bulk Import JSON')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->modalHeading('Bulk Import Courses from JSON')
                ->modalDescription('Upload a JSON array of courses. Existing rows with matching course code will be updated.')
                ->form([
                    FileUpload::make('json_file')
                        ->label('JSON File')
                        ->disk('local')
                        ->directory('imports/courses')
                        ->acceptedFileTypes(['application/json', 'text/json'])
                        ->maxSize(2048)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $filePath = (string) ($data['json_file'] ?? '');

                    if ($filePath === '' || ! Storage::disk('local')->exists($filePath)) {
                        Notification::make()
                            ->title('Import failed')
                            ->body('Uploaded file could not be found. Please try again.')
                            ->danger()
                            ->send();

                        return;
                    }

                    try {
                        $raw = Storage::disk('local')->get($filePath);
                        $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

                        if (! is_array($decoded)) {
                            throw new \RuntimeException('JSON root must be an array of courses.');
                        }

                        $created = 0;
                        $updated = 0;
                        $skipped = 0;

                        foreach ($decoded as $index => $row) {
                            if (! is_array($row)) {
                                $skipped++;

                                continue;
                            }

                            $title = trim((string) Arr::get($row, 'title', ''));
                            $code = trim((string) Arr::get($row, 'code', ''));

                            if ($title === '' || $code === '') {
                                $skipped++;

                                continue;
                            }

                            $payload = $this->mapCoursePayload($row);
                            $existing = Course::query()->where('code', $code)->first();

                            if ($existing) {
                                $existing->fill($payload)->save();
                                $updated++;

                                continue;
                            }

                            Course::query()->create($payload);
                            $created++;
                        }

                        Notification::make()
                            ->title('Import completed')
                            ->body("Created: {$created} | Updated: {$updated} | Skipped: {$skipped}")
                            ->success()
                            ->send();
                    } catch (Throwable $exception) {
                        report($exception);

                        Notification::make()
                            ->title('Import failed')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    } finally {
                        Storage::disk('local')->delete($filePath);
                    }
                }),
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function mapCoursePayload(array $row): array
    {
        $requirements = Arr::get($row, 'requirements');
        $levels = Arr::get($row, 'levels_progression', Arr::get($row, 'level_progression'));

        return [
            'title' => trim((string) Arr::get($row, 'title', '')),
            'code' => trim((string) Arr::get($row, 'code', '')),
            'description' => $this->normalizeText(Arr::get($row, 'description')),
            'overview' => $this->normalizeText(Arr::get($row, 'overview')),
            'timeline' => $this->normalizeText(Arr::get($row, 'timeline')),
            'fees' => $this->normalizeFees(Arr::get($row, 'fees')),
            'requirements' => $this->normalizeList($requirements),
            'level_progression' => $this->normalizeLevels($levels),
            'key_outcome' => $this->normalizeText(Arr::get($row, 'key_outcome')),
            'is_active' => (bool) Arr::get($row, 'is_active', true),
        ];
    }

    /**
     * @param mixed $value
     */
    private function normalizeText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    /**
     * @param mixed $value
     */
    private function normalizeList(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            return $this->normalizeText($value);
        }

        if (! is_array($value)) {
            return null;
        }

        $lines = [];

        foreach ($value as $item) {
            $line = trim((string) $item);

            if ($line !== '') {
                $lines[] = $line;
            }
        }

        return $lines === [] ? null : implode("\n", $lines);
    }

    /**
     * @param mixed $value
     */
    private function normalizeLevels(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            return $this->normalizeText($value);
        }

        if (! is_array($value)) {
            return null;
        }

        $lines = [];

        foreach ($value as $item) {
            if (is_array($item)) {
                $level = trim((string) Arr::get($item, 'level', ''));
                $details = trim((string) Arr::get($item, 'details', ''));

                $line = trim(($level !== '' ? $level . ': ' : '') . $details);

                if ($line !== '') {
                    $lines[] = $line;
                }

                continue;
            }

            $line = trim((string) $item);

            if ($line !== '') {
                $lines[] = $line;
            }
        }

        return $lines === [] ? null : implode("\n", $lines);
    }

    /**
     * @param mixed $value
     */
    private function normalizeFees(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            return $this->normalizeText($value);
        }

        if (! is_array($value)) {
            return null;
        }

        $lines = [];

        foreach (['one_on_one' => 'One-on-One', 'group' => 'Group'] as $key => $label) {
            $entries = Arr::get($value, $key, []);

            if (! is_array($entries)) {
                continue;
            }

            foreach ($entries as $entry) {
                if (! is_array($entry)) {
                    continue;
                }

                $level = trim((string) Arr::get($entry, 'level', ''));
                $amount = trim((string) Arr::get($entry, 'amount', ''));
                $duration = trim((string) Arr::get($entry, 'duration', ''));

                $parts = array_filter([$label, $level !== '' ? $level : null]);
                $left = implode(' - ', $parts);

                $line = trim($left . ($amount !== '' ? ': ' . $amount : '') . ($duration !== '' ? ' (' . $duration . ')' : ''));

                if ($line !== '') {
                    $lines[] = $line;
                }
            }
        }

        if ($lines !== []) {
            return implode("\n", $lines);
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?: null;
    }
}
