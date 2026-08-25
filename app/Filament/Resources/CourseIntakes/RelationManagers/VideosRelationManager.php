<?php

namespace App\Filament\Resources\CourseIntakes\RelationManagers;

use App\Models\CourseIntake;
use App\Models\ResourceVideo;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VideosRelationManager extends RelationManager
{
    protected static string $relationship = 'resourceVideos';

    protected static ?string $title = 'Recorded Videos & Lessons';

    public function table(Table $table): Table
    {
        /** @var CourseIntake $intake */
        $intake = $this->getOwnerRecord();

        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('Video Title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('youtube_url')
                    ->label('YouTube / Video Link')
                    ->limit(40)
                    ->copyable(),

                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Video for Cohort')
                    ->icon('heroicon-o-plus')
                    ->form([
                        TextInput::make('title')
                            ->label('Video Title')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('youtube_url')
                            ->label('YouTube URL / Video Link')
                            ->required()
                            ->url()
                            ->columnSpanFull(),

                        Select::make('category')
                            ->label('Category')
                            ->options(array_combine(ResourceVideo::CATEGORIES, ResourceVideo::CATEGORIES))
                            ->default('Recorded Lessons')
                            ->required(),

                        Select::make('target_level')
                            ->label('Target Level')
                            ->options([
                                'Beginner' => 'Beginner',
                                'Intermediate' => 'Intermediate',
                                'Advanced' => 'Advanced',
                            ])
                            ->nullable(),

                        Toggle::make('is_published')
                            ->label('Published')
                            ->default(true),

                        Textarea::make('description')
                            ->label('Description / Lesson Summary')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->mutateFormDataUsing(function (array $data) use ($intake): array {
                        $data['course_id'] = $intake->course_id;
                        $data['course_intake_id'] = $intake->id;
                        $data['is_recorded_lesson'] = true;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->form([
                        TextInput::make('title')
                            ->label('Video Title')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('youtube_url')
                            ->label('YouTube URL / Video Link')
                            ->required()
                            ->url()
                            ->columnSpanFull(),

                        Select::make('category')
                            ->label('Category')
                            ->options(array_combine(ResourceVideo::CATEGORIES, ResourceVideo::CATEGORIES))
                            ->required(),

                        Select::make('target_level')
                            ->label('Target Level')
                            ->options([
                                'Beginner' => 'Beginner',
                                'Intermediate' => 'Intermediate',
                                'Advanced' => 'Advanced',
                            ]),

                        Toggle::make('is_published')
                            ->label('Published'),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(2)
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
