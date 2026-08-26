<x-mail::message>
# Live Session Rescheduled

@php
    $rawName = trim((string) ($recipientName ?? $notifiable->name ?? 'Learner'));
    $firstName = explode(' ', $rawName)[0] ?? $rawName;
    $dateObj = $session->getEffectiveDate();
    $formattedDate = $dateObj->format('l, M j, Y');
    $startTimeStr = $session->getEffectiveStartTime();
    $formattedTime = filled($startTimeStr) ? \Illuminate\Support\Carbon::parse($startTimeStr)->format('g:i A') : '—';
@endphp
Hello {{ $firstName }}!

Please take note that a scheduled live session for **{{ $courseName }}** has been moved to a new time.

<x-mail::panel>
**Course:** {{ $courseName }}  
**Updated Schedule:** **{{ $formattedDate }} at {{ $formattedTime }}**  
</x-mail::panel>

Please update your personal calendar accordingly. You can view your complete updated schedule on the student portal.

<x-mail::button :url="url('/learn/schedule')" color="primary">
View Updated Schedule &rarr;
</x-mail::button>

Best regards,  
**{{ $signerName ?? config('app.name') }}**
</x-mail::message>
