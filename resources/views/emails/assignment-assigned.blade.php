<x-mail::message>
# 📝 New Assignment

Hello **{{ $notifiable->name }}**,

A new assignment has been posted and is waiting for you.

<x-mail::panel>
**Assignment:** {{ $assignment->name }}

**Course:** {{ $assignment->course?->name ?? 'General' }}

**Due Date:** {{ $assignment->due_date?->format('M d, Y \a\t h:i A') ?? 'No deadline' }}

@if($assignment->description)
**Description:** {{ Str::limit(strip_tags($assignment->description), 150) }}
@endif
</x-mail::panel>

Don't wait until the last minute — start working on it now!

<x-mail::button :url="route('dashboard')" color="primary">
View Assignment
</x-mail::button>

If you have any questions, reach out to your instructor.

Best regards,<br>
**Thinker HUB**
</x-mail::message>
