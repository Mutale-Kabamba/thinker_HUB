<?php

namespace App\Filament\Resources\CourseIntakes\RelationManagers;

use App\Models\CourseIntake;
use App\Models\Quiz;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuizzesRelationManager extends RelationManager
{
    protected static string $relationship = 'quizzes';

    protected static ?string $title = 'Quizzes';

    public function table(Table $table): Table
    {
        /** @var CourseIntake $intake */
        $intake = $this->getOwnerRecord();

        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('Quiz Title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('time_limit')
                    ->label('Time Limit')
                    ->formatStateUsing(fn (?int $state): string => $state ? "{$state} mins" : 'No limit')
                    ->sortable(),

                TextColumn::make('questions_count')
                    ->label('Questions')
                    ->counts('questions')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Create Quiz for Cohort')
                    ->icon('heroicon-o-plus')
                    ->form([
                        TextInput::make('title')
                            ->label('Quiz Title')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('time_limit')
                            ->label('Time Limit (Minutes)')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('e.g. 30'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
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
                            TextInput::make('title')
                                ->label('Quiz Title')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('time_limit')
                                ->label('Time Limit (Minutes)')
                                ->numeric()
                                ->minValue(1),

                            Toggle::make('is_active')
                                ->label('Active'),
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
