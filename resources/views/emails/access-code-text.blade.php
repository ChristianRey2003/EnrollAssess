Eastern Visayas State University
Computer Studies Department

Dear {{ $applicant->full_name }},

Congratulations! You have been pre-qualified for the Computer Studies Department entrance evaluation process.

Your application has been reviewed and you are now eligible to take the computerized entrance examination. Please use the access code below to begin your exam.

YOUR EXAM ACCESS CODE
{{ $accessCode->code }}
@if($expiresAt)
Expires: {{ $expiresAt->format('F j, Y \a\t g:i A') }}
@endif

EXAM INFORMATION
Application No: {{ $applicant->application_no }}
@if($applicant->accessCode && $applicant->accessCode->exam)
Exam: {{ $applicant->accessCode->exam->title }}
Duration: {{ $applicant->accessCode->exam->formatted_duration }}
@endif
Format: Online Computer-Based Test

EXAM URL
https://enrollassess-evsu.com/applicant/login

IMPORTANT INSTRUCTIONS
- Use a reliable computer with stable internet connection
- Find a quiet environment free from distractions
- Have a valid ID ready for verification purposes
- Do not refresh or close the browser during the exam
- Complete the exam in one sitting - you cannot pause and resume
- Answer all questions before submitting your exam

IMPORTANT: This access code is unique to you and can only be used once. Do not share it with anyone. If you encounter any technical issues during the exam, immediately contact the Computer Studies Department.

We wish you the best of luck with your examination. This is an important step toward joining our Computer Studies program.

Best regards,
Computer Studies Department
Eastern Visayas State University

---
Computer Studies Department
Eastern Visayas State University
Tacloban City, Philippines

Email: cs.department@evsu.edu.ph
Phone: (053) 123-4567
Website: www.evsu.edu.ph

This is an automated message from EnrollAssess. Please do not reply to this email.
If you have questions, contact the Computer Studies Department directly.

