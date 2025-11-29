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
            
            // Default sort by order number
            $query->orderBy('order_number', 'asc');
            
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
        
        // Get delegation info if user is accessing via delegation
        $delegation = null;
        $isDelegated = false;
        if (auth()->check() && auth()->user()->role === 'instructor') {
            // Check for any questions-related delegation (granular capabilities)
            $delegation = auth()->user()->delegatedPermissions()
                ->whereIn('permission', ['questions.view', 'questions.create', 'questions.edit', 'questions.delete', 'questions.manage_exam_settings', 'manage_questions'])
                ->where('status', 'active')
                ->where(function($q) {
                    $q->whereNull('starts_at')
                      ->orWhere('starts_at', '<=', now());
                })
                ->first();
            
            if ($delegation && !$delegation->isExpired()) {
                $isDelegated = true;
            }
        }

        return view('admin.sets-questions', compact('currentExam', 'questions', 'stats', 'quotaProgress', 'delegation', 'isDelegated'));
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
    
    /**
     * Download CSV template for bulk question import.
     */
    public function downloadTemplate()
    {
        $filename = 'question_import_template.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Write BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header row
            fputcsv($file, [
                'Question Text',
                'Question Type',
                'Points',
                'Option 1',
                'Option 2',
                'Option 3',
                'Option 4',
                'Correct Answer'
            ]);
            
            // Example rows
            fputcsv($file, [
                'What is 2+2?',
                'multiple_choice',
                '1',
                '2',
                '3',
                '4',
                '5',
                'Option 3'
            ]);
            
            fputcsv($file, [
                'The sky is blue.',
                'true_false',
                '1',
                '',
                '',
                '',
                '',
                'True'
            ]);
            
            fputcsv($file, [
                'Which programming language is used for web development?',
                'multiple_choice',
                '2',
                'Python',
                'JavaScript',
                'C++',
                'Java',
                'Option 2'
            ]);
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Process bulk import from CSV file.
     */
    public function processImport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
            'import_as_draft' => 'nullable|boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->all(),
            ], 422);
        }
        
        try {
            $currentExam = Exam::where('is_active', true)->first();
            
            if (!$currentExam) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active exam found. Please create and activate an exam first.'
                ], 422);
            }
            
            $file = $request->file('csv_file');
            $csvContent = file_get_contents($file->getPathname());
            $lines = explode("\n", $csvContent);
            
            if (count($lines) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'CSV file must contain at least a header row and one data row.'
                ], 422);
            }
            
            // Parse header - remove BOM and normalize
            $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $lines[0]);
            $header = array_map(function($h) { 
                return trim($h, " \t\n\r\0\x0B\"'"); 
            }, str_getcsv($firstLine));
            
            // Expected header columns
            $expectedColumns = [
                'Question Text',
                'Question Type',
                'Points',
                'Option 1',
                'Option 2',
                'Option 3',
                'Option 4',
                'Correct Answer'
            ];
            
            // Validate header
            if (count($header) < 8) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid CSV format. Please download the template and use it as a guide.'
                ], 422);
            }
            
            $importResults = [
                'total' => 0,
                'successful' => 0,
                'failed' => 0,
                'errors' => [],
            ];
            
            $importAsDraft = $request->boolean('import_as_draft', false);
            $maxOrderNumber = Question::where('exam_id', $currentExam->exam_id)->max('order_number') ?? 0;
            
            DB::transaction(function () use ($lines, $header, $currentExam, $importAsDraft, &$importResults, &$maxOrderNumber) {
                for ($i = 1; $i < count($lines); $i++) {
                    $line = trim($lines[$i]);
                    if (empty($line)) continue;
                    
                    $importResults['total']++;
                    $lineNumber = $i + 1;
                    
                    try {
                        $data = str_getcsv($line);
                        
                        // Ensure we have enough columns
                        if (count($data) < 8) {
                            $importResults['errors'][] = "Line {$lineNumber}: Insufficient columns. Expected 8 columns.";
                            $importResults['failed']++;
                            continue;
                        }
                        
                        // Map data to named array
                        $record = [];
                        foreach ($header as $index => $headerName) {
                            $record[$headerName] = isset($data[$index]) ? trim($data[$index]) : '';
                        }
                        
                        // Extract values
                        $questionText = $record['Question Text'] ?? '';
                        $questionType = strtolower(trim($record['Question Type'] ?? ''));
                        $points = !empty($record['Points']) ? (int)$record['Points'] : 1;
                        $option1 = trim($record['Option 1'] ?? '');
                        $option2 = trim($record['Option 2'] ?? '');
                        $option3 = trim($record['Option 3'] ?? '');
                        $option4 = trim($record['Option 4'] ?? '');
                        $correctAnswer = trim($record['Correct Answer'] ?? '');
                        
                        // Validate required fields
                        if (empty($questionText)) {
                            $importResults['errors'][] = "Line {$lineNumber}: Question Text is required.";
                            $importResults['failed']++;
                            continue;
                        }
                        
                        if (!in_array($questionType, ['multiple_choice', 'true_false'])) {
                            $importResults['errors'][] = "Line {$lineNumber}: Question Type must be 'multiple_choice' or 'true_false'.";
                            $importResults['failed']++;
                            continue;
                        }
                        
                        if ($points < 1 || $points > 100) {
                            $importResults['errors'][] = "Line {$lineNumber}: Points must be between 1 and 100.";
                            $importResults['failed']++;
                            continue;
                        }
                        
                        // Validate based on question type
                        if ($questionType === 'multiple_choice') {
                            // Collect non-empty options
                            $options = array_filter([$option1, $option2, $option3, $option4], function($opt) {
                                return !empty(trim($opt));
                            });
                            
                            if (count($options) < 2) {
                                $importResults['errors'][] = "Line {$lineNumber}: Multiple choice questions must have at least 2 options.";
                                $importResults['failed']++;
                                continue;
                            }
                            
                            if (count($options) > 6) {
                                $importResults['errors'][] = "Line {$lineNumber}: Multiple choice questions cannot have more than 6 options.";
                                $importResults['failed']++;
                                continue;
                            }
                            
                            // Validate correct answer format
                            if (!preg_match('/^Option\s*([1-4])$/i', $correctAnswer, $matches)) {
                                $importResults['errors'][] = "Line {$lineNumber}: Correct Answer must be in format 'Option 1', 'Option 2', 'Option 3', or 'Option 4'.";
                                $importResults['failed']++;
                                continue;
                            }
                            
                            $correctOptionIndex = (int)$matches[1] - 1; // Convert to 0-based index
                            $optionsArray = array_values($options);
                            
                            if ($correctOptionIndex >= count($optionsArray)) {
                                $importResults['errors'][] = "Line {$lineNumber}: Correct Answer refers to an option that doesn't exist.";
                                $importResults['failed']++;
                                continue;
                            }
                            
                            // Create question
                            $maxOrderNumber++;
                            $question = Question::create([
                                'exam_id' => $currentExam->exam_id,
                                'question_text' => $questionText,
                                'question_type' => 'multiple_choice',
                                'points' => $points,
                                'order_number' => $maxOrderNumber,
                                'explanation' => null,
                                'is_active' => !$importAsDraft,
                            ]);
                            
                            // Create options
                            foreach ($optionsArray as $index => $optionText) {
                                QuestionOption::create([
                                    'question_id' => $question->question_id,
                                    'option_text' => $optionText,
                                    'is_correct' => $index === $correctOptionIndex,
                                    'order_number' => $index + 1,
                                ]);
                            }
                            
                        } elseif ($questionType === 'true_false') {
                            // Validate correct answer for true/false
                            $correctAnswerLower = strtolower($correctAnswer);
                            if (!in_array($correctAnswerLower, ['true', 'false'])) {
                                $importResults['errors'][] = "Line {$lineNumber}: Correct Answer for True/False must be 'True' or 'False'.";
                                $importResults['failed']++;
                                continue;
                            }
                            
                            $isTrueCorrect = $correctAnswerLower === 'true';
                            
                            // Create question
                            $maxOrderNumber++;
                            $question = Question::create([
                                'exam_id' => $currentExam->exam_id,
                                'question_text' => $questionText,
                                'question_type' => 'true_false',
                                'points' => $points,
                                'order_number' => $maxOrderNumber,
                                'explanation' => null,
                                'is_active' => !$importAsDraft,
                            ]);
                            
                            // Create True and False options
                            QuestionOption::create([
                                'question_id' => $question->question_id,
                                'option_text' => 'True',
                                'is_correct' => $isTrueCorrect,
                                'order_number' => 1,
                            ]);
                            
                            QuestionOption::create([
                                'question_id' => $question->question_id,
                                'option_text' => 'False',
                                'is_correct' => !$isTrueCorrect,
                                'order_number' => 2,
                            ]);
                        }
                        
                        $importResults['successful']++;
                        
                    } catch (\Exception $e) {
                        $importResults['errors'][] = "Line {$lineNumber}: " . $e->getMessage();
                        $importResults['failed']++;
                    }
                }
            });
            
            $message = "Import completed! {$importResults['successful']} question(s) imported successfully.";
            if ($importResults['failed'] > 0) {
                $message .= " {$importResults['failed']} question(s) failed.";
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'results' => $importResults
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process import: ' . $e->getMessage()
            ], 500);
        }
    }
}
