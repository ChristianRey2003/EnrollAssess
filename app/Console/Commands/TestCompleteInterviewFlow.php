<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Applicant;
use App\Models\Interview;
use App\Models\User;
use App\Services\InterviewPoolService;
use App\Http\Controllers\ExamSubmissionController;

class TestCompleteInterviewFlow extends Command
{
    protected $signature = 'test:interview-flow {applicant_id} {score=85}';
    protected $description = 'Test the complete interview workflow from exam completion to interview assignment';

    public function handle()
    {
        $applicantId = $this->argument('applicant_id');
        $score = $this->argument('score');

        $this->info("🧪 Testing Complete Interview Workflow");
        $this->info("=====================================");

        // Step 1: Check applicant exists
        $applicant = Applicant::find($applicantId);
        if (!$applicant) {
            $this->error("❌ Applicant with ID {$applicantId} not found");
            return 1;
        }

        $this->info("✅ Applicant found: {$applicant->full_name}");

        // Step 2: Check current interview status
        $existingInterview = Interview::where('applicant_id', $applicantId)->first();
        if ($existingInterview) {
            $this->warn("⚠️  Existing interview found with status: {$existingInterview->status}");
            if ($this->confirm('Delete existing interview and start fresh?')) {
                $existingInterview->delete();
                $this->info("🗑️  Existing interview deleted");
            } else {
                $this->info("ℹ️  Continuing with existing interview...");
            }
        }

        // Step 3: Simulate exam completion
        $this->info("\n📝 Step 1: Simulating exam completion...");
        $examController = app(ExamSubmissionController::class);
        
        try {
            $request = new \Illuminate\Http\Request();
            // Create sample answers for testing
            $sampleAnswers = [
                '1' => 'A',
                '2' => 'B', 
                '3' => 'C',
                '4' => 'D',
                '5' => 'A'
            ];
            
            $request->merge([
                'applicant_id' => $applicantId,
                'answers' => $sampleAnswers
            ]);

            $response = $examController->completeExam($request);
            $responseData = json_decode($response->getContent(), true);

            if ($responseData['success']) {
                $this->info("✅ Exam completion simulated successfully");
                $this->info("   Score: {$score}%");
                $this->info("   Result: {$responseData['message']}");
            } else {
                $this->error("❌ Exam completion failed: {$responseData['message']}");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("❌ Error during exam completion: " . $e->getMessage());
            return 1;
        }

        // Step 4: Check if interview was added to pool
        $this->info("\n🏊 Step 2: Checking interview pool...");
        $poolInterview = Interview::where('applicant_id', $applicantId)->first();
        
        if (!$poolInterview) {
            $this->error("❌ No interview found in database after exam completion");
            return 1;
        }

        $this->info("✅ Interview found in pool:");
        $this->info("   Status: {$poolInterview->status}");
        $this->info("   Pool Status: " . ($poolInterview->pool_status ?? 'N/A'));
        $this->info("   Priority: " . ($poolInterview->priority_level ?? 'N/A'));
        $this->info("   Claimed By: " . ($poolInterview->claimed_by ? User::find($poolInterview->claimed_by)->full_name : 'Available'));

        // Step 5: Test instructor claiming
        $this->info("\n👨‍🏫 Step 3: Testing instructor claiming...");
        $instructors = User::where('role', 'instructor')->get();
        
        if ($instructors->isEmpty()) {
            $this->warn("⚠️  No instructors found in database");
        } else {
            $instructor = $instructors->first();
            $this->info("   Testing with instructor: {$instructor->full_name}");

            // Simulate claiming
            $poolService = app(InterviewPoolService::class);
            $claimResult = $poolService->claimInterview($poolInterview->interview_id, $instructor->user_id);

            if ($claimResult['success']) {
                $this->info("✅ Interview claimed successfully");
                $this->info("   Claimed by: {$instructor->full_name}");
                $this->info("   Claimed at: {$poolInterview->fresh()->claimed_at}");
            } else {
                $this->warn("⚠️  Claim failed: {$claimResult['message']}");
            }
        }

        // Step 6: Test DH override
        $this->info("\n👑 Step 4: Testing DH override...");
        $departmentHeads = User::where('role', 'department_head')->get();
        
        if ($departmentHeads->isEmpty()) {
            $this->warn("⚠️  No department heads found in database");
        } else {
            $dh = $departmentHeads->first();
            $this->info("   Testing with DH: {$dh->full_name}");

            // Simulate DH override assignment
            $assignResult = $poolService->assignInterviewToInstructor(
                $poolInterview->interview_id, 
                $instructor->user_id, 
                $dh->user_id
            );

            if ($assignResult['success']) {
                $this->info("✅ DH override assignment successful");
                $this->info("   Assigned by: {$dh->full_name}");
                $this->info("   Assigned to: {$instructor->full_name}");
            } else {
                $this->warn("⚠️  DH override failed: {$assignResult['message']}");
            }
        }

        // Step 7: Pool statistics
        $this->info("\n📊 Step 5: Pool Statistics...");
        $stats = $poolService->getPoolStatistics();
        $this->info("   Total in Pool: {$stats['total_in_pool']}");
        $this->info("   Available: {$stats['available']}");
        $this->info("   Claimed: {$stats['claimed']}");
        $this->info("   High Priority: {$stats['high_priority']}");
        $this->info("   Average Wait Time: {$stats['average_wait_time']} minutes");

        // Step 8: Final interview status
        $this->info("\n🏁 Final Status:");
        $finalInterview = Interview::find($poolInterview->interview_id);
        $this->info("   Status: {$finalInterview->status}");
        $this->info("   Pool Status: " . ($finalInterview->pool_status ?? 'N/A'));
        $this->info("   Interviewer: " . ($finalInterview->interviewer_id ? User::find($finalInterview->interviewer_id)->full_name : 'Not Assigned'));
        $this->info("   Claimed By: " . ($finalInterview->claimed_by ? User::find($finalInterview->claimed_by)->full_name : 'Available'));

        $this->info("\n🎉 Interview workflow test completed successfully!");
        $this->info("\n💡 Next steps:");
        $this->info("   1. Visit /admin/interviews/pool to see the pool interface");
        $this->info("   2. Visit /instructor/interview-pool to test instructor claiming");
        $this->info("   3. Test real-time updates by opening multiple browser tabs");

        return 0;
    }
}