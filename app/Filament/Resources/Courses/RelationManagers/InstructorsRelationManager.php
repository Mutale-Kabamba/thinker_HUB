<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use App\Models\Course;
use App\Models\InstructorApplication;
use App\Models\User;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class InstructorsRelationManager extends RelationManager
{
    protected static string $relationship = 'instructors';

    protected static ?string $inverseRelationship = 'instructorCourses';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Assigned Instructors';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Instructor Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'success',
                        'instructor' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('proficiency')
                    ->label('Expertise')
                    ->placeholder('General')
                    ->badge()
                    ->color('info'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->defaultSort('name')
            ->headerActions([
                AttachAction::make()
                    ->label('Assign Instructor')
                    ->recordTitle(fn (User $record): string => $record->name)
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query
                        ->where('is_active', true)
                        ->where(fn (Builder $q) => $q
                            ->where('role', 'instructor')
                            ->orWhere('role', 'admin')
                            ->orWhereHas('instructorApplication', fn (Builder $sub) => $sub->where('status', 'approved'))
                            ->orWhereHas('instructorCourses')
                        )
                        ->orderBy('name')
                    )
                    ->recordSelectSearchColumns(['name', 'email']),
            ])
            ->recordActions([
                DetachAction::make()
                    ->label('Unassign')
                    ->modalHeading('Unassign Instructor')
                    ->modalDescription(fn (User $record): string => "Are you sure you want to unassign {$record->name} from this course?")
                    ->after(function (User $record, InstructorsRelationManager $livewire): void {
                        /** @var Course $course */
                        $course = $livewire->getOwnerRecord();
                        InstructorApplication::query()
                            ->where('user_id', $record->id)
                            ->where('assigned_course_id', $course->id)
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
                        ->modalHeading('Unassign Selected Instructors')
                        ->modalDescription('Are you sure you want to unassign the selected instructors from this course?')
                        ->after(function (Collection $records, InstructorsRelationManager $livewire): void {
                            /** @var Course $course */
                            $course = $livewire->getOwnerRecord();
                            InstructorApplication::query()
                                ->whereIn('user_id', $records->pluck('id'))
                                ->where('assigned_course_id', $course->id)
                                ->update([
                                    'assigned_course_id' => null,
                                    'course_assignment_action' => 'unassign',
                                ]);
                        }),
                ]),
            ]);
    }
}
