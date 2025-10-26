<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Settings;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Email Settings - Gmail Defaults (FREE)
            [
                'key' => 'mail_mailer',
                'value' => 'smtp',
                'group' => 'email',
                'type' => 'select',
                'description' => 'Mail driver (smtp recommended for Gmail)',
            ],
            [
                'key' => 'mail_host',
                'value' => 'smtp.gmail.com',
                'group' => 'email',
                'type' => 'text',
                'description' => 'SMTP server hostname (Gmail: smtp.gmail.com)',
            ],
            [
                'key' => 'mail_port',
                'value' => '587',
                'group' => 'email',
                'type' => 'number',
                'description' => 'SMTP server port (Gmail: 587 for TLS, 465 for SSL)',
            ],
            [
                'key' => 'mail_username',
                'value' => 'your-email@gmail.com',
                'group' => 'email',
                'type' => 'text',
                'description' => 'Your Gmail address',
            ],
            [
                'key' => 'mail_password',
                'value' => '',
                'group' => 'email',
                'type' => 'password',
                'description' => 'Gmail App Password (16 characters from Google Account Security)',
            ],
            [
                'key' => 'mail_encryption',
                'value' => 'tls',
                'group' => 'email',
                'type' => 'select',
                'description' => 'Encryption method (tls for port 587, ssl for port 465)',
            ],
            [
                'key' => 'mail_from_address',
                'value' => 'your-email@gmail.com',
                'group' => 'email',
                'type' => 'text',
                'description' => 'From email address (usually same as Gmail username)',
            ],
            [
                'key' => 'mail_from_name',
                'value' => 'EnrollAssess System',
                'group' => 'email',
                'type' => 'text',
                'description' => 'Display name for sent emails',
            ],

            // System Settings
            [
                'key' => 'app_name',
                'value' => 'EnrollAssess',
                'group' => 'system',
                'type' => 'text',
                'description' => 'Application name',
            ],
            [
                'key' => 'app_timezone',
                'value' => 'Asia/Manila',
                'group' => 'system',
                'type' => 'select',
                'description' => 'Application timezone',
            ],
            [
                'key' => 'date_format',
                'value' => 'Y-m-d',
                'group' => 'system',
                'type' => 'text',
                'description' => 'Date format (PHP date format)',
            ],
            [
                'key' => 'time_format',
                'value' => 'H:i:s',
                'group' => 'system',
                'type' => 'text',
                'description' => 'Time format (PHP time format)',
            ],
            [
                'key' => 'items_per_page',
                'value' => '15',
                'group' => 'system',
                'type' => 'number',
                'description' => 'Number of items per page in lists',
            ],

            // Exam Settings
            [
                'key' => 'exam_default_duration',
                'value' => '60',
                'group' => 'exam',
                'type' => 'number',
                'description' => 'Default exam duration in minutes',
            ],
            [
                'key' => 'exam_passing_score',
                'value' => '70',
                'group' => 'exam',
                'type' => 'number',
                'description' => 'Default passing score percentage',
            ],
            [
                'key' => 'exam_max_attempts',
                'value' => '1',
                'group' => 'exam',
                'type' => 'number',
                'description' => 'Maximum number of exam attempts allowed',
            ],
            [
                'key' => 'exam_shuffle_questions',
                'value' => 'true',
                'group' => 'exam',
                'type' => 'boolean',
                'description' => 'Shuffle questions in exam',
            ],
            [
                'key' => 'exam_show_results_immediately',
                'value' => 'false',
                'group' => 'exam',
                'type' => 'boolean',
                'description' => 'Show exam results immediately after completion',
            ],

            // Notification Settings
            [
                'key' => 'notify_exam_assignment',
                'value' => 'true',
                'group' => 'notifications',
                'type' => 'boolean',
                'description' => 'Send email when exam is assigned to applicant',
            ],
            [
                'key' => 'notify_exam_completion',
                'value' => 'true',
                'group' => 'notifications',
                'type' => 'boolean',
                'description' => 'Send email when applicant completes exam',
            ],
            [
                'key' => 'notify_interview_schedule',
                'value' => 'true',
                'group' => 'notifications',
                'type' => 'boolean',
                'description' => 'Send email when interview is scheduled',
            ],
            [
                'key' => 'notify_exam_results',
                'value' => 'true',
                'group' => 'notifications',
                'type' => 'boolean',
                'description' => 'Send email with exam results',
            ],

            // Interview Settings
            [
                'key' => 'interview_default_duration',
                'value' => '30',
                'group' => 'interview',
                'type' => 'number',
                'description' => 'Default interview duration in minutes',
            ],
            [
                'key' => 'interview_min_score',
                'value' => '0',
                'group' => 'interview',
                'type' => 'number',
                'description' => 'Minimum interview score',
            ],
            [
                'key' => 'interview_max_score',
                'value' => '100',
                'group' => 'interview',
                'type' => 'number',
                'description' => 'Maximum interview score',
            ],
        ];

        foreach ($settings as $setting) {
            Settings::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
