<x-mail::message>
# 📬 New Student Submission Received

Hello {{ $recipientName ?? $notifiable->name ?? 'Instructor' }},

A learner has submitted coursework that is awaiting your review and evaluation.

<x-mail::panel>
**Learner:** {{ $studentName }}  
**Submission Item:** {{ $itemTitle }}  
**Item Type:** {{ ucfirst($submissionType) }}  
**Submitted On:** {{ now()->format('l, M j, Y \a\t h:i A') }}  
</x-mail::panel>

Please review the submitted materials and provide constructive feedback for the student from your dashboard.

<x-mail::button :url="route('dashboard')" color="primary">
Review & Grade Submission &rarr;
</x-mail::button>

Best regards,  
**{{ $signerName ?? config('app.name') }}**
</x-mail::message>
