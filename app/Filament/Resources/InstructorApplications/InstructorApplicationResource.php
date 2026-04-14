<?php

namespace App\Filament\Resources\InstructorApplications;

use App\Filament\Resources\InstructorApplications\Pages\EditInstructorApplication;
use App\Filament\Resources\InstructorApplications\Pages\ListInstructorApplications;
use App\Filament\Resources\InstructorApplications\Schemas\InstructorApplicationForm;
use App\Filament\Resources\InstructorApplications\Tables\InstructorApplicationsTable;
use App\Models\InstructorApplication;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InstructorApplicationResource extends Resource
{
    protected static ?string $model = InstructorApplication::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $navigationGroup = 'Users';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Instructor Applications';

    public static function form(Schema $schema): Schema
    {
        return InstructorApplicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InstructorApplicationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInstructorApplications::route('/'),
            'edit' => EditInstructorApplication::route('/{record}/edit'),
        ];
    }
}
