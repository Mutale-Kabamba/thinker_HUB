<?php

namespace App\Filament\Resources\Assignments\Schemas;

use App\Models\Course;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name of Assignment')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('Description')
                    ->rows(4)
                    ->columnSpanFull(),

                Select::make('course_id')
                    ->label('Course')
                    ->required()
                    ->searchable()
                    ->options(fn (): array => Course::query()->where('is_active', true)->orderBy('title')->pluck('title', 'id')->toArray())
                    ->live(),

                Select::make('target_level')
                    ->label('Target Track / Level')
                    ->required()
                    ->options([
                        'Beginner' => 'Beginner',
                        'Intermediate' => 'Intermediate',
                        'Advanced' => 'Advanced',
                    ])
                    ->live(),

                Select::make('target_user_id')
                    ->label('Target User')
                    ->searchable()
                    ->options(function (callable $get): array {
                        $courseId = $get('course_id');
                        $level = $get('target_level');

                        $options = [
                            'all' => 'All Students',
                        ];

                        if (! $courseId || ! $level) {
                            return $options;
                        }

                        $students = User::query()
                            ->where(function ($query): void {
                                $query->whereNull('role')->orWhere('role', '!=', 'admin');
                            })
                            ->where('track', $level)
                            ->whereHas('courses', fn ($query) => $query->where('courses.id', $courseId))
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();

                        return $options + $students;
                    })
                    ->default('all')
                    ->required()
                    ->dehydrateStateUsing(fn (mixed $state): mixed => $state === 'all' ? null : $state)
                    ->helperText('Choose All Students to send to all students in the selected course and level.'),

                FileUpload::make('file_path')
                    ->label('File Upload')
                    ->directory('assignments')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-powerpoint',
                        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'text/plain',
                        'text/csv',
                    ])
                    ->required()
                    ->columnSpanFull(),

                DatePicker::make('date_given')
                    ->label('Date Given')
                    ->required()
                    ->default(now()),

                DatePicker::make('due_date')
                    ->label('Due Date')
                    ->required(),
            ]);
    }
}
