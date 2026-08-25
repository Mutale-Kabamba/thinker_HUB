<?php

namespace App\Filament\Resources\CourseIntakes\RelationManagers;

use App\Models\CourseIntake;
use App\Models\Enrollment;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';

    protected static ?string $title = 'Enrolled Students (Cohort)';

    public function table(Table $table): Table
    {
        /** @var CourseIntake $intake */
        $intake = $this->getOwnerRecord();

        return $table
            ->recordTitleAttribute('user.name')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Student Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('user.track')
                    ->label('Level / Track')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Enrolled Date')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),

                IconColumn::make('completed')
                    ->label('Course Completed')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Enroll Student into Cohort')
                    ->icon('heroicon-o-user-plus')
                    ->modalHeading('Enroll Student into Cohort')
                    ->form([
                        Select::make('user_id')
                            ->label('Select Student')
                            ->searchable()
                            ->required()
                            ->options(function () use ($intake): array {
                                $enrolledUserIds = Enrollment::query()
                                    ->where('course_id', $intake->course_id)
                                    ->where('course_intake_id', $intake->id)
                                    ->pluck('user_id');

                                return User::query()
                                    ->where(function ($q) {
                                        $q->whereNull('role')->orWhere('role', 'student');
                                    })
                                    ->whereNotIn('id', $enrolledUserIds)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            }),
                    ])
                    ->mutateFormDataUsing(function (array $data) use ($intake): array {
                        $data['course_id'] = $intake->course_id;
                        $data['course_intake_id'] = $intake->id;

                        return $data;
                    })
                    ->after(function () {
                        Notification::make()
                            ->title('Student successfully enrolled into this cohort.')
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->label('Unenroll')
                    ->modalHeading('Remove student from this cohort'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Unenroll Selected'),
                ]),
            ]);
    }
}
