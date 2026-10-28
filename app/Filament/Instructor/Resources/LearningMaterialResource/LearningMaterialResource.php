<?php

namespace App\Filament\Instructor\Resources\LearningMaterialResource;

use App\Filament\Instructor\Concerns\ScopedToInstructor;
use App\Filament\Instructor\Resources\LearningMaterialResource\Pages\CreateLearningMaterial;
use App\Filament\Instructor\Resources\LearningMaterialResource\Pages\EditLearningMaterial;
use App\Filament\Instructor\Resources\LearningMaterialResource\Pages\ListLearningMaterials;
use App\Models\LearningMaterial;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
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
                        default => 'Upload Attachment / File (PDF, PPTX, ZIP, .py, etc.)',
                    })
                    ->disk('public')
                    ->directory('materials')
                    ->storeFileNamesIn('file_name')
                    ->openable()
                    ->downloadable()
                    ->maxSize(10240)
                    ->helperText('All file types supported (PDF, PPTX, ZIP, .py, notebooks, docs, etc.) — maximum 10 MB.')
                    ->visible(fn (callable $get): bool => in_array($get('material_type'), ['Document', 'Image']))
                    ->required(fn (callable $get, string $operation): bool => $operation === 'create' && ($get('material_type') === 'Document' || $get('material_type') === 'Image'))
                    ->columnSpanFull(),

                TextInput::make('video_url')
                    ->label('Embedded Video URL (YouTube, Vimeo, Loom, etc.)')
                    ->url()
                    ->placeholder('https://www.youtube.com/watch?v=... or https://vimeo.com/...')
                    ->visible(fn (callable $get): bool => $get('material_type') === 'Video')
                    ->required(fn (callable $get): bool => $get('material_type') === 'Video')
                    ->helperText('Embed a video from YouTube, Vimeo, Loom, Dailymotion, Wistia, Google Drive, or any video platform.')
                    ->columnSpanFull(),

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
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('course.title')
                    ->label('Course')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Curriculum' => 'primary',
                        'Study Material' => 'success',
                        'Quiz Preps' => 'warning',
                        'Answer Kits' => 'info',
                        'Project Guides' => 'indigo',
                        'Cheat Sheets' => 'emerald',
                        'Practice Exercises' => 'teal',
                        'Past Papers' => 'amber',
                        'Rules' => 'danger',
                        'General Notices' => 'gray',
                        'Supplementary Resources' => 'cyan',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('material_type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),
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
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Action::make('view_file')
                    ->label('View')
                    ->icon('heroicon-m-eye')
                    ->color('info')
                    ->visible(fn (LearningMaterial $record): bool => filled($record->file_path) || filled($record->link_url))
                    ->url(fn (LearningMaterial $record): string => filled($record->file_path)
                        ? route('file.view', ['type' => 'material', 'id' => $record->id])
                        : $record->link_url
                    )
                    ->openUrlInNewTab(),
                Action::make('download_file')
                    ->label('Download')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('gray')
                    ->visible(fn (LearningMaterial $record): bool => filled($record->file_path))
                    ->url(fn (LearningMaterial $record): string => route('file.download', ['type' => 'material', 'id' => $record->id]))
                    ->openUrlInNewTab(),
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
