<x-mail::message>
# 📬 New Student Submission

Hello **{{ $notifiable->name }}**,

A student has submitted work that needs your attention.

<x-mail::panel>
**Student:** {{ $studentName }}

**Type:** {{ ucfirst($submissionType) }}

**Item:** {{ $itemTitle }}

**Submitted:** {{ now()->format('M d, Y \a\t h:i A') }}
</x-mail::panel>

Please review the submission and provide feedback when you can.

<x-mail::button :url="route('dashboard')" color="primary">
Review Submission
</x-mail::button>

Best regards,<br>
**Thinker HUB**
</x-mail::message>
