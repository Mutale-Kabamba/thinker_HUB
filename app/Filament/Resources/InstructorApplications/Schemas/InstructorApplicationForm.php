<?php

namespace App\Filament\Resources\InstructorApplications\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Schema;

class InstructorApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Applicant Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->disabled(),

                        TextInput::make('email')
                            ->disabled(),

                        TextInput::make('phone')
                            ->disabled(),

                        TextInput::make('linkedin_url')
                            ->label('LinkedIn')
                            ->disabled(),

                        TextInput::make('portfolio_url')
                            ->label('Portfolio')
                            ->disabled(),

                        Textarea::make('bio')
                            ->disabled()
                            ->columnSpanFull()
                            ->rows(3),
                    ]),

                Section::make('Qualifications & Experience')
                    ->schema([
                        Textarea::make('qualifications')
                            ->disabled()
                            ->rows(4)
                            ->columnSpanFull(),

                        Textarea::make('experience')
                            ->disabled()
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Course Proposal')
                    ->schema([
                        Textarea::make('course_concept_note')
                            ->label('Concept Note')
                            ->disabled()
                            ->rows(5)
                            ->columnSpanFull(),

                        Textarea::make('proposed_curriculum')
                            ->label('Proposed Curriculum / Roadmap')
                            ->disabled()
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),

                Section::make('Review Decision')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending Review',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required(),

                        Textarea::make('admin_notes')
                            ->label('Admin Notes / Feedback')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
