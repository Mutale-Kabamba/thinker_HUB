<?php

namespace App\Filament\Resources\CourseSessions\RelationManagers;

use App\Models\Attendance;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;

class AttendancesRelationManager extends RelationManager
{
    protected static string $relationship = 'attendances';

    protected static ?string $title = 'Attendance';

    public function mount(): void
    {
        parent::mount();

        // Idempotently create an attendance row for every student who should
        // attend this session, so the marker sees one row per student.
        if ($this->ownerRecord) {
            Attendance::syncForSession($this->ownerRecord);
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.name')
                    ->label('Student')
                    ->searchable(),
                SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        Attendance::STATUS_PRESENT => 'Present',
                        Attendance::STATUS_ABSENT => 'Absent',
                        Attendance::STATUS_LATE => 'Late',
                    ])
                    ->selectablePlaceholder(false),
                TextInputColumn::make('notes')
                    ->label('Notes')
                    ->placeholder('Optional note'),
                TextColumn::make('updated_at')
                    ->label('Marked At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id');
    }
}
