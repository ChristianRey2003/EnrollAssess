<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Applicant;
use App\Models\AccessCode;
use App\Models\Interview;
use App\Models\Result;
use Illuminate\Support\Facades\DB;

class ClearApplicantData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'applicants:clear {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all applicant data including related records (access codes, interviews, results)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if force flag is used
        if (!$this->option('force')) {
            // Show warning and ask for confirmation
            $this->warn('⚠️  WARNING: This will permanently delete ALL applicant data!');
            $this->line('This includes:');
            $this->line('  • All applicants');
            $this->line('  • All access codes');
            $this->line('  • All interviews');
            $this->line('  • All exam results');
            $this->line('');
            
            if (!$this->confirm('Are you sure you want to proceed?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        // Start transaction for safety
        DB::beginTransaction();
        
        try {
            // Get counts before deletion for reporting (including soft-deleted)
            $applicantCount = Applicant::withTrashed()->count();
            $accessCodeCount = AccessCode::count();
            $interviewCount = Interview::count();
            $resultCount = Result::count();

            $this->info('Starting data deletion...');
            
            // Show progress bar
            $progressBar = $this->output->createProgressBar(4);
            $progressBar->start();

            // Delete in order to respect foreign key constraints
            // Results first (references applicants)
            $this->line('');
            $this->info('Deleting exam results...');
            Result::query()->delete();
            $progressBar->advance();

            // Interviews second (references applicants)
            $this->info('Deleting interviews...');
            Interview::query()->delete();
            $progressBar->advance();

            // Access codes third (references applicants)
            $this->info('Deleting access codes...');
            AccessCode::query()->delete();
            $progressBar->advance();

            // Applicants last (main table) - FORCE DELETE to permanently remove
            $this->info('Deleting applicants (permanently)...');
            Applicant::withTrashed()->forceDelete();
            $progressBar->advance();

            $progressBar->finish();
            $this->line('');

            // Commit transaction
            DB::commit();

            // Show summary
            $this->info('✅ Data deletion completed successfully!');
            $this->line('');
            $this->table(
                ['Table', 'Records Deleted'],
                [
                    ['Applicants', $applicantCount],
                    ['Access Codes', $accessCodeCount],
                    ['Interviews', $interviewCount],
                    ['Exam Results', $resultCount],
                ]
            );

            $this->line('');
            $this->info('You can now test with fresh data.');

        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            
            $this->error('❌ Error occurred during deletion:');
            $this->error($e->getMessage());
            
            return 1;
        }

        return 0;
    }
}
