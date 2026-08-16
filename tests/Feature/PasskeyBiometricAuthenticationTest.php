<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Laravel\Passkeys\Passkey;
use Tests\TestCase;

class PasskeyBiometricAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_implements_passkey_user_interface(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(PasskeyUser::class, $user);
        $this->assertFalse($user->hasPasskeysEnabled());
        $this->assertNotEmpty($user->getPasskeyUserHandle());
        $this->assertEquals($user->name, $user->getPasskeyDisplayName());
        $this->assertEquals($user->email, $user->getPasskeyUsername());
    }

    public function test_guest_can_request_passkey_login_options(): void
    {
        $response = $this->getJson(route('passkey.login-options'));

        $response->assertOk();
        $response->assertJsonStructure([
            'options' => [
                'challenge',
                'rpId',
                'timeout',
                'userVerification',
            ],
        ]);
        $this->assertNotEmpty(session('passkey.verification_options'));
    }

    public function test_authenticated_user_can_request_passkey_registration_options(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($user)->getJson(route('passkey.registration-options'));

        $response->assertOk();
        $response->assertJsonStructure([
            'options' => [
                'challenge',
                'rp' => ['name', 'id'],
                'user' => ['id', 'name', 'displayName'],
                'pubKeyCredParams',
                'timeout',
            ],
        ]);
    }

    public function test_authenticated_user_can_delete_their_passkey(): void
    {
        $user = User::factory()->create();
        $passkey = $user->passkeys()->create([
            'name' => 'Pixel Fingerprint',
            'credential_id' => 'dummy_cred_id_' . uniqid(),
            'credential' => [
                'id' => 'dummy_cred_id',
                'type' => 'public-key',
                'transports' => ['internal'],
            ],
        ]);

        $this->assertTrue($user->fresh()->hasPasskeysEnabled());

        $response = $this->actingAs($user)->deleteJson(route('passkey.destroy', $passkey));

        $response->assertOk();
        $this->assertDatabaseMissing('passkeys', ['id' => $passkey->id]);
        $this->assertFalse($user->fresh()->hasPasskeysEnabled());
    }

    public function test_login_page_renders_fingerprint_signin_button(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('fingerprint-signin-login', false);
        $response->assertSee('Fingerprint / Passkey', false);
    }

    public function test_student_settings_page_renders_fingerprint_biometrics_section(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($user)->get('/learn/settings');

        $response->assertOk();
        $response->assertSee('Fingerprint');
        $response->assertSee('Enable Fingerprint / Passkey');
    }

    public function test_instructor_settings_page_renders_fingerprint_biometrics_section(): void
    {
        $user = User::factory()->create(['role' => 'instructor']);

        $response = $this->actingAs($user)->get('/teach/settings');

        $response->assertOk();
        $response->assertSee('Fingerprint');
        $response->assertSee('Enable Fingerprint / Passkey');
    }
}
