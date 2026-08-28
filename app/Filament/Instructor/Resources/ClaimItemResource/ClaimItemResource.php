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
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
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
            ->contentGrid([
                'default' => 1,
                'md' => null,
            ])
            ->columns([
                // Mobile Card View Structure (Stacked & Clean)
                Stack::make([
                    Split::make([
                        ImageColumn::make('image_path')
                            ->disk('public')
                            ->circular()
                            ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->title) . '&background=f59e0b&color=ffffff')
                            ->grow(false),
                        Stack::make([
                            TextColumn::make('title')
                                ->weight('bold')
                                ->size('sm')
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
                                ->size('xs'),
                        ]),
                        TextColumn::make('coin_cost')
                            ->label('Cost')
                            ->formatStateUsing(fn ($state): string => '🪙 '.number_format((int) $state).' TC')
                            ->badge()
                            ->color('warning')
                            ->grow(false),
                    ]),
                    Split::make([
                        TextColumn::make('stock_quantity')
                            ->label('Stock')
                            ->formatStateUsing(fn ($state): string => (int) $state < 0 ? 'Unlimited' : ((int) $state === 0 ? 'Out of Stock' : "Stock: {$state}"))
                            ->badge()
                            ->color(fn ($state): string => (int) $state === 0 ? 'danger' : ((int) $state < 0 ? 'success' : 'info'))
                            ->size('xs'),
                        TextColumn::make('course.title')
                            ->badge()
                            ->color('primary')
                            ->size('xs'),
                    ])->extraAttributes(['class' => 'pt-2 border-t border-gray-100 dark:border-gray-800']),
                ])
                ->extraAttributes([
                    'class' => 'p-4 bg-white dark:bg-[#111b21] rounded-2xl border border-gray-200/80 dark:border-gray-800/80 shadow-sm space-y-2 md:hidden',
                ]),

                // Desktop Table Columns (Hidden on Mobile)
                ImageColumn::make('image_path')
                    ->label('Image')
                    ->disk('public')
                    ->circular()
                    ->visibleFrom('md'),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->visibleFrom('md'),

                TextColumn::make('course.title')
                    ->label('Course')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable()
                    ->visibleFrom('md'),

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
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('coin_cost')
                    ->label('Cost')
                    ->formatStateUsing(fn ($state): string => '🪙 '.number_format((int) $state).' TC')
                    ->badge()
                    ->color('warning')
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->formatStateUsing(fn ($state): string => (int) $state < 0 ? 'Unlimited' : ((int) $state === 0 ? 'Out of Stock' : (string) $state))
                    ->badge()
                    ->color(fn ($state): string => (int) $state === 0 ? 'danger' : ((int) $state < 0 ? 'success' : 'info'))
                    ->sortable()
                    ->visibleFrom('md'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable()
                    ->visibleFrom('md'),
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
                \Filament\Actions\ActionGroup::make([
                    EditAction::make()->icon('heroicon-m-pencil-square'),
                    DeleteAction::make()->icon('heroicon-m-trash'),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray'),
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
