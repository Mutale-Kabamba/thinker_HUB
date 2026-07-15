think.er HUB - Student Registration Notification

A new student account has been registered.

Student: {{ $student->name }}
Email: {{ $student->email }}
Course: {{ $course->code }} - {{ $course->title }}
Track / Level: {{ $student->track ?: 'Beginner' }}
Registered At: {{ optional($student->created_at)->toDayDateTimeString() ?: now()->toDayDateTimeString() }}

@if ($requiresPaymentApproval)
Status: Pending approval (payment verification required)
Action: Follow up with the student and complete payment confirmation before activation.
@else
Status: Active
Action: No payment follow-up required for this registration.
@endif

@if (!empty($instructorContacts))
Assigned Instructors:
@foreach ($instructorContacts as $instructorContact)
- {{ $instructorContact }}
@endforeach
@endif

This is an automated transactional notification from think.er HUB.
