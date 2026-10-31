<?php

namespace Tests\Feature;

use App\Filament\Pages\AccountingDashboard;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AccountingDashboardFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_and_edit_legacy_student_on_accounting_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        // Legacy course with string/text fees (such as 'ZMW 9.00')
        $course = Course::create([
            'title' => 'Microsoft Office Suite Mastery',
            'code' => 'MSO-101',
            'fees' => 'ZMW 9.00',
            'is_active' => true,
        ]);

        // Legacy enrollment with null paid_at, showing direct_checkout
        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'registration_type' => 'direct_checkout',
            'fee_amount' => 0.0,
            'paid_at' => null,
            'payment_status' => 'paid',
            'is_free' => false,
        ]);

        $this->actingAs($admin);

        // Mount Livewire component and click editTransaction
        Livewire::test(AccountingDashboard::class)
            ->call('editTransaction', $enrollment->id)
            ->assertSet('editingEnrollmentId', $enrollment->id)
            ->assertSet('editFeeAmount', 9.0)
            ->set('editRegistrationType', 'admin_manual')
            ->set('editPaymentStatus', 'paid')
            ->set('editFeeAmount', 9.00)
            ->set('editPaymentMethod', 'cash')
            ->set('editTransactionReference', 'REC-TEST-001')
            ->call('saveTransaction')
            ->assertHasNoErrors()
            ->assertSet('editingEnrollmentId', null);

        // Verify database reflects reconciled data
        $enrollment->refresh();
        $this->assertEquals('admin_manual', $enrollment->registration_type);
        $this->assertEquals(9.00, (float) $enrollment->fee_amount);
        $this->assertEquals('REC-TEST-001', $enrollment->transaction_reference);
        $this->assertNotNull($enrollment->paid_at);
        $this->assertFalse($enrollment->isFree());
    }

    public function test_editing_handles_non_numeric_and_free_courses_safely(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $freeCourse = Course::create([
            'title' => 'Free Community Intro',
            'code' => 'FREE-101',
            'fees' => 'Free',
            'is_active' => true,
        ]);

        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $freeCourse->id,
            'registration_type' => 'admin_manual',
            'fee_amount' => 0.0,
            'paid_at' => null,
            'payment_status' => 'free',
            'is_free' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(AccountingDashboard::class)
            ->call('editTransaction', $enrollment->id)
            ->assertSet('editingEnrollmentId', $enrollment->id)
            ->assertSet('editPaymentStatus', 'free')
            ->assertSet('editFeeAmount', 0.0)
            ->call('setFeeToCourseTuition')
            ->call('saveTransaction')
            ->assertHasNoErrors();
    }

    public function test_csv_export_streams_without_exceptions(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $course = Course::create([
            'title' => 'Python Bootcamp',
            'code' => 'PY-101',
            'fees' => '1500',
            'is_active' => true,
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'registration_type' => 'admin_manual',
            'fee_amount' => 1500.0,
            'paid_at' => null,
            'payment_status' => 'paid',
            'is_free' => false,
        ]);

        $this->actingAs($admin);

        $response = Livewire::test(AccountingDashboard::class)
            ->call('exportCsv');

        $this->assertTrue($response->instance()->exportCsv() instanceof \Symfony\Component\HttpFoundation\StreamedResponse);
    }
}
