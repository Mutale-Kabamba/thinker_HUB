<?php

namespace App\Filament\Parent\Pages;

use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\Enrollment;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ManageKids extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Manage My Kids';

    protected static ?string $title = 'Manage My Kids';

    protected static ?int $navigationSort = 2;

    protected static string|\UnitEnum|null $navigationGroup = 'KIDS MANAGEMENT';

    protected string $view = 'filament.parent.pages.manage-kids';

    // Modals state
    public bool $showAddKidModal = false;

    public bool $showLinkKidModal = false;

    public bool $showEnrollKidModal = false;

    public bool $showEditKidModal = false;

    // Form inputs for Add Kid
    public string $newKidName = '';

    public string $newKidEmail = '';

    public ?int $newKidAge = null;

    public string $newKidSchool = '';

    public string $newKidGrade = '';

    public string $newKidPassword = '';

    public string $newKidTrack = 'Beginner';

    public string $newKidGender = '';

    public string $newKidRelationship = 'Parent';

    public string $newKidNotes = '';

    public ?int $newKidCourseId = null;

    // Form inputs for Link Kid
    public string $linkKidEmail = '';

    public string $linkKidRelationship = 'Parent';

    public string $linkKidNotes = '';

    // Form inputs for Enroll Kid
    public ?int $enrollKidId = null;

    public ?int $enrollCourseId = null;

    public ?int $enrollIntakeId = null;

    // Form inputs for Edit Kid
    public ?int $editingKidId = null;

    public string $editKidName = '';

    public string $editKidUniqueCode = '';

    public ?int $editKidAge = null;

    public string $editKidSchool = '';

    public string $editKidGrade = '';

    public string $editKidPassword = '';

    public string $editKidTrack = 'Beginner';

    public string $editKidRelationship = 'Parent';

    public string $editKidNotes = '';

    public array $kids = [];

    public array $availableKidsCourses = [];

    public function mount(): void
    {
        $this->loadKids();
    }

    public function loadKids(): void
    {
        $parent = auth()->user();

        if (! $parent) {
            return;
        }

        $this->kids = $parent->kids()
            ->with(['enrollments.course', 'enrollments.intake'])
            ->get()
            ->map(function (User $kid): array {
                $courses = $kid->enrollments->map(fn ($e): array => [
                    'enrollment_id' => $e->id,
                    'course_id' => $e->course?->id,
                    'title' => $e->course?->title ?? 'Course',
                    'code' => $e->course?->code,
                    'is_for_kids' => (bool) $e->course?->is_for_kids,
                    'intake' => $e->intake?->name,
                    'enrolled_at' => ($e->created_at ?? $e->enrolled_at)?->format('M d, Y'),
                ])->all();

                return [
                    'id' => $kid->id,
                    'name' => $kid->name,
                    'email' => $kid->email,
                    'unique_code' => $kid->unique_code,
                    'age' => $kid->age,
                    'school' => $kid->school,
                    'grade' => $kid->grade,
                    'gender' => $kid->gender,
                    'track' => $kid->track ?? 'Beginner',
                    'relationship' => $kid->pivot->relationship ?? 'Parent',
                    'notes' => $kid->pivot->notes ?? '',
                    'avatar' => $kid->getFilamentAvatarUrl(),
                    'xp' => (int) ($kid->lifetime_xp ?? 0),
                    'courses' => $courses,
                    'courses_count' => count($courses),
                    'created_at' => $kid->created_at?->format('M d, Y'),
                ];
            })
            ->all();

        // Available active courses (prioritizing Kids courses)
        $this->availableKidsCourses = Course::query()
            ->where('is_active', true)
            ->with('intakes')
            ->orderByDesc('is_for_kids')
            ->orderBy('title')
            ->get()
            ->map(fn (Course $c): array => [
                'id' => $c->id,
                'title' => $c->title,
                'code' => $c->code,
                'is_for_kids' => (bool) $c->is_for_kids,
                'intakes' => $c->intakes->map(fn ($i): array => [
                    'id' => $i->id,
                    'name' => $i->name,
                ])->all(),
            ])
            ->all();
    }

    public function openAddKidModal(): void
    {
        $this->reset(['newKidName', 'newKidEmail', 'newKidPassword', 'newKidGender', 'newKidNotes', 'newKidCourseId', 'newKidAge', 'newKidSchool', 'newKidGrade']);
        $this->newKidTrack = 'Beginner';
        $this->newKidRelationship = 'Parent';
        $this->showAddKidModal = true;
    }

    public function createKid(): mixed
    {
        $parent = auth()->user();

        if (! $parent) {
            return null;
        }

        $this->validate([
            'newKidName' => ['required', 'string', 'max:255'],
            'newKidEmail' => ['nullable', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')],
            'newKidPassword' => ['required', 'string', 'min:6'],
            'newKidAge' => ['nullable', 'integer', 'min:3', 'max:18'],
            'newKidSchool' => ['nullable', 'string', 'max:255'],
            'newKidGrade' => ['nullable', 'string', 'max:100'],
            'newKidTrack' => ['required', 'in:Beginner,Intermediate,Advanced'],
            'newKidRelationship' => ['required', 'string', 'max:50'],
            'newKidGender' => ['nullable', 'string', 'max:20'],
            'newKidNotes' => ['nullable', 'string', 'max:1000'],
            'newKidCourseId' => ['nullable', 'exists:courses,id'],
        ]);

        $uniqueCode = User::generateKidUniqueCode();
        $kidEmail = $this->newKidEmail ? trim($this->newKidEmail) : strtolower($uniqueCode).'@kids.thinkerhub.ac.zm';

        $kid = DB::transaction(function () use ($parent, $uniqueCode, $kidEmail): User {
            $kid = User::create([
                'name' => trim($this->newKidName),
                'email' => $kidEmail,
                'unique_code' => $uniqueCode,
                'password' => $this->newKidPassword, // User model has hashed cast
                'age' => $this->newKidAge,
                'school' => $this->newKidSchool ? trim($this->newKidSchool) : null,
                'grade' => $this->newKidGrade ? trim($this->newKidGrade) : null,
                'role' => 'student',
                'is_kid' => true,
                'is_active' => true,
                'track' => $this->newKidTrack,
                'gender' => $this->newKidGender ? ucfirst(strtolower($this->newKidGender)) : null,
                'default_portal' => 'student',
                'email_verified_at' => now(), // Auto-verify kid created by parent
            ]);

            // Link to parent
            $parent->kids()->attach($kid->id, [
                'relationship' => trim($this->newKidRelationship),
                'age' => $this->newKidAge,
                'school' => $this->newKidSchool ? trim($this->newKidSchool) : null,
                'grade' => $this->newKidGrade ? trim($this->newKidGrade) : null,
                'notes' => trim($this->newKidNotes),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $kid;
        });

        // If course selected, require payment by redirecting to checkout
        if ($this->newKidCourseId) {
            $course = Course::find($this->newKidCourseId);
            $fee = $course ? (method_exists($course, 'getNumericFee') ? $course->getNumericFee() : 0) : 0;
            if ($course && $fee <= 0 && isset($course->price) && is_numeric($course->price)) {
                $fee = (float) $course->price;
            }

            if ($course && ($fee > 0 || ! empty($course->requires_payment_approval))) {
                $this->showAddKidModal = false;

                return $this->redirectRoute('checkout.show', [
                    'course' => $course->id,
                    'kid_id' => $kid->id,
                ]);
            }

            if ($course) {
                Enrollment::firstOrCreate([
                    'user_id' => $kid->id,
                    'course_id' => $course->id,
                ]);
            }
        }

        $this->showAddKidModal = false;
        $this->loadKids();

        Notification::make()
            ->success()
            ->title('Child Account Created')
            ->body("{$this->newKidName}'s account has been created (Code: {$uniqueCode}) and securely connected to your portal.")
            ->send();

        return null;
    }

    public function openLinkKidModal(): void
    {
        $this->reset(['linkKidEmail', 'linkKidNotes']);
        $this->linkKidRelationship = 'Parent';
        $this->showLinkKidModal = true;
    }

    public function linkKid(): void
    {
        $parent = auth()->user();

        if (! $parent) {
            return;
        }

        $this->validate([
            'linkKidEmail' => ['required', 'string', 'max:255'],
            'linkKidRelationship' => ['required', 'string', 'max:50'],
            'linkKidNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        $kid = User::findKidByCodeOrEmail($this->linkKidEmail);

        if (! $kid) {
            Notification::make()
                ->danger()
                ->title('Student Not Found')
                ->body('No registered student account was found with that login code or email address.')
                ->send();

            return;
        }

        if ($kid->id === $parent->id) {
            Notification::make()
                ->danger()
                ->title('Invalid Link')
                ->body('You cannot link your own parent account as a child.')
                ->send();

            return;
        }

        // Check if already linked
        if ($parent->kids()->where('users.id', $kid->id)->exists()) {
            Notification::make()
                ->warning()
                ->title('Already Linked')
                ->body("{$kid->name} is already linked to your parent account.")
                ->send();

            return;
        }

        $kid->is_kid = true;
        if (empty($kid->unique_code)) {
            $kid->unique_code = User::generateKidUniqueCode();
        }
        $kid->save();

        $parent->kids()->attach($kid->id, [
            'relationship' => trim($this->linkKidRelationship),
            'notes' => trim($this->linkKidNotes),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->showLinkKidModal = false;
        $this->loadKids();

        Notification::make()
            ->success()
            ->title('Child Linked Successfully')
            ->body("{$kid->name} (Code: {$kid->unique_code}) has been linked to your Parents Portal.")
            ->send();
    }

    public function openEnrollKidModal(int $kidId): void
    {
        $this->enrollKidId = $kidId;
        $this->enrollCourseId = null;
        $this->enrollIntakeId = null;
        $this->showEnrollKidModal = true;
    }

    public function enrollKid(): mixed
    {
        $parent = auth()->user();

        if (! $parent || ! $this->enrollKidId || ! $this->enrollCourseId) {
            return null;
        }

        $kid = $parent->kids()->where('users.id', $this->enrollKidId)->first();

        if (! $kid) {
            Notification::make()->danger()->title('Child not found')->send();

            return null;
        }

        $course = Course::query()->find($this->enrollCourseId);

        if (! $course) {
            Notification::make()->danger()->title('Course not found')->send();

            return null;
        }

        // Check already enrolled
        $exists = Enrollment::query()
            ->where('user_id', $kid->id)
            ->where('course_id', $course->id)
            ->exists();

        if ($exists) {
            Notification::make()
                ->warning()
                ->title('Already Enrolled')
                ->body("{$kid->name} is already enrolled in {$course->title}.")
                ->send();

            return null;
        }

        // Enrolling existing child into an additional course requires payment
        $fee = method_exists($course, 'getNumericFee') ? $course->getNumericFee() : 0;
        if ($fee <= 0 && isset($course->price) && is_numeric($course->price)) {
            $fee = (float) $course->price;
        }
        if ($fee > 0 || ! empty($course->requires_payment_approval)) {
            $this->showEnrollKidModal = false;

            return $this->redirectRoute('checkout.show', [
                'course' => $course->id,
                'kid_id' => $kid->id,
                'intake_id' => $this->enrollIntakeId ?: null,
            ]);
        }

        Enrollment::create([
            'user_id' => $kid->id,
            'course_id' => $course->id,
            'course_intake_id' => $this->enrollIntakeId ?: null,
            'enrolled_at' => now(),
        ]);

        $this->showEnrollKidModal = false;
        $this->loadKids();

        Notification::make()
            ->success()
            ->title('Enrolled Successfully')
            ->body("{$kid->name} is now enrolled in {$course->title}!")
            ->send();

        return null;
    }

    public function openEditKidModal(int $kidId): void
    {
        $parent = auth()->user();
        $kid = $parent?->kids()->where('kid_id', $kidId)->first();

        if (! $kid) {
            return;
        }

        $this->editingKidId = $kid->id;
        $this->editKidName = $kid->name;
        $this->editKidUniqueCode = $kid->unique_code ?? '';
        $this->editKidAge = $kid->age;
        $this->editKidSchool = $kid->school ?? '';
        $this->editKidGrade = $kid->grade ?? '';
        $this->editKidPassword = '';
        $this->editKidTrack = $kid->track ?? 'Beginner';
        $this->editKidRelationship = $kid->pivot->relationship ?? 'Parent';
        $this->editKidNotes = $kid->pivot->notes ?? '';
        $this->showEditKidModal = true;
    }

    public function updateKid(): void
    {
        $parent = auth()->user();

        if (! $parent || ! $this->editingKidId) {
            return;
        }

        $kid = $parent->kids()->where('kid_id', $this->editingKidId)->first();

        if (! $kid) {
            return;
        }

        $this->validate([
            'editKidName' => ['required', 'string', 'max:255'],
            'editKidUniqueCode' => ['required', 'string', 'max:50', Rule::unique('users', 'unique_code')->ignore($kid->id)],
            'editKidAge' => ['nullable', 'integer', 'min:3', 'max:18'],
            'editKidSchool' => ['nullable', 'string', 'max:255'],
            'editKidGrade' => ['nullable', 'string', 'max:100'],
            'editKidTrack' => ['required', 'in:Beginner,Intermediate,Advanced'],
            'editKidRelationship' => ['required', 'string', 'max:50'],
            'editKidPassword' => ['nullable', 'string', 'min:6'],
            'editKidNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        $kidUpdate = [
            'name' => trim($this->editKidName),
            'unique_code' => strtoupper(trim($this->editKidUniqueCode)),
            'age' => $this->editKidAge,
            'school' => $this->editKidSchool ? trim($this->editKidSchool) : null,
            'grade' => $this->editKidGrade ? trim($this->editKidGrade) : null,
            'track' => $this->editKidTrack,
        ];

        if (! empty($this->editKidPassword)) {
            $kidUpdate['password'] = Hash::make($this->editKidPassword);
        }

        $kid->update($kidUpdate);

        $parent->kids()->updateExistingPivot($kid->id, [
            'relationship' => trim($this->editKidRelationship),
            'age' => $this->editKidAge,
            'school' => $this->editKidSchool ? trim($this->editKidSchool) : null,
            'grade' => $this->editKidGrade ? trim($this->editKidGrade) : null,
            'notes' => trim($this->editKidNotes),
            'updated_at' => now(),
        ]);

        $this->showEditKidModal = false;
        $this->loadKids();

        Notification::make()
            ->success()
            ->title('Child Details Updated')
            ->body("Information for {$this->editKidName} has been saved.")
            ->send();
    }

    public function switchToKid(int $kidId)
    {
        $parent = auth()->user();

        if (! $parent) {
            return null;
        }

        $kid = $parent->kids()->where('users.id', $kidId)->first();

        if (! $kid) {
            Notification::make()
                ->danger()
                ->title('Child not found')
                ->body('Unable to switch into this account.')
                ->send();

            return null;
        }

        session(['impersonating_parent_id' => $parent->id]);
        auth()->login($kid);

        Notification::make()
            ->title('Switched to ' . $kid->name)
            ->body('Viewing learning space as ' . $kid->name . '.')
            ->success()
            ->send();

        return redirect()->route('filament.student.pages.overview');
    }

    public function unlinkKid(int $kidId): void
    {
        $parent = auth()->user();

        if (! $parent) {
            return;
        }

        $parent->kids()->detach($kidId);

        $this->loadKids();

        Notification::make()
            ->success()
            ->title('Child Unlinked')
            ->body('The child has been disconnected from your parent portal.')
            ->send();
    }
}
