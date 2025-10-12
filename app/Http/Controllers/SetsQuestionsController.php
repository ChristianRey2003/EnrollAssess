<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SetsQuestionsController extends Controller
{
    /**
     * Display the Question Bank management interface.
     */
    public function index(Request $request)
    {
        // Single-exam mode: pick active exam, or latest if none active
        $currentExam = Exam::where('is_active', true)->first() ?? Exam::latest()->first();
        
        $questions = collect();
        $examSets = collect(); // Empty collection for backward compatibility with view
        $selectedSet = null;
        
        if ($currentExam) {
            // Get all questions for the current exam (Question Bank approach)
            $questions = Question::where('exam_id', $currentExam->exam_id)
                ->with('options')
                ->orderBy('order_number')
                ->get();
        }
        
        // Calculate statistics
        $stats = [
            'total_questions' => $questions->count(),
            'active_questions' => $questions->where('is_active', true)->count(),
            'mcq_count' => $questions->where('question_type', 'multiple_choice')->count(),
            'tf_count' => $questions->where('question_type', 'true_false')->count(),
            'draft_questions' => $questions->where('is_active', false)->count(),
        ];
        
        return view('admin.sets-questions', compact('currentExam', 'questions', 'stats', 'examSets', 'selectedSet'));
    }
    
    /**
     * Create a new semester by duplicating or starting fresh.
     */
    public function newSemester(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'semester_option' => 'required|in:duplicate,fresh'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $validator->errors()->first()
            ]);
        }
        
        try {
            DB::transaction(function () use ($request) {
                // Archive current exam if exists
                $currentExam = Exam::where('is_active', true)->first();
                if ($currentExam) {
                    $currentExam->update(['is_active' => false]);
                }
                
                // Create new exam
                $newExam = Exam::create([
                    'title' => $request->title,
                    'description' => $request->description,
                    'duration_minutes' => $currentExam->duration_minutes ?? 90,
                    'is_active' => false, // Start as draft
                ]);
                
                if ($request->semester_option === 'duplicate' && $currentExam) {
                    // Duplicate all sets and questions
                    $this->duplicateExamContent($currentExam, $newExam);
                }
            });
            
            return response()->json([
                'success' => true,
                'message' => 'New semester created successfully!'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create new semester: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Publish the current exam (make it active for applicants).
     */
    public function publishExam($id)
    {
        try {
            $exam = Exam::findOrFail($id);
            
            // Validation checks before publishing
            $validationErrors = $this->validateExamForPublishing($exam);
            if (!empty($validationErrors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot publish exam: ' . implode(', ', $validationErrors)
                ]);
            }
            
            // Deactivate other exams and activate this one
            Exam::where('exam_id', '!=', $exam->exam_id)->update(['is_active' => false]);
            $exam->update(['is_active' => true]);
            
            return response()->json([
                'success' => true,
                'message' => 'Exam published successfully!'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to publish exam: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Archive old exams and their data.
     */
    public function archiveOldExams()
    {
        try {
            $currentExam = Exam::where('is_active', true)->first();
            $oldExams = Exam::where('is_active', false)
                ->where('created_at', '<', now()->subMonths(6))
                ->get();
            
            if ($oldExams->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No old exams to archive.',
                    'archived_count' => 0
                ]);
            }
            
            DB::transaction(function () use ($oldExams) {
                foreach ($oldExams as $exam) {
                    // Mark exam as archived (you could add an 'archived' column)
                    $exam->update([
                        'description' => '[ARCHIVED] ' . $exam->description,
                        'is_active' => false
                    ]);
                    
                    // Optionally, you could move data to archive tables
                    // or just keep them marked as archived
                }
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Archived ' . $oldExams->count() . ' old exams successfully!',
                'archived_count' => $oldExams->count()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to archive old exams: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Run consistency checks on the exam.
     */
    public function consistencyCheck($id)
    {
        try {
            $exam = Exam::with(['questions.options'])->findOrFail($id);
            $issues = [];
            
            // Check for duplicate questions
            $duplicateQuestions = $exam->questions->groupBy('question_text')
                ->filter(function($group) { return $group->count() > 1; })
                ->keys();
            
            if ($duplicateQuestions->count() > 0) {
                $issues[] = [
                    'type' => 'duplicate_questions',
                    'message' => 'Found ' . $duplicateQuestions->count() . ' duplicate questions',
                    'details' => $duplicateQuestions->take(5)->toArray()
                ];
            }
            
            // Check for questions without correct answers
            $questionsWithoutAnswers = $exam->questions->filter(function($question) {
                if ($question->question_type === 'multiple_choice') {
                    return $question->options->where('is_correct', true)->count() === 0;
                }
                return false;
            });
            
            if ($questionsWithoutAnswers->count() > 0) {
                $issues[] = [
                    'type' => 'missing_answers',
                    'message' => 'Found ' . $questionsWithoutAnswers->count() . ' questions without correct answers',
                    'details' => $questionsWithoutAnswers->take(5)->pluck('question_text')->toArray()
                ];
            }
            
            // Check quota compliance
            $validation = $exam->validateQuotas();
            if (!empty($validation)) {
                $issues[] = [
                    'type' => 'quota_mismatch',
                    'message' => 'Quota validation errors',
                    'details' => $validation
                ];
            }
            
            return response()->json([
                'success' => true,
                'issues' => $issues,
                'total_issues' => count($issues)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to run consistency check: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Duplicate exam content from one exam to another.
     */
    private function duplicateExamContent($sourceExam, $targetExam)
    {
        foreach ($sourceExam->questions as $originalQuestion) {
            $newQuestion = Question::create([
                'exam_id' => $targetExam->exam_id,
                'question_text' => $originalQuestion->question_text,
                'question_type' => $originalQuestion->question_type,
                'points' => $originalQuestion->points,
                'order_number' => $originalQuestion->order_number,
                'explanation' => $originalQuestion->explanation,
                'is_active' => false, // Start as draft
            ]);
            
            // Duplicate question options
            foreach ($originalQuestion->options as $originalOption) {
                QuestionOption::create([
                    'question_id' => $newQuestion->question_id,
                    'option_text' => $originalOption->option_text,
                    'is_correct' => $originalOption->is_correct,
                    'order_number' => $originalOption->order_number,
                ]);
            }
        }
    }
    
    /**
     * Validate exam before publishing.
     */
    private function validateExamForPublishing($exam)
    {
        $errors = [];
        
        // Check if exam has questions
        if ($exam->questions->count() === 0) {
            $errors[] = 'Exam must have at least one question in the question bank';
        }
        
        // Check quota validation
        $quotaErrors = $exam->validateQuotas();
        $errors = array_merge($errors, $quotaErrors);
        
        // Check if each multiple choice question has a correct answer
        foreach ($exam->questions as $question) {
            if ($question->question_type === 'multiple_choice') {
                $correctAnswers = $question->options->where('is_correct', true)->count();
                if ($correctAnswers === 0) {
                    $errors[] = "Question '{$question->question_text}' has no correct answer";
                }
                if ($correctAnswers > 1) {
                    $errors[] = "Question '{$question->question_text}' has multiple correct answers";
                }
            }
        }
        
        return $errors;
    }
}
