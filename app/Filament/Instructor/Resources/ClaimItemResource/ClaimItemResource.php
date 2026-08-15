<?php

namespace App\Filament\Instructor\Resources\ClaimItemResource;

use App\Filament\Instructor\Concerns\ScopedToInstructor;
use App\Filament\Instructor\Resources\ClaimItemResource\Pages\CreateClaimItem;
use App\Filament\Instructor\Resources\ClaimItemResource\Pages\EditClaimItem;
use App\Filament\Instructor\Resources\ClaimItemResource\Pages\ListClaimItems;
use App\Models\ClaimItem;
use App\Models\Course;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClaimItemResource extends Resource
{
    use ScopedToInstructor;

    protected static ?string $model = ClaimItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static string|\UnitEnum|null $navigationGroup = 'REWARDS & CLAIMS';

    protected static ?string $navigationLabel = 'Course Rewards Catalog';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Reward Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. 5GB Monthly High-Speed Data Bundle'),

                        Select::make('course_id')
                            ->label('Course')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(fn (): array => Course::query()
                                ->whereIn('id', static::instructorCourseIds())
                                ->pluck('title', 'id')
                                ->all())
                            ->helperText('Select which of your courses this reward belongs to.'),

                        Select::make('category')
                            ->options(ClaimItem::CATEGORIES)
                            ->required(),

                        TextInput::make('coin_cost')
                            ->label('Cost in Thinker Coins (TC)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->placeholder('e.g. 350'),

                        TextInput::make('stock_quantity')
                            ->label('Stock Quantity (-1 for unlimited)')
                            ->numeric()
                            ->default(-1)
                            ->required()
                            ->helperText('Enter -1 for infinite digital stock or exact count for limited physical swag.'),

                        FileUpload::make('image_path')
                            ->label('Reward Image')
                            ->image()
                            ->directory('claim-items')
                            ->disk('public')
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull()
                            ->placeholder('Detailed description of this reward and fulfillment details...'),

                        Toggle::make('is_active')
                            ->label('Active & Available in Storefront')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Image')
                    ->disk('public')
                    ->circular(),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('course.title')
                    ->label('Course')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'data' => 'info',
                        'merch' => 'success',
                        'voucher' => 'warning',
                        'perk' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ClaimItem::CATEGORIES[$state] ?? $state)
                    ->sortable(),

                TextColumn::make('coin_cost')
                    ->label('Cost')
                    ->formatStateUsing(fn ($state): string => '🪙 '.number_format((int) $state).' TC')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->formatStateUsing(fn ($state): string => (int) $state < 0 ? 'Unlimited' : ((int) $state === 0 ? 'Out of Stock' : (string) $state))
                    ->badge()
                    ->color(fn ($state): string => (int) $state === 0 ? 'danger' : ((int) $state < 0 ? 'success' : 'info'))
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('coin_cost', 'asc')
            ->filters([
                SelectFilter::make('course_id')
                    ->label('Course')
                    ->options(fn (): array => Course::query()
                        ->whereIn('id', static::instructorCourseIds())
                        ->pluck('title', 'id')
                        ->all()),

                SelectFilter::make('category')
                    ->options(ClaimItem::CATEGORIES),

                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('course_id', static::instructorCourseIds()));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClaimItems::route('/'),
            'create' => CreateClaimItem::route('/create'),
            'edit' => EditClaimItem::route('/{record}/edit'),
        ];
    }
}
