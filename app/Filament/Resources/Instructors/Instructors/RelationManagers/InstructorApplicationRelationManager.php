<?php

namespace App\Filament\Resources\Instructors\Instructors\RelationManagers;

use App\Models\InstructorApplication;
use App\Services\InstructorApplicationPdfService;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InstructorApplicationRelationManager extends RelationManager
{
    protected static string $relationship = 'instructorApplication';

    protected static ?string $title = 'Instructor Application';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('proposal_type')
                    ->label('Proposal Type'),
                TextColumn::make('phone'),
                TextColumn::make('qualifications')
                    ->limit(50)
                    ->tooltip(fn ($record): ?string => $record->qualifications),
                TextColumn::make('experience')
                    ->limit(50)
                    ->tooltip(fn ($record): ?string => $record->experience),
                TextColumn::make('reviewed_at')
                    ->label('Reviewed')
                    ->dateTime()
                    ->placeholder('Not reviewed'),
                TextColumn::make('created_at')
                    ->label('Applied')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                \Filament\Actions\ActionGroup::make([
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
                        ->visible(fn (InstructorApplication $record): bool => app(\App\Services\InstructorApplicationAttachmentService::class)->hasAttachments($record))
                        ->action(fn (InstructorApplication $record) => app(\App\Services\InstructorApplicationAttachmentService::class)->downloadApplicationAttachmentsZip($record)),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray'),
            ]);
    }
}
