<x-mail::message>
# 🎉 Your Submission Was Reviewed & Graded

Hello {{ $recipientName ?? $notifiable->name ?? 'Learner' }},

Great news! Your instructor has reviewed and assessed your {{ $submissionType }}.

<x-mail::panel>
**{{ ucfirst($submissionType) }}:** {{ $itemTitle }}  
**Result / Score:** **{{ $scoreOrGrade !== null ? $scoreOrGrade : 'Assessed' }}**  

@if($feedback)
**Instructor Feedback:**  
> {{ $feedback }}
@else
*Your submission was reviewed successfully with no additional notes.*
@endif
</x-mail::panel>

<x-mail::button :url="route('dashboard')" color="success">
View Detailed Results on Dashboard &rarr;
</x-mail::button>

Keep up the outstanding effort and momentum in your studies!

Best regards,  
**{{ $signerName ?? config('app.name') }}**
</x-mail::message>
