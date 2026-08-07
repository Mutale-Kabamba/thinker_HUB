think.er HUB - {{ $course->title ?? 'Course Announcement' }}

{{ $subjectLine }}
From: {{ $sender->name ? 'Instructor ' . $sender->name : 'Course Instructor' }}

MESSAGE:
{{ $messageBody }}

---
You are receiving this announcement because you are enrolled in {{ $course->title ?? 'this course' }} on think.er HUB.
Visit {{ route('dashboard') }} to access your coursework.

think.er HUB • Livingstone, Zambia
https://oristudiozm.com
