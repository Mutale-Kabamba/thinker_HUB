@component('mail::message')
# Reschedule Request

**{{ $studentName }}** has requested to reschedule a session.

@component('mail::panel')
**Course:** {{ $session->course->title ?? 'Course' }}
**Original Date:** {{ $session->session_date->format('l, M j, Y') }} at {{ $session->start_time }}
**Reason:** {{ $reason }}
@if ($preferredDate)
**Preferred Date:** {{ $preferredDate }}
@endif
@if ($preferredTime)
**Preferred Time:** {{ $preferredTime }}
@endif
@endcomponent

Please review and take action from your dashboard.

@component('mail::button', ['url' => url('/teach/schedule')])
View Schedule
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
