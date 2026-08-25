<?php

namespace App\Filament\Resources\CourseIntakes\RelationManagers;

use App\Models\Assessment;
use App\Models\CourseIntake;
use App\Models\User;
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

class AssessmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'assessments';

    protected static ?string $title = 'Assessments';

    public function table(Table $table): Table
    {
        /** @var CourseIntake $intake */
        $intake = $this->getOwnerRecord();

        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Assessment Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('target_level')
                    ->label('Level')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Target Student')
                    ->placeholder('All Cohort Students')
                    ->sortable(),

                TextColumn::make('date_given')
                    ->label('Date Given')
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
                    ->label('Create Assessment for Cohort')
                    ->icon('heroicon-o-plus')
                    ->form([
                        TextInput::make('name')
                            ->label('Assessment Name')
                            ->required()
                            ->maxLength(255),

                        Select::make('target_level')
                            ->label('Target Track / Level')
                            ->required()
                            ->options([
                                'Beginner' => 'Beginner',
                                'Intermediate' => 'Intermediate',
                                'Advanced' => 'Advanced',
                            ])
                            ->live(),

                        Select::make('user_id')
                            ->label('Target Student')
                            ->searchable()
                            ->options(function () use ($intake): array {
                                $options = ['all' => 'All Students in this Cohort'];
                                $students = User::query()
                                    ->whereHas('enrollments', fn ($q) => $q->where('course_intake_id', $intake->id))
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->toArray();

                                return $options + $students;
                            })
                            ->default('all')
                            ->dehydrateStateUsing(fn (mixed $state): mixed => $state === 'all' ? null : $state),

                        DatePicker::make('date_given')
                            ->label('Date Given')
                            ->default(now()->toDateString())
                            ->required(),

                        DatePicker::make('due_date')
                            ->label('Due Date')
                            ->afterOrEqual('date_given')
                            ->required(),

                        FileUpload::make('file_paths')
                            ->label('Assessment Document(s)')
                            ->disk('public')
                            ->directory('assessments')
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
                EditAction::make()
                    ->form([
                        TextInput::make('name')
                            ->label('Assessment Name')
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
                            ->label('Assessment Document(s)')
                            ->disk('public')
                            ->directory('assessments')
                            ->multiple()
                            ->reorderable()
                            ->maxSize(10240)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Instructions / Description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
