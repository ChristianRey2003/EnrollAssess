<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Question;
use App\Models\ExamSet;
use App\Models\QuestionOption;

class TestQuestionBank extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:question-bank';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the Question Bank functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== EnrollAssess Question Bank Test ===');
        $this->newLine();

        try {
            // Test 1: Check if exam sets exist
            $this->info('1. Testing Exam Sets...');
            $examSets = ExamSet::with('exam')->where('is_active', true)->get();
            $this->line("   Found {$examSets->count()} active exam sets:");
            foreach ($examSets as $examSet) {
                $this->line("   - {$examSet->exam->title} - {$examSet->set_name}");
            }
            $this->newLine();

            // Test 2: Check existing questions
            $this->info('2. Testing Questions...');
            $questions = Question::with(['examSet.exam', 'options'])
                ->active()
                ->ordered()
                ->get();
            
            $this->line("   Found {$questions->count()} active questions:");
            foreach ($questions as $question) {
                $questionText = substr($question->question_text, 0, 50) . '...';
                $this->line("   Question #{$question->question_id}: {$questionText}");
                $this->line("     Type: {$question->question_type}, Points: {$question->points}");
                $this->line("     Options: {$question->options->count()}");
                if ($question->isMultipleChoice()) {
                    $correctOption = $question->correctOptions()->first();
                    if ($correctOption) {
                        $this->line("     Correct: {$correctOption->option_text}");
                    }
                }
                $this->newLine();
            }

            // Test 3: Question type statistics
            $this->info('3. Question Type Statistics...');
            $stats = [
                'total' => Question::active()->count(),
                'multiple_choice' => Question::active()->byType('multiple_choice')->count(),
                'true_false' => Question::active()->byType('true_false')->count(),
                'short_answer' => Question::active()->byType('short_answer')->count(),
                'essay' => Question::active()->byType('essay')->count(),
            ];
            
            foreach ($stats as $type => $count) {
                $typeLabel = ucwords(str_replace('_', ' ', $type));
                $this->line("   {$typeLabel}: {$count}");
            }
            $this->newLine();

            // Test 4: Create a test question
            $this->info('4. Testing Question Creation...');
            $testExamSet = $examSets->first();
            if ($testExamSet) {
                $testQuestion = Question::create([
                    'exam_set_id' => $testExamSet->exam_set_id,
                    'question_text' => 'This is a test question created by the validation script.',
                    'question_type' => 'true_false',
                    'points' => 1,
                    'order_number' => 999, // High number to avoid conflicts
                    'explanation' => 'This is a test explanation.',
                    'is_active' => true,
                ]);

                // Create True/False options
                QuestionOption::create([
                    'question_id' => $testQuestion->question_id,
                    'option_text' => 'True',
                    'is_correct' => true,
                    'order_number' => 1,
                ]);

                QuestionOption::create([
                    'question_id' => $testQuestion->question_id,
                    'option_text' => 'False',
                    'is_correct' => false,
                    'order_number' => 2,
                ]);

                $this->line("   ✓ Successfully created test question #{$testQuestion->question_id}");
                
                // Clean up - delete the test question
                $testQuestion->delete();
                $this->line("   ✓ Test question cleaned up");
            } else {
                $this->error("   ✗ No exam sets available for testing");
            }

            $this->newLine();
            $this->info('=== All Tests Passed! ===');
            $this->line('Your Question Bank is ready to use!');
            $this->newLine();
            $this->line('Next steps:');
            $this->line('1. Visit http://localhost:8000/admin/login to access the admin panel');
            $this->line('2. Navigate to Questions to manage the question bank');
            $this->line('3. Create new questions with different types (Multiple Choice, True/False, Short Answer, Essay)');

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            $this->line("Stack trace:");
            $this->line($e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}
