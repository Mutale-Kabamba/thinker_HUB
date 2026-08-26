<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports\Pages\GenerateReports;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ReportResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'reports';

    protected static ?string $modelLabel = 'Academic Report';

    protected static ?string $pluralModelLabel = 'Academic Reports';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'GRADING & EVALUATIONS';

    protected static ?string $navigationLabel = 'Academic Reports';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return (bool) (auth()->user()?->role === 'admin' || auth()->user()?->canSwitchToAdmin());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => GenerateReports::route('/'),
        ];
    }
}
