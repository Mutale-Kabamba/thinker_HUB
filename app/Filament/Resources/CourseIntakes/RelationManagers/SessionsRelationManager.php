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
            ->defaultSort('session_date', 'asc')
            ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query
                ->orderByRaw("CASE WHEN status = 'scheduled' THEN 0 WHEN status = 'rescheduled' THEN 1 WHEN status = 'completed' THEN 2 WHEN status = 'cancelled' THEN 3 ELSE 4 END")
                ->orderBy('session_date', 'asc')
                ->orderBy('start_time', 'asc')
            )
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

                        Select::make('delivery_mode')
                            ->label('Delivery Mode')
                            ->options(CourseSession::DELIVERY_MODES)
                            ->default(CourseSession::DELIVERY_LIVE_VIRTUAL)
                            ->required()
                            ->live(),

                        TextInput::make('meeting_link')
                            ->label('Virtual Classroom Link (Google Meet, Zoom, etc.)')
                            ->placeholder('https://meet.google.com/...')
                            ->url()
                            ->visible(fn (callable $get): bool => ($get('delivery_mode') ?? CourseSession::DELIVERY_LIVE_VIRTUAL) === CourseSession::DELIVERY_LIVE_VIRTUAL)
                            ->required(fn (callable $get): bool => ($get('delivery_mode') ?? CourseSession::DELIVERY_LIVE_VIRTUAL) === CourseSession::DELIVERY_LIVE_VIRTUAL)
                            ->columnSpanFull(),

                        TextInput::make('physical_address')
                            ->label('Physical Venue / Class Address')
                            ->placeholder('e.g. Thinker Hub Lab, Room 2B, Main Campus')
                            ->visible(fn (callable $get): bool => $get('delivery_mode') === CourseSession::DELIVERY_LIVE_PHYSICAL)
                            ->required(fn (callable $get): bool => $get('delivery_mode') === CourseSession::DELIVERY_LIVE_PHYSICAL)
                            ->columnSpanFull(),

                        TextInput::make('video_url')
                            ->label('Embedded Video Link (YouTube, Vimeo, Loom, etc.)')
                            ->placeholder('https://www.youtube.com/watch?v=... or https://vimeo.com/...')
                            ->url()
                            ->helperText('Paste a video URL from YouTube, Vimeo, Loom, Dailymotion, Wistia, Google Drive, or any video platform.')
                            ->visible(fn (callable $get): bool => $get('delivery_mode') === CourseSession::DELIVERY_VIDEO)
                            ->columnSpanFull(),

                        \Filament\Forms\Components\FileUpload::make('reading_file_path')
                            ->label('Upload Reading Material / Attachment (PDF, PPTX, ZIP, .py, etc.)')
                            ->disk('public')
                            ->directory('session-readings')
                            ->visibility('public')
                            ->maxSize(10240)
                            ->helperText('All file types supported up to 10 MB.')
                            ->visible(fn (callable $get): bool => $get('delivery_mode') === CourseSession::DELIVERY_READ)
                            ->columnSpanFull(),

                        Textarea::make('reading_content')
                            ->label('Or Written Article / Reading Guide Content')
                            ->placeholder('Write or paste the reading text or lesson content here...')
                            ->rows(5)
                            ->visible(fn (callable $get): bool => $get('delivery_mode') === CourseSession::DELIVERY_READ)
                            ->columnSpanFull(),

                        TextInput::make('required_duration_minutes')
                            ->label('Required Time to Spend (Minutes)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(720)
                            ->default(15)
                            ->helperText('Time the student must spend actively engaging before attendance is marked as present.')
                            ->visible(fn (callable $get): bool => in_array($get('delivery_mode'), [CourseSession::DELIVERY_VIDEO, CourseSession::DELIVERY_READ], true))
                            ->required(fn (callable $get): bool => in_array($get('delivery_mode'), [CourseSession::DELIVERY_VIDEO, CourseSession::DELIVERY_READ], true)),

                        Textarea::make('notes')
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

                            Select::make('delivery_mode')
                                ->label('Delivery Mode')
                                ->options(CourseSession::DELIVERY_MODES)
                                ->default(CourseSession::DELIVERY_LIVE_VIRTUAL)
                                ->required()
                                ->live(),

                            TextInput::make('meeting_link')
                                ->label('Virtual Classroom Link (Google Meet, Zoom, etc.)')
                                ->placeholder('https://meet.google.com/...')
                                ->url()
                                ->visible(fn (callable $get): bool => ($get('delivery_mode') ?? CourseSession::DELIVERY_LIVE_VIRTUAL) === CourseSession::DELIVERY_LIVE_VIRTUAL)
                                ->required(fn (callable $get): bool => ($get('delivery_mode') ?? CourseSession::DELIVERY_LIVE_VIRTUAL) === CourseSession::DELIVERY_LIVE_VIRTUAL)
                                ->columnSpanFull(),

                            TextInput::make('physical_address')
                                ->label('Physical Venue / Class Address')
                                ->placeholder('e.g. Thinker Hub Lab, Room 2B, Main Campus')
                                ->visible(fn (callable $get): bool => $get('delivery_mode') === CourseSession::DELIVERY_LIVE_PHYSICAL)
                                ->required(fn (callable $get): bool => $get('delivery_mode') === CourseSession::DELIVERY_LIVE_PHYSICAL)
                                ->columnSpanFull(),

                            TextInput::make('video_url')
                                ->label('Embedded Video Link (YouTube, Vimeo, Loom, etc.)')
                                ->placeholder('https://www.youtube.com/watch?v=... or https://vimeo.com/...')
                                ->url()
                                ->helperText('Paste a video URL from YouTube, Vimeo, Loom, Dailymotion, Wistia, Google Drive, or any video platform.')
                                ->visible(fn (callable $get): bool => $get('delivery_mode') === CourseSession::DELIVERY_VIDEO)
                                ->columnSpanFull(),

                            \Filament\Forms\Components\FileUpload::make('reading_file_path')
                                ->label('Upload Reading Material / Attachment (PDF, PPTX, ZIP, .py, etc.)')
                                ->disk('public')
                                ->directory('session-readings')
                                ->visibility('public')
                                ->maxSize(10240)
                                ->helperText('All file types supported up to 10 MB.')
                                ->visible(fn (callable $get): bool => $get('delivery_mode') === CourseSession::DELIVERY_READ)
                                ->columnSpanFull(),

                            Textarea::make('reading_content')
                                ->label('Or Written Article / Reading Guide Content')
                                ->placeholder('Write or paste the reading text or lesson content here...')
                                ->rows(5)
                                ->visible(fn (callable $get): bool => $get('delivery_mode') === CourseSession::DELIVERY_READ)
                                ->columnSpanFull(),

                            TextInput::make('required_duration_minutes')
                                ->label('Required Time to Spend (Minutes)')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(720)
                                ->default(15)
                                ->helperText('Time the student must spend actively engaging before attendance is marked as present.')
                                ->visible(fn (callable $get): bool => in_array($get('delivery_mode'), [CourseSession::DELIVERY_VIDEO, CourseSession::DELIVERY_READ], true))
                                ->required(fn (callable $get): bool => in_array($get('delivery_mode'), [CourseSession::DELIVERY_VIDEO, CourseSession::DELIVERY_READ], true)),

                            Textarea::make('notes')
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
