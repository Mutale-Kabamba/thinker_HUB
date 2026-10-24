<?php

namespace App\Filament\Resources\Instructors\Instructors\RelationManagers;

use App\Models\Course;
use App\Models\InstructorApplication;
use App\Models\User;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class CoursesRelationManager extends RelationManager
{
    protected static string $relationship = 'instructorCourses';

    protected static ?string $inverseRelationship = 'instructors';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $title = 'Assigned Courses';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Course')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable(),
                TextColumn::make('enrollments_count')
                    ->label('Students')
                    ->counts('enrollments')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->defaultSort('title')
            ->headerActions([
                AttachAction::make()
                    ->label('Assign Course')
                    ->recordTitle(fn (Course $record): string => "{$record->code} - {$record->title}" . (! $record->is_active ? ' (Inactive)' : ''))
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->orderBy('title'))
                    ->recordSelectSearchColumns(['title', 'code']),
            ])
            ->recordActions([
                DetachAction::make()
                    ->label('Unassign')
                    ->modalHeading('Unassign Course')
                    ->modalDescription(fn (Course $record): string => "Are you sure you want to unassign {$record->title} from this instructor?")
                    ->after(function (Course $record, CoursesRelationManager $livewire): void {
                        /** @var User $instructor */
                        $instructor = $livewire->getOwnerRecord();
                        InstructorApplication::query()
                            ->where('user_id', $instructor->id)
                            ->where('assigned_course_id', $record->id)
                            ->update([
                                'assigned_course_id' => null,
                                'course_assignment_action' => 'unassign',
                            ]);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->label('Unassign Selected')
                        ->modalHeading('Unassign Selected Courses')
                        ->modalDescription('Are you sure you want to unassign the selected courses from this instructor?')
                        ->after(function (Collection $records, CoursesRelationManager $livewire): void {
                            /** @var User $instructor */
                            $instructor = $livewire->getOwnerRecord();
                            InstructorApplication::query()
                                ->where('user_id', $instructor->id)
                                ->whereIn('assigned_course_id', $records->pluck('id'))
                                ->update([
                                    'assigned_course_id' => null,
                                    'course_assignment_action' => 'unassign',
                                ]);
                        }),
                ]),
            ]);
    }
}
