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
                    ->columns(2)
                    ->schema([
                        TextInput::make('proposal_type')
                            ->label('Proposal Type')
                            ->disabled()
                            ->columnSpanFull()
                            ->formatStateUsing(fn ($state) => $state === 'new' ? 'New Course' : 'Existing Course'),

                        // Existing course fields
                        TextInput::make('preferredCourse.title')
                            ->label('Selected Course')
                            ->disabled()
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record?->proposal_type === 'existing'),

                        Textarea::make('motivation_note')
                            ->label('Motivation Note')
                            ->disabled()
                            ->rows(5)
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record?->proposal_type === 'existing'),

                        Textarea::make('competence_note')
                            ->label('Competence Note')
                            ->disabled()
                            ->rows(5)
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record?->proposal_type === 'existing'),

                        TextInput::make('roadmap_path')
                            ->label('Roadmap PDF')
                            ->disabled()
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record?->proposal_type === 'existing' && $record?->roadmap_path),

                        // New course fields
                        TextInput::make('proposed_course_name')
                            ->label('Proposed Course Name')
                            ->disabled()
                            ->visible(fn ($record) => $record?->proposal_type === 'new'),

                        TextInput::make('teaching_location')
                            ->label('Teaching Location')
                            ->disabled()
                            ->visible(fn ($record) => $record?->proposal_type === 'new'),

                        TextInput::make('full_roadmap_path')
                            ->label('Full Roadmap PDF')
                            ->disabled()
                            ->visible(fn ($record) => $record?->proposal_type === 'new' && $record?->full_roadmap_path),

                        TextInput::make('curriculum_path')
                            ->label('Curriculum PDF')
                            ->disabled()
                            ->visible(fn ($record) => $record?->proposal_type === 'new' && $record?->curriculum_path),

                        // Legacy fields (for older applications)
                        Textarea::make('course_concept_note')
                            ->label('Concept Note')
                            ->disabled()
                            ->rows(5)
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record?->course_concept_note && !$record?->proposal_type),

                        Textarea::make('proposed_curriculum')
                            ->label('Proposed Curriculum / Roadmap')
                            ->disabled()
                            ->rows(5)
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record?->proposed_curriculum && !$record?->proposal_type),
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
