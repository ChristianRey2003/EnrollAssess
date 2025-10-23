<?php

use App\Models\Exam;
use App\Models\Applicant;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\AccessCode;
use App\Models\Result;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;

uses(RefreshDatabase::class);

describe('Exam Scoring - Assigned Questions Only', function () {
    
    test('scores exam based only on assigned questions', function () {
        // Create exam with 30 total items
        $exam = Exam::factory()->create([
            'title' => 'Test Exam',
            'total_items' => 30,
            'mcq_quota' => 20,
            'tf_quota' => 10,
            'duration_minutes' => 60,
            'is_active' => true,
        ]);

        // Create 53 questions in the question bank
        $mcqQuestions = Question::factory()->count(40)->create([
            'exam_id' => $exam->exam_id,
            'question_type' => 'multiple_choice',
            'points' => 1,
            'is_active' => true,
        ]);
        
        foreach ($mcqQuestions as $question) {
            QuestionOption::factory()->count(4)->create([
                'question_id' => $question->question_id,
                'is_correct' => false,
            ]);
            // Set one option as correct
            QuestionOption::factory()->create([
                'question_id' => $question->question_id,
                'is_correct' => true,
            ]);
        }

        $tfQuestions = Question::factory()->count(13)->create([
            'exam_id' => $exam->exam_id,
            'question_type' => 'true_false',
            'points' => 1,
            'correct_answer' => true,
            'is_active' => true,
        ]);

        // Create applicant with access code
        $applicant = Applicant::factory()->create();
        $accessCode = AccessCode::factory()->create([
            'applicant_id' => $applicant->applicant_id,
            'exam_id' => $exam->exam_id,
            'is_used' => false,
        ]);

        // Simulate exam session with 30 assigned questions (20 MCQ + 10 TF)
        $assignedMcqIds = $mcqQuestions->take(20)->pluck('question_id')->toArray();
        $assignedTfIds = $tfQuestions->take(10)->pluck('question_id')->toArray();
        $assignedQuestionIds = array_merge($assignedMcqIds, $assignedTfIds);

        Session::put('exam_session', [
            'applicant_id' => $applicant->applicant_id,
            'exam_id' => $exam->exam_id,
            'question_ids' => $assignedQuestionIds,
            'started_at' => now()->toDateTimeString(),
            'duration_minutes' => 60,
        ]);

        // Create answers for the 30 assigned questions (all correct)
        $answers = [];
        foreach ($assignedMcqIds as $questionId) {
            $correctOption = QuestionOption::where('question_id', $questionId)
                ->where('is_correct', true)
                ->first();
            $answers[$questionId] = $correctOption->option_id;
        }
        foreach ($assignedTfIds as $questionId) {
            $answers[$questionId] = 'true_' . $questionId;
        }

        // Submit exam
        $response = $this->postJson(route('exam.complete'), [
            'applicant_id' => $applicant->applicant_id,
            'answers' => $answers,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total_questions' => 30, // Should be 30, not 53
            'correct_answers' => 30,
            'max_score' => 30,
            'total_score' => 30,
            'score' => 100,
        ]);

        // Verify only 30 results were stored
        $storedResults = Result::where('applicant_id', $applicant->applicant_id)->count();
        expect($storedResults)->toBe(30);
    });

    test('ignores extra answers not in assigned questions', function () {
        $exam = Exam::factory()->create([
            'total_items' => 10,
            'is_active' => true,
        ]);

        $questions = Question::factory()->count(20)->create([
            'exam_id' => $exam->exam_id,
            'question_type' => 'multiple_choice',
            'points' => 1,
            'is_active' => true,
        ]);
        
        foreach ($questions as $question) {
            QuestionOption::factory()->count(4)->create([
                'question_id' => $question->question_id,
                'is_correct' => false,
            ]);
            QuestionOption::factory()->create([
                'question_id' => $question->question_id,
                'is_correct' => true,
            ]);
        }

        $applicant = Applicant::factory()->create();
        AccessCode::factory()->create([
            'applicant_id' => $applicant->applicant_id,
            'exam_id' => $exam->exam_id,
            'is_used' => false,
        ]);

        // Only 10 questions assigned
        $assignedQuestionIds = $questions->take(10)->pluck('question_id')->toArray();

        Session::put('exam_session', [
            'applicant_id' => $applicant->applicant_id,
            'exam_id' => $exam->exam_id,
            'question_ids' => $assignedQuestionIds,
            'started_at' => now()->toDateTimeString(),
            'duration_minutes' => 60,
        ]);

        // Create answers for all 20 questions (including non-assigned ones)
        $answers = [];
        foreach ($questions as $question) {
            $correctOption = QuestionOption::where('question_id', $question->question_id)
                ->where('is_correct', true)
                ->first();
            $answers[$question->question_id] = $correctOption->option_id;
        }

        $response = $this->postJson(route('exam.complete'), [
            'applicant_id' => $applicant->applicant_id,
            'answers' => $answers,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total_questions' => 10, // Should count only assigned questions
            'max_score' => 10,
        ]);

        // Verify only 10 results were stored (not 20)
        $storedResults = Result::where('applicant_id', $applicant->applicant_id)->count();
        expect($storedResults)->toBe(10);
    });

    test('returns error when exam session is missing', function () {
        $applicant = Applicant::factory()->create();
        
        Session::forget('exam_session');

        $response = $this->postJson(route('exam.complete'), [
            'applicant_id' => $applicant->applicant_id,
            'answers' => [],
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Exam session expired or invalid. Please contact the administrator.',
        ]);
    });

    test('scores true_false questions correctly when submitted with option IDs', function () {
        $exam = Exam::factory()->create([
            'total_items' => 10,
            'tf_quota' => 10,
            'is_active' => true,
        ]);

        // Create 10 true/false questions each with two options: True/False
        $tfQuestions = Question::factory()->count(10)->create([
            'exam_id' => $exam->exam_id,
            'question_type' => 'true_false',
            'points' => 1,
            'correct_answer' => true,
            'is_active' => true,
        ]);

        foreach ($tfQuestions as $question) {
            // False option (incorrect)
            QuestionOption::factory()->create([
                'question_id' => $question->question_id,
                'option_text' => 'False',
                'is_correct' => false,
            ]);

            // True option (correct)
            QuestionOption::factory()->create([
                'question_id' => $question->question_id,
                'option_text' => 'True',
                'is_correct' => true,
            ]);
        }

        $applicant = Applicant::factory()->create();

        // Assign all 10 TF questions
        $assignedTfIds = $tfQuestions->pluck('question_id')->toArray();

        Session::put('exam_session', [
            'applicant_id' => $applicant->applicant_id,
            'exam_id' => $exam->exam_id,
            'question_ids' => $assignedTfIds,
            'started_at' => now()->toDateTimeString(),
            'duration_minutes' => 30,
        ]);

        // Answer using option IDs (correct option each)
        $answers = [];
        foreach ($assignedTfIds as $questionId) {
            $correctOption = QuestionOption::where('question_id', $questionId)
                ->where('is_correct', true)
                ->first();
            $answers[$questionId] = $correctOption->option_id;
        }

        $response = $this->postJson(route('exam.complete'), [
            'applicant_id' => $applicant->applicant_id,
            'answers' => $answers,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total_questions' => 10,
            'correct_answers' => 10,
            'max_score' => 10,
            'total_score' => 10,
            'score' => 100,
        ]);
    });

    test('clears exam session after successful submission', function () {
        $exam = Exam::factory()->create([
            'total_items' => 5,
            'is_active' => true,
        ]);

        $questions = Question::factory()->count(5)->create([
            'exam_id' => $exam->exam_id,
            'question_type' => 'multiple_choice',
            'points' => 1,
            'is_active' => true,
        ]);
        
        foreach ($questions as $question) {
            QuestionOption::factory()->create([
                'question_id' => $question->question_id,
                'is_correct' => true,
            ]);
        }

        $applicant = Applicant::factory()->create();
        AccessCode::factory()->create([
            'applicant_id' => $applicant->applicant_id,
            'exam_id' => $exam->exam_id,
            'is_used' => false,
        ]);

        $assignedQuestionIds = $questions->pluck('question_id')->toArray();

        Session::put('exam_session', [
            'applicant_id' => $applicant->applicant_id,
            'exam_id' => $exam->exam_id,
            'question_ids' => $assignedQuestionIds,
            'started_at' => now()->toDateTimeString(),
            'duration_minutes' => 60,
        ]);

        $answers = [];
        foreach ($questions as $question) {
            $correctOption = QuestionOption::where('question_id', $question->question_id)
                ->where('is_correct', true)
                ->first();
            $answers[$question->question_id] = $correctOption->option_id;
        }

        $response = $this->postJson(route('exam.complete'), [
            'applicant_id' => $applicant->applicant_id,
            'answers' => $answers,
        ]);

        $response->assertStatus(200);
        
        // Verify session was cleared
        expect(Session::has('exam_session'))->toBeFalse();
    });
});

describe('Exam Back Button Prevention', function () {
    
    test('exam routes have no-cache headers', function () {
        $applicant = Applicant::factory()->create();
        Session::put('applicant_id', $applicant->applicant_id);
        
        $response = $this->get(route('exam.interface'));

        $response->assertHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->assertHeader('Pragma', 'no-cache');
        $response->assertHeader('Expires', '0');
    });

    test('redirects to results page when exam is already completed', function () {
        $exam = Exam::factory()->create(['is_active' => true]);
        $applicant = Applicant::factory()->create([
            'exam_completed_at' => now(),
        ]);
        
        Session::put('applicant_id', $applicant->applicant_id);

        $response = $this->get(route('exam.interface'));

        $response->assertRedirect(route('exam.results'));
        $response->assertSessionHas('info', 'You have already completed the exam.');
    });
});

