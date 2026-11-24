<?php

namespace App\Filament\Resources\ScormPackages\Pages;

use App\Filament\Resources\ScormPackages\ScormPackageResource;
use App\Models\Course;
use App\Models\Tenant;
use App\Services\ScormPackageService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListScormPackages extends ListRecords
{
    protected static string $resource = ScormPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('uploadPackage')
                ->label('Upload SCORM / xAPI (.zip)')
                ->icon('heroicon-m-arrow-up-tray')
                ->color('primary')
                ->form([
                    TextInput::make('title')
                        ->label('Module Title (optional — auto-detected from manifest)')
                        ->maxLength(255),
                    Select::make('course_id')
                        ->label('Attach to Course (optional)')
                        ->options(fn () => Course::query()
                            ->visibleToAdministrator(auth()->user(), includeGlobal: true)
                            ->orderBy('title')
                            ->pluck('title', 'id'))
                        ->searchable(),
                    Select::make('tenant_id')
                        ->label('Program / Tenant (optional)')
                        ->options(fn () => Tenant::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                        ->default(fn () => auth()->user()?->managedTenantId())
                        ->disabled(fn (): bool => (bool) auth()->user()?->isProgramAdmin())
                        ->dehydrated()
                        ->searchable(),
                    FileUpload::make('archive')
                        ->label('SCORM 1.2 / 2004 or xAPI ZIP Archive')
                        ->disk('local')
                        ->directory('scorm-uploads')
                        ->acceptedFileTypes(['application/zip', 'application/x-zip-compressed'])
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $zipPath = Storage::disk('local')->path((string) $data['archive']);

                    try {
                        $package = app(ScormPackageService::class)->importZip($zipPath, [
                            'title' => $data['title'] ?? null,
                            'course_id' => $data['course_id'] ?? null,
                            'tenant_id' => $data['tenant_id'] ?? auth()->user()?->managedTenantId(),
                        ]);

                        Notification::make()
                            ->title('Package Imported')
                            ->body("{$package->title} ({$package->standard}) is ready for learners.")
                            ->success()
                            ->send();
                    } finally {
                        Storage::disk('local')->delete((string) $data['archive']);
                    }
                }),
        ];
    }
}
