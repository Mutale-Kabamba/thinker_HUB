think.er HUB - Official Payment Receipt

@php
    $rawStudentName = trim((string) ($student->name ?? ''));
    $studentFirstName = $rawStudentName !== '' ? (explode(' ', $rawStudentName)[0] ?? $rawStudentName) : 'Learner';
@endphp
Hello {{ $studentFirstName }}!

Your course enrollment payment has been received and verified.

TRANSACTION DETAILS:
- Reference #: {{ $payment->reference }}
- Course: {{ $course->code }} - {{ $course->title }}
- Amount: {{ $payment->formattedAmount() }}
- Method: {{ str_replace('_', ' ', $payment->payment_method) }} ({{ strtoupper($payment->provider ?? 'Gateway') }})
- Status: PAID & VERIFIED
- Date: {{ $payment->paid_at?->format('F j, Y, g:i a') ?? now()->format('F j, Y, g:i a') }}

HOW TO ACCESS YOUR COURSE:
Visit {{ route('dashboard') }} and sign in to access all coursework, sessions, and materials.

If you have any questions, reach us at {{ config('mail.contact_to', 'thinkerhub@oristudiozm.com') }}.

think.er HUB • 10A Off Natwange Street, Airport, Livingstone, Zambia
https://oristudiozm.com
