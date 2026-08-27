<?php

namespace App\Filament\Resources\CourseIntakes\RelationManagers;

use App\Models\Assignment;
use App\Models\CourseIntake;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'assignments';

    protected static ?string $title = 'Assignments';

    public function table(Table $table): Table
    {
        /** @var CourseIntake $intake */
        $intake = $this->getOwnerRecord();

        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Assignment Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('target_level')
                    ->label('Level')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('date_given')
                    ->label('Given Date')
                    ->date('M j, Y')
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date('M j, Y')
                    ->sortable(),

                TextColumn::make('submissions_count')
                    ->label('Submissions')
                    ->counts('submissions')
                    ->badge()
                    ->color('info')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Create Assignment for Cohort')
                    ->icon('heroicon-o-plus')
                    ->form([
                        TextInput::make('name')
                            ->label('Assignment Name')
                            ->required()
                            ->maxLength(255),

                        Select::make('target_level')
                            ->label('Target Track / Level')
                            ->required()
                            ->options([
                                'Beginner' => 'Beginner',
                                'Intermediate' => 'Intermediate',
                                'Advanced' => 'Advanced',
                            ]),

                        DatePicker::make('date_given')
                            ->label('Date Given')
                            ->default(now()->toDateString())
                            ->required(),

                        DatePicker::make('due_date')
                            ->label('Due Date')
                            ->afterOrEqual('date_given')
                            ->required(),

                        FileUpload::make('file_paths')
                            ->label('Assignment Document(s)')
                            ->disk('public')
                            ->directory('assignments')
                            ->multiple()
                            ->reorderable()
                            ->maxSize(10240)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Instructions / Description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->mutateFormDataUsing(function (array $data) use ($intake): array {
                        $data['course_id'] = $intake->course_id;
                        $data['course_intake_id'] = $intake->id;

                        return $data;
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->icon('heroicon-m-pencil-square')
                        ->form([
                            TextInput::make('name')
                                ->label('Assignment Name')
                                ->required()
                                ->maxLength(255),

                            Select::make('target_level')
                                ->label('Target Track / Level')
                                ->required()
                                ->options([
                                    'Beginner' => 'Beginner',
                                    'Intermediate' => 'Intermediate',
                                    'Advanced' => 'Advanced',
                                ]),

                            DatePicker::make('date_given')
                                ->label('Date Given')
                                ->required(),

                            DatePicker::make('due_date')
                                ->label('Due Date')
                                ->afterOrEqual('date_given')
                                ->required(),

                            FileUpload::make('file_paths')
                                ->label('Assignment Document(s)')
                                ->disk('public')
                                ->directory('assignments')
                                ->multiple()
                                ->reorderable()
                                ->maxSize(10240)
                                ->columnSpanFull(),

                            Textarea::make('description')
                                ->label('Instructions / Description')
                                ->rows(3)
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
