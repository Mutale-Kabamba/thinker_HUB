<?php

namespace App\Filament\Resources\Users\Tables;

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
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
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
                \Filament\Actions\Action::make('approve_contributor')
                    ->label('Approve Account')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (\App\Models\User $record): bool => ! $record->is_active && in_array($record->role, ['blogger', 'researcher', 'employer', 'instructor'], true))
                    ->action(fn (\App\Models\User $record) => $record->update([
                        'is_active' => true,
                        'email_verified_at' => $record->email_verified_at ?: now(),
                    ])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
