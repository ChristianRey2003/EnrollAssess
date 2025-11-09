<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Applicant;
use App\Models\ApplicantBasicInfo;
use App\Models\AccessCode;
use App\Models\Interview;
use App\Models\User;
use App\Models\Exam;
use App\Data\PhilippineLocations;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating test data for applicants, basic info, exam scores, and interviews...');

        // Get exam and instructors
        $exam = Exam::first();
        if (!$exam) {
            $this->command->error('No exam found. Please run ExamSeeder first.');
            return;
        }

        $instructors = User::where('role', 'instructor')->get();
        if ($instructors->isEmpty()) {
            $this->command->error('No instructors found. Please run UserSeeder first.');
            return;
        }

        // Philippine provinces (focus on Eastern Visayas)
        $provinces = [
            'Leyte', 'Southern Leyte', 'Biliran', 'Samar', 'Eastern Samar', 'Northern Samar',
            'Cebu', 'Bohol', 'Negros Oriental', 'Metro Manila', 'Quezon', 'Laguna',
            'Palawan', 'Davao del Sur', 'Cagayan', 'Ilocos Norte', 'Pangasinan', 'Bulacan'
        ];

        // Cities for major provinces
        $cities = [
            'Leyte' => ['Ormoc City', 'Tacloban City', 'Baybay City', 'Maasin City', 'Calbayog City'],
            'Southern Leyte' => ['Maasin City', 'Sogod', 'Liloan', 'San Juan'],
            'Biliran' => ['Naval', 'Kawayan', 'Almeria', 'Biliran'],
            'Samar' => ['Calbayog City', 'Catbalogan City', 'Basey', 'Paranas'],
            'Eastern Samar' => ['Borongan City', 'Guiuan', 'Dolores', 'Salcedo'],
            'Northern Samar' => ['Catarman', 'Laoang', 'Allen', 'San Isidro'],
            'Cebu' => ['Cebu City', 'Lapu-Lapu City', 'Mandaue City', 'Talisay City'],
            'Bohol' => ['Tagbilaran City', 'Carmen', 'Dauis', 'Panglao'],
        ];

        // Strands
        $strands = ['ABM', 'STEM', 'HUMSS', 'TVL', 'Others'];
        $otherStrands = ['GAS', 'Arts and Design', 'Sports Track', 'ICT', 'HE', 'IA', 'Agri-Fishery'];

        // Applicant types
        $applicantTypes = ['New College Applicant', 'Transferee', 'ALS passer'];

        // Statuses with distribution
        $statuses = [
            'pending' => 10,           // 10%
            'exam-completed' => 20,    // 20%
            'interview-scheduled' => 15, // 15%
            'interview-completed' => 25, // 25%
            'admitted' => 20,          // 20%
            'rejected' => 10,          // 10%
        ];

        // Create 200 applicants with diverse data
        $totalApplicants = 200;
        $created = 0;

        DB::beginTransaction();
        try {
            foreach ($statuses as $status => $count) {
                for ($i = 0; $i < $count; $i++) {
                    $applicant = $this->createApplicant($status, $exam, $instructors->random(), $provinces, $cities, $strands, $otherStrands, $applicantTypes);
                    $created++;
                    
                    if ($created % 50 == 0) {
                        $this->command->info("Created {$created}/{$totalApplicants} applicants...");
                    }
                }
            }

            DB::commit();
            $this->command->info("✅ Successfully created {$created} applicants with complete test data!");
            $this->command->info("   - Basic information data");
            $this->command->info("   - Exam scores");
            $this->command->info("   - Interview scores");
            $this->command->info("   - Access codes");
            $this->command->info("   - GWA scores");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Error creating test data: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create an applicant with all related data
     */
    private function createApplicant($status, $exam, $instructor, $provinces, $cities, $strands, $otherStrands, $applicantTypes)
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $middleName = fake()->optional(0.7)->firstName();
        
        // Determine scores based on status
        $enrollassessScore = null;
        $interviewScore = null;
        $gwa = fake()->randomFloat(2, 85, 98); // GWA between 85-98
        $examCompletedAt = null;
        
        if (in_array($status, ['exam-completed', 'interview-scheduled', 'interview-completed', 'admitted', 'rejected'])) {
            // Generate exam score (60-100)
            $enrollassessScore = fake()->randomFloat(2, 60, 100);
            $examCompletedAt = fake()->dateTimeBetween('-30 days', '-1 day');
        }
        
        // Interview score will be calculated from criteria scores
        $interviewScore = null;

        // Create applicant
        $applicant = Applicant::create([
            'application_no' => 'APP-' . str_pad(Applicant::count() + 1, 6, '0', STR_PAD_LEFT),
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'preferred_course' => fake()->randomElement([
                'BS Computer Science',
                'BS Information Technology',
                'BS Computer Engineering',
                'BS Data Science',
            ]),
            'email_address' => strtolower($firstName . '.' . $lastName . '@' . fake()->domainName()),
            'phone_number' => '+63' . fake()->numerify('9#########'),
            'assigned_instructor_id' => $instructor->user_id,
            'score' => $enrollassessScore,
            'enrollassess_score' => $enrollassessScore,
            'interview_score' => null, // Will be updated after interview is created
            'card_tor_gwa' => $gwa,
            'verbal_description' => $enrollassessScore ? $this->getVerbalDescription($enrollassessScore) : null,
            'status' => $status,
            'exam_completed_at' => $examCompletedAt,
            'created_at' => fake()->dateTimeBetween('-60 days', 'now'),
            'updated_at' => now(),
        ]);

        // Create basic info
        $province = fake()->randomElement($provinces);
        $provinceCities = $cities[$province] ?? ['City'];
        $city = fake()->randomElement($provinceCities);
        
        $strand = fake()->randomElement($strands);
        $strandOther = ($strand === 'Others') ? fake()->randomElement($otherStrands) : null;
        
        $birthDate = fake()->dateTimeBetween('-25 years', '-16 years');
        $age = Carbon::parse($birthDate)->age;
        
        ApplicantBasicInfo::create([
            'applicant_id' => $applicant->applicant_id,
            'sex' => fake()->randomElement(['Male', 'Female', 'Other', 'Prefer not to say']),
            'date_of_birth' => $birthDate,
            'age' => $age,
            'civil_status' => fake()->optional(0.8)->randomElement(['Single', 'Married', 'Widowed', 'Separated', 'Divorced']),
            'applicant_type' => fake()->randomElement($applicantTypes),
            'is_pwd' => fake()->randomElement(['Yes', 'No', 'Prefer not to answer']),
            'complete_address' => fake()->streetAddress() . ', ' . $city . ', ' . $province,
            'city_municipality' => $city,
            'province' => $province,
            'senior_high_school_strand' => $strand,
            'senior_high_school_strand_other' => $strandOther,
            'senior_high_school_name' => fake()->company() . ' Senior High School',
            'completed_at' => fake()->dateTimeBetween($applicant->created_at, 'now'),
            'created_at' => $applicant->created_at,
            'updated_at' => now(),
        ]);

        // Create access code (ensure uniqueness)
        do {
            $code = strtoupper(fake()->bothify('????####'));
        } while (AccessCode::where('code', $code)->exists());
        
        $accessCode = AccessCode::create([
            'code' => $code,
            'applicant_id' => $applicant->applicant_id,
            'exam_id' => $exam->exam_id,
            'is_used' => in_array($status, ['exam-completed', 'interview-scheduled', 'interview-completed', 'admitted', 'rejected']),
            'used_at' => in_array($status, ['exam-completed', 'interview-scheduled', 'interview-completed', 'admitted', 'rejected']) 
                ? $examCompletedAt 
                : null,
            'expires_at' => now()->addDays(30),
            'created_at' => $applicant->created_at,
            'updated_at' => now(),
        ]);

        // Create interview if status requires it
        if (in_array($status, ['interview-scheduled', 'interview-completed', 'admitted', 'rejected'])) {
            // Calculate interview criteria scores first, then derive overall score
            $criteriaScores = [];
            if (in_array($status, ['interview-completed', 'admitted', 'rejected'])) {
                // Generate individual criteria scores (each out of 10, must be integers)
                $criteriaScores = [
                    'communication_skills' => fake()->numberBetween(6, 10),
                    'motivation_interest' => fake()->numberBetween(6, 10),
                    'problem_solving_attitude' => fake()->numberBetween(6, 10),
                    'program_understanding' => fake()->numberBetween(6, 10),
                    'personality_attitude' => fake()->numberBetween(6, 10),
                    'it_background' => fake()->numberBetween(6, 10),
                    'willingness_to_learn' => fake()->numberBetween(6, 10),
                    'overall_impression' => fake()->numberBetween(6, 10),
                ];
                // Calculate overall score (sum of all criteria, max 80)
                $totalScore = array_sum($criteriaScores);
                // Overall score is the sum (0-80), not percentage
                $interviewScore = $totalScore;
            }
            
            $interview = Interview::create([
                'applicant_id' => $applicant->applicant_id,
                'interviewer_id' => $instructor->user_id,
                'schedule_date' => fake()->dateTimeBetween($examCompletedAt ?? '-20 days', '+10 days'),
                'status' => in_array($status, ['interview-completed', 'admitted', 'rejected']) ? 'completed' : 'scheduled',
                
                // Interview criteria scores (each out of 10)
                'communication_skills' => $criteriaScores['communication_skills'] ?? null,
                'motivation_interest' => $criteriaScores['motivation_interest'] ?? null,
                'problem_solving_attitude' => $criteriaScores['problem_solving_attitude'] ?? null,
                'program_understanding' => $criteriaScores['program_understanding'] ?? null,
                'personality_attitude' => $criteriaScores['personality_attitude'] ?? null,
                'it_background' => $criteriaScores['it_background'] ?? null,
                'willingness_to_learn' => $criteriaScores['willingness_to_learn'] ?? null,
                'overall_impression' => $criteriaScores['overall_impression'] ?? null,
                
                // Overall scores
                'overall_score' => $interviewScore,
                'overall_rating' => $interviewScore ? $this->getOverallRatingFromScore($interviewScore) : null,
                'recommendation' => $interviewScore ? $this->getRecommendationFromScore($interviewScore, $status) : null,
                
                // Written feedback
                'strengths' => $interviewScore ? fake()->paragraph(2) : null,
                'areas_improvement' => $interviewScore ? fake()->paragraph(1) : null,
                'interview_notes' => $interviewScore ? fake()->paragraph(3) : null,
                'evaluator_notes' => $interviewScore ? fake()->sentence() : null,
                'final_comments' => $interviewScore ? fake()->paragraph(2) : null,
                
                'created_at' => $examCompletedAt ?? fake()->dateTimeBetween('-20 days', 'now'),
                'updated_at' => now(),
            ]);
            
            // Update applicant's interview_score (convert to percentage 0-100)
            if ($interviewScore) {
                $interviewPercentage = ($interviewScore / 80) * 100;
                $applicant->update(['interview_score' => round($interviewPercentage, 2)]);
            }
        }

        return $applicant;
    }

    /**
     * Get verbal description based on score
     */
    private function getVerbalDescription($score)
    {
        if ($score >= 95) return 'Excellent';
        if ($score >= 85) return 'Very Good';
        if ($score >= 75) return 'Good';
        if ($score >= 65) return 'Satisfactory';
        if ($score >= 50) return 'Fair';
        return 'Needs Improvement';
    }

    /**
     * Get overall rating based on interview score (0-80 scale)
     */
    private function getOverallRatingFromScore($score)
    {
        // Convert to percentage for rating
        $percentage = ($score / 80) * 100;
        
        if ($percentage >= 90) return 'excellent';
        if ($percentage >= 80) return 'very_good';
        if ($percentage >= 70) return 'good';
        if ($percentage >= 60) return 'satisfactory';
        return 'needs_improvement';
    }

    /**
     * Get recommendation based on score and status
     * Valid values: 'highly_recommended', 'recommended', 'conditional', 'not_recommended'
     */
    private function getRecommendationFromScore($score, $status)
    {
        // Convert to percentage for recommendation
        $percentage = ($score / 80) * 100;
        
        if ($status === 'admitted') {
            return $percentage >= 85 ? 'highly_recommended' : 'recommended';
        }
        if ($status === 'rejected') {
            return 'not_recommended';
        }
        if ($percentage >= 85) return 'highly_recommended';
        if ($percentage >= 70) return 'recommended';
        if ($percentage >= 60) return 'conditional';
        return 'not_recommended';
    }
}

