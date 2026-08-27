<?php

namespace App\Filament\Resources\CourseIntakes\RelationManagers;

use App\Models\CourseIntake;
use App\Models\CourseSession;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SessionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sessions';

    protected static ?string $title = 'Schedules & Live Sessions';

    public function table(Table $table): Table
    {
        /** @var CourseIntake $intake */
        $intake = $this->getOwnerRecord();

        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('Session Title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('session_date')
                    ->label('Date')
                    ->date('M j, Y')
                    ->sortable(),

                TextColumn::make('start_time')
                    ->label('Time')
                    ->formatStateUsing(fn ($state, CourseSession $record): string => ($record->start_time ?? '') . ' - ' . ($record->end_time ?? ''))
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'group' => 'success',
                        'one-on-one' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Schedule Session for Cohort')
                    ->icon('heroicon-o-plus')
                    ->form([
                        TextInput::make('title')
                            ->label('Session Title')
                            ->placeholder('e.g. Week 1 Live Q&A, Module 2 Masterclass')
                            ->required()
                            ->maxLength(255),

                        DatePicker::make('session_date')
                            ->label('Session Date')
                            ->required(),

                        TimePicker::make('start_time')
                            ->label('Start Time')
                            ->required(),

                        TimePicker::make('end_time')
                            ->label('End Time')
                            ->required(),

                        Select::make('type')
                            ->label('Session Type')
                            ->options([
                                'group' => 'Group Class Session',
                                'one-on-one' => '1-on-1 Mentorship',
                            ])
                            ->default('group')
                            ->required(),

                        TextInput::make('meeting_url')
                            ->label('Meeting Link (Google Meet / Zoom)')
                            ->url()
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Agenda / Session Details')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->mutateFormDataUsing(function (array $data) use ($intake): array {
                        $data['course_id'] = $intake->course_id;
                        $data['course_intake_id'] = $intake->id;
                        $data['status'] = 'scheduled';

                        return $data;
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->icon('heroicon-m-pencil-square')
                        ->form([
                            TextInput::make('title')
                                ->label('Session Title')
                                ->required()
                                ->maxLength(255),

                            DatePicker::make('session_date')
                                ->label('Session Date')
                                ->required(),

                            TimePicker::make('start_time')
                                ->label('Start Time')
                                ->required(),

                            TimePicker::make('end_time')
                                ->label('End Time')
                                ->required(),

                            Select::make('type')
                                ->label('Session Type')
                                ->options([
                                    'group' => 'Group Class Session',
                                    'one-on-one' => '1-on-1 Mentorship',
                                ])
                                ->required(),

                            Select::make('status')
                                ->label('Session Status')
                                ->options([
                                    'scheduled' => 'Scheduled',
                                    'completed' => 'Completed',
                                    'cancelled' => 'Cancelled',
                                ])
                                ->required(),

                            TextInput::make('meeting_url')
                                ->label('Meeting Link')
                                ->url()
                                ->columnSpanFull(),

                            Textarea::make('description')
                                ->label('Agenda / Session Details')
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
