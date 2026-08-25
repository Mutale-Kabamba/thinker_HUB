<?php

namespace App\Filament\Instructor\Resources\CourseIntakeResource;

use App\Filament\Instructor\Concerns\ScopedToInstructor;
use App\Filament\Instructor\Resources\CourseIntakeResource\Pages\CreateCourseIntake;
use App\Filament\Instructor\Resources\CourseIntakeResource\Pages\EditCourseIntake;
use App\Filament\Instructor\Resources\CourseIntakeResource\Pages\ListCourseIntakes;
use App\Filament\Instructor\Resources\CourseIntakeResource\Pages\ViewCourseIntake;
use App\Filament\Resources\CourseIntakes\RelationManagers\AssignmentsRelationManager;
use App\Filament\Resources\CourseIntakes\RelationManagers\AssessmentsRelationManager;
use App\Filament\Resources\CourseIntakes\RelationManagers\MaterialsRelationManager;
use App\Filament\Resources\CourseIntakes\RelationManagers\QuizzesRelationManager;
use App\Filament\Resources\CourseIntakes\RelationManagers\SessionsRelationManager;
use App\Filament\Resources\CourseIntakes\RelationManagers\StudentsRelationManager;
use App\Filament\Resources\CourseIntakes\RelationManagers\VideosRelationManager;
use App\Filament\Resources\CourseIntakes\Schemas\CourseIntakeForm;
use App\Filament\Resources\CourseIntakes\Tables\CourseIntakesTable;
use App\Models\CourseIntake;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CourseIntakeResource extends Resource
{
    use ScopedToInstructor;

    protected static ?string $model = CourseIntake::class;

    protected static ?string $slug = 'course-intakes';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'ACADEMICS & CONTENT';

    protected static ?string $navigationLabel = 'Classes & Intakes';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return CourseIntakeForm::configure(
            $schema,
            fn (): array => static::instructorCourseOptions()
        );
    }

    public static function table(Table $table): Table
    {
        return CourseIntakesTable::configure(
            $table,
            fn (): array => static::instructorCourseOptions()
        )->modifyQueryUsing(
            fn (Builder $query) => $query->whereIn('course_id', static::instructorCourseIds())
        );
    }

    public static function getRelations(): array
    {
        return [
            StudentsRelationManager::class,
            AssignmentsRelationManager::class,
            AssessmentsRelationManager::class,
            QuizzesRelationManager::class,
            SessionsRelationManager::class,
            MaterialsRelationManager::class,
            VideosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourseIntakes::route('/'),
            'create' => CreateCourseIntake::route('/create'),
            'view' => ViewCourseIntake::route('/{record}'),
            'edit' => EditCourseIntake::route('/{record}/edit'),
        ];
    }
}
