<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\CertificateIssuedNotification;
use App\Services\CertificateService;
use App\Services\GamificationService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';

    protected static ?string $title = 'Enrolled Students';

    public function table(Table $table): Table
    {
        /** @var Course $course */
        $course = $this->getOwnerRecord();

        return $table
            ->recordTitleAttribute('user.name')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Student Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('user.track')
                    ->label('Level / Track')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('intake.name')
                    ->label('Class / Cohort')
                    ->badge()
                    ->color('info')
                    ->placeholder('General (No cohort)')
                    ->searchable(),

                TextColumn::make('payment_status')
                    ->label('Payment / Fee')
                    ->badge()
                    ->color(fn (Enrollment $record): string => $record->isFree() ? 'gray' : 'success')
                    ->formatStateUsing(function (Enrollment $record): string {
                        if ($record->isFree()) {
                            return 'Free / Sponsored';
                        }
                        return 'Paid • ZMW ' . number_format((float) ($record->fee_amount ?? 0), 2);
                    })
                    ->description(fn (Enrollment $record): ?string => $record->transaction_reference ? "Ref: {$record->transaction_reference}" : ($record->registration_type === 'direct_checkout' ? 'Direct Checkout' : null))
                    ->sortable(),

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
                    ->label('Enrolled Date')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),

                TextColumn::make('completed_at')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'warning')
                    ->formatStateUsing(fn ($state) => $state ? 'Completed (' . $state->format('M d, Y') . ')' : 'In Progress'),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Action::make('add_student')
                    ->label('Enrol Student(s)')
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->modalHeading("Enrol Student(s) into '{$course->title}'")
                    ->modalDescription('Specify student(s), class intake cohort, and whether this enrollment is Free or Paying (with transaction details).')
                    ->modalSubmitActionLabel('Confirm & Enrol Student(s)')
                    ->form([
                        Select::make('user_ids')
                            ->label('Select Student(s)')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Search by student name or email.')
                            ->options(function () use ($course): array {
                                $alreadyEnrolled = Enrollment::query()
                                    ->where('course_id', $course->id)
                                    ->pluck('user_id');

                                return User::query()
                                    ->where(function ($q) {
                                        $q->whereNull('role')->orWhere('role', 'student');
                                    })
                                    ->whereNotIn('id', $alreadyEnrolled)
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(fn (User $u) => [$u->id => "{$u->name} ({$u->email}) - Track: " . ($u->track ?? 'Beginner')])
                                    ->toArray();
                            }),

                        Select::make('course_intake_id')
                            ->label('Assign Class / Intake (Cohort)')
                            ->placeholder('General / No Specific Intake')
                            ->options(fn (): array => CourseIntake::query()
                                ->where('course_id', $course->id)
                                ->where('status', '!=', CourseIntake::STATUS_ARCHIVED)
                                ->orderBy('start_date', 'desc')
                                ->get()
                                ->mapWithKeys(fn (CourseIntake $i) => [$i->id => $i->name . ($i->is_active ? ' (Active)' : '')])
                                ->toArray()
                            )
                            ->searchable(),

                        Radio::make('fee_type')
                            ->label('Tuition / Payment Type')
                            ->options([
                                'paid' => '💳 Paying Student (Record Payment Transaction)',
                                'free' => '🎁 Free / Sponsored / Scholarship',
                            ])
                            ->default('paid')
                            ->live()
                            ->required(),

                        // --- Free Options ---
                        Select::make('free_reason')
                            ->label('Free Enrollment Justification / Reason')
                            ->options([
                                'Full Scholarship' => '🎓 Full Scholarship (Thinker HUB / Foundation)',
                                'Corporate / Partner Sponsorship' => '🏢 Corporate / Partner Sponsorship',
                                'Staff / Complimentary Pass' => '⭐ Staff / Complimentary Pass',
                                'Promotional Community Grant' => '🤝 Promotional Community Grant',
                                'Course Retake / Transfer' => '🔁 Course Retake / Cohort Transfer',
                                'Other' => '📝 Other Reason',
                            ])
                            ->default('Full Scholarship')
                            ->visible(fn (Get $get): bool => $get('fee_type') === 'free')
                            ->required(fn (Get $get): bool => $get('fee_type') === 'free'),

                        // --- Paid Options ---
                        TextInput::make('fee_amount')
                            ->label('Amount Paid Per Student')
                            ->numeric()
                            ->prefix('ZMW')
                            ->default(fn () => $course->getNumericFee() > 0 ? $course->getNumericFee() : 450.00)
                            ->helperText('Standard course tuition fee in Zambian Kwacha (ZMW).')
                            ->visible(fn (Get $get): bool => $get('fee_type') === 'paid')
                            ->required(fn (Get $get): bool => $get('fee_type') === 'paid'),

                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'cash' => '💵 Cash Payment',
                                'bank_transfer' => '🏦 Bank Transfer / Direct Deposit',
                                'mobile_money' => '📱 Mobile Money (Airtel / MTN / Zamtel)',
                                'card' => '💳 POS / Debit / Credit Card',
                                'cheque' => '📑 Company / Bank Cheque',
                                'other' => '🔄 Other Method',
                            ])
                            ->default('cash')
                            ->live()
                            ->visible(fn (Get $get): bool => $get('fee_type') === 'paid')
                            ->required(fn (Get $get): bool => $get('fee_type') === 'paid'),

                        TextInput::make('provider')
                            ->label('Provider / Bank Name')
                            ->placeholder('e.g. Airtel Money, MTN MoMo, Stanbic Bank, Zanaco, FNB')
                            ->visible(fn (Get $get): bool => $get('fee_type') === 'paid' && in_array($get('payment_method'), ['mobile_money', 'bank_transfer', 'card'])),

                        TextInput::make('transaction_reference')
                            ->label('Transaction Reference / Receipt #')
                            ->placeholder('e.g. TXN-8392019, Bank Slip #, Receipt Book #1204')
                            ->helperText('Unique transaction identifier or manual receipt number for accounting auditing.')
                            ->visible(fn (Get $get): bool => $get('fee_type') === 'paid')
                            ->required(fn (Get $get): bool => $get('fee_type') === 'paid'),

                        DateTimePicker::make('paid_at')
                            ->label('Payment Received Date & Time')
                            ->default(now())
                            ->visible(fn (Get $get): bool => $get('fee_type') === 'paid')
                            ->required(fn (Get $get): bool => $get('fee_type') === 'paid'),

                        Textarea::make('payment_notes')
                            ->label('Accounting / Registration Remarks')
                            ->placeholder('e.g. Payer name if different from student, invoice number, branch, cashier notes...')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data) use ($course): void {
                        $userIds = (array) ($data['user_ids'] ?? []);
                        $intakeId = $data['course_intake_id'] ?? null;
                        $isFree = ($data['fee_type'] ?? 'paid') === 'free';
                        $channel = auth()->user()?->isAdmin() ? Enrollment::CHANNEL_ADMIN_MANUAL : Enrollment::CHANNEL_INSTRUCTOR_MANUAL;
                        $count = 0;

                        $amount = $isFree ? 0.00 : (float) ($data['fee_amount'] ?? 0);
                        $method = $isFree ? 'free_registration' : ($data['payment_method'] ?? 'cash');
                        $paidAt = $isFree ? now() : ($data['paid_at'] ?? now());
                        $notes = trim((string) ($data['payment_notes'] ?? ''));

                        foreach ($userIds as $userId) {
                            $reference = $isFree
                                ? ('FREE-' . strtoupper(Str::random(8)))
                                : trim((string) ($data['transaction_reference'] ?? ('MAN-' . strtoupper(Str::random(8)))));

                            $fullNotes = $isFree
                                ? trim(($data['free_reason'] ?? 'Free Enrollment') . ($notes !== '' ? " | {$notes}" : ''))
                                : $notes;

                            $enrollment = Enrollment::updateOrCreate([
                                'user_id' => $userId,
                                'course_id' => $course->id,
                            ], [
                                'course_intake_id' => $intakeId,
                                'payment_status' => $isFree ? Enrollment::STATUS_FREE : Enrollment::STATUS_PAID,
                                'is_free' => $isFree,
                                'fee_amount' => $amount,
                                'payment_method' => $method,
                                'transaction_reference' => $reference,
                                'payment_notes' => $fullNotes,
                                'registered_by_user_id' => auth()->id(),
                                'registration_type' => $channel,
                                'paid_at' => $paidAt,
                            ]);

                            // Create an accounting payment ledger record
                            Payment::create([
                                'user_id' => $userId,
                                'course_id' => $course->id,
                                'enrollment_id' => $enrollment->id,
                                'amount' => $amount,
                                'original_amount' => $course->getNumericFee() > 0 ? $course->getNumericFee() : $amount,
                                'discount_amount' => $isFree ? ($course->getNumericFee() > 0 ? $course->getNumericFee() : 0.00) : 0.00,
                                'currency' => 'ZMW',
                                'payment_method' => $method,
                                'provider' => $isFree ? 'manual_waiver' : ($data['provider'] ?? $method),
                                'reference' => $reference,
                                'status' => Payment::STATUS_COMPLETED,
                                'paid_at' => $paidAt,
                                'registered_by_user_id' => auth()->id(),
                                'registration_type' => $channel,
                                'is_free' => $isFree,
                                'payment_notes' => $fullNotes,
                                'metadata' => [
                                    'registration_channel' => $channel,
                                    'registered_by_id' => auth()->id(),
                                    'registered_by_name' => auth()->user()?->name,
                                    'registered_by_role' => auth()->user()?->role,
                                    'is_free' => $isFree,
                                    'free_reason' => $isFree ? ($data['free_reason'] ?? null) : null,
                                    'provider' => $data['provider'] ?? null,
                                    'transaction_reference' => $reference,
                                    'notes' => $fullNotes,
                                ],
                            ]);

                            $count++;
                        }

                        $feeTypeLabel = $isFree ? 'Free/Scholarship' : "Paid (ZMW " . number_format($amount, 2) . ")";
                        Notification::make()
                            ->title("Successfully enrolled {$count} student(s) as {$feeTypeLabel} into '{$course->title}'.")
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('view_payment')
                        ->label('View Payment & Registration')
                        ->icon('heroicon-m-receipt-percent')
                        ->color('info')
                        ->modalHeading(fn (Enrollment $record): string => "Payment & Registration Details: {$record->user?->name}")
                        ->modalDescription('Financial transaction ledger & enrollment metadata.')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close')
                        ->form([
                            TextInput::make('student_info')
                                ->label('Student')
                                ->default(fn (Enrollment $record): string => "{$record->user?->name} ({$record->user?->email})")
                                ->disabled(),

                            TextInput::make('course_title')
                                ->label('Course Offering')
                                ->default(fn (Enrollment $record): string => $record->course?->title ?? '-')
                                ->disabled(),

                            TextInput::make('channel_label')
                                ->label('Registration Channel')
                                ->default(fn (Enrollment $record): string => $record->registrationChannelLabel())
                                ->disabled(),

                            TextInput::make('payment_status_label')
                                ->label('Payment Classification')
                                ->default(fn (Enrollment $record): string => $record->isFree() ? 'Free / Scholarship' : 'Paying Student')
                                ->disabled(),

                            TextInput::make('fee_amount_display')
                                ->label('Tuition Amount')
                                ->default(fn (Enrollment $record): string => $record->formattedFee())
                                ->disabled(),

                            TextInput::make('payment_method_display')
                                ->label('Payment Method / Provider')
                                ->default(fn (Enrollment $record): string => $record->paymentMethodLabel())
                                ->disabled(),

                            TextInput::make('transaction_ref_display')
                                ->label('Transaction Reference / Receipt #')
                                ->default(fn (Enrollment $record): string => $record->transaction_reference ?? ($record->payment?->reference ?? '—'))
                                ->disabled(),

                            TextInput::make('paid_at_display')
                                ->label('Payment Date & Time')
                                ->default(fn (Enrollment $record): string => ($record->paid_at ?? $record->payment?->paid_at)?->format('M d, Y H:i') ?? $record->created_at->format('M d, Y H:i'))
                                ->disabled(),

                            TextInput::make('registered_by_display')
                                ->label('Recorded / Enrolled By')
                                ->default(fn (Enrollment $record): string => $record->registeredBy?->name ?? ($record->payment?->registeredBy?->name ?? 'Direct Online Checkout'))
                                ->disabled(),

                            Textarea::make('notes_display')
                                ->label('Notes & Remarks')
                                ->default(fn (Enrollment $record): string => $record->payment_notes ?? ($record->payment?->payment_notes ?? '—'))
                                ->rows(3)
                                ->disabled()
                                ->columnSpanFull(),
                        ]),

                    Action::make('edit_payment')
                        ->label('Update Payment Details')
                        ->icon('heroicon-m-pencil-square')
                        ->color('warning')
                        ->modalHeading(fn (Enrollment $record): string => "Update Payment & Registration for {$record->user?->name}")
                        ->modalDescription('Edit financial transaction reference, channel, payment method, or fee remarks.')
                        ->fillForm(fn (Enrollment $record): array => [
                            'registration_type' => $record->registration_type ?? Enrollment::CHANNEL_ADMIN_MANUAL,
                            'fee_type' => $record->isFree() ? 'free' : 'paid',
                            'fee_amount' => $record->fee_amount ?? ($record->payment?->amount ?? ($record->course?->fees ?? 0)),
                            'payment_method' => $record->payment_method ?? ($record->payment?->payment_method ?? 'cash'),
                            'provider' => $record->payment?->provider ?? '',
                            'transaction_reference' => $record->transaction_reference ?? ($record->payment?->reference ?? ''),
                            'payment_notes' => $record->payment_notes ?? ($record->payment?->payment_notes ?? ''),
                        ])
                        ->form([
                            Select::make('registration_type')
                                ->label('Enrollment Channel')
                                ->options([
                                    Enrollment::CHANNEL_ADMIN_MANUAL => 'Admin Manual Added (Campus / Office)',
                                    Enrollment::CHANNEL_INSTRUCTOR_MANUAL => 'Instructor Added',
                                    Enrollment::CHANNEL_DIRECT_CHECKOUT => 'Direct Online Checkout',
                                ])
                                ->required(),

                            Radio::make('fee_type')
                                ->label('Tuition / Payment Type')
                                ->options([
                                    'paid' => '💳 Paying Student',
                                    'free' => '🎁 Free / Sponsored / Scholarship',
                                ])
                                ->live()
                                ->required(),

                            TextInput::make('fee_amount')
                                ->label('Fee Amount (ZMW)')
                                ->numeric()
                                ->prefix('ZMW')
                                ->visible(fn (Get $get): bool => $get('fee_type') === 'paid')
                                ->required(fn (Get $get): bool => $get('fee_type') === 'paid'),

                            Select::make('payment_method')
                                ->label('Payment Method')
                                ->options([
                                    'cash' => 'Cash Payment',
                                    'bank_transfer' => 'Bank Transfer / Deposit',
                                    'mobile_money' => 'Mobile Money',
                                    'card' => 'POS / Debit / Credit Card',
                                    'cheque' => 'Company / Bank Cheque',
                                    'other' => 'Other',
                                ])
                                ->visible(fn (Get $get): bool => $get('fee_type') === 'paid')
                                ->required(fn (Get $get): bool => $get('fee_type') === 'paid'),

                            TextInput::make('provider')
                                ->label('Bank / Provider / Terminal')
                                ->placeholder('e.g. Stanbic Bank, Airtel Money, Finance Reception Desk')
                                ->visible(fn (Get $get): bool => $get('fee_type') === 'paid'),

                            TextInput::make('transaction_reference')
                                ->label('Transaction Reference / Receipt #')
                                ->placeholder('e.g. TXN-8392019, Bank Slip #, Receipt Book #1204')
                                ->required(fn (Get $get): bool => $get('fee_type') === 'paid')
                                ->visible(fn (Get $get): bool => $get('fee_type') === 'paid'),

                            Textarea::make('payment_notes')
                                ->label('Accounting Remarks & Notes')
                                ->placeholder('Remarks, reason for waiver or payment verification notes')
                                ->rows(3)
                                ->columnSpanFull(),
                        ])
                        ->action(function (Enrollment $record, array $data): void {
                            $isFree = ($data['fee_type'] ?? 'paid') === 'free';
                            $amount = $isFree ? 0.00 : (float) ($data['fee_amount'] ?? 0);
                            $method = $isFree ? 'free_registration' : ($data['payment_method'] ?? 'cash');
                            $ref = $isFree
                                ? ($record->transaction_reference ?: ('FREE-' . strtoupper(Str::random(8))))
                                : trim((string) ($data['transaction_reference'] ?? ''));
                            $notes = trim((string) ($data['payment_notes'] ?? ''));
                            $channel = $data['registration_type'] ?? $record->registration_type ?? Enrollment::CHANNEL_ADMIN_MANUAL;

                            $record->update([
                                'registration_type' => $channel,
                                'payment_status' => $isFree ? Enrollment::STATUS_FREE : Enrollment::STATUS_PAID,
                                'is_free' => $isFree,
                                'fee_amount' => $amount,
                                'payment_method' => $method,
                                'transaction_reference' => $ref,
                                'payment_notes' => $notes,
                                'registered_by_user_id' => $record->registered_by_user_id ?? auth()->id(),
                                'paid_at' => $isFree ? null : ($record->paid_at ?? now()),
                            ]);

                            Payment::updateOrCreate(
                                ['enrollment_id' => $record->id],
                                [
                                    'user_id' => $record->user_id,
                                    'course_id' => $record->course_id,
                                    'amount' => $amount,
                                    'original_amount' => $record->course?->fees ?? $amount,
                                    'discount_amount' => $isFree ? ($record->course?->fees ?? 0) : 0,
                                    'currency' => 'ZMW',
                                    'payment_method' => $method,
                                    'provider' => $data['provider'] ?? ($isFree ? 'Tuition Waiver' : 'Manual Entry'),
                                    'reference' => $ref !== '' ? $ref : ('REC-' . $record->id),
                                    'status' => 'paid',
                                    'is_free' => $isFree,
                                    'registration_type' => $channel,
                                    'registered_by_user_id' => auth()->id(),
                                    'payment_notes' => $notes,
                                    'paid_at' => $isFree ? null : ($record->paid_at ?? now()),
                                ]
                            );

                            Notification::make()
                                ->title('Payment & registration details successfully updated.')
                                ->success()
                                ->send();
                        }),

                    Action::make('assign_intake')
                        ->label('Assign Cohort')
                        ->icon('heroicon-m-calendar-days')
                        ->color('primary')
                        ->modalHeading(fn (Enrollment $record): string => "Assign Intake Cohort for {$record->user?->name}")
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
                        ->icon(fn (Enrollment $record): string => $record->completed_at ? 'heroicon-m-arrow-path' : 'heroicon-m-check-circle')
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
                                    ->title('Course completion reset and certificate revoked.')
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

                    DeleteAction::make()
                        ->label('Remove from Course')
                        ->icon('heroicon-m-trash')
                        ->modalHeading(fn (Enrollment $record): string => "Remove {$record->user?->name} from {$record->course?->title}?")
                        ->modalDescription('Are you sure you want to remove this student from this course? This will unenrol them from the course.')
                        ->successNotificationTitle('Student removed from course successfully.'),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Remove Selected from Course')
                        ->modalHeading('Remove Selected Students from Course')
                        ->modalDescription('Are you sure you want to remove the selected students from this course?')
                        ->successNotificationTitle('Selected students removed from course successfully.'),
                ]),
            ]);
    }
}
