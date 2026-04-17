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
                        'admin' => 'Admin',
                    ])
                    ->required()
                    ->default('student')
                    ->live(),
                TextInput::make('track')
                    ->required(fn (callable $get): bool => $get('role') !== 'instructor')
                    ->default('Beginner')
                    ->visible(fn (callable $get): bool => $get('role') !== 'instructor'),
                Select::make('instructorCourses')
                    ->label('Assigned Courses')
                    ->relationship('instructorCourses', 'title')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->visible(fn (callable $get): bool => $get('role') === 'instructor'),
                TextInput::make('proficiency')
                    ->placeholder('e.g. Data Analytics, Python, SQL')
                    ->visible(fn (callable $get): bool => $get('role') === 'instructor'),
                TextInput::make('occupation')
                    ->placeholder('e.g. Senior Data Analyst')
                    ->visible(fn (callable $get): bool => $get('role') === 'instructor'),
                TextInput::make('whatsapp')
                    ->label('WhatsApp number')
                    ->placeholder('+260...')
                    ->visible(fn (callable $get): bool => $get('role') === 'instructor'),
                TextInput::make('linkedin_url')
                    ->label('LinkedIn URL')
                    ->url()
                    ->placeholder('https://linkedin.com/in/...')
                    ->visible(fn (callable $get): bool => $get('role') === 'instructor'),
                TextInput::make('facebook_url')
                    ->label('Facebook URL')
                    ->url()
                    ->placeholder('https://facebook.com/...')
                    ->visible(fn (callable $get): bool => $get('role') === 'instructor'),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->helperText('Inactive instructor accounts cannot access the instructor panel.'),
            ]);
    }
}
