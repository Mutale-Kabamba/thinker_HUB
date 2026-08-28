<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
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
                        ImageColumn::make('profile_picture')
                            ->circular()
                            ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=0d9488&color=ffffff')
                            ->grow(false),
                        Stack::make([
                            TextColumn::make('name')
                                ->weight('bold')
                                ->size('sm')
                                ->searchable(),
                            TextColumn::make('email')
                                ->size('xs')
                                ->color('gray')
                                ->searchable(),
                        ]),
                        TextColumn::make('role')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'admin' => 'danger',
                                'instructor' => 'info',
                                'blogger' => 'primary',
                                'researcher' => 'warning',
                                'employer' => 'success',
                                default => 'gray',
                            })
                            ->grow(false),
                    ]),
                    Split::make([
                        TextColumn::make('created_at')
                            ->label('Joined')
                            ->dateTime('M d, Y')
                            ->size('xs')
                            ->color('gray'),
                        IconColumn::make('is_active')
                            ->label('Active')
                            ->boolean()
                            ->size('xs'),
                    ])->extraAttributes(['class' => 'pt-2 border-t border-gray-100 dark:border-gray-800']),
                ])
                ->extraAttributes([
                    'class' => 'p-4 bg-white dark:bg-[#111b21] rounded-2xl border border-gray-200/80 dark:border-gray-800/80 shadow-sm space-y-2 md:hidden',
                ]),

                // Desktop Table Columns (Hidden on Mobile)
                TextColumn::make('name')
                    ->searchable()
                    ->grow()
                    ->weight('bold')
                    ->visibleFrom('md'),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->copyable()
                    ->visibleFrom('md'),
                TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'instructor' => 'info',
                        'blogger' => 'primary',
                        'researcher' => 'warning',
                        'employer' => 'success',
                        default => 'gray',
                    })
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('track')
                    ->label('Track')
                    ->badge()
                    ->color('gray')
                    ->placeholder('N/A')
                    ->searchable()
                    ->visibleFrom('md'),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('email_verified_at')
                    ->label('Verified')
                    ->dateTime('M d, Y')
                    ->placeholder('Unverified')
                    ->sortable()
                    ->visibleFrom('lg')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->visibleFrom('xl')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->visibleFrom('xl')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options([
                        'student' => 'Student',
                        'instructor' => 'Instructor',
                        'blogger' => 'Blogger',
                        'researcher' => 'Researcher',
                        'employer' => 'Employer',
                        'admin' => 'Admin',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    \Filament\Actions\Action::make('approve_contributor')
                        ->label('Approve Account')
                        ->icon('heroicon-m-check-badge')
                        ->color('success')
                        ->visible(fn (\App\Models\User $record): bool => ! $record->is_active && in_array($record->role, ['blogger', 'researcher', 'employer', 'instructor'], true))
                        ->action(fn (\App\Models\User $record) => $record->update([
                            'is_active' => true,
                            'email_verified_at' => $record->email_verified_at ?: now(),
                        ])),
                    EditAction::make()->icon('heroicon-m-pencil-square'),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
