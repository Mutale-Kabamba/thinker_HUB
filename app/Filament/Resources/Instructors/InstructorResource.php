<?php

namespace App\Filament\Resources\Instructors;

use App\Filament\Resources\Instructors\Pages\CreateInstructor;
use App\Filament\Resources\Instructors\Pages\EditInstructor;
use App\Filament\Resources\Instructors\Pages\ListInstructors;
use App\Filament\Resources\Instructors\Pages\ViewInstructor;
use App\Models\Course;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use UnitEnum;

class InstructorResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $navigationLabel = 'Instructors';

    protected static ?string $modelLabel = 'Instructor';

    protected static ?string $pluralModelLabel = 'Instructors';

    protected static ?string $slug = 'instructors';

    protected static string|UnitEnum|null $navigationGroup = 'PEOPLE & ROLES';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->minLength(8),

                Select::make('instructorCourses')
                    ->label('Assigned Courses')
                    ->relationship(
                        name: 'instructorCourses',
                        titleAttribute: 'title',
                        modifyQueryUsing: fn (Builder $query) => $query->orderBy('title')
                    )
                    ->getOptionLabelFromRecordUsing(fn (Course $record): string => "{$record->code} - {$record->title}")
                    ->multiple()
                    ->searchable()
                    ->preload(),

                TextInput::make('proficiency')
                    ->label('Proficiency / Expertise')
                    ->maxLength(255)
                    ->placeholder('e.g. Data Analytics, Web Development'),

                TextInput::make('occupation')
                    ->label('Occupation')
                    ->maxLength(255)
                    ->placeholder('e.g. Software Engineer'),

                TextInput::make('whatsapp')
                    ->label('WhatsApp Number')
                    ->maxLength(255)
                    ->placeholder('e.g. 260772640546'),

                TextInput::make('linkedin_url')
                    ->label('LinkedIn URL')
                    ->url()
                    ->maxLength(500)
                    ->placeholder('https://linkedin.com/in/...'),

                TextInput::make('facebook_url')
                    ->label('Facebook URL')
                    ->url()
                    ->maxLength(500)
                    ->placeholder('https://facebook.com/...'),

                TextInput::make('github_url')
                    ->label('GitHub URL')
                    ->url()
                    ->maxLength(500)
                    ->placeholder('https://github.com/...'),

                TextInput::make('instagram_url')
                    ->label('Instagram URL')
                    ->url()
                    ->maxLength(500)
                    ->placeholder('https://instagram.com/...'),

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
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->getStateUsing(fn (User $record): string => $record->isAdmin() ? 'Admin & Instructor' : 'Instructor')
                    ->color(fn (string $state): string => $state === 'Admin & Instructor' ? 'success' : 'info'),
                TextColumn::make('instructor_courses_count')
                    ->label('Courses')
                    ->counts('instructorCourses')
                    ->formatStateUsing(function ($state, $record) {
                        $codes = $record->instructorCourses()->pluck('code')->filter()->implode(', ');

                        return $codes ? "{$state} ({$codes})" : (string) $state;
                    })
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('instructorApplication.status')
                    ->label('Application')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->placeholder('None'),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('email_verified_at')
                    ->label('Verified')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not verified')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->where(fn (Builder $q) => $q->where('role', 'instructor')->orWhere(fn (Builder $sub) => $sub->where('role', 'admin')->whereHas('instructorCourses'))))
            ->filters([
                SelectFilter::make('course')
                    ->label('Course')
                    ->options(fn (): array => Course::query()->where('is_active', true)->orderBy('title')->pluck('title', 'id')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas('instructorCourses', fn (Builder $q) => $q->where('courses.id', $data['value']));
                    }),
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
                SelectFilter::make('application_status')
                    ->label('Application Status')
                    ->options([
                        'approved' => 'Approved',
                        'pending' => 'Pending',
                        'rejected' => 'Rejected',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas('instructorApplication', fn (Builder $q) => $q->where('status', $data['value']));
                    }),
            ])
            ->defaultSort('name')
            ->recordActions([
                \Filament\Actions\ActionGroup::make([
                    ViewAction::make()->icon('heroicon-m-eye'),
                    EditAction::make()->icon('heroicon-m-pencil-square'),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            Instructors\RelationManagers\CoursesRelationManager::class,
            Instructors\RelationManagers\InstructorApplicationRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInstructors::route('/'),
            'create' => CreateInstructor::route('/create'),
            'view' => ViewInstructor::route('/{record}'),
            'edit' => EditInstructor::route('/{record}/edit'),
        ];
    }
}
