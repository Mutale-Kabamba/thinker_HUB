<?php

namespace App\Filament\Resources\LearningMaterials\Schemas;

use App\Models\Course;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LearningMaterialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                Select::make('category')
                    ->label('Category')
                    ->required()
                    ->options([
                        'Curriculum' => 'Curriculum',
                        'Rules' => 'Rules',
                        'General Notices' => 'General Notices',
                    ])
                    ->default('General Notices'),

                Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->columnSpanFull(),

                Select::make('material_type')
                    ->label('Material Type')
                    ->required()
                    ->options([
                        'Document' => 'Document',
                        'Image' => 'Image',
                        'Video' => 'Video',
                        'Link' => 'Link',
                    ])
                    ->default('Document')
                    ->live(),

                Select::make('course_id')
                    ->label('Course')
                    ->required()
                    ->searchable()
                    ->options(fn (): array => Course::query()->where('is_active', true)->orderBy('title')->pluck('title', 'id')->toArray())
                    ->live(),

                Select::make('scope')
                    ->label('Scope')
                    ->required()
                    ->options([
                        'all' => 'All Students in Course',
                        'level' => 'Specific Level / Track',
                        'personal' => 'Specific Student',
                    ])
                    ->default('all')
                    ->live(),

                Select::make('target_track')
                    ->label('Target Track / Level')
                    ->options([
                        'Beginner' => 'Beginner',
                        'Intermediate' => 'Intermediate',
                        'Advanced' => 'Advanced',
                    ])
                    ->visible(fn (callable $get): bool => in_array($get('scope'), ['level', 'personal']))
                    ->required(fn (callable $get): bool => $get('scope') === 'level')
                    ->live(),

                Select::make('target_user_id')
                    ->label('Target Student')
                    ->searchable()
                    ->options(function (callable $get): array {
                        $courseId = $get('course_id');
                        $track = $get('target_track');

                        if (! $courseId) {
                            return [];
                        }

                        $query = User::query()
                            ->where(function ($q): void {
                                $q->whereNull('role')->orWhere('role', '!=', 'admin');
                            })
                            ->whereHas('courses', fn ($q) => $q->where('courses.id', $courseId));

                        if ($track) {
                            $query->where('track', $track);
                        }

                        return $query->orderBy('name')->pluck('name', 'id')->toArray();
                    })
                    ->visible(fn (callable $get): bool => $get('scope') === 'personal')
                    ->required(fn (callable $get): bool => $get('scope') === 'personal'),

                FileUpload::make('file_path')
                    ->label(fn (callable $get): string => match ($get('material_type')) {
                        'Image' => 'Upload Image',
                        'Video' => 'Upload Video',
                        default => 'Upload File',
                    })
                    ->disk('public')
                    ->directory('materials')
                    ->acceptedFileTypes(fn (callable $get): array => match ($get('material_type')) {
                        'Image' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp',
                            'image/svg+xml',
                        ],
                        'Video' => [
                            'video/mp4',
                            'video/webm',
                            'video/ogg',
                            'video/avi',
                            'video/quicktime',
                        ],
                        default => [
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-powerpoint',
                            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'text/plain',
                            'text/csv',
                        ],
                    })
                    ->visible(fn (callable $get): bool => in_array($get('material_type'), ['Document', 'Image', 'Video']))
                    ->required(fn (callable $get): bool => $get('material_type') === 'Document' || $get('material_type') === 'Image')
                    ->columnSpanFull(),

                TextInput::make('video_url')
                    ->label('Video URL (YouTube, Vimeo, etc.)')
                    ->url()
                    ->visible(fn (callable $get): bool => $get('material_type') === 'Video')
                    ->helperText('Provide a URL or upload a video file above, or both.'),

                TextInput::make('link_url')
                    ->label('Link URL')
                    ->url()
                    ->visible(fn (callable $get): bool => $get('material_type') === 'Link')
                    ->required(fn (callable $get): bool => $get('material_type') === 'Link'),
            ]);
    }
}
