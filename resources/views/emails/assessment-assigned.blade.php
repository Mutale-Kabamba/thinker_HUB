<x-mail::message>
# New Assessment Posted

@php
    $rawName = trim((string) ($recipientName ?? $notifiable->name ?? 'Learner'));
    $firstName = explode(' ', $rawName)[0] ?? $rawName;
@endphp
Hello {{ $firstName }}!

A new assessment evaluation has been posted for your course. Please review the instructions and complete your submission on time.

<x-mail::panel>
**Course:** {{ $assessment->course?->title ?? $assessment->course?->name ?? 'Course Offering' }}  
**Assessment:** {{ $assessment->name }}  
**Due Date:** {{ $assessment->due_date?->format('l, M j, Y \a\t h:i A') ?? 'No deadline specified' }}  

@if($assessment->description)
**Summary:**  
{{ Str::limit(strip_tags($assessment->description), 180) }}
@endif
</x-mail::panel>

<x-mail::button :url="route('dashboard')" color="primary">
View Assessment on Dashboard &rarr;
</x-mail::button>

If you have questions or need clarification on this assessment, please contact your course instructor.

Best regards,  
**{{ $signerName ?? config('app.name') }}**
</x-mail::message>
