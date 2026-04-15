@component('mail::message')
# Session Rescheduled

Your session for **{{ $courseName }}** has been rescheduled.

@component('mail::panel')
**Original Date:** {{ $session->session_date->format('l, M j, Y') }} at {{ $session->start_time }}
**New Date:** {{ $session->rescheduled_date->format('l, M j, Y') }} at {{ $session->rescheduled_start_time }}
@endcomponent

@component('mail::button', ['url' => url('/learn/schedule')])
View Schedule
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
