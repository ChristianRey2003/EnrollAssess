BSIT Entrance Exam Results
Computer Studies Department

Dear {{ $applicant->full_name }},

Your BSIT Entrance Examination has been evaluated. Below are your exam results:

EXAM RESULT: {{ $passed ? 'PASSED' : 'NEEDS REVIEW' }}

Score Details:
- Score Achieved: {{ $score }}
- Total Questions: {{ $totalQuestions }}
- Percentage: {{ $percentage }}%
- Passing Score: {{ $passingScore }}%

Application Information:
- Application No: {{ $applicant->application_no }}
- Exam Date: {{ $result->created_at->format('F d, Y') }}
- Time Taken: {{ $result->time_taken ?? 'N/A' }}
- Result Status: {{ $passed ? 'PASSED' : 'NEEDS REVIEW' }}

@if($passed)
NEXT STEPS

Congratulations on passing the entrance examination!

- You will be contacted for the next stage of the enrollment process
- Please prepare the required documents for enrollment
- Check your email regularly for further instructions
- You may be scheduled for an interview session

We look forward to welcoming you to the BSIT program!
@else
WHAT HAPPENS NEXT

Your application requires further review by the admissions committee.

- The Computer Studies Department will review your application
- You will be notified of the final decision within 5-7 business days
- Additional assessment may be required
- Please check your email regularly for updates

Thank you for your interest in the BSIT program. We appreciate the time and effort you invested in your application.
@endif

If you have any questions or concerns about your results, please contact the Computer Studies Department during office hours.

---
Computer Studies Department

This is an automated message. Please do not reply to this email.

© {{ date('Y') }} EnrollAssess System. All rights reserved.

