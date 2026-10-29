<?php

namespace App\Filament\Resources\CourseIntakes\Schemas;

use App\Models\Course;
use App\Models\CourseIntake;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class CourseIntakeForm
{
    /**
     * @param  (callable(): array<string, string>)|null  $courseOptions
     */
    public static function configure(Schema $schema, ?callable $courseOptions = null): Schema
    {
        $resolveCourseOptions = $courseOptions ?? fn (): array => Course::query()
            ->where('is_active', true)
            ->orderBy('title')
            ->get()
            ->mapWithKeys(fn (Course $c) => [$c->id => $c->title . ' (' . $c->code . ')'])
            ->toArray();

        return $schema
            ->components([
                Section::make('Cohort Metrics & Overview')
                    ->icon('heroicon-o-chart-bar-square')
                    ->collapsible()
                    ->visible(fn (string $operation): bool => $operation !== 'create')
                    ->schema([
                        Placeholder::make('metrics_summary')
                            ->label('')
                            ->content(function (?CourseIntake $record): ?HtmlString {
                                if (! $record) {
                                    return null;
                                }

                                $studentCount = $record->enrollments()->count();
                                $capacityText = $record->max_capacity ? " <span style='font-size:0.85rem;font-weight:500;color:var(--hub-muted,#94a3b8);'>/ {$record->max_capacity} seats</span>" : '';
                                $assignmentCount = $record->assignments()->count();
                                $assessmentCount = $record->assessments()->count();
                                $quizCount = $record->quizzes()->count();
                                $sessionCount = $record->sessions()->count();
                                $materialCount = $record->learningMaterials()->count();
                                $videoCount = $record->resourceVideos()->count();

                                return new HtmlString("
                                    <div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 0.75rem;'>
                                        <div style='background: var(--hub-surface, #1e293b); padding: 0.85rem; border-radius: 0.65rem; border: 1px solid var(--hub-border, #334155);'>
                                            <div style='font-size: 0.72rem; color: var(--hub-muted, #94a3b8); font-weight: 600; text-transform: uppercase;'>Enrolled Students</div>
                                            <div style='font-size: 1.35rem; font-weight: 800; color: #38bdf8;'>{$studentCount}{$capacityText}</div>
                                        </div>
                                        <div style='background: var(--hub-surface, #1e293b); padding: 0.85rem; border-radius: 0.65rem; border: 1px solid var(--hub-border, #334155);'>
                                            <div style='font-size: 0.72rem; color: var(--hub-muted, #94a3b8); font-weight: 600; text-transform: uppercase;'>Assignments</div>
                                            <div style='font-size: 1.35rem; font-weight: 800; color: #a855f7;'>{$assignmentCount}</div>
                                        </div>
                                        <div style='background: var(--hub-surface, #1e293b); padding: 0.85rem; border-radius: 0.65rem; border: 1px solid var(--hub-border, #334155);'>
                                            <div style='font-size: 0.72rem; color: var(--hub-muted, #94a3b8); font-weight: 600; text-transform: uppercase;'>Assessments</div>
                                            <div style='font-size: 1.35rem; font-weight: 800; color: #ec4899;'>{$assessmentCount}</div>
                                        </div>
                                        <div style='background: var(--hub-surface, #1e293b); padding: 0.85rem; border-radius: 0.65rem; border: 1px solid var(--hub-border, #334155);'>
                                            <div style='font-size: 0.72rem; color: var(--hub-muted, #94a3b8); font-weight: 600; text-transform: uppercase;'>Quizzes</div>
                                            <div style='font-size: 1.35rem; font-weight: 800; color: #eab308;'>{$quizCount}</div>
                                        </div>
                                        <div style='background: var(--hub-surface, #1e293b); padding: 0.85rem; border-radius: 0.65rem; border: 1px solid var(--hub-border, #334155);'>
                                            <div style='font-size: 0.72rem; color: var(--hub-muted, #94a3b8); font-weight: 600; text-transform: uppercase;'>Schedules / Sessions</div>
                                            <div style='font-size: 1.35rem; font-weight: 800; color: #10b981;'>{$sessionCount}</div>
                                        </div>
                                        <div style='background: var(--hub-surface, #1e293b); padding: 0.85rem; border-radius: 0.65rem; border: 1px solid var(--hub-border, #334155);'>
                                            <div style='font-size: 0.72rem; color: var(--hub-muted, #94a3b8); font-weight: 600; text-transform: uppercase;'>Materials</div>
                                            <div style='font-size: 1.35rem; font-weight: 800; color: #06b6d4;'>{$materialCount}</div>
                                        </div>
                                        <div style='background: var(--hub-surface, #1e293b); padding: 0.85rem; border-radius: 0.65rem; border: 1px solid var(--hub-border, #334155);'>
                                            <div style='font-size: 0.72rem; color: var(--hub-muted, #94a3b8); font-weight: 600; text-transform: uppercase;'>Videos</div>
                                            <div style='font-size: 1.35rem; font-weight: 800; color: #f43f5e;'>{$videoCount}</div>
                                        </div>
                                    </div>
                                ");
                            }),
                    ])
                    ->columnSpanFull(),

                Section::make('Cohort Identity & Operational Status')
                    ->description('Configure cohort designation, assigned course, and enrollment capacity.')
                    ->icon('heroicon-o-academic-cap')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('course_id')
                            ->label('Course')
                            ->options($resolveCourseOptions)
                            ->searchable()
                            ->required()
                            ->helperText('Assigned course offering.')
                            ->columnSpan(['default' => 1, 'sm' => 2, 'lg' => 2]),

                        Select::make('status')
                            ->label('Intake Status')
                            ->options(CourseIntake::STATUSES)
                            ->default(CourseIntake::STATUS_UPCOMING)
                            ->selectablePlaceholder(false)
                            ->required(),

                        TextInput::make('name')
                            ->label('Intake / Class Name')
                            ->placeholder('e.g. Intake 1 - Jan 2026, Cohort Alpha, Weekend Class')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(['default' => 1, 'sm' => 2, 'lg' => 2]),

                        TextInput::make('max_capacity')
                            ->label('Max Capacity (Seats)')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('e.g. 30 (Unlimited if blank)')
                            ->helperText('Optional admission seat cap.'),

                        Toggle::make('is_active')
                            ->label('Set as Current Active Intake')
                            ->helperText('When enabled, new student enrollments and checkouts are automatically assigned to this cohort.')
                            ->default(false)
                            ->columnSpanFull(),
                    ])
                    ->columns(['default' => 1, 'sm' => 2, 'lg' => 3]),

                Section::make('Timeline & Milestone Schedule')
                    ->description('Cohort start and completion dates, registration cutoff, and subsequent cohort broadcast.')
                    ->icon('heroicon-o-calendar-days')
                    ->columnSpanFull()
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Intake Start Date')
                            ->required(),

                        DatePicker::make('end_date')
                            ->label('Intake End Date')
                            ->afterOrEqual('start_date')
                            ->helperText('Leave empty if self-paced or ongoing.'),

                        DatePicker::make('registration_deadline')
                            ->label('Registration Deadline')
                            ->helperText('Last date for students to enroll.'),

                        DatePicker::make('next_intake_start_date')
                            ->label('Next Cohort Start Date')
                            ->helperText('Forecasted start of next intake.'),

                        Textarea::make('notes')
                            ->label('Internal Notes & Schedule Guidelines')
                            ->rows(3)
                            ->placeholder('Optional guidelines, instructor notes, meeting links, or cohort details...')
                            ->columnSpanFull(),
                    ])
                    ->columns(['default' => 1, 'md' => 2]),
            ]);
    }
}
