<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->copyable(),
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
                    ->searchable(),
                TextColumn::make('track')
                    ->label('Track')
                    ->badge()
                    ->color('gray')
                    ->placeholder('N/A')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('email_verified_at')
                    ->label('Verified')
                    ->dateTime('M d, Y')
                    ->placeholder('Unverified')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime('M d, Y')
                    ->sortable()
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
