<?php

namespace App\Filament\Instructor\Resources\StudentResource\Pages;

use App\Filament\Instructor\Resources\StudentResource\StudentResource;
use App\Models\Badge;
use App\Models\Course;
use App\Models\User;
use App\Services\GamificationService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewStudent extends ViewRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('award_gamification')
                ->label('Award XP & Badges')
                ->icon('heroicon-o-sparkles')
                ->color('warning')
                ->modalHeading(fn (): string => "Award XP & Badges to {$this->record->name}")
                ->modalDescription('Recognize off-platform achievements such as classroom presentations, hackathon wins, live participation, leadership, or custom achievements.')
                ->modalSubmitActionLabel('Award Reward')
                ->form([
                    Select::make('course_id')
                        ->label('Associated Course')
                        ->options(function (): array {
                            $user = auth()->user();
                            if (! $user) {
                                return [];
                            }

                            return Course::query()
                                ->where('instructor_id', $user->id)
                                ->pluck('title', 'id')
                                ->all();
                        })
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->helperText('Select the course tied to this activity (Optional).'),

                    Select::make('activity_type')
                        ->label('Activity / Reason')
                        ->options([
                            'Outstanding Presentation' => '🎤 Outstanding Presentation',
                            'Classroom Participation & Debate' => '💬 Classroom Participation & Debate',
                            'Project Demo & Showcase' => '💻 Project Demo & Showcase',
                            'Hackathon / Competition Winner' => '🚀 Hackathon / Competition Winner',
                            'Peer Mentoring & Collaboration' => '🤝 Peer Mentoring & Collaboration',
                            'Lab Practical Excellence' => '⚙️ Lab Practical Excellence',
                            'Leadership & Teamwork' => '👑 Leadership & Teamwork',
                            'Extracurricular Contribution' => '🌟 Extracurricular Contribution',
                            'custom' => '➕ Other / Custom Activity',
                        ])
                        ->required()
                        ->default('Outstanding Presentation')
                        ->live(),

                    TextInput::make('custom_activity_name')
                        ->label('Custom Activity Name')
                        ->placeholder('e.g. AI Prompt Engineering Challenge')
                        ->visible(fn ($get) => $get('activity_type') === 'custom')
                        ->required(fn ($get) => $get('activity_type') === 'custom')
                        ->maxLength(100),

                    TextInput::make('xp')
                        ->label('XP Points to Award')
                        ->numeric()
                        ->required()
                        ->default(50)
                        ->minValue(1)
                        ->maxValue(2000)
                        ->live(debounce: 300)
                        ->afterStateUpdated(function ($state, callable $set) {
                            $set('coins', (int) round(((float) $state) * 0.30));
                        })
                        ->helperText('Contributes to the student lifetime XP and Rank Tier progression.'),

                    TextInput::make('coins')
                        ->label('Thinker Coins (TC)')
                        ->numeric()
                        ->nullable()
                        ->default(15)
                        ->minValue(0)
                        ->helperText('Spendable coins for Claim Hub rewards (defaults to 30% of XP).'),

                    Select::make('badge_id')
                        ->label('Award Badge (Optional)')
                        ->options(function () {
                            return Badge::query()
                                ->get()
                                ->mapWithKeys(fn (Badge $b) => [$b->id => "{$b->icon} {$b->name} (+{$b->xp_reward} XP)"])
                                ->all();
                        })
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->placeholder('Select a badge to grant (Optional)')
                        ->helperText('Optionally award a permanent recognition badge to the student profile.'),

                    Toggle::make('award_badge_xp')
                        ->label("Also grant Badge's inherent bonus XP reward")
                        ->default(false)
                        ->visible(fn ($get) => filled($get('badge_id'))),

                    Textarea::make('note')
                        ->label('Commendation Note / Reason')
                        ->placeholder('e.g. Delivered an exceptional presentation on Neural Networks with live code demonstrations.')
                        ->rows(3)
                        ->maxLength(500)
                        ->nullable(),
                ])
                ->action(function (array $data): void {
                    $instructor = auth()->user();
                    /** @var User $student */
                    $student = $this->record;

                    if (! $instructor || ! $student) {
                        return;
                    }

                    $activityName = $data['activity_type'] === 'custom'
                        ? ($data['custom_activity_name'] ?? 'Special Recognition')
                        : $data['activity_type'];

                    $course = ! empty($data['course_id']) ? Course::find($data['course_id']) : null;
                    $xp = (int) ($data['xp'] ?? 0);
                    $coins = isset($data['coins']) && $data['coins'] !== '' ? (int) $data['coins'] : null;
                    $badgeId = $data['badge_id'] ?? null;
                    $awardBadgeXp = (bool) ($data['award_badge_xp'] ?? false);
                    $note = $data['note'] ?? null;

                    $result = app(GamificationService::class)->awardManualInstructorReward(
                        instructor: $instructor,
                        student: $student,
                        course: $course,
                        activityName: $activityName,
                        xp: $xp,
                        coins: $coins,
                        badgeKeyOrId: $badgeId,
                        awardBadgeXp: $awardBadgeXp,
                        note: $note
                    );

                    if ($result['success'] ?? false) {
                        Notification::make()
                            ->title('Recognition Awarded!')
                            ->body("Successfully awarded +{$result['xp']} XP & +{$result['coins']} TC" . ($result['badge'] ? " and the '{$result['badge']}' badge" : '') . " to {$student->name}.")
                            ->success()
                            ->send();
                    }
                }),
            EditAction::make(),
        ];
    }
}
