<?php

namespace App\Filament\Resources\InstructorApplications\Tables;

use App\Models\InstructorApplication;
use App\Services\InstructorApplicationAttachmentService;
use App\Services\InstructorApplicationPdfService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class InstructorApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('proposal_type')
                    ->label('Proposal')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'new' => 'info',
                        'existing' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'new' => 'New Course',
                        'existing' => 'Existing Course',
                        default => 'Legacy',
                    })
                    ->sortable(),
                TextColumn::make('preferredCourse.title')
                    ->label('Preferred Course')
                    ->placeholder('Not specified')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Applied')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('reviewed_at')
                    ->label('Reviewed')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not reviewed'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->recordActions([
                Action::make('download_pdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->action(function (InstructorApplication $record) {
                        $service = app(InstructorApplicationPdfService::class);
                        $pdf = $service->renderApplicationPdf($record);
                        $filename = $service->getPdfFilename($record);

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            $filename,
                            ['Content-Type' => 'application/pdf']
                        );
                    }),
                Action::make('download_attachments')
                    ->label('Download Attachments (ZIP)')
                    ->icon('heroicon-o-folder-arrow-down')
                    ->color('success')
                    ->visible(fn (InstructorApplication $record): bool => app(InstructorApplicationAttachmentService::class)->hasAttachments($record))
                    ->action(fn (InstructorApplication $record) => app(InstructorApplicationAttachmentService::class)->downloadApplicationAttachmentsZip($record)),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('download_selected_pdf')
                        ->label('Download Selected (PDF)')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->action(function (Collection $records) {
                            if ($records->isEmpty()) {
                                return null;
                            }

                            $service = app(InstructorApplicationPdfService::class);

                            if ($records->count() === 1) {
                                /** @var InstructorApplication $first */
                                $first = $records->first();
                                $pdf = $service->renderApplicationPdf($first);
                                $filename = $service->getPdfFilename($first);
                            } else {
                                $pdf = $service->renderCombinedApplicationsPdf($records);
                                $filename = 'Instructor_Applications_Batch_' . date('Ymd_His') . '.pdf';
                            }

                            return response()->streamDownload(
                                fn () => print($pdf->output()),
                                $filename,
                                ['Content-Type' => 'application/pdf']
                            );
                        }),
                    BulkAction::make('download_selected_attachments')
                        ->label('Download Selected Attachments (ZIP)')
                        ->icon('heroicon-o-folder-arrow-down')
                        ->color('success')
                        ->action(function (Collection $records) {
                            return app(InstructorApplicationAttachmentService::class)->downloadMultipleApplicationsAttachmentsZip($records);
                        }),
                ]),
            ]);
    }
}
