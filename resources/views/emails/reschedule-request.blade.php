<x-mail::message>
# 🔄 Session Reschedule Request

@php
    $rawName = trim((string) ($recipientName ?? $notifiable->name ?? 'Instructor'));
    $firstName = explode(' ', $rawName)[0] ?? $rawName;
@endphp
Hello {{ $firstName }}!

**{{ $studentName }}** has requested to reschedule an upcoming course session.

<x-mail::panel>
**Course:** {{ $session->course->title ?? $session->course->name ?? 'Course' }}  
**Original Session:** {{ $session->session_date->format('l, M j, Y') }} at {{ $session->start_time }}  

@if ($preferredDate)
**Requested Date:** {{ $preferredDate }}  
@endif

@if ($preferredTime)
**Requested Time:** {{ $preferredTime }}  
@endif

**Reason for Request:**  
{{ $reason }}
</x-mail::panel>

Please review the requested changes and approve or decline from your instructor schedule dashboard.

<x-mail::button :url="url('/teach/schedule')" color="primary">
Review & Manage Schedule &rarr;
</x-mail::button>

Best regards,  
**{{ $signerName ?? config('app.name') }}**
</x-mail::message>
