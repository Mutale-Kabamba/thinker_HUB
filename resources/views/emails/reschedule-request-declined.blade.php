# Reschedule Request Declined

Your request to reschedule **{{ $courseName }}** was declined.

@if (! empty($reason))
**Reason:** {{ $reason }}
@endif

Your session remains on the existing schedule.

Thanks,
{{ config('app.name') }}
