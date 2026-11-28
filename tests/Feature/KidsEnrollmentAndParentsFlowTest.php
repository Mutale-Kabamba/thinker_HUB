<?php

namespace Tests\Feature;

use App\Filament\Parent\Pages\ManageKids;
use App\Filament\Parent\Pages\ParentsChat;
use App\Mail\KidAccountCredentialsMail;
use App\Models\ChatRoom;
use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\User;
use App\Services\Payment\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class KidsEnrollmentAndParentsFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    private function createKidsCourse(array $attributes = []): Course
    {
        static $sequence = 1;
        $seq = $sequence++;

        $price = $attributes['price'] ?? 1500;

        return Course::create(array_merge([
            'title' => 'Scratch Coding for Kids ' . $seq,
            'code' => 'KID-CODE-' . $seq,
            'overview' => 'Kids programming course',
            'fees' => (string) $price,
            'is_active' => true,
            'is_for_kids' => true,
            'is_open_enrollment' => true,
        ], $attributes, [
            'fees' => (string) ($attributes['fees'] ?? $attributes['price'] ?? $price),
        ]));
    }

    public function test_kids_course_checkout_requires_both_kid_and_parent_fields(): void
    {
        $course = $this->createKidsCourse();

        // Submitting with missing kid/parent details should fail validation
        $response = $this->from(route('checkout.show', $course))->post(route('checkout.process', $course), [
            'course_id' => $course->id,
            'track' => 'Beginner',
            'payment_method' => 'mobile_money',
            'phone' => '0971234567',
            'is_for_kids' => '1',
            // Missing parent & kid required fields
        ]);

        $response->assertSessionHasErrors([
            'parent_name',
            'parent_email',
            'parent_password',
            'parent_phone',
            'parent_occupation',
            'parent_company',
            'parent_address',
            'kid_name',
            'kid_age',
            'kid_school',
            'kid_grade',
            'relationship',
            'kid_password',
        ]);
    }

    public function test_payment_fulfillment_creates_parent_and_kid_accounts_and_sends_credentials_mail(): void
    {
        Mail::fake();

        $course = $this->createKidsCourse(['price' => 1200]);
        $intake = CourseIntake::create([
            'course_id' => $course->id,
            'name' => 'Cohort Kids Alpha',
            'start_date' => now()->addDays(5),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        $kidsData = [
            'parent_name' => 'Mutale Mwila',
            'parent_email' => 'mutale.parent@example.com',
            'parent_password' => 'ParentSecret123!',
            'parent_phone' => '+260971112233',
            'parent_occupation' => 'Software Engineer',
            'parent_company' => 'Tech Hub Ltd',
            'parent_address' => 'Plot 45, Woodlands, Lusaka',
            'kid_name' => 'Lupiya Mwila',
            'kid_age' => 9,
            'kid_school' => 'Rhodes Park School',
            'kid_grade' => 'Grade 4',
            'relationship' => 'Father',
            'kid_password' => 'KidSecret123!',
        ];

        $payment = Payment::create([
            'transaction_id' => 'TX-KIDS-' . uniqid(),
            'reference' => 'REF-KIDS-' . uniqid(),
            'course_id' => $course->id,
            'course_intake_id' => $intake->id,
            'amount' => 1200,
            'currency' => 'ZMW',
            'gateway' => 'airtel_money',
            'phone' => '+260971112233',
            'status' => 'pending',
            'payment_type' => 'full',
            'metadata' => [
                'is_kids_course' => true,
                'kids_data' => $kidsData,
            ],
        ]);

        /** @var PaymentService $paymentService */
        $paymentService = app(PaymentService::class);
        $paymentService->fulfillPayment($payment);

        $payment->refresh();
        $this->assertEquals('completed', $payment->status);

        // 1. Verify Parent Account
        $parent = User::where('email', 'mutale.parent@example.com')->first();
        $this->assertNotNull($parent, 'Parent account must be created.');
        $this->assertEquals('Mutale Mwila', $parent->name);
        $this->assertEquals('parent', $parent->role);
        $this->assertEquals('parent', $parent->default_portal);
        $this->assertEquals('+260971112233', $parent->whatsapp);
        $this->assertEquals('Software Engineer', $parent->occupation);
        $this->assertEquals('Tech Hub Ltd', $parent->employer);
        $this->assertEquals('Plot 45, Woodlands, Lusaka', $parent->address);
        $this->assertTrue(Hash::check('ParentSecret123!', $parent->password));

        // 2. Verify Kid Account
        $kid = User::where('name', 'Lupiya Mwila')->where('is_kid', true)->first();
        $this->assertNotNull($kid, 'Kid account must be created.');
        $this->assertStringStartsWith('KID-', $kid->unique_code);
        $this->assertMatchesRegularExpression('/^KID-\d{4,5}$/', $kid->unique_code);
        $this->assertLessThanOrEqual(9, strlen($kid->unique_code));
        $this->assertEquals(9, $kid->age);
        $this->assertEquals('Rhodes Park School', $kid->school);
        $this->assertEquals('Grade 4', $kid->grade);
        $this->assertEquals('student', $kid->role);
        $this->assertEquals('student', $kid->default_portal);
        $this->assertTrue(Hash::check('KidSecret123!', $kid->password));

        // 3. Verify Pivot Link
        $this->assertTrue($parent->kids()->where('kid_id', $kid->id)->exists());
        $pivot = $parent->kids()->first()->pivot;
        $this->assertEquals('Father', $pivot->relationship);
        $this->assertEquals(9, $pivot->age);
        $this->assertEquals('Rhodes Park School', $pivot->school);
        $this->assertEquals('Grade 4', $pivot->grade);

        // 4. Verify Kid is enrolled in course & intake
        $this->assertTrue(
            Enrollment::where('user_id', $kid->id)
                ->where('course_id', $course->id)
                ->where('course_intake_id', $intake->id)
                ->exists(),
            'Kid must be enrolled in the course and active intake.'
        );

        // 5. Verify KidAccountCredentialsMail dispatched to parent
        Mail::assertSent(KidAccountCredentialsMail::class, function (KidAccountCredentialsMail $mail) use ($parent, $kid) {
            return $mail->hasTo($parent->email)
                && $mail->kid->id === $kid->id
                && $mail->uniqueCode === $kid->unique_code;
        });
    }

    public function test_kid_can_log_in_using_unique_code_and_password(): void
    {
        $kid = User::factory()->create([
            'name' => 'Chileshe Banda',
            'email' => 'kid.chileshe.unique@thinkerhub.local',
            'unique_code' => 'KID-4892',
            'password' => 'ChileshePass2026!',
            'role' => 'student',
            'is_kid' => true,
            'is_active' => true,
        ]);

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        // Standard login: KID-4892
        $response = $this->post('/login', [
            'login' => 'KID-4892',
            'password' => 'ChileshePass2026!',
        ]);
        $this->assertAuthenticatedAs($kid);

        auth()->logout();

        // Lowercase and hyphenless login: kid4892
        $response = $this->post('/login', [
            'login' => 'kid4892',
            'password' => 'ChileshePass2026!',
        ]);
        $this->assertAuthenticatedAs($kid);

        auth()->logout();

        // Just digits login: 4892
        $response = $this->post('/login', [
            'login' => '4892',
            'password' => 'ChileshePass2026!',
        ]);
        $this->assertAuthenticatedAs($kid);
    }

    public function test_parent_can_switch_into_kid_account_and_switch_back(): void
    {
        $parent = User::factory()->create([
            'name' => 'Mrs. Grace Zulu',
            'email' => 'grace.zulu@example.com',
            'role' => 'parent',
            'default_portal' => 'parent',
        ]);

        $kid = User::factory()->create([
            'name' => 'Kondwani Zulu',
            'unique_code' => 'TH-KID-33441',
            'role' => 'student',
            'is_kid' => true,
        ]);

        $parent->kids()->attach($kid->id, ['relationship' => 'Mother']);

        // Parent switches into kid's student portal
        $response = $this->actingAs($parent)->get(route('parent.switch-to-kid', $kid));
        $response->assertRedirect(route('filament.student.pages.overview'));
        $this->assertAuthenticatedAs($kid);
        $this->assertEquals($parent->id, session('impersonating_parent_id'));

        // Parent switches back
        $responseBack = $this->get(route('parent.switch-back'));
        $responseBack->assertRedirect(route('filament.parent.pages.manage-kids'));
        $this->assertAuthenticatedAs($parent);
        $this->assertNull(session('impersonating_parent_id'));
    }

    public function test_kid_account_cannot_switch_to_parent_portal(): void
    {
        $kid = User::factory()->create([
            'name' => 'Little Jimmy',
            'unique_code' => 'TH-KID-11223',
            'role' => 'student',
            'is_kid' => true,
        ]);

        $this->assertFalse($kid->canSwitchToParent());
        $this->assertFalse($kid->isParent());
        $this->assertEquals(['student'], array_keys($kid->getAvailablePortals()));

        // Kid attempting to access parent portal directly gets forbidden / redirected
        $response = $this->actingAs($kid)->get('/parent/overview');
        $this->assertTrue(in_array($response->status(), [302, 403]));
    }

    public function test_parent_cannot_switch_into_unlinked_kid(): void
    {
        $parent = User::factory()->create(['role' => 'parent']);
        $strangerKid = User::factory()->create(['is_kid' => true]);

        $response = $this->actingAs($parent)->get(route('parent.switch-to-kid', $strangerKid));
        $response->assertForbidden();
    }

    public function test_parents_community_chat_only_allows_mutual_parents(): void
    {
        // Kids Course A
        $courseA = $this->createKidsCourse(['title' => 'Robotics for Kids']);

        // Kids Course B
        $courseB = $this->createKidsCourse(['title' => 'Animation for Kids']);

        // Parent 1 with Kid 1 in Course A
        $parent1 = User::factory()->create(['name' => 'Parent Alpha', 'role' => 'parent']);
        $kid1 = User::factory()->create(['name' => 'Kid Alpha', 'is_kid' => true]);
        $parent1->kids()->attach($kid1->id, ['relationship' => 'Father']);
        Enrollment::create(['user_id' => $kid1->id, 'course_id' => $courseA->id]);

        // Parent 2 with Kid 2 in Course A (Mutual parent!)
        $parent2 = User::factory()->create(['name' => 'Parent Beta (Mutual)', 'role' => 'parent']);
        $kid2 = User::factory()->create(['name' => 'Kid Beta', 'is_kid' => true]);
        $parent2->kids()->attach($kid2->id, ['relationship' => 'Mother']);
        Enrollment::create(['user_id' => $kid2->id, 'course_id' => $courseA->id]);

        // Parent 3 with Kid 3 in Course B (Unrelated parent)
        $parent3 = User::factory()->create(['name' => 'Parent Gamma (Unrelated)', 'role' => 'parent']);
        $kid3 = User::factory()->create(['name' => 'Kid Gamma', 'is_kid' => true]);
        $parent3->kids()->attach($kid3->id, ['relationship' => 'Mother']);
        Enrollment::create(['user_id' => $kid3->id, 'course_id' => $courseB->id]);

        $this->actingAs($parent1);

        $livewire = Livewire::test(ParentsChat::class);
        $mutualParents = collect($livewire->get('mutualParents'))->pluck('id')->all();

        // Must see Parent Beta (mutual)
        $this->assertContains($parent2->id, $mutualParents);

        // Must NOT see Parent Gamma (enrolled only in different course)
        $this->assertNotContains($parent3->id, $mutualParents);

        // Must NOT see themselves
        $this->assertNotContains($parent1->id, $mutualParents);

        // Parent 1 can start direct chat with mutual Parent 2
        $livewire->call('startDirectChat', $parent2->id);
        $selectedRoomId = $livewire->get('selectedRoomId');
        $this->assertNotNull($selectedRoomId);

        // Check active room is created and both are members
        $room = ChatRoom::find($selectedRoomId);
        $this->assertNotNull($room);
        $this->assertTrue($room->members()->where('users.id', $parent1->id)->exists());
        $this->assertTrue($room->members()->where('users.id', $parent2->id)->exists());

        // Parent 1 attempting to start chat with unrelated parent 3 should be rejected
        $livewire->call('startDirectChat', $parent3->id);
        // Room should not switch to parent3
        $this->assertNotEquals(
            ChatRoom::whereHas('members', fn ($q) => $q->where('users.id', $parent3->id))->value('id'),
            $livewire->get('selectedRoomId')
        );
    }

    public function test_existing_student_is_upgraded_to_parent_and_not_enrolled_as_student_when_purchasing_kids_course(): void
    {
        Mail::fake();

        $existingStudent = User::factory()->create([
            'name' => 'Existing Student Parent',
            'email' => 'student.parent@example.com',
            'role' => 'student',
            'default_portal' => 'student',
            'is_kid' => false,
        ]);

        $course = $this->createKidsCourse(['price' => 2000]);
        $intake = CourseIntake::create([
            'course_id' => $course->id,
            'name' => 'Cohort Kids Beta',
            'start_date' => now()->addDays(3),
            'end_date' => now()->addMonths(2),
            'is_active' => true,
        ]);

        $kidsData = [
            'parent_name' => 'Existing Student Parent',
            'parent_email' => 'student.parent@example.com',
            'parent_phone' => '+260977889900',
            'parent_occupation' => 'Architect',
            'parent_company' => 'Design Studio',
            'parent_address' => '123 Great East Rd, Lusaka',
            'kid_name' => 'Little Kondwani',
            'kid_age' => 8,
            'kid_school' => 'Baobab College',
            'kid_grade' => 'Grade 3',
            'relationship' => 'Mother',
            'kid_password' => 'LittlePass123!',
        ];

        // Simulate an initial enrollment that might have been created during checkout initiation
        Enrollment::create([
            'user_id' => $existingStudent->id,
            'course_id' => $course->id,
            'status' => 'pending',
        ]);

        $payment = Payment::create([
            'transaction_id' => 'TX-KIDS-UPGRADE-' . uniqid(),
            'reference' => 'REF-KIDS-UPGRADE-' . uniqid(),
            'user_id' => $existingStudent->id,
            'course_id' => $course->id,
            'course_intake_id' => $intake->id,
            'amount' => 2000,
            'currency' => 'ZMW',
            'gateway' => 'airtel_money',
            'phone' => '+260977889900',
            'status' => 'pending',
            'payment_type' => 'full',
            'metadata' => [
                'is_kids_course' => true,
                'kids_data' => $kidsData,
            ],
        ]);

        /** @var PaymentService $paymentService */
        $paymentService = app(PaymentService::class);
        $paymentService->fulfillPayment($payment);

        $existingStudent->refresh();
        $this->assertEquals('parent', $existingStudent->role, 'User must be upgraded to parent role.');
        $this->assertEquals('parent', $existingStudent->default_portal, 'User default portal must be parent.');
        $this->assertFalse((bool) $existingStudent->is_kid, 'Parent must not be marked as kid.');

        // Verify parent is NOT enrolled directly in kids course
        $this->assertFalse(
            Enrollment::where('user_id', $existingStudent->id)->where('course_id', $course->id)->exists(),
            'Parent must not have an enrollment in the kids course.'
        );

        // Verify kid was created and enrolled
        $kid = User::where('name', 'Little Kondwani')->where('is_kid', true)->first();
        $this->assertNotNull($kid, 'Kid user must be created.');
        $this->assertEquals('student', $kid->role);
        $this->assertTrue((bool) $kid->is_kid);

        $this->assertTrue(
            Enrollment::where('user_id', $kid->id)->where('course_id', $course->id)->exists(),
            'Kid must be enrolled in the course.'
        );

        // Verify relationship link in parent_kids
        $this->assertTrue(
            $existingStudent->kids()->where('kid_id', $kid->id)->exists(),
            'Parent must be linked to kid in parent_kids table.'
        );
        $pivot = $existingStudent->kids()->where('kid_id', $kid->id)->first()->pivot;
        $this->assertEquals('Mother', $pivot->relationship);
        $this->assertEquals(8, $pivot->age);
        $this->assertEquals('Baobab College', $pivot->school);
        $this->assertEquals('Grade 3', $pivot->grade);
    }

    public function test_fallback_fulfillment_for_kids_course_creates_parent_role(): void
    {
        $course = $this->createKidsCourse(['price' => 1000]);

        // Payment for kids course without kids_data in metadata (reconstructed fallback)
        $payment = Payment::create([
            'transaction_id' => 'TX-FALLBACK-' . uniqid(),
            'reference' => 'REF-FALLBACK-' . uniqid(),
            'course_id' => $course->id,
            'amount' => 1000,
            'currency' => 'ZMW',
            'gateway' => 'airtel_money',
            'phone' => '+260971234567',
            'status' => 'pending',
            'payment_type' => 'full',
            'metadata' => [
                'is_kids_course' => true,
                'parent_name' => 'Guardian Mwape',
                'parent_email' => 'guardian.mwape@example.com',
                'kid_name' => 'Junior Mwape',
                'relationship' => 'Guardian',
            ],
        ]);

        /** @var PaymentService $paymentService */
        $paymentService = app(PaymentService::class);
        $paymentService->fulfillPayment($payment);

        $parent = User::where('email', 'guardian.mwape@example.com')->first();
        $this->assertNotNull($parent);
        $this->assertEquals('parent', $parent->role);
        $this->assertEquals('parent', $parent->default_portal);
        $this->assertFalse((bool) $parent->is_kid);

        $kid = User::where('name', 'Junior Mwape')->where('is_kid', true)->first();
        $this->assertNotNull($kid);
        $this->assertTrue($parent->kids()->where('kid_id', $kid->id)->exists());
    }

    public function test_parent_can_register_and_link_admin_registered_kid_skipping_payment_and_kid_info(): void
    {
        $adminRegisteredKid = User::create([
            'name' => 'Mwila Administered Kid',
            'email' => 'kid4892@kids.thinkerhub.ac.zm',
            'unique_code' => 'KID-4892',
            'password' => 'KidPass123!',
            'role' => 'student',
            'default_portal' => 'student',
            'is_kid' => true,
            'is_active' => true,
            'track' => 'Beginner',
            'age' => 8,
            'school' => 'Rhodes Park School',
            'grade' => 'Grade 3',
            'email_verified_at' => now(),
        ]);

        $response = $this->post(route('register'), [
            'account_type' => 'parent',
            'kid_unique_code' => 'KID-4892',
            'relationship' => 'Father',
            'phone_number' => '0971234567',
            'occupation' => 'Architect',
            'company' => 'Studio Designs',
            'address' => 'Plot 10, Kabulonga, Lusaka',
            'name' => 'Patrick Mwila',
            'email' => 'patrick.mwila@example.com',
            'password' => 'ParentSecret123!',
            'password_confirmation' => 'ParentSecret123!',
            'accept_terms' => 1,
            'accept_requirements' => 1,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('filament.parent.pages.manage-kids'));

        $parent = User::where('email', 'patrick.mwila@example.com')->first();
        $this->assertNotNull($parent, 'Parent account must be created.');
        $this->assertEquals('parent', $parent->role);
        $this->assertEquals('parent', $parent->default_portal);
        $this->assertFalse((bool) $parent->is_kid);

        // Verify parent is linked to kid
        $this->assertTrue(
            $parent->kids()->where('users.id', $adminRegisteredKid->id)->exists(),
            'Admin registered kid must be linked to parent.'
        );
        $this->assertEquals('Father', $parent->kids()->first()->pivot->relationship);

        // Verify parent is authenticated
        $this->assertAuthenticatedAs($parent);
    }

    public function test_registered_parent_can_link_admin_registered_kid_via_portal_using_unique_code(): void
    {
        $parent = User::create([
            'name' => 'Mrs. Chileshe',
            'email' => 'mrs.chileshe@example.com',
            'password' => 'Password123!',
            'role' => 'parent',
            'default_portal' => 'parent',
            'is_kid' => false,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $kid = User::create([
            'name' => 'Bwalya Chileshe',
            'email' => 'kid7777@kids.thinkerhub.ac.zm',
            'unique_code' => 'KID-7777',
            'password' => 'KidPass123!',
            'role' => 'student',
            'default_portal' => 'student',
            'is_kid' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        Livewire::actingAs($parent)
            ->test(ManageKids::class)
            ->set('linkKidEmail', 'kid-7777')
            ->set('linkKidRelationship', 'Mother')
            ->call('linkKid')
            ->assertHasNoErrors();

        $this->assertTrue(
            $parent->kids()->where('users.id', $kid->id)->exists(),
            'Kid must be linked to parent using unique code.'
        );
    }

    public function test_registered_parent_enrolling_existing_kid_in_second_course_redirects_to_checkout_and_requires_payment(): void
    {
        $parent = User::create([
            'name' => 'Mr. Bwalya',
            'email' => 'mr.bwalya@example.com',
            'password' => 'Password123!',
            'role' => 'parent',
            'default_portal' => 'parent',
            'is_kid' => false,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $kid = User::create([
            'name' => 'Kondwani Bwalya',
            'email' => 'kid8888@kids.thinkerhub.ac.zm',
            'unique_code' => 'KID-8888',
            'password' => 'KidPass123!',
            'role' => 'student',
            'default_portal' => 'student',
            'is_kid' => true,
            'is_active' => true,
            'age' => 11,
            'school' => 'Baobab',
            'grade' => 'Grade 6',
            'email_verified_at' => now(),
        ]);

        $parent->kids()->attach($kid->id, ['relationship' => 'Father']);

        $course1 = $this->createKidsCourse(['title' => 'Scratch Beginners', 'price' => 1500]);
        Enrollment::create(['user_id' => $kid->id, 'course_id' => $course1->id]);

        $course2 = $this->createKidsCourse(['title' => 'Python for Kids', 'price' => 2000]);

        // Attempting to enroll existing kid in Course 2 from portal redirects to checkout (requires payment)
        Livewire::actingAs($parent)
            ->test(ManageKids::class)
            ->set('enrollKidId', $kid->id)
            ->set('enrollCourseId', $course2->id)
            ->call('enrollKid')
            ->assertRedirect(route('checkout.show', ['course' => $course2->id, 'kid_id' => $kid->id]));

        // Process payment with existing_kid_id
        Http::fake([
            '*/collections/mobile-money*' => Http::response([
                'status' => 'pending',
                'message' => 'USSD Prompt sent. Enter PIN to approve.',
                'data' => [
                    'id' => 'BP-TEST-1234',
                    'status' => 'pending',
                ],
            ], 200),
            '*' => Http::response(['status' => 'success'], 200),
        ]);

        $response = $this->actingAs($parent)->post(route('checkout.process', $course2), [
            'course_id' => $course2->id,
            'existing_kid_id' => $kid->id,
            'track' => 'Beginner',
            'payment_method' => 'mobile_money',
            'provider' => 'airtel',
            'phone_number' => '0971112233',
        ]);

        $response->assertSessionHasNoErrors();

        // Verify payment record
        $payment = Payment::where('course_id', $course2->id)->where('user_id', $parent->id)->first();
        $this->assertNotNull($payment, 'Payment record must be created.');

        /** @var PaymentService $paymentService */
        $paymentService = app(PaymentService::class);
        $paymentService->fulfillPayment($payment);

        // Verify kid is now enrolled in Course 2
        $this->assertTrue(
            Enrollment::where('user_id', $kid->id)->where('course_id', $course2->id)->exists(),
            'Existing kid must be enrolled in Course 2 upon payment fulfillment.'
        );

        // Verify NO duplicate user was created
        $this->assertEquals(
            1,
            User::where('name', 'Kondwani Bwalya')->count(),
            'No duplicate kid user must be created when enrolling existing kid in an additional course.'
        );
    }
}

