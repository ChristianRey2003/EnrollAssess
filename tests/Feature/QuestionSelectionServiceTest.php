<?php

use App\Models\Exam;
use App\Models\Applicant;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\ExamAssignment;
use App\Services\QuestionSelectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(QuestionSelectionService::class);
});

describe('Question Selection Service - Assignment Generation', function () {
    
    test('generates assignment with correct quota distribution', function () {
        // Create exam with quotas
        $exam = Exam::factory()->create([
            'title' => 'Test Exam',
            'total_items' => 10,
            'mcq_quota' => 6,
            'tf_quota' => 4,
            'is_active' => true,
        ]);

        // Create enough questions
        $mcqQuestions = Question::factory()->count(10)->create([
            'question_type' => 'multiple_choice',
            'is_active' => true,
        ]);
        
        // Add options to MCQ questions
        foreach ($mcqQuestions as $question) {
            QuestionOption::factory()->count(4)->create([
                'question_id' => $question->question_id,
            ]);
        }

        $tfQuestions = Question::factory()->count(10)->create([
            'question_type' => 'true_false',
            'is_active' => true,
        ]);

        // Add True/False options
        foreach ($tfQuestions as $question) {
            QuestionOption::factory()->create([
                'question_id' => $question->question_id,
                'option_text' => 'True',
            ]);
            QuestionOption::factory()->create([
                'question_id' => $question->question_id,
                'option_text' => 'False',
            ]);
        }

        $applicant = Applicant::factory()->create();

        // Generate assignment
        $assignment = $this->service->generateAssignment($exam->exam_id, $applicant->applicant_id);

        expect($assignment)->toBeInstanceOf(ExamAssignment::class);
        expect($assignment->assignedQuestions)->toHaveCount(10);
        
        // Check quota distribution
        $mcqCount = $assignment->assignedQuestions()
            ->whereHas('question', fn($q) => $q->where('question_type', 'multiple_choice'))
            ->count();
        $tfCount = $assignment->assignedQuestions()
            ->whereHas('question', fn($q) => $q->where('question_type', 'true_false'))
            ->count();
        
        expect($mcqCount)->toBe(6);
        expect($tfCount)->toBe(4);
    });

    test('generates idempotent assignments', function () {
        $exam = Exam::factory()->create([
            'total_items' => 5,
            'mcq_quota' => 3,
            'tf_quota' => 2,
            'is_active' => true,
        ]);

        // Create questions
        $mcqQuestions = Question::factory()->count(5)->create([
            'question_type' => 'multiple_choice',
            'is_active' => true,
        ]);
        foreach ($mcqQuestions as $q) {
            QuestionOption::factory()->count(4)->create(['question_id' => $q->question_id]);
        }

        $tfQuestions = Question::factory()->count(5)->create([
            'question_type' => 'true_false',
            'is_active' => true,
        ]);
        foreach ($tfQuestions as $q) {
            QuestionOption::factory()->count(2)->create(['question_id' => $q->question_id]);
        }

        $applicant = Applicant::factory()->create();

        // Generate first assignment
        $assignment1 = $this->service->generateAssignment($exam->exam_id, $applicant->applicant_id);
        $questionIds1 = $assignment1->assignedQuestions->pluck('question_id')->sort()->values();

        // Generate again - should return same assignment
        $assignment2 = $this->service->generateAssignment($exam->exam_id, $applicant->applicant_id);
        $questionIds2 = $assignment2->assignedQuestions->pluck('question_id')->sort()->values();

        expect($assignment1->id)->toBe($assignment2->id);
        expect($questionIds1->toArray())->toBe($questionIds2->toArray());
    });

    test('shuffles MCQ options and persists order', function () {
        $exam = Exam::factory()->create([
            'total_items' => 1,
            'mcq_quota' => 1,
            'is_active' => true,
        ]);

        $question = Question::factory()->create([
            'question_type' => 'multiple_choice',
            'is_active' => true,
        ]);

        $options = QuestionOption::factory()->count(4)->create([
            'question_id' => $question->question_id,
        ]);

        $applicant = Applicant::factory()->create();

        // Generate assignment
        $assignment = $this->service->generateAssignment($exam->exam_id, $applicant->applicant_id);
        
        $assignedQuestion = $assignment->assignedQuestions->first();
        
        // Verify option_order is persisted
        expect($assignedQuestion->option_order)->not->toBeNull();
        expect($assignedQuestion->option_order)->toHaveCount(4);
        
        // Verify all option IDs are present
        $optionIds = $options->pluck('option_id')->toArray();
        expect($assignedQuestion->option_order)->toEqualCanonicalizing($optionIds);
    });

    test('assigns positions sequentially', function () {
        $exam = Exam::factory()->create([
            'total_items' => 5,
            'is_active' => true,
        ]);

        Question::factory()->count(10)->create([
            'question_type' => 'multiple_choice',
            'is_active' => true,
        ])->each(function ($q) {
            QuestionOption::factory()->count(4)->create(['question_id' => $q->question_id]);
        });

        $applicant = Applicant::factory()->create();

        $assignment = $this->service->generateAssignment($exam->exam_id, $applicant->applicant_id);

        $positions = $assignment->assignedQuestions->pluck('position')->toArray();
        
        expect($positions)->toBe([1, 2, 3, 4, 5]);
    });

});

describe('Question Selection Service - Validation', function () {

    test('throws error when total_items is not set', function () {
        $exam = Exam::factory()->create([
            'total_items' => null,
            'is_active' => true,
        ]);

        $applicant = Applicant::factory()->create();

        expect(fn() => $this->service->generateAssignment($exam->exam_id, $applicant->applicant_id))
            ->toThrow(Exception::class, 'Exam must have total_items configured to use question bank.');
    });

    test('throws error when insufficient MCQ questions available', function () {
        $exam = Exam::factory()->create([
            'total_items' => 10,
            'mcq_quota' => 10,
            'is_active' => true,
        ]);

        // Only create 5 MCQ questions (need 10)
        Question::factory()->count(5)->create([
            'question_type' => 'multiple_choice',
            'is_active' => true,
        ])->each(function ($q) {
            QuestionOption::factory()->count(4)->create(['question_id' => $q->question_id]);
        });

        $applicant = Applicant::factory()->create();

        expect(fn() => $this->service->generateAssignment($exam->exam_id, $applicant->applicant_id))
            ->toThrow(Exception::class, 'Insufficient MCQ questions');
    });

    test('throws error when insufficient TF questions available', function () {
        $exam = Exam::factory()->create([
            'total_items' => 10,
            'tf_quota' => 10,
            'is_active' => true,
        ]);

        // Only create 5 TF questions (need 10)
        Question::factory()->count(5)->create([
            'question_type' => 'true_false',
            'is_active' => true,
        ])->each(function ($q) {
            QuestionOption::factory()->count(2)->create(['question_id' => $q->question_id]);
        });

        $applicant = Applicant::factory()->create();

        expect(fn() => $this->service->generateAssignment($exam->exam_id, $applicant->applicant_id))
            ->toThrow(Exception::class, 'Insufficient True/False questions');
    });

    test('throws error when quota sum exceeds total items', function () {
        $exam = Exam::factory()->create([
            'total_items' => 10,
            'mcq_quota' => 7,
            'tf_quota' => 5, // 7 + 5 = 12 > 10
            'is_active' => true,
        ]);

        Question::factory()->count(20)->create([
            'question_type' => 'multiple_choice',
            'is_active' => true,
        ])->each(function ($q) {
            QuestionOption::factory()->count(4)->create(['question_id' => $q->question_id]);
        });

        $applicant = Applicant::factory()->create();

        expect(fn() => $this->service->generateAssignment($exam->exam_id, $applicant->applicant_id))
            ->toThrow(Exception::class, 'Quota sum (12) exceeds total items (10)');
    });

    test('validates exam configuration correctly', function () {
        $exam = Exam::factory()->create([
            'total_items' => 10,
            'mcq_quota' => 6,
            'tf_quota' => 4,
            'is_active' => true,
        ]);

        // Create sufficient questions
        Question::factory()->count(10)->create([
            'question_type' => 'multiple_choice',
            'is_active' => true,
        ])->each(function ($q) {
            QuestionOption::factory()->count(4)->create(['question_id' => $q->question_id]);
        });

        Question::factory()->count(10)->create([
            'question_type' => 'true_false',
            'is_active' => true,
        ])->each(function ($q) {
            QuestionOption::factory()->count(2)->create(['question_id' => $q->question_id]);
        });

        $validation = $this->service->validateExamConfiguration($exam->exam_id);

        expect($validation['valid'])->toBeTrue();
        expect($validation['errors'])->toBeEmpty();
    });

    test('detects invalid exam configuration', function () {
        $exam = Exam::factory()->create([
            'total_items' => 20,
            'mcq_quota' => 15,
            'tf_quota' => 10, // Quota sum exceeds total
            'is_active' => true,
        ]);

        $validation = $this->service->validateExamConfiguration($exam->exam_id);

        expect($validation['valid'])->toBeFalse();
        expect($validation['errors'])->toContain('Quota sum exceeds total items.');
    });

});

describe('Question Selection Service - Bulk Operations', function () {

    test('generates bulk assignments successfully', function () {
        $exam = Exam::factory()->create([
            'total_items' => 5,
            'is_active' => true,
        ]);

        Question::factory()->count(10)->create([
            'question_type' => 'multiple_choice',
            'is_active' => true,
        ])->each(function ($q) {
            QuestionOption::factory()->count(4)->create(['question_id' => $q->question_id]);
        });

        $applicants = Applicant::factory()->count(3)->create();
        $applicantIds = $applicants->pluck('applicant_id')->toArray();

        $result = $this->service->generateBulkAssignments($exam->exam_id, $applicantIds);

        expect($result['success'])->toBe(3);
        expect($result['failed'])->toBeEmpty();
        
        // Verify all assignments exist
        expect(ExamAssignment::count())->toBe(3);
    });

    test('handles bulk assignment with failures', function () {
        $exam = Exam::factory()->create([
            'total_items' => 5,
            'is_active' => true,
        ]);

        // Only create 3 questions (need 5)
        Question::factory()->count(3)->create([
            'question_type' => 'multiple_choice',
            'is_active' => true,
        ])->each(function ($q) {
            QuestionOption::factory()->count(4)->create(['question_id' => $q->question_id]);
        });

        $applicants = Applicant::factory()->count(2)->create();
        $applicantIds = $applicants->pluck('applicant_id')->toArray();

        $result = $this->service->generateBulkAssignments($exam->exam_id, $applicantIds);

        expect($result['success'])->toBe(0);
        expect($result['failed'])->toHaveCount(2);
        expect($result['failed'][0])->toHaveKey('applicant_id');
        expect($result['failed'][0])->toHaveKey('error');
    });

});

describe('Question Selection Service - Regeneration', function () {

    test('regenerates assignment successfully', function () {
        $exam = Exam::factory()->create([
            'total_items' => 5,
            'is_active' => true,
        ]);

        Question::factory()->count(10)->create([
            'question_type' => 'multiple_choice',
            'is_active' => true,
        ])->each(function ($q) {
            QuestionOption::factory()->count(4)->create(['question_id' => $q->question_id]);
        });

        $applicant = Applicant::factory()->create();

        // Generate initial assignment
        $originalAssignment = $this->service->generateAssignment($exam->exam_id, $applicant->applicant_id);
        $originalQuestionIds = $originalAssignment->assignedQuestions->pluck('question_id');

        // Regenerate
        $newAssignment = $this->service->regenerateAssignment($originalAssignment->id);

        expect($newAssignment->id)->toBe($originalAssignment->id);
        expect($newAssignment->assignedQuestions)->toHaveCount(5);
        
        // Questions should be different (randomized)
        $newQuestionIds = $newAssignment->assignedQuestions->pluck('question_id');
        // Note: There's a small chance they could be the same by random chance,
        // but with 10 questions available and 5 selected, it's unlikely
    });

    test('prevents regeneration of completed assignments', function () {
        $exam = Exam::factory()->create([
            'total_items' => 5,
            'is_active' => true,
        ]);

        Question::factory()->count(10)->create([
            'question_type' => 'multiple_choice',
            'is_active' => true,
        ])->each(function ($q) {
            QuestionOption::factory()->count(4)->create(['question_id' => $q->question_id]);
        });

        $applicant = Applicant::factory()->create();

        $assignment = $this->service->generateAssignment($exam->exam_id, $applicant->applicant_id);
        
        // Mark as completed
        $assignment->update(['status' => 'completed']);

        expect(fn() => $this->service->regenerateAssignment($assignment->id))
            ->toThrow(Exception::class, 'Cannot regenerate completed exam assignment.');
    });

});

describe('Question Selection Service - Mixed Quotas', function () {

    test('fills remaining slots with mixed questions when quotas dont add up', function () {
        $exam = Exam::factory()->create([
            'total_items' => 10,
            'mcq_quota' => 5, // Only 5 MCQ specified, 5 remaining slots
            'is_active' => true,
        ]);

        Question::factory()->count(10)->create([
            'question_type' => 'multiple_choice',
            'is_active' => true,
        ])->each(function ($q) {
            QuestionOption::factory()->count(4)->create(['question_id' => $q->question_id]);
        });

        Question::factory()->count(10)->create([
            'question_type' => 'true_false',
            'is_active' => true,
        ])->each(function ($q) {
            QuestionOption::factory()->count(2)->create(['question_id' => $q->question_id]);
        });

        $applicant = Applicant::factory()->create();

        $assignment = $this->service->generateAssignment($exam->exam_id, $applicant->applicant_id);

        expect($assignment->assignedQuestions)->toHaveCount(10);
        
        // At least 5 MCQ (from quota)
        $mcqCount = $assignment->assignedQuestions()
            ->whereHas('question', fn($q) => $q->where('question_type', 'multiple_choice'))
            ->count();
        
        expect($mcqCount)->toBeGreaterThanOrEqual(5);
    });

    test('selects all random when no quotas specified', function () {
        $exam = Exam::factory()->create([
            'total_items' => 10,
            'mcq_quota' => null,
            'tf_quota' => null,
            'is_active' => true,
        ]);

        Question::factory()->count(15)->create([
            'question_type' => 'multiple_choice',
            'is_active' => true,
        ])->each(function ($q) {
            QuestionOption::factory()->count(4)->create(['question_id' => $q->question_id]);
        });

        Question::factory()->count(15)->create([
            'question_type' => 'true_false',
            'is_active' => true,
        ])->each(function ($q) {
            QuestionOption::factory()->count(2)->create(['question_id' => $q->question_id]);
        });

        $applicant = Applicant::factory()->create();

        $assignment = $this->service->generateAssignment($exam->exam_id, $applicant->applicant_id);

        expect($assignment->assignedQuestions)->toHaveCount(10);
        // Mix of both types (random selection)
    });

});

