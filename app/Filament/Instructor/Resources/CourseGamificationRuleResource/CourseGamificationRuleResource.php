<?php

namespace App\Filament\Instructor\Resources\CourseGamificationRuleResource;

use App\Filament\Instructor\Concerns\ScopedToInstructor;
use App\Filament\Instructor\Resources\CourseGamificationRuleResource\Pages\CreateCourseGamificationRule;
use App\Filament\Instructor\Resources\CourseGamificationRuleResource\Pages\EditCourseGamificationRule;
use App\Filament\Instructor\Resources\CourseGamificationRuleResource\Pages\ListCourseGamificationRules;
use App\Filament\Resources\CourseGamificationRules\Schemas\CourseGamificationRuleForm;
use App\Filament\Resources\CourseGamificationRules\Tables\CourseGamificationRulesTable;
use App\Models\CourseGamificationRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CourseGamificationRuleResource extends Resource
{
    use ScopedToInstructor;

    protected static ?string $model = CourseGamificationRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|\UnitEnum|null $navigationGroup = 'REWARDS & CLAIMS';

    protected static ?string $navigationLabel = 'Course Point Rules';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return CourseGamificationRuleForm::configure($schema, true, static::instructorCourseIds());
    }

    public static function table(Table $table): Table
    {
        return CourseGamificationRulesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('course_id', static::instructorCourseIds());
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourseGamificationRules::route('/'),
            'create' => CreateCourseGamificationRule::route('/create'),
            'edit' => EditCourseGamificationRule::route('/{record}/edit'),
        ];
    }
}
