<?php

namespace Database\Seeders;

use App\Models\AccessCode;
use App\Models\Applicant;
use App\Models\ApplicantBasicInfo;
use App\Models\Exam;
use App\Models\Interview;
use App\Models\SchoolYear;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CompleteApplicantsSeeder extends Seeder
{
    /**
    * Seed 150 applicants with completed exam and interview data.
    *
    * - Generates application numbers using the app helper.
    * - Assigns active exam + access code (used).
    * - Fills applicant basic info (Leyte cities/SHS names).
    * - Sets exam (UEE), EnrollAssess, interview, and GWA scores.
    * - Creates completed interviews within the Dec 8-20 window.
    */
    public function run(): void
    {
        $faker = Faker::create('en_PH');

        $exam = Exam::first();
        if (!$exam) {
            $this->command->error('No exam found. Run ExamSeeder first.');
            return;
        }

        $schoolYear = SchoolYear::where('is_current', true)->first() ?? SchoolYear::first();
        if (!$schoolYear) {
            // Create a current school year if missing
            $start = Carbon::now()->month >= 6
                ? Carbon::create(Carbon::now()->year, 6, 1)
                : Carbon::create(Carbon::now()->year - 1, 6, 1);
            $end = (clone $start)->addYear()->subDay();
            $schoolYear = SchoolYear::create([
                'name' => 'AY ' . $start->year . '-' . $end->year,
                'start_date' => $start,
                'end_date' => $end,
                'is_current' => true,
                'is_active' => true,
            ]);
        }

        $instructors = User::where('role', 'instructor')->pluck('user_id')->all();
        if (empty($instructors)) {
            $this->command->error('No instructors found. Seed users first.');
            return;
        }

        $maleNames = ['Juan', 'Carlos', 'Miguel', 'Paolo', 'Jose', 'Andrei', 'Mark', 'Aaron', 'Francis', 'Rafael', 'Noel', 'Emmanuel', 'Jomar', 'Angelo', 'Michael'];
        $femaleNames = ['Maria', 'Angela', 'Patricia', 'Louise', 'Andrea', 'Katrina', 'Princess', 'Jasmine', 'Sophia', 'Clarisse', 'Faith', 'Camille', 'Joyce', 'Rochelle', 'Dianne'];
        $lastNames = ['Santos', 'Reyes', 'Cruz', 'Bautista', 'Torres', 'Navarro', 'Garcia', 'Lopez', 'Dela Cruz', 'Ramos', 'Villanueva', 'Mendoza', 'Gonzales', 'Aquino', 'Domingo', 'Ferrer', 'Marquez', 'Salazar', 'Velasquez', 'Flores'];

        $cities = ['Ormoc City', 'Baybay City', 'Albuera', 'Kananga', 'Palo', 'Tacloban City', 'Villaba', 'Carigara', 'Burauen'];
        $shsNames = [
            'Ormoc City Senior High School',
            'Leyte National High School',
            'Baybay City Senior High School',
            'Albuera National High School',
            'Kananga National High School',
            'Palo National High School',
            'Villaba National High School',
            'Carigara National High School',
            'Burauen Comprehensive National High School',
        ];
        $strands = ['ABM', 'STEM', 'HUMSS', 'TVL'];
        $courses = ['BSIT', 'BSCS', 'BSEMC', 'BSIS'];

        $deadlineStart = Carbon::create(2025, 12, 8, 8, 0, 0);
        $deadlineEnd = Carbon::create(2025, 12, 20, 17, 0, 0);

        DB::transaction(function () use (
            $faker,
            $exam,
            $schoolYear,
            $instructors,
            $maleNames,
            $femaleNames,
            $lastNames,
            $cities,
            $shsNames,
            $strands,
            $courses,
            $deadlineStart,
            $deadlineEnd
        ) {
            for ($i = 1; $i <= 150; $i++) {
                $sex = $faker->randomElement(['Male', 'Female']);
                $firstName = $sex === 'Male'
                    ? $faker->randomElement($maleNames)
                    : $faker->randomElement($femaleNames);
                $middleName = Str::upper($faker->randomLetter());
                $lastName = $faker->randomElement($lastNames);

                // Unique email
                $emailSlug = Str::slug($firstName . '.' . $lastName);
                $email = $emailSlug . $i . '@gmail.com';

                // Dates
                $examDate = Carbon::create(2025, 12, 8, rand(8, 16), rand(0, 59));
                $interviewDate = Carbon::create(2025, 12, rand(15, 20), rand(8, 16), rand(0, 59));

                // Scores
                $ueeScore = rand(3000, 5900) / 100;           // 30 - 59 (below 60%)
                $skillScore = rand(7200, 9700) / 100;         // EnrollAssess 72 - 97
                $gwaScore = rand(8400, 9600) / 100;           // 84 - 96
                $interviewScore = rand(7600, 9800) / 100;     // 76 - 98

                $applicant = Applicant::create([
                    'application_no' => Applicant::generateApplicationNumber(),
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                    'last_name' => $lastName,
                    'preferred_course' => 'BSIT',
                    'email_address' => $email,
                    'phone_number' => '09' . $faker->numberBetween(100000000, 999999999),
                    'assigned_instructor_id' => $faker->randomElement($instructors),
                    'school_year_id' => $schoolYear->school_year_id,
                    'score' => $ueeScore,
                    'enrollassess_score' => $skillScore,
                    'interview_score' => $interviewScore,
                    'card_tor_gwa' => $gwaScore,
                    'status' => 'interview-completed',
                    'exam_completed_at' => $examDate,
                    'violation_count' => 0,
                ]);

                AccessCode::create([
                    'code' => AccessCode::generateUniqueCode('BSIT', 8),
                    'applicant_id' => $applicant->applicant_id,
                    'exam_id' => $exam->exam_id,
                    'is_used' => true,
                    'used_at' => $examDate,
                    'expires_at' => (clone $examDate)->addDays(30),
                ]);

                $birthDate = Carbon::create(rand(1999, 2007), rand(1, 12), rand(1, 28));
                $city = $faker->randomElement($cities);
                $strand = $faker->randomElement($strands);
                $shs = $faker->randomElement($shsNames);

                ApplicantBasicInfo::create([
                    'applicant_id' => $applicant->applicant_id,
                    'sex' => $sex,
                    'date_of_birth' => $birthDate,
                    'age' => $birthDate->age,
                    'civil_status' => 'Single',
                    'applicant_type' => 'New College Applicant',
                    'is_pwd' => 'No',
                    'complete_address' => $faker->streetAddress . ', ' . $city . ', Leyte',
                    'city_municipality' => $city,
                    'province' => 'Leyte',
                    'senior_high_school_strand' => $strand,
                    'senior_high_school_strand_other' => null,
                    'senior_high_school_name' => $shs,
                    'facebook_link' => 'https://www.facebook.com/' . Str::slug($firstName . '.' . $lastName . '.' . $i),
                    'completed_at' => (clone $examDate)->subDay(),
                ]);

                // Interview rubric scores (0-10 per criterion)
                $criteriaScores = [
                    rand(7, 10),
                    rand(7, 10),
                    rand(7, 10),
                    rand(7, 10),
                    rand(7, 10),
                    rand(7, 10),
                    rand(7, 10),
                    rand(7, 10),
                ];
                $criteriaTotal = array_sum($criteriaScores); // max 80
                $recommendation = $faker->randomElement(['highly_recommended', 'recommended', 'conditional']);
                $recommendationPoints = match ($recommendation) {
                    'highly_recommended' => 20,
                    'recommended' => 10,
                    'conditional' => 5,
                    default => 0,
                };
                $overallInterviewScore = min(100, $criteriaTotal + $recommendationPoints);

                Interview::create([
                    'applicant_id' => $applicant->applicant_id,
                    'interviewer_id' => $applicant->assigned_instructor_id,
                    'schedule_date' => $interviewDate,
                    'status' => 'completed',
                    'communication_skills' => $criteriaScores[0],
                    'motivation_interest' => $criteriaScores[1],
                    'problem_solving_attitude' => $criteriaScores[2],
                    'program_understanding' => $criteriaScores[3],
                    'personality_attitude' => $criteriaScores[4],
                    'it_background' => $criteriaScores[5],
                    'willingness_to_learn' => $criteriaScores[6],
                    'overall_impression' => $criteriaScores[7],
                    'overall_score' => $overallInterviewScore,
                    'overall_rating' => $faker->randomElement(['excellent', 'very_good', 'good', 'satisfactory']),
                    'recommendation' => $recommendation,
                    'strengths' => 'Shows readiness for BSIT coursework.',
                    'areas_improvement' => 'Continue improving problem-solving speed.',
                    'interview_notes' => 'Completed interview on schedule.',
                    'evaluator_notes' => 'Good communication and motivation.',
                    'final_comments' => 'Recommended for admission.',
                    'claimed_by' => $applicant->assigned_instructor_id,
                    'claimed_at' => (clone $interviewDate)->subDays(1),
                    'assignment_notes' => 'Auto-assigned during seeding.',
                    'interview_deadline_start' => $deadlineStart,
                    'interview_deadline_end' => $deadlineEnd,
                    'remarks' => null,
                ]);

                // Keep applicant interview_score aligned with rubric score
                $applicant->update(['interview_score' => $overallInterviewScore]);
            }
        });

        $this->command->info('CompleteApplicantsSeeder: seeded 150 applicants with exam + interview data.');
    }
}

