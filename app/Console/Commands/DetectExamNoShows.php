<?php

namespace App\Console\Commands;

use App\Models\ExamSchedule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DetectExamNoShows extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exam:detect-no-shows';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detect and mark applicants who did not show up for their scheduled exam (runs daily)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting exam no-show detection...');

        // Get schedules that need no-show detection
        // Criteria: scheduled_date was yesterday or earlier, status is still 'scheduled', 
        // and applicant has not completed the exam
        $yesterday = now()->subDay()->toDateString();
        
        $schedules = ExamSchedule::where('status', 'scheduled')
            ->where('scheduled_date', '<=', $yesterday)
            ->whereHas('applicant', function($query) {
                $query->where('status', 'exam-scheduled')
                      ->whereNull('exam_completed_at');
            })
            ->get();

        $noShowCount = 0;
        $errors = [];

        foreach ($schedules as $schedule) {
            try {
                DB::transaction(function () use ($schedule, &$noShowCount) {
                    // Mark schedule as no-show
                    $schedule->markAsNoShow();
                    
                    $noShowCount++;
                    
                    // Log the action
                    Log::info("Exam no-show detected", [
                        'schedule_id' => $schedule->exam_schedule_id,
                        'applicant_id' => $schedule->applicant_id,
                        'applicant_name' => $schedule->applicant->full_name,
                        'scheduled_date' => $schedule->scheduled_date->format('Y-m-d'),
                        'scheduled_time' => $schedule->scheduled_time,
                    ]);
                });
            } catch (\Exception $e) {
                $errors[] = "Failed to mark schedule #{$schedule->exam_schedule_id} as no-show: {$e->getMessage()}";
                Log::error("Failed to detect no-show", [
                    'schedule_id' => $schedule->exam_schedule_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        if ($noShowCount > 0) {
            $this->info("Detected {$noShowCount} no-show(s).");
        } else {
            $this->info('No no-shows detected.');
        }

        if (count($errors) > 0) {
            $this->error('Errors occurred:');
            foreach ($errors as $error) {
                $this->error("  - {$error}");
            }
        }

        return Command::SUCCESS;
    }
}
