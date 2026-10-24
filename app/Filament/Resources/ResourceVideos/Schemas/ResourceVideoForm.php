<?php

namespace App\Filament\Resources\ResourceVideos\Schemas;

use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\ResourceVideo;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class ResourceVideoForm
{
    /**
     * @param  (callable(): array<string, string>)|null  $courseOptions
     */
    public static function configure(Schema $schema, ?callable $courseOptions = null): Schema
    {
        $resolveCourseOptions = $courseOptions ?? fn (): array => Course::query()->where('is_active', true)->orderBy('title')->pluck('title', 'id')->toArray();

        return $schema
            ->components([
                Section::make('Video Content & Stream Source')
                    ->description('Media title, author attribution, and streaming video source.')
                    ->icon('heroicon-o-video-camera')
                    ->schema([
                        TextInput::make('title')
                            ->label('Video Title')
                            ->placeholder('e.g. Masterclass: Building High-Performance Web Apps')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(['default' => 1, 'sm' => 2, 'lg' => 2]),

                        TextInput::make('channel_name')
                            ->label('Channel / Source Attribution')
                            ->placeholder('e.g. think.er HUB / Instructor Name')
                            ->maxLength(255),

                        TextInput::make('sort_order')
                            ->label('Catalog Sort Order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first in the library.'),

                        ToggleButtons::make('video_source')
                            ->label('Video Source Format')
                            ->options([
                                'youtube' => 'YouTube Stream / Embed',
                                'upload' => 'Upload Video File (Local/Storage)',
                            ])
                            ->icons([
                                'youtube' => 'heroicon-o-play-circle',
                                'upload' => 'heroicon-o-arrow-up-tray',
                            ])
                            ->colors([
                                'youtube' => 'primary',
                                'upload' => 'primary',
                            ])
                            ->default(fn (?ResourceVideo $record): string => $record?->hasLocalVideo() ? 'upload' : 'youtube')
                            ->inline()
                            ->live()
                            ->columnSpanFull(),

                        TextInput::make('youtube_url')
                            ->label('YouTube Stream URL')
                            ->placeholder('https://www.youtube.com/watch?v=...')
                            ->url()
                            ->helperText('Paste any YouTube link (watch, youtu.be, shorts, or embed).')
                            ->maxLength(255)
                            ->visible(fn (callable $get): bool => $get('video_source') !== 'upload')
                            ->required(fn (callable $get): bool => $get('video_source') !== 'upload')
                            ->live()
                            ->columnSpanFull(),

                        Placeholder::make('video_preview')
                            ->label('Live Video Player Preview')
                            ->visible(fn (callable $get): bool => $get('video_source') !== 'upload' && filled($get('youtube_url')))
                            ->content(function (callable $get, ?ResourceVideo $record): HtmlString {
                                $url = $get('youtube_url') ?? $record?->youtube_url;
                                if ($get('video_source') !== 'upload' && filled($url)) {
                                    preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/', (string) $url, $matches);
                                    $ytId = $matches[1] ?? null;
                                    if ($ytId) {
                                        return new HtmlString("
                                            <div style='position: relative; max-width: 640px; padding-bottom: 36.25%; height: 0; overflow: hidden; border-radius: 0.75rem; border: 1px solid var(--hub-border, #334155); background: #000; box-shadow: 0 4px 14px rgba(0,0,0,0.15);'>
                                                <iframe src='https://www.youtube.com/embed/{$ytId}' style='position: absolute; top:0; left: 0; width: 100%; height: 100%; border: 0;' allowfullscreen></iframe>
                                            </div>
                                        ");
                                    }
                                }

                                return new HtmlString('');
                            })
                            ->columnSpanFull(),

                        FileUpload::make('video_file')
                            ->label('Video Media File')
                            ->disk(config('videos.upload_disk', 'public'))
                            ->directory('resource-videos')
                            ->visibility('public')
                            ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/quicktime', 'video/x-matroska', 'video/mpeg', 'video/3gpp'])
                            ->maxSize(30720)
                            ->storeFileNamesIn('video_file_original_name')
                            ->helperText('MP4, WebM, MOV, MKV up to 30 MB. Background transcoding runs automatically.')
                            ->visible(fn (callable $get): bool => $get('video_source') === 'upload')
                            ->required(fn (callable $get, ?ResourceVideo $record): bool => $get('video_source') === 'upload' && ! $record?->hasLocalVideo())
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Video Description & Key Topics')
                            ->rows(3)
                            ->placeholder('Summarize lesson topics, timestamps, prerequisites, or downloadable attachments...')
                            ->columnSpanFull(),
                    ])
                    ->columns(['default' => 1, 'sm' => 2, 'lg' => 3]),

                Section::make('Access Scope & Classification')
                    ->description('Assign viewing audience, course connection, cohort level, and publication status.')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Select::make('category')
                            ->label('Category')
                            ->options(ResourceVideo::categoryOptions())
                            ->default('General')
                            ->required()
                            ->searchable(),

                        Select::make('course_id')
                            ->label('Course Scope')
                            ->options($resolveCourseOptions)
                            ->searchable()
                            ->nullable()
                            ->placeholder('All Courses (Visible to all students)')
                            ->helperText('Assign to a specific course, or leave open to all students.')
                            ->live(),

                        Select::make('course_intake_id')
                            ->label('Target Intake / Class')
                            ->nullable()
                            ->searchable()
                            ->options(function (callable $get): array {
                                $courseId = $get('course_id');
                                if (! $courseId) {
                                    return [];
                                }

                                return CourseIntake::query()
                                    ->where('course_id', $courseId)
                                    ->where('status', '!=', CourseIntake::STATUS_ARCHIVED)
                                    ->orderBy('start_date', 'desc')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->placeholder('All Intakes / Entire Course')
                            ->helperText('Leave empty to share across all cohorts.')
                            ->visible(fn (callable $get): bool => filled($get('course_id'))),

                        Select::make('target_level')
                            ->label('Target Level')
                            ->options([
                                'Beginner' => 'Beginner',
                                'Intermediate' => 'Intermediate',
                                'Advanced' => 'Advanced',
                            ])
                            ->nullable()
                            ->placeholder('All Levels')
                            ->helperText('Target a specific proficiency tier.')
                            ->visible(fn (callable $get): bool => filled($get('course_id'))),

                        Toggle::make('is_recorded_lesson')
                            ->label('Recorded Lesson')
                            ->helperText('Tag as an official cohort recorded lesson.')
                            ->default(false)
                            ->afterStateUpdated(function ($state, callable $set): void {
                                if ($state) {
                                    $set('category', 'Recorded Lessons');
                                }
                            })
                            ->live(),

                        Toggle::make('is_published')
                            ->label('Published (Visible to Students)')
                            ->default(true),
                    ])
                    ->columns(['default' => 1, 'sm' => 2, 'lg' => 4]),
            ]);
    }
}
