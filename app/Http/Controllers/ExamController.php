<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Question;
use App\Models\Applicant;
use App\Models\Result;
use App\Services\QuestionSelectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExamController extends Controller
{
    /**
     * Display a listing of the exams.
     */
    public function index(Request $request)
    {
        $query = Exam::with(['questions' => function($q) {
            $q->where('is_active', true);
        }]);

        // Search functionality
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $exams = $query->orderBy('created_at', 'desc')->paginate(10);

        // Get exam statistics
        $examStats = [
            'total' => Exam::count(),
            'active' => Exam::active()->count(),
            'inactive' => Exam::where('is_active', false)->count(),
            'total_questions' => Question::count(),
            'active_questions' => Question::where('is_active', true)->count(),
        ];

        return view('admin.exams.index', compact('exams', 'examStats'));
    }

    /**
     * Show the form for creating a new exam.
     */
    public function create()
    {
        return view('admin.exams.create');
    }

    /**
     * Store a newly created exam in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255|unique:exams,title',
            'description' => 'nullable|string|max:1000',
            'duration_minutes' => 'required|integer|min:5|max:480', // 5 minutes to 8 hours
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $exam = Exam::create([
                'title' => $request->title,
                'description' => $request->description,
                'duration_minutes' => $request->duration_minutes,
                'is_active' => (bool) $request->input('is_active', false),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Exam created successfully!',
                    'exam_id' => $exam->exam_id,
                ]);
            }

            return redirect()->route('admin.exams.show', $exam->exam_id)
                ->with('success', 'Exam created successfully!');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create exam. Please try again.'
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to create exam. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified exam.
     */
    public function show($id)
    {
        $exam = Exam::with(['questions' => function($q) {
            $q->where('is_active', true)->orderBy('order_number');
        }])->findOrFail($id);

        // Calculate exam statistics
        $stats = [
            'total_questions' => $exam->questions()->count(),
            'active_questions' => $exam->activeQuestions()->count(),
            'mcq_count' => $exam->multipleChoiceQuestions()->count(),
            'tf_count' => $exam->trueFalseQuestions()->count(),
            'total_points' => $exam->questions()
                ->where('is_active', true)
                ->sum('points'),
        ];

        return view('admin.exams.show', compact('exam', 'stats'));
    }

    /**
     * Show the form for editing the specified exam.
     */
    public function edit($id)
    {
        $exam = Exam::findOrFail($id);
        return view('admin.exams.edit', compact('exam'));
    }

    /**
     * Update the specified exam in storage.
     */
    public function update(Request $request, $id)
    {
        $exam = Exam::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255|unique:exams,title,' . $exam->exam_id . ',exam_id',
            'description' => 'nullable|string|max:1000',
            'duration_minutes' => 'nullable|integer|min:1|max:600',
            'total_items' => 'nullable|integer|min:1',
            'mcq_quota' => 'nullable|integer|min:0',
            'tf_quota' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Additional custom validation
        $mcqQuota = $request->input('mcq_quota', $exam->mcq_quota) ?? 0;
        $tfQuota = $request->input('tf_quota', $exam->tf_quota) ?? 0;
        $totalItems = $request->input('total_items', $exam->total_items);

        if ($totalItems && ($mcqQuota + $tfQuota) > $totalItems) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The sum of MCQ and True/False quotas cannot exceed total items.',
                    'errors' => [
                        'total_items' => ['The sum of MCQ and True/False quotas cannot exceed total items.']
                    ],
                ], 422);
            }

            return redirect()->back()
                ->withErrors(['total_items' => 'The sum of MCQ and True/False quotas cannot exceed total items.'])
                ->withInput();
        }

        // Check if enough questions exist for quotas
        if ($mcqQuota > 0) {
            $mcqAvailable = $exam->multipleChoiceQuestions()->count();
            if ($mcqAvailable < $mcqQuota) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Not enough MCQ questions. Available: {$mcqAvailable}, Required: {$mcqQuota}",
                        'errors' => [
                            'mcq_quota' => ["Not enough MCQ questions. Available: {$mcqAvailable}, Required: {$mcqQuota}"]
                        ],
                    ], 422);
                }

                return redirect()->back()
                    ->withErrors(['mcq_quota' => "Not enough MCQ questions. Available: {$mcqAvailable}, Required: {$mcqQuota}"])
                    ->withInput();
            }
        }

        if ($tfQuota > 0) {
            $tfAvailable = $exam->trueFalseQuestions()->count();
            if ($tfAvailable < $tfQuota) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Not enough True/False questions. Available: {$tfAvailable}, Required: {$tfQuota}",
                        'errors' => [
                            'tf_quota' => ["Not enough True/False questions. Available: {$tfAvailable}, Required: {$tfQuota}"]
                        ],
                    ], 422);
                }

                return redirect()->back()
                    ->withErrors(['tf_quota' => "Not enough True/False questions. Available: {$tfAvailable}, Required: {$tfQuota}"])
                    ->withInput();
            }
        }

        try {
            $updateData = array_filter([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'duration_minutes' => $request->input('duration_minutes'),
                'total_items' => $request->input('total_items'),
                'mcq_quota' => $request->input('mcq_quota'),
                'tf_quota' => $request->input('tf_quota'),
                'starts_at' => $request->input('starts_at'),
                'ends_at' => $request->input('ends_at'),
            ], function ($value) {
                return !is_null($value);
            });

            // Only update is_active if provided
            if ($request->has('is_active')) {
                $updateData['is_active'] = (bool) $request->is_active;
            }

            $exam->update($updateData);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Exam settings updated successfully!',
                    'exam' => $exam->fresh(),
                ]);
            }

            return redirect()->route('admin.exams.show', $exam->exam_id)
                ->with('success', 'Exam updated successfully!');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update exam. Please try again.'
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to update exam. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified exam from storage.
     */
    public function destroy($id)
    {
        try {
            $exam = Exam::findOrFail($id);

            // Check if exam has questions in question bank
            $hasQuestions = $exam->questions()->exists();
            if ($hasQuestions) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete exam that contains questions. Please delete all questions first.'
                ]);
            }

            $exam->delete();

            return response()->json([
                'success' => true,
                'message' => 'Exam deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete exam. Please try again.'
            ]);
        }
    }

    /**
     * Toggle exam active status.
     */
    public function toggleStatus($id)
    {
        try {
            $exam = Exam::findOrFail($id);
            $exam->update(['is_active' => !$exam->is_active]);

            return response()->json([
                'success' => true,
                'is_active' => $exam->is_active,
                'message' => 'Exam status updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update exam status.'
            ]);
        }
    }

    /**
     * Duplicate an exam with all its questions.
     */
    public function duplicate($id)
    {
        try {
            $originalExam = Exam::with('questions.options')->findOrFail($id);

            DB::transaction(function () use ($originalExam) {
                // Create new exam
                $newExam = Exam::create([
                    'title' => $originalExam->title . ' (Copy)',
                    'description' => $originalExam->description,
                    'duration_minutes' => $originalExam->duration_minutes,
                    'total_items' => $originalExam->total_items,
                    'mcq_quota' => $originalExam->mcq_quota,
                    'tf_quota' => $originalExam->tf_quota,
                    'is_active' => false, // Start as inactive
                ]);

                // Duplicate questions
                foreach ($originalExam->questions as $originalQuestion) {
                    $newQuestion = $newExam->questions()->create([
                        'question_text' => $originalQuestion->question_text,
                        'question_type' => $originalQuestion->question_type,
                        'points' => $originalQuestion->points,
                        'order_number' => $originalQuestion->order_number,
                        'explanation' => $originalQuestion->explanation,
                        'is_active' => $originalQuestion->is_active,
                    ]);

                    // Duplicate question options
                    foreach ($originalQuestion->options as $originalOption) {
                        $newQuestion->options()->create([
                            'option_text' => $originalOption->option_text,
                            'is_correct' => $originalOption->is_correct,
                            'order_number' => $originalOption->order_number,
                        ]);
                    }
                }

                return $newExam;
            });

            return response()->json([
                'success' => true,
                'message' => 'Exam duplicated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to duplicate exam. Please try again.'
            ]);
        }
    }

    /**
     * Start exam interface using Question Bank with per-examinee randomization
     */
    public function startExam(Request $request)
    {
        // Get applicant ID from session (set during access code verification)
        $applicantId = $request->session()->get('applicant_id');
        
        if (!$applicantId) {
            return response()->json([
                'success' => false,
                'message' => 'No applicant session found. Please verify your access code first.'
            ], 401);
        }
        
        try {
            $applicant = Applicant::findOrFail($applicantId);
            
            // Get exam from access code
            $accessCode = $applicant->accessCode;
            if (!$accessCode || !$accessCode->exam) {
                return response()->json([
                    'success' => false,
                    'message' => 'No exam assigned to this applicant.'
                ], 400);
            }
            
            $exam = $accessCode->exam;
            $selectionService = new QuestionSelectionService();
            
            // Validate exam configuration
            $validation = $selectionService->validateExamConfiguration($exam);
            if (!$validation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => implode(' ', $validation['errors'])
                ], 400);
            }
            
            // Select random questions for this applicant (per-examinee randomization)
            $selectedQuestions = $selectionService->selectQuestionsForApplicant(
                $exam, 
                $applicant->applicant_id
            );
            
            // Store selected question IDs in session for consistency
            $questionIds = $selectedQuestions->pluck('question_id')->toArray();
            
            // Initialize exam session
            $examSession = [
                'applicant_id' => $applicant->applicant_id,
                'exam_id' => $exam->exam_id,
                'question_ids' => $questionIds, // Store for reload consistency
                'started_at' => now()->toDateTimeString(),
                'duration_minutes' => $exam->duration_minutes ?? 30,
                'current_section' => 0,
                'sections_completed' => [],
                'answers' => []
            ];
            
            session(['exam_session' => $examSession]);
            
            return response()->json([
                'success' => true,
                'exam_session' => $examSession,
                'redirect_url' => route('exam.interface')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start exam: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sectioned exam interface using Question Bank
     */
    public function getExamInterface(Request $request)
    {
        $examSession = session('exam_session');
        
        if (!$examSession) {
            // Check if applicant is in session, if so redirect to start exam
            $applicantId = $request->session()->get('applicant_id');
            if ($applicantId) {
                return redirect()->route('exam.pre-requirements')
                    ->with('info', 'Please complete the pre-requirements to start your exam.');
            }
            
            return redirect()->route('applicant.login')
                ->with('error', 'Please verify your access code first.');
        }

        try {
            $applicant = Applicant::findOrFail($examSession['applicant_id']);
            $exam = Exam::findOrFail($examSession['exam_id']);
            $selectionService = new QuestionSelectionService();
            
            // Retrieve same questions as when exam started (using stored question_ids)
            if (isset($examSession['question_ids'])) {
                $questions = Question::with('options')
                    ->whereIn('question_id', $examSession['question_ids'])
                    ->get()
                    ->sortBy(function($question) use ($examSession) {
                        return array_search($question->question_id, $examSession['question_ids']);
                    });
            } else {
                // Fallback: regenerate questions (should not happen in normal flow)
                $questions = $selectionService->selectQuestionsForApplicant($exam, $applicant->applicant_id);
            }
            
            // Group questions by type and shuffle options for MCQs
            $questionsByType = collect();
            foreach ($questions as $question) {
                if ($question->isMultipleChoice()) {
                    $question->shuffled_options = $selectionService->getShuffledOptions($question, $applicant->applicant_id);
                }
                
                $type = $question->question_type;
                if (!$questionsByType->has($type)) {
                    $questionsByType->put($type, collect());
                }
                $questionsByType->get($type)->push($question);
            }
            
            // Define section order and labels
            $sectionConfig = [
                'multiple_choice' => ['label' => 'Multiple Choice', 'icon' => 'MC'],
                'true_false' => ['label' => 'True/False', 'icon' => 'T/F'],
                'essay' => ['label' => 'Essay', 'icon' => 'Essay']
            ];

            // Filter out empty sections and prepare section data
            $sections = collect($sectionConfig)
                ->filter(function ($config, $type) use ($questionsByType) {
                    return $questionsByType->has($type) && $questionsByType[$type]->count() > 0;
                })
                ->map(function ($config, $type) use ($questionsByType) {
                    return array_merge($config, [
                        'type' => $type,
                        'questions' => $questionsByType[$type],
                        'count' => $questionsByType[$type]->count()
                    ]);
                })
                ->values();

            $totalQuestions = $questions->count();
            $timeRemaining = $this->calculateTimeRemaining($examSession);

            return view('exam.sectioned-interface', compact(
                'applicant',
                'sections', 
                'examSession',
                'totalQuestions',
                'timeRemaining'
            ));

        } catch (\Exception $e) {
            return redirect()->route('exam.pre-requirements')
                ->with('error', 'Failed to load exam: ' . $e->getMessage());
        }
    }

    /**
     * Submit section answers
     */
    public function submitSection(Request $request)
    {
        $examSession = session('exam_session');
        
        if (!$examSession) {
            return response()->json([
                'success' => false,
                'message' => 'Exam session not found.'
            ], 400);
        }

        $request->validate([
            'section_type' => 'required|string',
            'answers' => 'required|array'
        ]);

        try {
            // Update session with answers
            $examSession['answers'] = array_merge(
                $examSession['answers'] ?? [],
                $request->answers
            );
            
            // Mark section as completed
            $examSession['sections_completed'][] = $request->section_type;
            $examSession['current_section']++;
            
            session(['exam_session' => $examSession]);

            return response()->json([
                'success' => true,
                'message' => 'Section submitted successfully!',
                'current_section' => $examSession['current_section'],
                'sections_completed' => $examSession['sections_completed']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit section: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate remaining time in seconds
     */
    private function calculateTimeRemaining($examSession)
    {
        $startTime = strtotime($examSession['started_at']);
        $durationSeconds = ($examSession['duration_minutes'] ?? 30) * 60;
        $elapsed = time() - $startTime;
        
        return max(0, $durationSeconds - $elapsed);
    }

}