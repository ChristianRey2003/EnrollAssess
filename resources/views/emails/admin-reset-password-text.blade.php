Faculty Portal - Password Reset Request

Hello {{ $userName ?? 'User' }}!

You are receiving this email because we received a password reset request for your Faculty Portal account.

Click the link below to reset your password. If you did not request a password reset, you can safely ignore this email.

RESET PASSWORD LINK:
{{ $resetUrl }}

IMPORTANT: This password reset link will expire in {{ $expireMinutes ?? 60 }} minutes.

SECURITY REMINDER
For your security, please:
- Never share your password reset link with anyone
- Choose a strong, unique password
- If you didn't request this, please contact the administrator immediately

DIDN'T REQUEST THIS?
If you didn't request a password reset, no further action is required. Your account remains secure. If you're concerned about your account security, please contact the system administrator.

---
{{ config('app.name', 'EnrollAssess') }}
Computer Studies Department
Eastern Visayas State University

This is an automated email. Please do not reply to this message.

