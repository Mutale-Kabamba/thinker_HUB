<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageMail;
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
            'message' => ['required', 'string', 'max:3000'],
        ]);

        $recipientConfig = (string) config('mail.contact_to', config('mail.from.address'));
        $recipients = collect(explode(',', $recipientConfig))
            ->map(static fn (string $email): string => trim($email))
            ->filter(static fn (string $email): bool => $email !== '')
            ->values()
            ->all();

        if ($recipients === []) {
            return back()
                ->withInput()
                ->withErrors(['contact' => 'Contact email is not configured yet. Please try again later.']);
        }

        try {
            Mail::to($recipients)->send(new ContactMessageMail(
                name: $validated['name'],
                email: $validated['email'],
                bodyText: $validated['message'],
            ));

            Log::info('Contact form message sent.', [
                'sender_email' => $validated['email'],
                'recipients' => $recipients,
            ]);

            return back()->with('contact_success', 'Message sent successfully. We will get back to you soon.');
        } catch (\Throwable $exception) {
            Log::error('Failed to send contact form message.', [
                'email' => $validated['email'],
                'error' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['contact' => 'We could not send your message right now. Please try again shortly.']);
        }
    }
}
