<x-mail::message>
# ✅ Your Submission Was Reviewed

Hello **{{ $notifiable->name }}**,

Great news — your {{ $submissionType }} has been reviewed by your instructor.

<x-mail::panel>
**{{ ucfirst($submissionType) }}:** {{ $itemTitle }}

**Score:** {{ $scoreOrGrade !== null ? $scoreOrGrade : 'N/A' }}

@if($feedback)
**Instructor Feedback:**
> {{ $feedback }}
@else
*No additional feedback was provided.*
@endif
</x-mail::panel>

<x-mail::button :url="route('dashboard')" color="success">
View Results
</x-mail::button>

Keep up the great work!

Best regards,<br>
**Thinker HUB**
</x-mail::message>
