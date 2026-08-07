<x-mail::message>
# 📚 New Learning Material Available

@php
    $rawName = trim((string) ($recipientName ?? $notifiable->name ?? 'Learner'));
    $firstName = explode(' ', $rawName)[0] ?? $rawName;
@endphp
Hello {{ $firstName }}!

New learning materials have been uploaded to support your coursework. Take some time to review and download the resources.

<x-mail::panel>
**Course:** {{ $material->course?->title ?? $material->course?->name ?? 'Course Offering' }}  
**Title:** {{ $material->title }}  
**Resource Type:** {{ ucfirst($material->type ?? 'Document / Slide') }}  

@if($material->description)
**Overview:**  
{{ Str::limit(strip_tags($material->description), 180) }}
@endif
</x-mail::panel>

<x-mail::button :url="route('dashboard')" color="primary">
Access Learning Material &rarr;
</x-mail::button>

Happy learning and upskilling!

Best regards,  
**{{ $signerName ?? config('app.name') }}**
</x-mail::message>
