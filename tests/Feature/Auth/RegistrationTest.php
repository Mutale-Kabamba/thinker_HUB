<?php

namespace Tests\Feature\Auth;

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $course = Course::query()->create([
            'title' => 'Data Foundations',
            'code' => 'DF-101',
            'description' => 'Introductory course',
            'is_active' => true,
        ]);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'course_id' => $course->id,
            'track' => 'Beginner',
            'accept_terms' => true,
            'accept_requirements' => true,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }
}
