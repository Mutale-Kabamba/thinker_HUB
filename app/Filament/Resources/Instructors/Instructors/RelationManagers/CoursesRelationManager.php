<?php

namespace App\Filament\Resources\Instructors\Instructors\RelationManagers;

use App\Models\Course;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CoursesRelationManager extends RelationManager
{
    protected static string $relationship = 'instructorCourses';

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
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['title', 'code']),
            ])
            ->recordActions([
                DetachAction::make()->label('Unassign'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()->label('Unassign Selected'),
                ]),
            ]);
    }
}
