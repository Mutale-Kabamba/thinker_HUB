<?php

namespace App\Filament\Resources\CourseGamificationRules;

use App\Filament\Resources\CourseGamificationRules\Pages\CreateCourseGamificationRule;
use App\Filament\Resources\CourseGamificationRules\Pages\EditCourseGamificationRule;
use App\Filament\Resources\CourseGamificationRules\Pages\ListCourseGamificationRules;
use App\Filament\Resources\CourseGamificationRules\Schemas\CourseGamificationRuleForm;
use App\Filament\Resources\CourseGamificationRules\Tables\CourseGamificationRulesTable;
use App\Models\CourseGamificationRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CourseGamificationRuleResource extends Resource
{
    protected static ?string $model = CourseGamificationRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|\UnitEnum|null $navigationGroup = 'REWARDS & CLAIMS';

    protected static ?string $navigationLabel = 'Point Rules Matrix';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return CourseGamificationRuleForm::configure($schema, false, []);
    }

    public static function table(Table $table): Table
    {
        return CourseGamificationRulesTable::configure($table);
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
