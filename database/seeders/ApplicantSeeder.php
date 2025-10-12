<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Applicant;
use App\Models\AccessCode;
use App\Models\Interview;
use App\Models\Result;
use App\Models\Exam;
use App\Models\User;

class ApplicantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $exam = Exam::first();
        $instructor = User::where('role', 'instructor')->first();

        $applicants = [
            [
                'application_no' => '2024-001',
                'full_name' => 'John Michael Doe',
                'email_address' => 'john.doe@email.com',
                'phone_number' => '+63 912 345 6701',
                'address' => '123 University Ave, Ormoc City, Leyte',
                'education_background' => 'Ormoc City High School, Class of 2023',
                'assigned_instructor_id' => $instructor?->user_id,
                'score' => 85.50,
                'status' => 'interview-scheduled',
                'exam_completed_at' => now()->subDays(3),
            ],
            [
                'application_no' => '2024-002',
                'full_name' => 'Maria Christina Santos',
                'email_address' => 'maria.santos@email.com',
                'phone_number' => '+63 912 345 6702',
                'address' => '456 Main St, Ormoc City, Leyte',
                'education_background' => 'EVSU High School, Class of 2023',
                'assigned_instructor_id' => $instructor?->user_id,
                'score' => 92.00,
                'status' => 'exam-completed',
                'exam_completed_at' => now()->subDays(2),
            ],
            [
                'application_no' => '2024-003',
                'full_name' => 'Anna Patricia Cruz',
                'email_address' => 'anna.cruz@email.com',
                'phone_number' => '+63 912 345 6703',
                'address' => '789 School Rd, Ormoc City, Leyte',
                'education_background' => 'Ormoc National High School, Class of 2023',
                'assigned_instructor_id' => null,
                'score' => 78.25,
                'status' => 'pending',
                'exam_completed_at' => null,
            ],
        ];

        foreach ($applicants as $applicantData) {
            $applicant = Applicant::updateOrCreate(
                ['application_no' => $applicantData['application_no']],
                $applicantData
            );

            // Create access code for each applicant linked to the exam
            if ($exam) {
                AccessCode::updateOrCreate(
                    ['applicant_id' => $applicant->applicant_id],
                    [
                        'code' => 'BSIT-' . strtoupper(substr(md5($applicant->application_no), 0, 8)),
                        'applicant_id' => $applicant->applicant_id,
                        'exam_id' => $exam->exam_id,
                        'is_used' => $applicant->status !== 'pending',
                        'used_at' => $applicant->status !== 'pending' ? $applicant->exam_completed_at : null,
                        'expires_at' => now()->addDays(30),
                    ]
                );
            }

            // Create interview for assigned applicants
            if ($applicant->status === 'interview-scheduled' && $instructor) {
                Interview::updateOrCreate(
                    ['applicant_id' => $applicant->applicant_id],
                    [
                        'applicant_id' => $applicant->applicant_id,
                        'interviewer_id' => $instructor->user_id,
                        'schedule_date' => now()->addDays(2),
                        'status' => 'scheduled',
                        'rating_communication' => null,
                        'rating_technical' => null,
                        'rating_problem_solving' => null,
                        'notes' => 'Initial interview scheduled',
                    ]
                );
            }
        }

        $this->command->info('Applicants seeded successfully!');
    }
}