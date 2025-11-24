# EnrollAssess

A comprehensive examination and enrollment management system built with Laravel.

## Features

- **Examination Management**: Create, manage, and conduct online examinations
- **Applicant Management**: Track and manage applicant information
- **Interview Assignment**: Assign and manage interview schedules
- **Report Generation**: Generate reports in XLSX, PDF, and DOCX formats
- **Email Notifications**: Send exam invitations and notifications via AWS SES or SMTP
- **Real-time Broadcasting**: Live updates using Pusher
- **Security**: Comprehensive security measures for exam integrity

## Requirements

- PHP 8.2+
- MySQL 5.7+ or MariaDB 10.3+
- Redis (for caching and queues)
- Node.js 20.x
- Composer
- LibreOffice (for PDF exports)

## Installation

### Local Development

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   npm install
   ```
3. Copy environment file:
   ```bash
   cp .env.example .env
   ```
4. Generate application key:
   ```bash
   php artisan key:generate
   ```
5. Configure database in `.env`
6. Run migrations:
   ```bash
   php artisan migrate
   ```
7. Build frontend assets:
   ```bash
   npm run build
   ```

## Deployment

See [DEPLOYMENT_STEPS.md](DEPLOYMENT_STEPS.md) for detailed deployment instructions.

## Documentation

- **Deployment**: [DEPLOYMENT_STEPS.md](DEPLOYMENT_STEPS.md) - Step-by-step deployment guide
- **Environment Setup**: [ENV_SETUP_GUIDE.md](ENV_SETUP_GUIDE.md) - Environment configuration guide
- **Security**: [SECURITY_AUDIT_REPORT.md](SECURITY_AUDIT_REPORT.md) - Security audit report
- **System Architecture**: [SYSTEM_ARCHITECTURE.md](SYSTEM_ARCHITECTURE.md) - System architecture documentation
- **Email Configuration**: [EMAIL_CUSTOMIZATION_GUIDE.md](EMAIL_CUSTOMIZATION_GUIDE.md) - Email setup guide
- **AWS SES**: [AMAZON_SES_IMPLEMENTATION.md](AMAZON_SES_IMPLEMENTATION.md) - Amazon SES setup guide
- **DNS Setup**: [NAMECHEAP_DNS_SETUP.md](NAMECHEAP_DNS_SETUP.md) - Namecheap DNS configuration
- **SES Sandbox**: [SES_SANDBOX_EXIT_GUIDE.md](SES_SANDBOX_EXIT_GUIDE.md) - Exiting SES sandbox mode
- **Redis Setup**: [REDIS_PRODUCTION_SETUP.md](REDIS_PRODUCTION_SETUP.md) - Redis production configuration
- **PDF Export**: [LIBREOFFICE_PDF_SETUP.md](LIBREOFFICE_PDF_SETUP.md) - LibreOffice PDF setup

## Key Features Documentation

- **Exam System**: [EXAMINATION_SYSTEM_ANALYSIS.md](EXAMINATION_SYSTEM_ANALYSIS.md)
- **Exam Security**: [EXAM_SECURITY_IMPLEMENTATION.md](EXAM_SECURITY_IMPLEMENTATION.md)
- **Exam Recovery**: [EXAM_RECOVERY_IMPLEMENTATION.md](EXAM_RECOVERY_IMPLEMENTATION.md)
- **Single Active Exam**: [SINGLE_ACTIVE_EXAM_IMPLEMENTATION.md](SINGLE_ACTIVE_EXAM_IMPLEMENTATION.md)
- **Switching Exams**: [HOW_TO_SWITCH_EXAMS.md](HOW_TO_SWITCH_EXAMS.md)
- **System Flow**: [ENROLLASSESS_SYSTEM_FLOW.md](ENROLLASSESS_SYSTEM_FLOW.md)

## Implementation Guides

- **PDF Export**: [PDF_EXPORT_IMPLEMENTATION.md](PDF_EXPORT_IMPLEMENTATION.md)
- **Reports**: [STUDENT_INFORMATION_REPORTS_IMPLEMENTATION.md](STUDENT_INFORMATION_REPORTS_IMPLEMENTATION.md)
- **Qualifiers List**: [QUALIFIERS_LIST_DOCX_IMPLEMENTATION.md](QUALIFIERS_LIST_DOCX_IMPLEMENTATION.md)
- **Interview Assignment**: [INTERVIEW_ASSIGNMENT_ENHANCEMENT_SUMMARY.md](INTERVIEW_ASSIGNMENT_ENHANCEMENT_SUMMARY.md)

## License

This project is proprietary software. All rights reserved.
