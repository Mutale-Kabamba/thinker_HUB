<?php

namespace App\Filament\Resources\AssessmentSubmissions;

use App\Filament\Resources\AssessmentSubmissions\Pages\EditAssessmentSubmission;
use App\Filament\Resources\AssessmentSubmissions\Pages\ListAssessmentSubmissions;
use App\Filament\Resources\AssessmentSubmissions\Schemas\AssessmentSubmissionForm;
use App\Filament\Resources\AssessmentSubmissions\Tables\AssessmentSubmissionsTable;
use App\Models\AssessmentSubmission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AssessmentSubmissionResource extends Resource
{
    protected static ?string $model = AssessmentSubmission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'Assessment Submissions';

    protected static string|\UnitEnum|null $navigationGroup = 'Submissions';

    public static function form(Schema $schema): Schema
    {
        return AssessmentSubmissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssessmentSubmissionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssessmentSubmissions::route('/'),
            'edit' => EditAssessmentSubmission::route('/{record}/edit'),
        ];
    }
}
