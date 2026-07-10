<x-mail::message>
# 📚 New Learning Material

Hello, {{ $recipientName ?? $notifiable->name ?? 'there' }}

New learning material has been published for you to explore.

<x-mail::panel>
**Title:** {{ $material->title }}

**Course:** {{ $material->course?->name ?? 'General' }}

**Type:** {{ ucfirst($material->type ?? 'Document') }}

@if($material->description)
**About:** {{ Str::limit(strip_tags($material->description), 150) }}
@endif
</x-mail::panel>

Take some time to go through the material — it will help you stay on track.

<x-mail::button :url="route('dashboard')" color="primary">
View Material
</x-mail::button>

Happy learning!

Best regards,<br>
{{ $signerName ?? config('app.name') }}
</x-mail::message>
