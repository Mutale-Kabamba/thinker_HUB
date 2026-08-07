<x-mail::message>
# 🗓️ Course Session Rescheduled

Hello {{ $recipientName ?? $notifiable->name ?? 'Learner' }},

Please take note that a scheduled live session for **{{ $courseName }}** has been moved to a new time.

<x-mail::panel>
**Course:** {{ $courseName }}  
**Original Schedule:** {{ $session->session_date->format('l, M j, Y') }} at {{ $session->start_time }}  
**Updated Schedule:** **{{ $session->rescheduled_date->format('l, M j, Y') }} at {{ $session->rescheduled_start_time }}**  
</x-mail::panel>

Please update your personal calendar accordingly. You can view your complete updated schedule on the student portal.

<x-mail::button :url="url('/learn/schedule')" color="primary">
View Updated Schedule &rarr;
</x-mail::button>

Best regards,  
**{{ $signerName ?? config('app.name') }}**
</x-mail::message>
