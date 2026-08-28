<?php

namespace App\Filament\Instructor\Resources\LearningMaterialResource;

use App\Filament\Instructor\Concerns\ScopedToInstructor;
use App\Filament\Instructor\Resources\LearningMaterialResource\Pages\CreateLearningMaterial;
use App\Filament\Instructor\Resources\LearningMaterialResource\Pages\EditLearningMaterial;
use App\Filament\Instructor\Resources\LearningMaterialResource\Pages\ListLearningMaterials;
use App\Models\LearningMaterial;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LearningMaterialResource extends Resource
{
    use ScopedToInstructor;

    protected static ?string $model = LearningMaterial::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|\UnitEnum|null $navigationGroup = 'ACADEMICS & CONTENT';

    protected static ?string $navigationLabel = 'Materials';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                Select::make('category')
                    ->label('Category')
                    ->required()
                    ->searchable()
                    ->options(LearningMaterial::categoryOptions())
                    ->default('Study Material'),

                Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->columnSpanFull(),

                Select::make('material_type')
                    ->label('Material Type')
                    ->required()
                    ->options([
                        'Document' => 'Document',
                        'Image' => 'Image',
                        'Video' => 'Video',
                        'Link' => 'Link',
                    ])
                    ->default('Document')
                    ->live(),

                Select::make('course_id')
                    ->label('Course')
                    ->required()
                    ->searchable()
                    ->options(fn (): array => static::instructorCourseOptions())
                    ->live(),

                Select::make('course_intake_id')
                    ->label('Target Intake / Class')
                    ->nullable()
                    ->searchable()
                    ->options(function (callable $get): array {
                        $courseId = $get('course_id');
                        if (! $courseId) {
                            return [];
                        }

                        return \App\Models\CourseIntake::query()
                            ->where('course_id', $courseId)
                            ->where('status', '!=', \App\Models\CourseIntake::STATUS_ARCHIVED)
                            ->orderBy('start_date', 'desc')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->placeholder('All Intakes / Entire Course')
                    ->helperText('Leave empty to share with all cohorts across the course.'),

                Select::make('scope')
                    ->label('Scope')
                    ->required()
                    ->options([
                        'all' => 'All Students in Course',
                        'level' => 'Specific Level / Track',
                        'personal' => 'Specific Student',
                    ])
                    ->default('all')
                    ->live(),

                Select::make('target_track')
                    ->label('Target Track / Level')
                    ->options([
                        'Beginner' => 'Beginner',
                        'Intermediate' => 'Intermediate',
                        'Advanced' => 'Advanced',
                    ])
                    ->visible(fn (callable $get): bool => in_array($get('scope'), ['level', 'personal']))
                    ->required(fn (callable $get): bool => $get('scope') === 'level')
                    ->live(),

                Select::make('target_user_id')
                    ->label('Target Student')
                    ->searchable()
                    ->options(function (callable $get): array {
                        $courseId = $get('course_id');
                        $track = $get('target_track');

                        if (! $courseId) {
                            return [];
                        }

                        $query = User::query()
                            ->where('role', 'student')
                            ->whereHas('courses', fn ($q) => $q->where('courses.id', $courseId));

                        if ($track) {
                            $query->where('track', $track);
                        }

                        return $query->orderBy('name')->pluck('name', 'id')->toArray();
                    })
                    ->visible(fn (callable $get): bool => $get('scope') === 'personal')
                    ->required(fn (callable $get): bool => $get('scope') === 'personal'),

                FileUpload::make('file_path')
                    ->label(fn (callable $get): string => match ($get('material_type')) {
                        'Image' => 'Upload Image',
                        'Video' => 'Upload Video',
                        default => 'Upload File',
                    })
                    ->disk('public')
                    ->directory('materials')
                    ->maxSize(20480)
                    ->acceptedFileTypes(fn (callable $get): array => match ($get('material_type')) {
                        'Image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'],
                        'Video' => ['video/mp4', 'video/webm', 'video/ogg', 'video/avi', 'video/quicktime'],
                        default => [
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-powerpoint',
                            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'text/plain',
                            'text/csv',
                        ],
                    })
                    ->visible(fn (callable $get): bool => in_array($get('material_type'), ['Document', 'Image', 'Video']))
                    ->required(fn (callable $get, string $operation): bool => $operation === 'create' && ($get('material_type') === 'Document' || $get('material_type') === 'Image'))
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->columnSpanFull(),

                TextInput::make('video_url')
                    ->label('Video URL (YouTube, Vimeo, etc.)')
                    ->url()
                    ->visible(fn (callable $get): bool => $get('material_type') === 'Video')
                    ->helperText('Provide a URL or upload a video file above, or both.'),

                TextInput::make('link_url')
                    ->label('Link URL')
                    ->url()
                    ->visible(fn (callable $get): bool => $get('material_type') === 'Link')
                    ->required(fn (callable $get): bool => $get('material_type') === 'Link'),
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
                        Stack::make([
                            TextColumn::make('title')
                                ->weight('bold')
                                ->size('sm')
                                ->searchable(),
                            TextColumn::make('course.title')
                                ->size('xs')
                                ->color('gray')
                                ->searchable(),
                        ]),
                        TextColumn::make('category')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Curriculum' => 'primary',
                                'Study Material' => 'success',
                                'Quiz Preps' => 'warning',
                                'Answer Kits' => 'info',
                                'Project Guides' => 'indigo',
                                'Cheat Sheets' => 'purple',
                                'Practice Exercises' => 'teal',
                                'Past Papers' => 'amber',
                                'Rules' => 'danger',
                                'General Notices' => 'gray',
                                'Supplementary Resources' => 'cyan',
                                default => 'gray',
                            })
                            ->grow(false),
                    ]),
                    Split::make([
                        TextColumn::make('material_type')
                            ->badge()
                            ->color('gray')
                            ->size('xs'),
                        TextColumn::make('scope')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'all' => 'success',
                                'level' => 'info',
                                'personal' => 'warning',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'all' => 'All Students',
                                'level' => 'Level',
                                'personal' => 'Personal',
                                default => ucfirst($state),
                            })
                            ->size('xs'),
                    ])->extraAttributes(['class' => 'pt-2 border-t border-gray-100 dark:border-gray-800']),
                ])
                ->extraAttributes([
                    'class' => 'p-4 bg-white dark:bg-[#111b21] rounded-2xl border border-gray-200/80 dark:border-gray-800/80 shadow-sm space-y-2 md:hidden',
                ]),

                // Desktop Table Columns (Hidden on Mobile)
                TextColumn::make('title')
                    ->searchable()
                    ->limit(40)
                    ->visibleFrom('md'),
                TextColumn::make('course.title')
                    ->label('Course')
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Curriculum' => 'primary',
                        'Study Material' => 'success',
                        'Quiz Preps' => 'warning',
                        'Answer Kits' => 'info',
                        'Project Guides' => 'indigo',
                        'Cheat Sheets' => 'purple',
                        'Practice Exercises' => 'teal',
                        'Past Papers' => 'amber',
                        'Rules' => 'danger',
                        'General Notices' => 'gray',
                        'Supplementary Resources' => 'cyan',
                        default => 'gray',
                    })
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('material_type')
                    ->label('Type')
                    ->badge()
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('scope')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'all' => 'success',
                        'level' => 'info',
                        'personal' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'all' => 'All Students',
                        'level' => 'Level',
                        'personal' => 'Personal',
                        default => ucfirst($state),
                    })
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visibleFrom('md'),
            ])
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('course_id', static::instructorCourseIds()))
            ->filters([
                SelectFilter::make('category')
                    ->options(LearningMaterial::categoryOptions()),
                SelectFilter::make('material_type')
                    ->label('Type')
                    ->options([
                        'Document' => 'Document',
                        'Image' => 'Image',
                        'Video' => 'Video',
                        'Link' => 'Link',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLearningMaterials::route('/'),
            'create' => CreateLearningMaterial::route('/create'),
            'edit' => EditLearningMaterial::route('/{record}/edit'),
        ];
    }
}
