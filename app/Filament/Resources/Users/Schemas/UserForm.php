<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Course;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state)),
                Select::make('role')
                    ->options([
                        'student' => 'Student',
                        'instructor' => 'Instructor',
                        'blogger' => 'Blogger (Short Blogs)',
                        'researcher' => 'Researcher (Tips & Tricks)',
                        'employer' => 'Employer (Opportunities)',
                        'admin' => 'Admin',
                    ])
                    ->required()
                    ->default('student')
                    ->live(),
                TextInput::make('track')
                    ->required(fn (callable $get): bool => $get('role') === 'student')
                    ->default('Beginner')
                    ->visible(fn (callable $get): bool => $get('role') === 'student'),
                Select::make('instructorCourses')
                    ->label('Assigned Instructor Courses')
                    ->relationship('instructorCourses', 'title')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->helperText('Courses this user teaches/instructs.')
                    ->visible(fn (callable $get): bool => in_array($get('role'), ['instructor', 'admin'], true)),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->helperText('Inactive accounts cannot access workspaces.'),
            ]);
    }
}

use Filament\Forms\Components\Textarea;

Textarea::make('bio')
    ->label('Instructor Bio')
    ->rows(4)
    ->columnSpanFull();
