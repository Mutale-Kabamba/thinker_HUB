<?php

namespace App\Filament\Resources\Courses\RelationManagers;

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

class InstructorsRelationManager extends RelationManager
{
    protected static string $relationship = 'instructors';

    protected static ?string $inverseRelationship = 'instructorCourses';

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
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query->where(fn (Builder $q) => $q->where('role', 'instructor')->orWhere('role', 'admin')))
                    ->recordSelectSearchColumns(['name', 'email']),
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
