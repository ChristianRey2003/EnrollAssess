<?php

namespace App\Console\Commands;

use App\Models\Applicant;
use App\Models\Interview;
use Illuminate\Console\Command;

class ResetAutoAdmittedApplicants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'applicants:reset-auto-admitted 
                            {--dry-run : Show what would be changed without actually changing anything}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset applicants who were auto-admitted back to interview-completed status for department head review';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        // Find all applicants with status 'admitted' who have a completed interview
        $applicants = Applicant::where('status', 'admitted')
            ->whereHas('interviews', function ($query) {
                $query->where('status', 'completed');
            })
            ->with(['interviews' => function ($query) {
                $query->where('status', 'completed')->latest()->limit(1);
            }])
            ->get();

        if ($applicants->isEmpty()) {
            $this->info('✅ No auto-admitted applicants found that need resetting.');
            return Command::SUCCESS;
        }

        $this->info("Found {$applicants->count()} applicant(s) with 'admitted' status who have completed interviews:");
        $this->newLine();

        $tableData = [];
        foreach ($applicants as $applicant) {
            $latestInterview = $applicant->interviews->first();
            $tableData[] = [
                'ID' => $applicant->applicant_id,
                'Name' => $applicant->full_name,
                'Application No' => $applicant->application_no,
                'Interview Score' => $latestInterview->overall_score ?? 'N/A',
                'Recommendation' => $latestInterview->recommendation ?? 'N/A',
            ];
        }

        $this->table(['ID', 'Name', 'Application No', 'Interview Score', 'Recommendation'], $tableData);
        $this->newLine();

        if ($isDryRun) {
            $this->info('💡 Run without --dry-run flag to reset these applicants to "interview-completed" status.');
            return Command::SUCCESS;
        }

        if (!$this->confirm('Do you want to reset these applicants to "interview-completed" status?')) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        $updated = 0;
        foreach ($applicants as $applicant) {
            $applicant->update([
                'status' => 'interview-completed',
            ]);
            $updated++;
        }

        $this->info("✅ Successfully reset {$updated} applicant(s) to 'interview-completed' status.");
        $this->info('📋 These applicants are now available for department head review and admission decision.');

        return Command::SUCCESS;
    }
}

