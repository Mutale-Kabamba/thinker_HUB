<x-mail::message>
# 📝 New Assignment Posted

@php
    $rawName = trim((string) ($recipientName ?? $notifiable->name ?? 'Learner'));
    $firstName = explode(' ', $rawName)[0] ?? $rawName;
@endphp
Hello {{ $firstName }}!

A new assignment has been posted for your course. Please review the details and start your submission early.

<x-mail::panel>
**Course:** {{ $assignment->course?->title ?? $assignment->course?->name ?? 'Course Offering' }}  
**Assignment:** {{ $assignment->name }}  
**Due Date:** {{ $assignment->due_date?->format('l, M j, Y \a\t h:i A') ?? 'No deadline specified' }}  

@if($assignment->description)
**Summary:**  
{{ Str::limit(strip_tags($assignment->description), 180) }}
@endif
</x-mail::panel>

<x-mail::button :url="route('dashboard')" color="primary">
View Assignment on Dashboard &rarr;
</x-mail::button>

If you have questions or need guidance on this assignment, reach out to your course instructor.

Best regards,  
**{{ $signerName ?? config('app.name') }}**
</x-mail::message>
