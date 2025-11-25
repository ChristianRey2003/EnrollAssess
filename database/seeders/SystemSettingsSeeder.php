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
            // Email Settings - Resend Default (FREE)
            [
                'key' => 'mail_mailer',
                'value' => 'resend',
                'group' => 'email',
                'type' => 'select',
                'description' => 'Mail driver (resend for Resend, ses for Amazon SES)',
            ],
            [
                'key' => 'mail_from_address',
                'value' => '',
                'group' => 'email',
                'type' => 'text',
                'description' => 'From email address (must be verified in Resend or use Resend test domain)',
            ],
            [
                'key' => 'mail_from_name',
                'value' => 'EnrollAssess System',
                'group' => 'email',
                'type' => 'text',
                'description' => 'Display name for sent emails',
            ],

            // Amazon SES Settings (only required if mail_mailer is 'ses')
            [
                'key' => 'aws_access_key_id',
                'value' => '',
                'group' => 'email',
                'type' => 'password',
                'description' => 'AWS Access Key ID for Amazon SES (required for SES mailer)',
            ],
            [
                'key' => 'aws_secret_access_key',
                'value' => '',
                'group' => 'email',
                'type' => 'password',
                'description' => 'AWS Secret Access Key for Amazon SES (required for SES mailer)',
            ],
            [
                'key' => 'aws_region',
                'value' => 'ap-southeast-1',
                'group' => 'email',
                'type' => 'select',
                'description' => 'AWS Region for SES (ap-southeast-1: Singapore - recommended for Philippines)',
            ],

            // Resend Settings (only required if mail_mailer is 'resend')
            [
                'key' => 'resend_api_key',
                'value' => '',
                'group' => 'email',
                'type' => 'password',
                'description' => 'Resend API Key (get from https://resend.com/api-keys)',
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

            // Report Signature Settings
            [
                'key' => 'report_control_no',
                'value' => 'EVSU- SASO-F-131',
                'group' => 'reports',
                'type' => 'text',
                'description' => 'Control number for reports',
            ],
            [
                'key' => 'report_revision_no',
                'value' => '0',
                'group' => 'reports',
                'type' => 'text',
                'description' => 'Revision number for reports',
            ],
            [
                'key' => 'report_signature_prepared_by_name',
                'value' => 'JOSEPH JAYMEL S. MORPOS',
                'group' => 'reports',
                'type' => 'text',
                'description' => 'Name of person who prepared the report',
            ],
            [
                'key' => 'report_signature_prepared_by_title',
                'value' => 'Head, Computer Studies Department',
                'group' => 'reports',
                'type' => 'text',
                'description' => 'Title of person who prepared the report',
            ],
            [
                'key' => 'report_signature_noted_name',
                'value' => 'DR. JEFFRY V. OCAY',
                'group' => 'reports',
                'type' => 'text',
                'description' => 'Name of person who noted the report',
            ],
            [
                'key' => 'report_signature_noted_title',
                'value' => 'Director, Ormoc Campus',
                'group' => 'reports',
                'type' => 'text',
                'description' => 'Title of person who noted the report',
            ],
            [
                'key' => 'report_signature_recommending_name',
                'value' => 'LYDIA M. MORANTE, D.A.',
                'group' => 'reports',
                'type' => 'text',
                'description' => 'Name of person recommending approval',
            ],
            [
                'key' => 'report_signature_recommending_title',
                'value' => 'Vice President for Academic Affairs',
                'group' => 'reports',
                'type' => 'text',
                'description' => 'Title of person recommending approval',
            ],
            [
                'key' => 'report_signature_approved_name',
                'value' => 'DENNIS C. DE PAZ, Ph.D.',
                'group' => 'reports',
                'type' => 'text',
                'description' => 'Name of person who approved the report',
            ],
            [
                'key' => 'report_signature_approved_title',
                'value' => 'University President',
                'group' => 'reports',
                'type' => 'text',
                'description' => 'Title of person who approved the report',
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
