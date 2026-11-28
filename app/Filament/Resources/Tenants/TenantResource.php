<?php

namespace App\Filament\Resources\Tenants;

use App\Filament\Resources\Tenants\Pages\CreateTenant;
use App\Filament\Resources\Tenants\Pages\EditTenant;
use App\Filament\Resources\Tenants\Pages\ListTenants;
use App\Models\Tenant;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';

    protected static string|\UnitEnum|null $navigationGroup = 'PEOPLE & ROLES';

    protected static ?string $navigationLabel = 'Programs & Tenants';

    protected static ?string $modelLabel = 'Program / Tenant';

    protected static ?string $pluralModelLabel = 'Programs & Tenants';

    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return (bool) ($user && ($user->isAdmin() || $user->isProgramAdmin()));
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->withCount(['users', 'courses', 'enrollments']);
        $user = auth()->user();

        if ($user && $user->isProgramAdmin()) {
            $tenantId = $user->managedTenantId();

            return $tenantId !== null
                ? $query->whereKey($tenantId)
                : $query->whereRaw('1 = 0');
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Program / Cohort Name')
                ->placeholder('e.g. Southern Africa Digital Skills, Global Fellowship, UK Partner Cohort')
                ->required()
                ->maxLength(255),

            TextInput::make('slug')
                ->label('Slug (auto-generated if blank)')
                ->placeholder('e.g. zambia-digital-skills')
                ->unique(ignoreRecord: true)
                ->maxLength(255),

            TextInput::make('country_code')
                ->label('Country Code (ISO 2-letter, optional)')
                ->placeholder('e.g. ZM, MW, NA, KE, NG, GB, US')
                ->maxLength(10),

            TextInput::make('country_name')
                ->label('Country / Territory (optional)')
                ->placeholder('e.g. Zambia, Malawi, Namibia, United Kingdom, Global')
                ->maxLength(120),

            TextInput::make('region_label')
                ->label('Region / Division (optional)')
                ->placeholder('e.g. Southern Africa, East Africa, Europe, Global')
                ->maxLength(120),

            TextInput::make('timezone')
                ->label('Default Timezone')
                ->placeholder('e.g. Africa/Lusaka, UTC, Europe/London')
                ->default('UTC')
                ->required()
                ->maxLength(64),

            TextInput::make('currency')
                ->label('Default Currency (ISO)')
                ->placeholder('e.g. USD, ZMW, MWK, NAD, GBP, EUR')
                ->default('USD')
                ->required()
                ->maxLength(10),

            Toggle::make('is_active')
                ->label('Active')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Program / Tenant')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('country_name')
                    ->label('Country')
                    ->placeholder('Global')
                    ->description(fn (Tenant $record): ?string => $record->country_code ?: null)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('region_label')
                    ->label('Region')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('currency')
                    ->badge()
                    ->color('info'),
                TextColumn::make('users_count')
                    ->label('Users')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('courses_count')
                    ->label('Courses')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('enrollments_count')
                    ->label('Enrollments')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTenants::route('/'),
            'create' => CreateTenant::route('/create'),
            'edit' => EditTenant::route('/{record}/edit'),
        ];
    }
}
