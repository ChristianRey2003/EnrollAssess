<?php

namespace App\Constants;

/**
 * System Capabilities
 * 
 * Defines granular permissions that can be delegated to users.
 * Each capability represents a specific action or set of actions.
 */
class Capabilities
{
    // Question Bank Capabilities
    const QUESTIONS_VIEW = 'questions.view';
    const QUESTIONS_CREATE = 'questions.create';
    const QUESTIONS_EDIT = 'questions.edit';
    const QUESTIONS_DELETE = 'questions.delete';
    const QUESTIONS_MANAGE_EXAM_SETTINGS = 'questions.manage_exam_settings';

    // Applicant Management Capabilities
    const APPLICANTS_VIEW = 'applicants.view';
    const APPLICANTS_CREATE = 'applicants.create';
    const APPLICANTS_EDIT = 'applicants.edit';
    const APPLICANTS_DELETE = 'applicants.delete';
    const APPLICANTS_ASSIGN = 'applicants.assign';
    const APPLICANTS_SCHEDULE_EXAM = 'applicants.schedule_exam';
    const APPLICANTS_BULK_OPERATIONS = 'applicants.bulk_operations';

    // Reports Capabilities
    const REPORTS_VIEW = 'reports.view';
    const REPORTS_GENERATE = 'reports.generate';
    const REPORTS_MANAGE_ARCHIVE = 'reports.manage_archive';

    /**
     * Get all capabilities grouped by category
     */
    public static function getAllGrouped(): array
    {
        return [
            'question_bank' => [
                'label' => 'Question Bank',
                'capabilities' => [
                    self::QUESTIONS_VIEW => [
                        'label' => 'View Questions',
                        'description' => 'View question bank and exam sets',
                    ],
                    self::QUESTIONS_CREATE => [
                        'label' => 'Add Questions',
                        'description' => 'Create new questions',
                    ],
                    self::QUESTIONS_EDIT => [
                        'label' => 'Edit Questions',
                        'description' => 'Modify existing questions',
                    ],
                    self::QUESTIONS_DELETE => [
                        'label' => 'Delete Questions',
                        'description' => 'Remove questions from the bank',
                    ],
                    self::QUESTIONS_MANAGE_EXAM_SETTINGS => [
                        'label' => 'Manage Exam Settings',
                        'description' => 'Publish exams, toggle status, change exam settings',
                    ],
                ],
            ],
            'applicants' => [
                'label' => 'Applicant Management',
                'capabilities' => [
                    self::APPLICANTS_VIEW => [
                        'label' => 'View Applicants',
                        'description' => 'View applicant lists and details',
                    ],
                    self::APPLICANTS_CREATE => [
                        'label' => 'Add Applicants',
                        'description' => 'Create new applicants',
                    ],
                    self::APPLICANTS_EDIT => [
                        'label' => 'Edit Applicants',
                        'description' => 'Modify applicant information',
                    ],
                    self::APPLICANTS_DELETE => [
                        'label' => 'Delete Applicants',
                        'description' => 'Remove applicants from the system',
                    ],
                    self::APPLICANTS_ASSIGN => [
                        'label' => 'Assign Applicants',
                        'description' => 'Assign applicants to instructors',
                    ],
                    self::APPLICANTS_SCHEDULE_EXAM => [
                        'label' => 'Schedule Exam / Send Notification',
                        'description' => 'Schedule exams and send exam notifications to applicants',
                    ],
                    self::APPLICANTS_BULK_OPERATIONS => [
                        'label' => 'Bulk Operations',
                        'description' => 'Import, export, and bulk actions',
                    ],
                ],
            ],
            'reports' => [
                'label' => 'Reports & Analytics',
                'capabilities' => [
                    self::REPORTS_VIEW => [
                        'label' => 'View Reports',
                        'description' => 'View reports and analytics',
                    ],
                    self::REPORTS_GENERATE => [
                        'label' => 'Generate Reports',
                        'description' => 'Create and export reports',
                    ],
                    self::REPORTS_MANAGE_ARCHIVE => [
                        'label' => 'Manage Archive',
                        'description' => 'Archive and restore reports',
                    ],
                ],
            ],
        ];
    }

    /**
     * Get all capability keys as flat array
     */
    public static function getAll(): array
    {
        $all = [];
        foreach (self::getAllGrouped() as $group) {
            $all = array_merge($all, array_keys($group['capabilities']));
        }
        return $all;
    }

    /**
     * Get capability label
     */
    public static function getLabel(string $capability): string
    {
        foreach (self::getAllGrouped() as $group) {
            if (isset($group['capabilities'][$capability])) {
                return $group['capabilities'][$capability]['label'];
            }
        }
        return ucwords(str_replace(['.', '_'], [' ', ' '], $capability));
    }

    /**
     * Get capability description
     */
    public static function getDescription(string $capability): string
    {
        foreach (self::getAllGrouped() as $group) {
            if (isset($group['capabilities'][$capability])) {
                return $group['capabilities'][$capability]['description'];
            }
        }
        return '';
    }

    /**
     * Check if a capability requires another capability (dependencies)
     * e.g., you can't edit without view
     */
    public static function getDependencies(string $capability): array
    {
        $dependencies = [
            self::QUESTIONS_CREATE => [self::QUESTIONS_VIEW],
            self::QUESTIONS_EDIT => [self::QUESTIONS_VIEW],
            self::QUESTIONS_DELETE => [self::QUESTIONS_VIEW],
            self::QUESTIONS_MANAGE_EXAM_SETTINGS => [self::QUESTIONS_VIEW],
            self::APPLICANTS_CREATE => [self::APPLICANTS_VIEW],
            self::APPLICANTS_EDIT => [self::APPLICANTS_VIEW],
            self::APPLICANTS_DELETE => [self::APPLICANTS_VIEW],
            self::APPLICANTS_ASSIGN => [self::APPLICANTS_VIEW],
            self::APPLICANTS_SCHEDULE_EXAM => [self::APPLICANTS_VIEW],
            self::APPLICANTS_BULK_OPERATIONS => [self::APPLICANTS_VIEW],
            self::REPORTS_GENERATE => [self::REPORTS_VIEW],
            self::REPORTS_MANAGE_ARCHIVE => [self::REPORTS_VIEW],
        ];

        return $dependencies[$capability] ?? [];
    }
}

