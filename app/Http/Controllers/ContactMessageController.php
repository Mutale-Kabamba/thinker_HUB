<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageMail;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactMessageController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:120'],
            'custom_subject' => ['nullable', 'string', 'max:120', 'required_if:subject,Other'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        $subject = $validated['subject'] === 'Other'
            ? trim((string) ($validated['custom_subject'] ?? ''))
            : $validated['subject'];

        if ($subject === '') {
            return back()
                ->withInput()
                ->withErrors(['custom_subject' => 'Please type a custom subject.']);
        }

        $recipientConfig = (string) config('mail.contact_to', config('mail.from.address'));
        $recipients = collect(explode(',', $recipientConfig))
            ->map(static fn (string $email): string => trim($email))
            ->filter(static fn (string $email): bool => $email !== '')
            ->values()
            ->all();

        $contactMessage = ContactMessage::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $subject,
            'message' => $validated['message'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($recipients === []) {
            Log::warning('Contact message saved but no recipient configured.', [
                'contact_message_id' => $contactMessage->id,
                'sender_email' => $validated['email'],
            ]);

            return back()->with('contact_success', 'Message received. Our team will review it shortly.');
        }

        try {
            Mail::to($recipients)->send(new ContactMessageMail(
                name: $validated['name'],
                email: $validated['email'],
                contactSubject: $subject,
                bodyText: $validated['message'],
            ));

            $contactMessage->forceFill([
                'mailed_at' => now(),
                'mail_error' => null,
            ])->save();

            Log::info('Contact form message sent.', [
                'contact_message_id' => $contactMessage->id,
                'sender_email' => $validated['email'],
                'recipients' => $recipients,
            ]);

            return back()->with('contact_success', 'Message sent successfully. We will get back to you soon.');
        } catch (\Throwable $exception) {
            $contactMessage->forceFill([
                'mail_error' => $exception->getMessage(),
            ])->save();

            Log::error('Failed to send contact form message.', [
                'contact_message_id' => $contactMessage->id,
                'email' => $validated['email'],
                'error' => $exception->getMessage(),
            ]);

            return back()->with('contact_warning', 'Message received. Email delivery is delayed, but our team can still follow up.');
        }
    }
}
