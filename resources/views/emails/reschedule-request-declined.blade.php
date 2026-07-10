<x-mail::message>
# Reschedule Request Declined

Hello, {{ $recipientName ?? $notifiable->name ?? 'there' }}

Your request to reschedule **{{ $courseName }}** was declined.

@if (! empty($reason))
**Reason:** {{ $reason }}
@endif

Your session remains on the existing schedule.

Regards,<br>
{{ $signerName ?? config('app.name') }}
</x-mail::message>
