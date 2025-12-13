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
        // Common surnames from various municipalities in Leyte, Philippines
        $lastNames = [
            // Ormoc, Leyte
            'Laurente', 'Pepito', 'Perez', 'Parrilla', 'Villamor', 'Mendoza', 'Malinao', 'Gonzales', 'Omega', 'Cabahug',
            'Matuguina', 'Canete', 'Estrera', 'Ablen', 'Sanchez', 'Magallanes', 'Sacay', 'Pilapil', 'Dela Cruz', 'Donayre',
            'Pitogo', 'Lopez', 'Roble', 'Gonzaga', 'Tan', 'Garciano', 'Laude', 'Codilla', 'Maglasang', 'Dejano',
            // Alangalang, Leyte
            'Gatela', 'Velarde', 'Caones', 'Vergara', 'Catindoy', 'Juntila', 'Sabela', 'Varona', 'Matobato', 'Garlando',
            'Tante', 'Pedrera', 'Aruta', 'Yu', 'Baldesco', 'Jomadiao', 'Barrantes', 'Apurillo', 'Capon', 'Maraya',
            // Albuera, Leyte
            'Cayanong', 'Alao', 'Mandras', 'Rosal', 'Caindoc', 'Celedio', 'Cabiling', 'Antigua', 'Cantiga', 'Pepito',
            'Montalban', 'Sotto', 'Perales', 'Bernal', 'Cabulong', 'Velarde', 'De Leon', 'Andrade', 'Maquilan', 'Puebla',
            // Babatngon, Leyte
            'Alvarez', 'Buena', 'Lacaba', 'Balboa', 'Bito', 'Montaño', 'Alcaraz', 'Salas', 'Ballais', 'Espares',
            'Agner', 'Cadano', 'Cajeda', 'Rosales', 'Codilan', 'Belches', 'Caysido', 'De La Cruz', 'Cacharro', 'Bobares',
            // Barugo, Leyte
            'Arpon', 'Balais', 'Panis', 'Peñaranda', 'Avila', 'Acebo', 'Aruta', 'Colibao', 'Ponferrada', 'Geraldo',
            'Cubilla', 'Avestruz', 'Astorga', 'Alcober', 'Cadorna', 'Cirera', 'Alberca', 'Cardines', 'Adizas', 'Ariza',
            // Bato, Leyte
            'Salvame', 'Mendoza', 'Germano', 'Aguilar', 'Tavera', 'Garzon', 'Perez', 'Cillo', 'Sanoria', 'Inguito',
            'Vasquez', 'Ruales', 'Rosal', 'Casinillo', 'Salva', 'Sotto', 'Kuizon', 'Albero', 'Bagay', 'Tablo',
            // Baybay City, Leyte
            'Fernandez', 'Managbanag', 'Valenzona', 'Nayre', 'Ibañez', 'Mazo', 'Nuñez', 'Varron', 'Morales', 'Loreto',
            'Gonzaga', 'Sanchez', 'Porazo', 'Alkuino', 'Granada', 'Galenzoga', 'Modina', 'Cerna', 'Prado', 'Bandalan',
            // Dulag, Leyte
            'De Paz', 'Cagara', 'Tupaz', 'Silvano', 'Garcia', 'Agullo', 'Raagas', 'Bautista', 'Kempis', 'Adonis',
            'Cinco', 'Asis', 'Lopez', 'Magos', 'Devaras', 'Medino', 'Lagunzad', 'Saño', 'Advincula', 'Ramos',
        ];

        $cities = ['Ormoc City', 'Baybay City', 'Albuera', 'Kananga', 'Tacloban City'];
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

        $streetNames = ['Rizal', 'Luna', 'Bonifacio', 'Mabini', 'Burgos', 'Gomez', 'Zamora', 'Del Pilar', 'Jacinto', 'Aguinaldo'];
        
        DB::transaction(function () use (
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
            $deadlineEnd,
            $streetNames
        ) {
            for ($i = 1; $i <= 150; $i++) {
                $sex = (['Male', 'Female'])[rand(0, 1)];
                $firstName = $sex === 'Male'
                    ? $maleNames[array_rand($maleNames)]
                    : $femaleNames[array_rand($femaleNames)];
                $middleName = Str::upper(chr(rand(65, 90))); // Random uppercase letter A-Z
                $lastName = $lastNames[array_rand($lastNames)];

                // Unique email
                $emailSlug = Str::slug($firstName . '.' . $lastName);
                $email = $emailSlug . $i . '@gmail.com';

                // Dates
                $examDate = Carbon::create(2025, 12, 8, rand(8, 16), rand(0, 59));
                $interviewDate = Carbon::create(2025, 12, rand(15, 20), rand(8, 16), rand(0, 59));

                // Scores
                $ueeScore = rand(1500, 4500) / 100;           // 15 - 45 (UEE capped at 45%)
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
                    'phone_number' => '09' . rand(100000000, 999999999),
                    'assigned_instructor_id' => $instructors[array_rand($instructors)],
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
                $city = $cities[array_rand($cities)];
                $strand = $strands[array_rand($strands)];
                $shs = $shsNames[array_rand($shsNames)];

                ApplicantBasicInfo::create([
                    'applicant_id' => $applicant->applicant_id,
                    'sex' => $sex,
                    'date_of_birth' => $birthDate,
                    'age' => $birthDate->age,
                    'civil_status' => 'Single',
                    'applicant_type' => 'New College Applicant',
                    'is_pwd' => 'No',
                    'complete_address' => rand(1, 999) . ' ' . $streetNames[array_rand($streetNames)] . ' Street, ' . $city . ', Leyte',
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
                $recommendationOptions = ['highly_recommended', 'recommended', 'conditional'];
                $recommendation = $recommendationOptions[array_rand($recommendationOptions)];
                $recommendationPoints = match ($recommendation) {
                    'highly_recommended' => 20,
                    'recommended' => 10,
                    'conditional' => 5,
                    default => 0,
                };
                $overallInterviewScore = min(100, $criteriaTotal + $recommendationPoints);
                
                $ratingOptions = ['excellent', 'very_good', 'good', 'satisfactory'];
                $overallRating = $ratingOptions[array_rand($ratingOptions)];

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
                    'overall_rating' => $overallRating,
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

            // Seed 50 additional applicants with no completion (no exam/interview/basic info)
            for ($i = 1; $i <= 50; $i++) {
                $sex = (['Male', 'Female'])[rand(0, 1)];
                $firstName = $sex === 'Male'
                    ? $maleNames[array_rand($maleNames)]
                    : $femaleNames[array_rand($femaleNames)];
                $middleName = Str::upper(chr(rand(65, 90))); // Random uppercase letter A-Z
                $lastName = $lastNames[array_rand($lastNames)];

                // Unique email
                $emailSlug = Str::slug($firstName . '.' . $lastName);
                $email = $emailSlug . 'pending' . $i . '@gmail.com';

                Applicant::create([
                    'application_no' => Applicant::generateApplicationNumber(),
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                    'last_name' => $lastName,
                    'preferred_course' => 'BSIT',
                    'email_address' => $email,
                    'phone_number' => '09' . rand(100000000, 999999999),
                    'assigned_instructor_id' => null,
                    'school_year_id' => $schoolYear->school_year_id,
                    // Leave scores and interview/exam fields null to represent not completed
                    'score' => null,
                    'enrollassess_score' => null,
                    'interview_score' => null,
                    'card_tor_gwa' => null,
                    'status' => 'pending',
                    'exam_completed_at' => null,
                    'violation_count' => 0,
                ]);
            }
        });

        $this->command->info('CompleteApplicantsSeeder: seeded 150 applicants with exam + interview data.');
    }
}

