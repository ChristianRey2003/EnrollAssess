@if(isset($isReschedule) && $isReschedule)
Interview Rescheduled
@else
Interview Scheduled
@endif

Dear {{ $applicant->full_name }},

@if(isset($isReschedule) && $isReschedule)
Your interview has been rescheduled. Please see the updated details below:
@else
Your interview has been scheduled! Please see the details below:
@endif

INTERVIEW DETAILS
Application No: {{ $applicant->application_no }}
Interview Date: {{ $scheduleDate }}
Interview Time: {{ $scheduleTime }}
Interviewer: {{ $instructor->name ?? 'TBA' }}

@if($interview->notes)
ADDITIONAL NOTES:
{{ $interview->notes }}
@endif

IMPORTANT REMINDERS:
- Please arrive 10 minutes before your scheduled time
- Bring a valid ID for verification
- Prepare your portfolio or relevant documents
- Dress appropriately for the interview

If you need to reschedule or have any questions, please contact us as soon as possible.

---
This is an automated email from {{ config('app.name') }}.
Please do not reply to this email.

