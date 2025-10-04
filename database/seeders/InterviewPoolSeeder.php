<?php

namespace Database\Seeders;

use App\Models\Applicant;
use App\Models\Interview;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InterviewPoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some applicants who have completed exams
        $applicants = Applicant::where('status', 'exam-completed')->take(10)->get();
        
        if ($applicants->isEmpty()) {
            // Create some test applicants if none exist
            $testApplicants = [
                [
                    'application_no' => 'APP-2025-001',
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email_address' => 'john.doe@example.com',
                    'phone_number' => '09123456789',
                    'preferred_course' => 'Computer Science',
                    'status' => 'exam-completed',
                    'score' => 85.5,
                    'exam_completed_at' => now()->subDays(2),
                    'verbal_description' => 'Very Good'
                ],
                [
                    'application_no' => 'APP-2025-002',
                    'first_name' => 'Jane',
                    'last_name' => 'Smith',
                    'email_address' => 'jane.smith@example.com',
                    'phone_number' => '09987654321',
                    'preferred_course' => 'Information Technology',
                    'status' => 'exam-completed',
                    'score' => 92.0,
                    'exam_completed_at' => now()->subDays(1),
                    'verbal_description' => 'Excellent'
                ],
                [
                    'application_no' => 'APP-2025-003',
                    'first_name' => 'Michael',
                    'last_name' => 'Johnson',
                    'email_address' => 'michael.johnson@example.com',
                    'phone_number' => '09111222333',
                    'preferred_course' => 'Computer Engineering',
                    'status' => 'exam-completed',
                    'score' => 78.5,
                    'exam_completed_at' => now()->subDays(3),
                    'verbal_description' => 'Good'
                ],
                [
                    'application_no' => 'APP-2025-004',
                    'first_name' => 'Sarah',
                    'last_name' => 'Wilson',
                    'email_address' => 'sarah.wilson@example.com',
                    'phone_number' => '09444555666',
                    'preferred_course' => 'Data Science',
                    'status' => 'exam-completed',
                    'score' => 88.0,
                    'exam_completed_at' => now()->subHours(12),
                    'verbal_description' => 'Very Good'
                ],
                [
                    'application_no' => 'APP-2025-005',
                    'first_name' => 'David',
                    'last_name' => 'Brown',
                    'email_address' => 'david.brown@example.com',
                    'phone_number' => '09777888999',
                    'preferred_course' => 'Software Engineering',
                    'status' => 'exam-completed',
                    'score' => 95.5,
                    'exam_completed_at' => now()->subHours(6),
                    'verbal_description' => 'Excellent'
                ]
            ];

            foreach ($testApplicants as $applicantData) {
                $applicants[] = Applicant::create($applicantData);
            }
        }

        // Create interview pool entries for these applicants
        foreach ($applicants as $applicant) {
            // Skip if interview already exists
            if (Interview::where('applicant_id', $applicant->applicant_id)->exists()) {
                continue;
            }

            // Determine priority based on score
            $priority = 'medium';
            $examPercentage = $applicant->score ?? 75; // Default to 75 if no score
            
            if ($examPercentage >= 90) {
                $priority = 'high';
            } elseif ($examPercentage >= 80) {
                $priority = 'medium';
            } else {
                $priority = 'low';
            }

            // Create interview in pool
            Interview::create([
                'applicant_id' => $applicant->applicant_id,
                'status' => 'available',
                'pool_status' => 'available',
                'priority_level' => $priority,
                'schedule_date' => null,
                'interviewer_id' => null,
                'claimed_by' => null,
                'claimed_at' => null,
                'dh_override' => false,
                'assignment_notes' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $this->command->info('Interview pool seeded successfully!');
    }
}
