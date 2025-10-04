<?php

namespace App\Console\Commands;

use App\Models\Applicant;
use App\Services\InterviewPoolService;
use Illuminate\Console\Command;

class TestExamCompletion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:exam-completion {applicant_id?} {score?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the exam completion and interview pool integration';

    /**
     * Execute the console command.
     */
    public function handle(InterviewPoolService $interviewPoolService)
    {
        $applicantId = $this->argument('applicant_id') ?? 1;
        $score = $this->argument('score') ?? 85;

        $this->info("Testing exam completion for applicant {$applicantId} with score {$score}%");

        try {
            // Test the exam completion process
            $interview = $interviewPoolService->processExamCompletion($applicantId, $score);

            $this->info("✅ Success! Interview created:");
            $this->info("   - Interview ID: {$interview->interview_id}");
            $this->info("   - Applicant ID: {$interview->applicant_id}");
            $this->info("   - Status: {$interview->status}");
            $this->info("   - Priority: {$interview->priority_level}");
            $this->info("   - Pool Status: {$interview->pool_status}");

            // Show pool statistics
            $stats = $interviewPoolService->getPoolStatistics();
            $this->info("\n📊 Current Pool Statistics:");
            $this->info("   - Available: {$stats['total_available']}");
            $this->info("   - Claimed: {$stats['total_claimed']}");
            $this->info("   - High Priority: {$stats['high_priority']}");
            $this->info("   - Medium Priority: {$stats['medium_priority']}");
            $this->info("   - Low Priority: {$stats['low_priority']}");

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}