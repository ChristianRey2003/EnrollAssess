<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Result;
use Illuminate\Support\Facades\DB;

class ClearQuestionData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'questions:clear {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all question data including related records (question options, results)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if force flag is used
        if (!$this->option('force')) {
            // Show warning and ask for confirmation
            $this->warn('⚠️  WARNING: This will permanently delete ALL question data!');
            $this->line('This includes:');
            $this->line('  • All questions');
            $this->line('  • All question options');
            $this->line('  • All exam results (answers to questions)');
            $this->line('');
            
            if (!$this->confirm('Are you sure you want to proceed?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        // Start transaction for safety
        DB::beginTransaction();
        
        try {
            // Get counts before deletion for reporting
            $questionCount = Question::count();
            $optionCount = QuestionOption::count();
            $resultCount = Result::count();

            $this->info('Starting data deletion...');
            
            // Show progress bar
            $progressBar = $this->output->createProgressBar(3);
            $progressBar->start();

            // Delete in order to respect foreign key constraints
            // Results first (references questions)
            $this->line('');
            $this->info('Deleting exam results...');
            Result::query()->delete();
            $progressBar->advance();

            // Question options second (references questions, but cascade will handle it)
            // However, we'll delete them explicitly for clarity
            $this->info('Deleting question options...');
            QuestionOption::query()->delete();
            $progressBar->advance();

            // Questions last (main table)
            $this->info('Deleting questions...');
            Question::query()->delete();
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
                    ['Questions', $questionCount],
                    ['Question Options', $optionCount],
                    ['Exam Results', $resultCount],
                ]
            );

            $this->line('');
            $this->info('You can now add fresh questions.');

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


