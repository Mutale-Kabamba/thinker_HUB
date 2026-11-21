<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorOverviewDirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_load_instructor_overview(): void
    {
        $user = User::where('role', 'instructor')->orWhere('role', 'admin')->first();
        if (! $user) {
            $user = User::factory()->create([
                'name' => 'Instructor Test',
                'email' => 'instructor_test@example.com',
                'role' => 'instructor',
                'is_active' => true,
            ]);
        }

        $this->actingAs($user);

        $response = $this->get('/teach/instructor-overview');

        $this->assertTrue(in_array($response->getStatusCode(), [200, 302]));
    }
}
