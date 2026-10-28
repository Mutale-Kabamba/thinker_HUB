<?php

namespace App\Filament\Resources\CourseIntakes\RelationManagers;

use App\Models\CourseIntake;
use App\Models\LearningMaterial;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MaterialsRelationManager extends RelationManager
{
    protected static string $relationship = 'learningMaterials';

    protected static ?string $title = 'Learning & Study Materials';

    public function table(Table $table): Table
    {
        /** @var CourseIntake $intake */
        $intake = $this->getOwnerRecord();

        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('Material Title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('material_type')
                    ->label('Type')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('file_path')
                    ->label('File / Link')
                    ->formatStateUsing(fn ($state): string => $state ? 'Available' : 'None')
                    ->badge(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Material for Cohort')
                    ->icon('heroicon-o-plus')
                    ->form([
                        TextInput::make('title')
                            ->label('Material Title')
                            ->required()
                            ->maxLength(255),

                        Select::make('category')
                            ->label('Category')
                            ->options([
                                'Study Material' => 'Study Material',
                                'Reading Material' => 'Reading Material',
                                'Syllabus' => 'Syllabus',
                                'Guide' => 'Guide',
                                'Past Papers' => 'Past Papers',
                                'Assignment Brief' => 'Assignment Brief',
                                'Other' => 'Other',
                            ])
                            ->default('Study Material')
                            ->required(),

                        Select::make('material_type')
                            ->label('Material Type')
                            ->options([
                                'Document' => 'Document / PDF',
                                'Spreadsheet' => 'Spreadsheet',
                                'Presentation' => 'Presentation',
                                'Archive' => 'Zip Archive',
                                'Image' => 'Image / Diagram',
                                'Other' => 'Other',
                            ])
                            ->default('Document')
                            ->required(),

                        FileUpload::make('file_path')
                            ->label('Upload File (PDF, PPTX, ZIP, .py, etc.)')
                            ->disk('public')
                            ->directory('learning-materials')
                            ->storeFileNamesIn('file_name')
                            ->maxSize(10240)
                            ->helperText('All file types supported up to 10 MB.')
                            ->columnSpanFull(),

                        TextInput::make('link_url')
                            ->label('External Link (Optional)')
                            ->url()
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Description / Notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->mutateFormDataUsing(function (array $data) use ($intake): array {
                        $data['course_id'] = $intake->course_id;
                        $data['course_intake_id'] = $intake->id;
                        $data['scope'] = 'all';

                        return $data;
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    \Filament\Actions\Action::make('view_file')
                        ->label('View')
                        ->icon('heroicon-m-eye')
                        ->color('info')
                        ->visible(fn (\App\Models\LearningMaterial $record): bool => filled($record->file_path) || filled($record->link_url))
                        ->url(fn (\App\Models\LearningMaterial $record): string => filled($record->file_path)
                            ? route('file.view', ['type' => 'material', 'id' => $record->id])
                            : $record->link_url
                        )
                        ->openUrlInNewTab(),
                    \Filament\Actions\Action::make('download_file')
                        ->label('Download')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->color('gray')
                        ->visible(fn (\App\Models\LearningMaterial $record): bool => filled($record->file_path))
                        ->url(fn (\App\Models\LearningMaterial $record): string => route('file.download', ['type' => 'material', 'id' => $record->id]))
                        ->openUrlInNewTab(),
                    EditAction::make()
                        ->icon('heroicon-m-pencil-square')
                        ->form([
                            TextInput::make('title')
                                ->label('Material Title')
                                ->required()
                                ->maxLength(255),

                            Select::make('category')
                                ->label('Category')
                                ->options([
                                    'Study Material' => 'Study Material',
                                    'Reading Material' => 'Reading Material',
                                    'Syllabus' => 'Syllabus',
                                    'Guide' => 'Guide',
                                    'Past Papers' => 'Past Papers',
                                    'Assignment Brief' => 'Assignment Brief',
                                    'Other' => 'Other',
                                ])
                                ->required(),

                            Select::make('material_type')
                                ->label('Material Type')
                                ->options([
                                    'Document' => 'Document / PDF',
                                    'Spreadsheet' => 'Spreadsheet',
                                    'Presentation' => 'Presentation',
                                    'Archive' => 'Zip Archive',
                                    'Image' => 'Image / Diagram',
                                    'Other' => 'Other',
                                ])
                                ->required(),

                            FileUpload::make('file_path')
                                ->label('Upload File (PDF, PPTX, ZIP, .py, etc.)')
                                ->disk('public')
                                ->directory('learning-materials')
                                ->storeFileNamesIn('file_name')
                                ->maxSize(10240)
                                ->helperText('All file types supported up to 10 MB.')
                                ->columnSpanFull(),

                            TextInput::make('link_url')
                                ->label('External Link')
                                ->url()
                                ->columnSpanFull(),

                            Textarea::make('description')
                                ->label('Description / Notes')
                                ->rows(2)
                                ->columnSpanFull(),
                        ]),
                    DeleteAction::make()->icon('heroicon-m-trash'),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
