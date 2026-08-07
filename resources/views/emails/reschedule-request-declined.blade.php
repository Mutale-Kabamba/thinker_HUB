<x-mail::message>
# 📅 Reschedule Request Update

Hello {{ $recipientName ?? $notifiable->name ?? 'Learner' }},

Your request to reschedule **{{ $courseName }}** could not be accommodated at this time.

@if (! empty($reason))
<x-mail::panel>
**Instructor Note:**  
{{ $reason }}
</x-mail::panel>
@endif

Your session remains active on the current original timetable. You can view your complete calendar on the student schedule portal.

<x-mail::button :url="url('/learn/schedule')" color="primary">
View Current Schedule &rarr;
</x-mail::button>

Best regards,  
**{{ $signerName ?? config('app.name') }}**
</x-mail::message>
