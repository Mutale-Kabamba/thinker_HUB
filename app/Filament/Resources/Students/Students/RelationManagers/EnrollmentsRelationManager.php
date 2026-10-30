<?php

namespace App\Filament\Resources\Students\Students\RelationManagers;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Notifications\CertificateIssuedNotification;
use App\Services\CertificateService;
use App\Services\GamificationService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class EnrollmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';

    protected static ?string $title = 'Course Enrollments';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('course_id')
                ->label('Course')
                ->options(fn (): array => Course::query()->where('is_active', true)->orderBy('title')->pluck('title', 'id')->toArray())
                ->required()
                ->searchable()
                ->live()
                ->afterStateUpdated(fn (Set $set) => $set('course_intake_id', null)),

            Select::make('course_intake_id')
                ->label('Class / Intake (Cohort)')
                ->placeholder('Select intake cohort (Optional)')
                ->options(function (Get $get): array {
                    $courseId = $get('course_id');
                    if (! $courseId) {
                        return [];
                    }

                    return CourseIntake::query()
                        ->where('course_id', $courseId)
                        ->where('status', '!=', CourseIntake::STATUS_ARCHIVED)
                        ->orderBy('start_date', 'desc')
                        ->get()
                        ->mapWithKeys(fn (CourseIntake $i) => [$i->id => $i->name . ($i->is_active ? ' (Active)' : '')])
                        ->toArray();
                })
                ->searchable(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('course.title')
                    ->label('Course')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('course.code')
                    ->label('Code'),

                TextColumn::make('intake.name')
                    ->label('Class / Intake')
                    ->badge()
                    ->color('info')
                    ->placeholder('No cohort assigned')
                    ->searchable(),

                TextColumn::make('payment_status')
                    ->label('Fee / Payment')
                    ->badge()
                    ->color(fn (Enrollment $record): string => $record->isFree() ? 'gray' : 'success')
                    ->formatStateUsing(fn (Enrollment $record): string => $record->isFree() ? 'Free / Sponsored' : 'Paid • ZMW ' . number_format($record->fee_amount ?? 0, 2))
                    ->description(fn (Enrollment $record): ?string => $record->transaction_reference ? "Ref: {$record->transaction_reference}" : ($record->registration_type === 'direct_checkout' ? 'Direct Checkout' : null)),

                TextColumn::make('registration_type')
                    ->label('Channel')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'admin_manual' => 'primary',
                        'instructor_manual' => 'warning',
                        default => 'info',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'admin_manual' => 'Admin Added',
                        'instructor_manual' => 'Instructor Added',
                        default => 'Direct Checkout',
                    })
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Enrolled At')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),

                TextColumn::make('completed_at')
                    ->label('Completion')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'warning')
                    ->formatStateUsing(fn ($state) => $state ? 'Completed (' . $state->format('M d, Y') . ')' : 'In Progress'),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->label('Enrol in Course')
                    ->icon('heroicon-o-plus-circle')
                    ->modalHeading('Enrol Student into Course')
                    ->modalDescription('Specify course, cohort intake, and whether this student is Paying or Free (scholarship/waiver).')
                    ->form([
                        Select::make('course_id')
                            ->label('Select Course')
                            ->options(fn (): array => Course::query()->where('is_active', true)->orderBy('title')->pluck('title', 'id')->toArray())
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state): void {
                                $set('course_intake_id', null);
                                if ($state) {
                                    $c = Course::find($state);
                                    if ($c && $c->fees) {
                                        $set('fee_amount', $c->fees);
                                    }
                                }
                            }),

                        Select::make('course_intake_id')
                            ->label('Class / Intake (Cohort)')
                            ->placeholder('Select intake cohort (Optional)')
                            ->options(function (Get $get): array {
                                $courseId = $get('course_id');
                                if (! $courseId) {
                                    return [];
                                }

                                return CourseIntake::query()
                                    ->where('course_id', $courseId)
                                    ->where('status', '!=', CourseIntake::STATUS_ARCHIVED)
                                    ->orderBy('start_date', 'desc')
                                    ->get()
                                    ->mapWithKeys(fn (CourseIntake $i) => [$i->id => $i->name . ($i->is_active ? ' (Active)' : '')])
                                    ->toArray();
                            })
                            ->searchable(),

                        Radio::make('fee_type')
                            ->label('Fee Classification')
                            ->options([
                                'paid' => 'Paying Student (Cash, Bank Transfer, Card, Mobile Money)',
                                'free' => 'Free / Sponsored (Scholarship, Corporate Grant, Complimentary, Retake)',
                            ])
                            ->default('paid')
                            ->live()
                            ->required(),

                        Grid::make(2)
                            ->visible(fn (Get $get): bool => $get('fee_type') === 'paid')
                            ->schema([
                                TextInput::make('fee_amount')
                                    ->label('Fee Amount (ZMW)')
                                    ->numeric()
                                    ->prefix('ZMW')
                                    ->required(fn (Get $get): bool => $get('fee_type') === 'paid')
                                    ->helperText('Tuition fee collected.'),

                                Select::make('payment_method')
                                    ->label('Payment Method')
                                    ->options([
                                        'bank_transfer' => 'Bank Transfer / Direct Deposit',
                                        'cash' => 'Cash (Campus / Finance Desk)',
                                        'mobile_money' => 'Mobile Money (Airtel / MTN / Zamtel)',
                                        'card' => 'Debit / Credit Card (POS / Terminal)',
                                        'cheque' => 'Cheque',
                                        'other' => 'Other / Direct Settlement',
                                    ])
                                    ->default('bank_transfer')
                                    ->required(fn (Get $get): bool => $get('fee_type') === 'paid'),

                                TextInput::make('provider')
                                    ->label('Bank / Provider / Terminal')
                                    ->placeholder('e.g. Stanbic Bank, Absa, Airtel Money, Reception POS')
                                    ->maxLength(255),

                                TextInput::make('transaction_reference')
                                    ->label('Transaction Reference / Receipt Number')
                                    ->placeholder('e.g. TX-STB-8812, RCPT-4412')
                                    ->required(fn (Get $get): bool => $get('fee_type') === 'paid')
                                    ->helperText('Essential for financial auditing and reconciliation.'),

                                DateTimePicker::make('paid_at')
                                    ->label('Payment Date & Time')
                                    ->default(now()),

                                Textarea::make('payment_notes')
                                    ->label('Accounting Remarks')
                                    ->placeholder('e.g. Bank slip verified against statement, official receipt handed over...')
                                    ->columnSpanFull(),
                            ]),

                        Grid::make(1)
                            ->visible(fn (Get $get): bool => $get('fee_type') === 'free')
                            ->schema([
                                Select::make('free_reason')
                                    ->label('Scholarship / Waiver Reason')
                                    ->options([
                                        'full_scholarship' => 'Full STEM / Academic Merit Scholarship',
                                        'sponsorship' => 'External Corporate / Donor Sponsorship',
                                        'complimentary' => 'Complimentary / Executive Approval',
                                        'staff_dependent' => 'Staff / Faculty Dependent',
                                        'retake' => 'Course Retake (No additional charge)',
                                        'other' => 'Other (Specified in remarks)',
                                    ])
                                    ->default('full_scholarship')
                                    ->required(fn (Get $get): bool => $get('fee_type') === 'free'),

                                Textarea::make('free_notes')
                                    ->label('Waiver Justification & Reference')
                                    ->placeholder('e.g. Approved by Academic Board. Grant Ref: STEM-MERIT-2026')
                                    ->required(fn (Get $get): bool => $get('fee_type') === 'free'),
                            ]),
                    ])
                    ->using(function (array $data, RelationManager $livewire): Enrollment {
                        $student = $livewire->getOwnerRecord();
                        $isFree = ($data['fee_type'] ?? 'paid') === 'free';
                        $course = Course::find($data['course_id']);
                        $amount = $isFree ? 0 : (float) ($data['fee_amount'] ?? $course?->fees ?? 0);
                        $method = $isFree ? 'scholarship' : ($data['payment_method'] ?? 'manual');
                        $ref = $isFree ? 'FREE-' . strtoupper(Str::random(8)) : ($data['transaction_reference'] ?? 'REC-' . strtoupper(Str::random(8)));
                        $notes = $isFree ? ($data['free_notes'] ?? 'Free Enrollment') : ($data['payment_notes'] ?? null);

                        $channel = auth()->user()?->isInstructor() ? Enrollment::CHANNEL_INSTRUCTOR_MANUAL : Enrollment::CHANNEL_ADMIN_MANUAL;

                        $enrollment = Enrollment::create([
                            'user_id' => $student->id,
                            'course_id' => $data['course_id'],
                            'course_intake_id' => $data['course_intake_id'] ?? null,
                            'registration_type' => $channel,
                            'registered_by_user_id' => auth()->id(),
                            'payment_status' => $isFree ? Enrollment::STATUS_FREE : Enrollment::STATUS_PAID,
                            'is_free' => $isFree,
                            'fee_amount' => $amount,
                            'payment_method' => $method,
                            'transaction_reference' => $ref,
                            'payment_notes' => $notes,
                            'paid_at' => $isFree ? null : ($data['paid_at'] ?? now()),
                        ]);

                        Payment::create([
                            'user_id' => $student->id,
                            'course_id' => $data['course_id'],
                            'enrollment_id' => $enrollment->id,
                            'amount' => $amount,
                            'original_amount' => $course?->fees ?? $amount,
                            'discount_amount' => $isFree ? ($course?->fees ?? 0) : 0,
                            'currency' => 'ZMW',
                            'payment_method' => $method,
                            'provider' => $isFree ? 'Scholarship / Waiver' : ($data['provider'] ?? 'Manual Registration'),
                            'reference' => $ref,
                            'status' => 'paid',
                            'is_free' => $isFree,
                            'registration_type' => $channel,
                            'registered_by_user_id' => auth()->id(),
                            'payment_notes' => $notes,
                            'paid_at' => $isFree ? null : ($data['paid_at'] ?? now()),
                        ]);

                        return $enrollment;
                    }),
            ])
            ->recordActions([
                \Filament\Actions\ActionGroup::make([
                    Action::make('view_payment')
                        ->label('View Receipt / Accounting')
                        ->icon('heroicon-o-receipt-percent')
                        ->color('info')
                        ->modalHeading('Enrollment & Financial Receipt Details')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close')
                        ->form([
                            Placeholder::make('summary')
                                ->content(fn (Enrollment $record): HtmlString => new HtmlString("
                                    <div class='p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 space-y-3 text-sm'>
                                        <div class='flex justify-between items-center pb-2 border-b border-slate-200 dark:border-slate-800'>
                                            <span class='text-slate-500 font-medium'>Tuition Fee Status:</span>
                                            <span class='font-bold " . ($record->isFree() ? 'text-indigo-600' : 'text-emerald-600') . "'>" . ($record->isFree() ? 'Free / Scholarship' : 'PAID • ZMW ' . number_format($record->fee_amount ?? 0, 2)) . "</span>
                                        </div>
                                        <div class='flex justify-between items-center'>
                                            <span class='text-slate-500'>Enrollment Channel:</span>
                                            <span class='font-semibold text-slate-800 dark:text-slate-200'>" . $record->registrationChannelLabel() . "</span>
                                        </div>
                                        <div class='flex justify-between items-center'>
                                            <span class='text-slate-500'>Transaction Reference / Receipt:</span>
                                            <span class='font-mono font-bold text-slate-800 dark:text-slate-200'>" . ($record->transaction_reference ?? 'N/A') . "</span>
                                        </div>
                                        <div class='flex justify-between items-center'>
                                            <span class='text-slate-500'>Payment Method:</span>
                                            <span class='font-medium text-slate-800 dark:text-slate-200'>" . $record->paymentMethodLabel() . "</span>
                                        </div>
                                        <div class='flex justify-between items-center'>
                                            <span class='text-slate-500'>Recorded / Verified By:</span>
                                            <span class='font-medium text-slate-800 dark:text-slate-200'>" . ($record->registeredBy?->name ?? ($record->registration_type === 'direct_checkout' ? 'Automated Payment Gateway' : 'System')) . "</span>
                                        </div>
                                        <div class='flex justify-between items-center'>
                                            <span class='text-slate-500'>Date Recorded:</span>
                                            <span class='text-slate-700 dark:text-slate-300'>" . ($record->paid_at ? $record->paid_at->format('M j, Y H:i') : $record->created_at->format('M j, Y H:i')) . "</span>
                                        </div>
                                        " . ($record->payment_notes ? "
                                        <div class='pt-2 border-t border-slate-200 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-400'>
                                            <strong>Audit Remarks:</strong> " . e($record->payment_notes) . "
                                        </div>" : '') . "
                                    </div>
                                ")),
                        ]),

                    Action::make('assign_intake')
                        ->label('Assign / Change Intake')
                        ->icon('heroicon-o-calendar-days')
                        ->color('primary')
                        ->modalHeading(fn (Enrollment $record): string => "Assign Intake Cohort for {$record->course?->title}")
                        ->form([
                            Select::make('course_intake_id')
                                ->label('Class / Intake')
                                ->placeholder('No specific cohort (General)')
                                ->options(fn (Enrollment $record): array => CourseIntake::query()
                                    ->where('course_id', $record->course_id)
                                    ->where('status', '!=', CourseIntake::STATUS_ARCHIVED)
                                    ->orderBy('start_date', 'desc')
                                    ->get()
                                    ->mapWithKeys(fn (CourseIntake $i) => [$i->id => $i->name . ($i->is_active ? ' (Active)' : '')])
                                    ->toArray()
                                )
                                ->default(fn (Enrollment $record) => $record->course_intake_id)
                                ->searchable(),
                        ])
                        ->action(function (Enrollment $record, array $data): void {
                            $record->update(['course_intake_id' => $data['course_intake_id'] ?? null]);
                            $intakeName = $record->fresh()->intake?->name ?? 'General (No cohort)';
                            Notification::make()
                                ->title("Intake updated to '{$intakeName}'.")
                                ->success()
                                ->send();
                        }),

                    Action::make('toggle_completion')
                        ->label(fn (Enrollment $record): string => $record->completed_at ? 'Reset Status' : 'Mark Complete')
                        ->icon(fn (Enrollment $record): string => $record->completed_at ? 'heroicon-o-arrow-path' : 'heroicon-o-check-circle')
                        ->color(fn (Enrollment $record): string => $record->completed_at ? 'gray' : 'success')
                        ->action(function (Enrollment $record): void {
                            if ($record->completed_at) {
                                $record->markAsIncomplete();
                                Certificate::query()
                                    ->where('user_id', $record->user_id)
                                    ->where('course_id', $record->course_id)
                                    ->delete();

                                if ($record->user && $record->course) {
                                    try {
                                        app(GamificationService::class)->revokeCourseCompleted($record->user, $record->course);
                                    } catch (\Throwable $e) {
                                        report($e);
                                    }
                                }

                                Notification::make()
                                    ->title('Course completion reset and certificate/badges locked')
                                    ->info()
                                    ->send();
                            } else {
                                $record->markAsCompleted(auth()->user());
                                if ($record->user && $record->course) {
                                    $certificate = app(CertificateService::class)->issue($record->user, $record->course, force: true);
                                    if ($certificate && $certificate->wasRecentlyCreated) {
                                        try {
                                            $record->user->notify(new CertificateIssuedNotification($certificate));
                                        } catch (\Throwable $e) {
                                            report($e);
                                        }
                                    }

                                    try {
                                        app(GamificationService::class)->awardCourseCompleted($record->user, $record->course);
                                    } catch (\Throwable $e) {
                                        report($e);
                                    }
                                }
                                Notification::make()
                                    ->title('Course Marked Complete!')
                                    ->body('Certificate and completion badge are now ready for ' . ($record->user?->name ?? 'student') . '.')
                                    ->success()
                                    ->send();
                            }
                        }),
                    DeleteAction::make()->label('Unenrol')->icon('heroicon-m-trash'),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Unenrol Selected'),
                ]),
            ]);
    }
}
