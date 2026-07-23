<?php

namespace App\Filament\Resources\ResourceVideos\Schemas;

use App\Models\Course;
use App\Models\ResourceVideo;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ResourceVideoForm
{
    /**
     * @param (callable(): array<string, string>)|null $courseOptions
     */
    public static function configure(Schema $schema, ?callable $courseOptions = null): Schema
    {
        $resolveCourseOptions = $courseOptions ?? fn (): array => Course::query()->where('is_active', true)->orderBy('title')->pluck('title', 'id')->toArray();

        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                TextInput::make('youtube_url')
                    ->label('YouTube URL')
                    ->required()
                    ->url()
                    ->helperText('Paste any YouTube link (watch, youtu.be, shorts, or embed).')
                    ->maxLength(255),

                Toggle::make('is_recorded_lesson')
                    ->label('Recorded lesson')
                    ->helperText('Turn on if this video is an official recorded lesson for enrolled students.')
                    ->default(false)
                    ->afterStateUpdated(function ($state, callable $set): void {
                        if ($state) {
                            $set('category', 'Recorded Lessons');
                        }
                    })
                    ->live(),

                Select::make('course_id')
                    ->label('Course')
                    ->options($resolveCourseOptions)
                    ->searchable()
                    ->visible(fn (callable $get): bool => (bool) $get('is_recorded_lesson'))
                    ->required(fn (callable $get): bool => (bool) $get('is_recorded_lesson')),

                Select::make('target_level')
                    ->label('Target level')
                    ->options([
                        'Beginner' => 'Beginner',
                        'Intermediate' => 'Intermediate',
                        'Advanced' => 'Advanced',
                    ])
                    ->helperText('Leave empty to show this recorded lesson to all levels in the selected course.')
                    ->visible(fn (callable $get): bool => (bool) $get('is_recorded_lesson')),

                Select::make('category')
                    ->label('Category')
                    ->options(ResourceVideo::categoryOptions())
                    ->default('General')
                    ->required()
                    ->searchable(),

                TextInput::make('channel_name')
                    ->label('Channel / Source')
                    ->maxLength(255),

                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),

                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Lower numbers appear first.'),

                Toggle::make('is_published')
                    ->label('Published (visible to students)')
                    ->default(true),
            ]);
    }
}
