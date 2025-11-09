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
     * Always shows the single active exam (or latest if none active).
     */
    public function index(Request $request)
    {
        // Single active exam mode: only one exam can be active at a time
        $currentExam = Exam::where('is_active', true)->first() ?? Exam::latest()->first();
        
        $questions = collect();
        
        if ($currentExam) {
            // Build query with filters
            $query = Question::where('exam_id', $currentExam->exam_id)
                ->with('options');
            
            // Search filter
            if ($request->filled('search')) {
                $query->where('question_text', 'like', '%' . $request->search . '%');
            }
            
            // Type filter
            if ($request->filled('type')) {
                $query->where('question_type', $request->type);
            }
            
            // Status filter
            if ($request->filled('status')) {
                $query->where('is_active', $request->status === 'active');
            }
            
            // Sort
            $sortBy = $request->get('sort_by', 'order_number');
            $sortOrder = $request->get('sort_order', 'asc');
            
            if ($sortBy === 'points') {
                $query->orderBy('points', $sortOrder);
            } elseif ($sortBy === 'type') {
                $query->orderBy('question_type', $sortOrder);
            } elseif ($sortBy === 'status') {
                $query->orderBy('is_active', $sortOrder === 'asc' ? 'desc' : 'asc');
            } else {
                $query->orderBy('order_number', $sortOrder);
            }
            
            // Pagination
            $perPage = $request->get('per_page', 15);
            $questions = $query->paginate($perPage)->withQueryString();
        } else {
            // Empty paginator for consistency
            $questions = \Illuminate\Pagination\LengthAwarePaginator::make([], 0, 15);
        }
        
        // Calculate statistics (from all questions, not just paginated)
        $allQuestions = $currentExam ? Question::where('exam_id', $currentExam->exam_id)->get() : collect();
        $stats = [
            'total_questions' => $allQuestions->count(),
            'active_questions' => $allQuestions->where('is_active', true)->count(),
            'mcq_count' => $allQuestions->where('question_type', 'multiple_choice')->count(),
            'tf_count' => $allQuestions->where('question_type', 'true_false')->count(),
            'draft_questions' => $allQuestions->where('is_active', false)->count(),
        ];
        
        // Calculate quota progress
        $quotaProgress = null;
        if ($currentExam) {
            $quotaProgress = [
                'total_items' => $currentExam->total_items ?? 0,
                'mcq_quota' => $currentExam->mcq_quota ?? 0,
                'tf_quota' => $currentExam->tf_quota ?? 0,
                'mcq_available' => $allQuestions->where('question_type', 'multiple_choice')->where('is_active', true)->count(),
                'tf_available' => $allQuestions->where('question_type', 'true_false')->where('is_active', true)->count(),
            ];
        }
        
        return view('admin.sets-questions', compact('currentExam', 'questions', 'stats', 'quotaProgress'));
    }
    
    /**
     * Create a new semester exam (archives current active exam).
     * Only one exam can be active at a time - the new exam starts as draft.
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
                // Archive current active exam (only one can be active)
                $currentExam = Exam::where('is_active', true)->first();
                if ($currentExam) {
                    $currentExam->update(['is_active' => false]);
                }
                
                // Create new exam as draft
                $newExam = Exam::create([
                    'title' => $request->title,
                    'description' => $request->description,
                    'duration_minutes' => $currentExam->duration_minutes ?? 90,
                    'is_active' => false, // Start as draft, publish when ready
                ]);
                
                if ($request->semester_option === 'duplicate' && $currentExam) {
                    // Duplicate question bank from previous semester
                    $this->duplicateExamContent($currentExam, $newExam);
                }
            });
            
            return response()->json([
                'success' => true,
                'message' => 'New semester exam created successfully! Review questions and publish when ready.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create new semester: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Publish the current exam (make it the single active exam).
     * Deactivates all other exams - only one exam can be active at a time.
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
            
            // Enforce single active exam: deactivate all others
            DB::transaction(function () use ($exam) {
                Exam::where('exam_id', '!=', $exam->exam_id)->update(['is_active' => false]);
                $exam->update(['is_active' => true]);
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Exam published successfully! This is now the active exam for applicants.'
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
    
    /**
     * Bulk update question status (activate/deactivate).
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_ids' => 'required|array',
            'question_ids.*' => 'required|exists:questions,question_id',
            'status' => 'required|boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $validator->errors()->first()
            ], 422);
        }
        
        try {
            $count = Question::whereIn('question_id', $request->question_ids)
                ->update(['is_active' => $request->status]);
            
            return response()->json([
                'success' => true,
                'message' => "Updated {$count} question(s) successfully!",
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update questions: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Bulk delete questions.
     */
    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_ids' => 'required|array',
            'question_ids.*' => 'required|exists:questions,question_id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $validator->errors()->first()
            ], 422);
        }
        
        try {
            $result = DB::transaction(function () use ($request) {
                $questions = Question::whereIn('question_id', $request->question_ids)
                    ->with('results')
                    ->get();
                
                $deletedCount = 0;
                $skippedCount = 0;
                
                foreach ($questions as $question) {
                    // Check if question has results
                    if ($question->results()->count() > 0) {
                        $skippedCount++;
                        continue;
                    }
                    
                    $question->options()->delete();
                    $question->delete();
                    $deletedCount++;
                }
                
                return [
                    'deleted_count' => $deletedCount,
                    'skipped_count' => $skippedCount
                ];
            });
            
            $message = "Deleted {$result['deleted_count']} question(s)";
            if ($result['skipped_count'] > 0) {
                $message .= ". {$result['skipped_count']} question(s) skipped (have been answered by applicants).";
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted_count' => $result['deleted_count'],
                'skipped_count' => $result['skipped_count']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete questions: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Bulk duplicate questions.
     */
    public function bulkDuplicate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_ids' => 'required|array',
            'question_ids.*' => 'required|exists:questions,question_id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $validator->errors()->first()
            ], 422);
        }
        
        try {
            $duplicatedCount = DB::transaction(function () use ($request) {
                $questions = Question::whereIn('question_id', $request->question_ids)
                    ->with('options')
                    ->get();
                
                $duplicatedCount = 0;
                
                foreach ($questions as $originalQuestion) {
                    $exam = $originalQuestion->exam;
                    $maxOrder = Question::where('exam_id', $exam->exam_id)->max('order_number') ?? 0;
                    
                    $newQuestion = Question::create([
                        'exam_id' => $originalQuestion->exam_id,
                        'question_text' => $originalQuestion->question_text . ' (Copy)',
                        'question_type' => $originalQuestion->question_type,
                        'points' => $originalQuestion->points,
                        'order_number' => $maxOrder + 1,
                        'explanation' => $originalQuestion->explanation,
                        'is_active' => false, // Start as draft
                    ]);
                    
                    foreach ($originalQuestion->options as $originalOption) {
                        QuestionOption::create([
                            'question_id' => $newQuestion->question_id,
                            'option_text' => $originalOption->option_text,
                            'is_correct' => $originalOption->is_correct,
                            'order_number' => $originalOption->order_number,
                        ]);
                    }
                    
                    $duplicatedCount++;
                }
                
                return $duplicatedCount;
            });
            
            return response()->json([
                'success' => true,
                'message' => "Duplicated {$duplicatedCount} question(s) successfully!",
                'count' => $duplicatedCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to duplicate questions: ' . $e->getMessage()
            ], 500);
        }
    }
}
