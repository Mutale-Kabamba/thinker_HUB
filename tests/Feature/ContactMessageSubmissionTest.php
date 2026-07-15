<?php

namespace Tests\Feature;

use App\Mail\ContactMessageMail;
use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactMessageSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_message_is_saved_and_email_is_sent_for_selected_subject(): void
    {
        Mail::fake();

        $response = $this->post(route('landing.contact.store'), [
            'name' => 'Jane Student',
            'email' => 'jane@example.com',
            'subject' => 'Enrollment Support',
            'message' => 'I would like details about upcoming classes.',
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHas('contact_success');

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Jane Student',
            'email' => 'jane@example.com',
            'subject' => 'Enrollment Support',
            'message' => 'I would like details about upcoming classes.',
        ]);

        Mail::assertSent(ContactMessageMail::class, function (ContactMessageMail $mail): bool {
            return $mail->name === 'Jane Student'
                && $mail->email === 'jane@example.com'
                && $mail->contactSubject === 'Enrollment Support'
                && $mail->bodyText === 'I would like details about upcoming classes.';
        });

        $saved = ContactMessage::query()->first();
        $this->assertNotNull($saved?->mailed_at);
    }

    public function test_contact_message_accepts_custom_subject_when_other_is_selected(): void
    {
        Mail::fake();

        $response = $this->post(route('landing.contact.store'), [
            'name' => 'John Partner',
            'email' => 'john@example.com',
            'subject' => 'Other',
            'custom_subject' => 'Scholarship Collaboration',
            'message' => 'I want to discuss sponsorship options.',
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHas('contact_success');

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'John Partner',
            'email' => 'john@example.com',
            'subject' => 'Scholarship Collaboration',
            'message' => 'I want to discuss sponsorship options.',
        ]);

        Mail::assertSent(ContactMessageMail::class, function (ContactMessageMail $mail): bool {
            return $mail->name === 'John Partner'
                && $mail->email === 'john@example.com'
                && $mail->contactSubject === 'Scholarship Collaboration'
                && $mail->bodyText === 'I want to discuss sponsorship options.';
        });
    }
}
