<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamSet;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SetsQuestionsController extends Controller
{
    /**
     * Display the Sets & Questions management interface.
     */
    public function index(Request $request)
    {
        // Get the current active exam (assuming one exam system)
        $currentExam = Exam::where('is_active', true)->first() ?? Exam::latest()->first();
        
        $examSets = collect();
        $selectedSet = null;
        
        if ($currentExam) {
            // Get all exam sets for the current exam
            $examSets = ExamSet::where('exam_id', $currentExam->exam_id)
                ->with(['questions' => function($q) {
                    $q->with('options')->orderBy('order_number');
                }])
                ->orderBy('set_name')
                ->get();
            
            // Get selected set from request or default to first set
            $selectedSetId = $request->get('set');
            if ($selectedSetId) {
                $selectedSet = $examSets->where('exam_set_id', $selectedSetId)->first();
            } else {
                $selectedSet = $examSets->first();
            }
        }
        
        // Calculate statistics
        $stats = [
            'total_sets' => $examSets->count(),
            'active_sets' => $examSets->where('is_active', true)->count(),
            'total_questions' => $examSets->sum(function($set) {
                return $set->questions->count();
            }),
            'draft_questions' => $examSets->sum(function($set) {
                return $set->questions->where('is_active', false)->count();
            }),
        ];
        
        return view('admin.sets-questions', compact('currentExam', 'examSets', 'selectedSet', 'stats'));
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
            $exam = Exam::with(['examSets.questions.options'])->findOrFail($id);
            $issues = [];
            
            // Check for duplicate questions
            $allQuestions = $exam->examSets->flatMap->questions;
            $duplicateQuestions = $allQuestions->groupBy('question_text')
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
            $questionsWithoutAnswers = $allQuestions->filter(function($question) {
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
            
            // Check for empty sets
            $emptySets = $exam->examSets->filter(function($set) {
                return $set->questions->count() === 0;
            });
            
            if ($emptySets->count() > 0) {
                $issues[] = [
                    'type' => 'empty_sets',
                    'message' => 'Found ' . $emptySets->count() . ' empty sets',
                    'details' => $emptySets->pluck('set_name')->toArray()
                ];
            }
            
            // Check for unbalanced sets (significant difference in question count)
            $setCounts = $exam->examSets->pluck('questions')->map->count();
            if ($setCounts->count() > 1) {
                $minCount = $setCounts->min();
                $maxCount = $setCounts->max();
                if ($maxCount - $minCount > 5) { // More than 5 questions difference
                    $issues[] = [
                        'type' => 'unbalanced_sets',
                        'message' => 'Sets have unbalanced question counts (min: ' . $minCount . ', max: ' . $maxCount . ')',
                        'details' => []
                    ];
                }
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
        foreach ($sourceExam->examSets as $originalSet) {
            $newSet = ExamSet::create([
                'exam_id' => $targetExam->exam_id,
                'set_name' => $originalSet->set_name,
                'description' => $originalSet->description,
                'is_active' => false, // Start as draft
            ]);
            
            foreach ($originalSet->questions as $originalQuestion) {
                $newQuestion = Question::create([
                    'exam_set_id' => $newSet->exam_set_id,
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
    }
    
    /**
     * Validate exam before publishing.
     */
    private function validateExamForPublishing($exam)
    {
        $errors = [];
        
        // Check if exam has sets
        if ($exam->examSets->count() === 0) {
            $errors[] = 'Exam must have at least one set';
        }
        
        // Check if each set has questions
        foreach ($exam->examSets as $set) {
            if ($set->questions->count() === 0) {
                $errors[] = "Set '{$set->set_name}' has no questions";
            }
            
            // Check if each multiple choice question has a correct answer
            foreach ($set->questions as $question) {
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
        }
        
        return $errors;
    }
}
