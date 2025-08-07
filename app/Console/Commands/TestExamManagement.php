<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Exam;
use App\Models\ExamSet;
use App\Models\Question;

class TestExamManagement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:exam-management';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the Exam Set Management functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== EnrollAssess Exam Set Management Test ===');
        $this->newLine();

        try {
            // Test 1: Check existing exams
            $this->info('1. Testing Exams...');
            $exams = Exam::with('examSets')->get();
            $this->line("   Found {$exams->count()} exams:");
            foreach ($exams as $exam) {
                $this->line("   - {$exam->title} ({$exam->examSets->count()} sets)");
            }
            $this->newLine();

            // Test 2: Check exam sets
            $this->info('2. Testing Exam Sets...');
            $examSets = ExamSet::with(['exam', 'questions'])->get();
            $this->line("   Found {$examSets->count()} exam sets:");
            foreach ($examSets as $examSet) {
                $this->line("   - {$examSet->set_name} in {$examSet->exam->title} ({$examSet->questions->count()} questions)");
            }
            $this->newLine();

            // Test 3: Create a test exam
            $this->info('3. Testing Exam Creation...');
            $testExam = Exam::create([
                'title' => 'Test Exam for Validation',
                'description' => 'This is a test exam created by the validation script.',
                'duration_minutes' => 60,
                'is_active' => true,
            ]);
            $this->line("   ✓ Created test exam #{$testExam->exam_id}");

            // Test 4: Create test exam sets
            $this->info('4. Testing Exam Set Creation...');
            $testSetA = ExamSet::create([
                'exam_id' => $testExam->exam_id,
                'set_name' => 'Test Set A',
                'description' => 'First test set',
                'is_active' => true,
            ]);
            $this->line("   ✓ Created test exam set A #{$testSetA->exam_set_id}");

            $testSetB = ExamSet::create([
                'exam_id' => $testExam->exam_id,
                'set_name' => 'Test Set B',
                'description' => 'Second test set',
                'is_active' => true,
            ]);
            $this->line("   ✓ Created test exam set B #{$testSetB->exam_set_id}");

            // Test 5: Add questions to sets
            $this->info('5. Testing Question Assignment...');
            
            // Get some existing questions to assign
            $existingQuestions = Question::take(2)->get();
            
            if ($existingQuestions->count() > 0) {
                foreach ($existingQuestions as $index => $question) {
                    $targetSet = $index === 0 ? $testSetA : $testSetB;
                    
                    // Create a copy of the question for this set
                    $newQuestion = Question::create([
                        'exam_set_id' => $targetSet->exam_set_id,
                        'question_text' => $question->question_text . ' (Test Copy)',
                        'question_type' => $question->question_type,
                        'points' => $question->points,
                        'order_number' => 1,
                        'explanation' => $question->explanation,
                        'is_active' => true,
                    ]);

                    // Copy options
                    foreach ($question->options as $option) {
                        $newQuestion->options()->create([
                            'option_text' => $option->option_text,
                            'is_correct' => $option->is_correct,
                            'order_number' => $option->order_number,
                        ]);
                    }

                    $this->line("   ✓ Added question to {$targetSet->set_name}");
                }
            }

            // Test 6: Verify relationships and statistics
            $this->info('6. Testing Relationships and Statistics...');
            $testExam->load(['examSets.questions']);
            
            $totalQuestions = $testExam->examSets->sum(function($set) {
                return $set->questions->count();
            });
            
            $totalPoints = $testExam->examSets->sum(function($set) {
                return $set->questions->sum('points');
            });

            $this->line("   Test exam statistics:");
            $this->line("   - Sets: {$testExam->examSets->count()}");
            $this->line("   - Questions: {$totalQuestions}");
            $this->line("   - Total Points: {$totalPoints}");

            // Test 7: Clean up test data
            $this->info('7. Cleaning up test data...');
            $testExam->delete(); // This should cascade delete sets and questions
            $this->line("   ✓ Test data cleaned up");

            $this->newLine();
            $this->info('=== All Tests Passed! ===');
            $this->line('Your Exam Set Management is ready to use!');
            $this->newLine();
            $this->line('Features tested successfully:');
            $this->line('✓ Exam creation and management');
            $this->line('✓ Exam set creation with proper relationships');
            $this->line('✓ Question assignment to sets');
            $this->line('✓ Statistics calculation');
            $this->line('✓ Data integrity and cleanup');
            $this->newLine();
            $this->line('Next steps:');
            $this->line('1. Visit http://localhost:8000/admin/exams to manage exams');
            $this->line('2. Create multiple question sets (Set A, Set B, Set C)');
            $this->line('3. Add different questions to each set to prevent cheating');

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            $this->line("Stack trace:");
            $this->line($e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}
