<?php

namespace App\Filament\Resources\ScormPackages;

use App\Filament\Resources\ScormPackages\Pages\ListScormPackages;
use App\Models\Course;
use App\Models\ScormPackage;
use App\Models\Tenant;
use Filament\Actions\DeleteAction;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ScormPackageResource extends Resource
{
    protected static ?string $model = ScormPackage::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cube-transparent';

    protected static string|\UnitEnum|null $navigationGroup = 'ACADEMICS & CONTENT';

    protected static ?string $navigationLabel = 'SCORM / xAPI Packages';

    protected static ?string $modelLabel = 'SCORM / xAPI Package';

    protected static ?string $pluralModelLabel = 'SCORM / xAPI Packages';

    protected static ?int $navigationSort = 12;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return (bool) ($user && ($user->isAdmin() || $user->isProgramAdmin()));
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['course', 'tenant'])
            ->withCount('attempts')
            ->visibleToAdministrator(auth()->user(), includeGlobal: true);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('standard')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => strtoupper(str_replace('_', ' ', $state)))
                    ->color(fn (string $state): string => match ($state) {
                        ScormPackage::STANDARD_XAPI => 'info',
                        ScormPackage::STANDARD_SCORM_2004 => 'success',
                        default => 'primary',
                    }),
                TextColumn::make('course.title')
                    ->label('Course')
                    ->placeholder('Standalone')
                    ->searchable(),
                TextColumn::make('tenant.name')
                    ->label('Program / Tenant')
                    ->placeholder('Global')
                    ->searchable(),
                TextColumn::make('entry_point')
                    ->label('Launch File')
                    ->limit(30),
                TextColumn::make('attempts_count')
                    ->label('Attempts')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScormPackages::route('/'),
        ];
    }
}
