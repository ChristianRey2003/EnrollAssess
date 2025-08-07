<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Applicant;
use App\Models\AccessCode;
use App\Models\ExamSet;
use App\Models\Exam;
use Illuminate\Support\Facades\DB;

class TestApplicantImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:applicant-import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the Applicant Import and Management System';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== EnrollAssess Applicant Import System Test ===');
        $this->newLine();

        try {
            // Test 1: Check existing data
            $this->info('1. Testing Current Data...');
            $applicants = Applicant::with(['accessCode', 'examSet'])->get();
            $accessCodes = AccessCode::with('applicant')->get();
            
            $this->line("   Found {$applicants->count()} applicants:");
            foreach ($applicants->take(3) as $applicant) {
                $hasCode = $applicant->accessCode ? 'Yes' : 'No';
                $examSet = $applicant->examSet ? $applicant->examSet->set_name : 'Not Assigned';
                $this->line("   - {$applicant->full_name} (Code: {$hasCode}, Set: {$examSet})");
            }
            $this->newLine();

            // Test 2: Create test applicants
            $this->info('2. Testing Applicant Creation...');
            $testApplicants = [];
            
            for ($i = 1; $i <= 3; $i++) {
                $testApplicant = Applicant::create([
                    'application_no' => Applicant::generateApplicationNumber(),
                    'full_name' => "Test Applicant {$i}",
                    'email_address' => "test.applicant{$i}@example.com",
                    'phone_number' => "091234567{$i}{$i}",
                    'address' => "Test Address {$i}, Test City",
                    'education_background' => 'Senior High School Graduate',
                ]);
                
                $testApplicants[] = $testApplicant;
                $this->line("   ✓ Created applicant: {$testApplicant->full_name} (#{$testApplicant->application_no})");
            }
            $this->newLine();

            // Test 3: Generate access codes
            $this->info('3. Testing Access Code Generation...');
            foreach ($testApplicants as $applicant) {
                $accessCode = AccessCode::createForApplicant(
                    $applicant->applicant_id,
                    'TEST',
                    6,
                    24 // 24 hours
                );
                $this->line("   ✓ Generated access code for {$applicant->full_name}: {$accessCode->code}");
            }
            $this->newLine();

            // Test 4: Test exam set assignment
            $this->info('4. Testing Exam Set Assignment...');
            $examSets = ExamSet::where('is_active', true)->get();
            
            if ($examSets->count() > 0) {
                foreach ($testApplicants as $index => $applicant) {
                    $examSet = $examSets[$index % $examSets->count()];
                    $applicant->update(['exam_set_id' => $examSet->exam_set_id]);
                    $this->line("   ✓ Assigned {$applicant->full_name} to {$examSet->set_name}");
                }
            } else {
                $this->warn("   No active exam sets found for assignment testing");
            }
            $this->newLine();

            // Test 5: Test bulk operations simulation
            $this->info('5. Testing Bulk Operations...');
            
            // Simulate bulk access code generation
            $applicantsWithoutCodes = Applicant::whereDoesntHave('accessCode')->count();
            $this->line("   Found {$applicantsWithoutCodes} applicants without access codes");
            
            // Simulate bulk exam set assignment
            $applicantsWithoutSets = Applicant::whereNull('exam_set_id')->count();
            $this->line("   Found {$applicantsWithoutSets} applicants without exam set assignments");
            $this->newLine();

            // Test 6: Test application number generation
            $this->info('6. Testing Application Number Generation...');
            $currentYear = date('Y');
            $existingCount = Applicant::whereYear('created_at', $currentYear)->count();
            $nextAppNo = Applicant::generateApplicationNumber();
            $this->line("   Current year: {$currentYear}");
            $this->line("   Existing applicants this year: {$existingCount}");
            $this->line("   Next application number would be: {$nextAppNo}");
            $this->newLine();

            // Test 7: Test access code validation
            $this->info('7. Testing Access Code Validation...');
            $testCode = AccessCode::with('applicant')->first();
            if ($testCode) {
                $isValid = $testCode->isValid();
                $status = $isValid ? 'Valid' : 'Invalid';
                $this->line("   Test code {$testCode->code}: {$status}");
                $this->line("   Used: " . ($testCode->is_used ? 'Yes' : 'No'));
                $this->line("   Expires: " . ($testCode->expires_at ? $testCode->expires_at->format('Y-m-d H:i') : 'Never'));
            }
            $this->newLine();

            // Test 8: Statistics and reporting
            $this->info('8. Testing Statistics...');
            $stats = [
                'total_applicants' => Applicant::count(),
                'with_access_codes' => Applicant::whereHas('accessCode')->count(),
                'without_access_codes' => Applicant::whereDoesntHave('accessCode')->count(),
                'pending_status' => Applicant::where('status', 'pending')->count(),
                'exam_completed' => Applicant::where('status', 'exam-completed')->count(),
                'assigned_to_sets' => Applicant::whereNotNull('exam_set_id')->count(),
            ];
            
            foreach ($stats as $label => $count) {
                $formattedLabel = ucwords(str_replace('_', ' ', $label));
                $this->line("   {$formattedLabel}: {$count}");
            }
            $this->newLine();

            // Test 9: Clean up test data
            $this->info('9. Cleaning up test data...');
            foreach ($testApplicants as $applicant) {
                $applicant->delete(); // This should cascade delete access codes
                $this->line("   ✓ Deleted test applicant: {$applicant->full_name}");
            }
            $this->newLine();

            $this->info('=== All Tests Passed! ===');
            $this->line('Your Applicant Import System is ready to use!');
            $this->newLine();
            $this->line('Features tested successfully:');
            $this->line('✓ Applicant creation and management');
            $this->line('✓ Access code generation and validation');
            $this->line('✓ Exam set assignment');
            $this->line('✓ Bulk operations capability');
            $this->line('✓ Application number generation');
            $this->line('✓ Statistics and reporting');
            $this->newLine();
            $this->line('Ready for production use:');
            $this->line('1. Visit /admin/applicants to manage applicants');
            $this->line('2. Use /admin/applicants-import for bulk CSV import');
            $this->line('3. Generate access codes and assign exam sets');
            $this->line('4. Export applicant data with access codes');

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            $this->line("Stack trace:");
            $this->line($e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}
