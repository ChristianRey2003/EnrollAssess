<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamSet;
use App\Models\Question;
use App\Models\Applicant;
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
        $query = Exam::with(['examSets' => function($q) {
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
            'total_sets' => ExamSet::count(),
            'active_sets' => ExamSet::active()->count(),
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
        $exam = Exam::with(['examSets.questions' => function($q) {
            $q->where('is_active', true)->orderBy('order_number');
        }])->findOrFail($id);

        // Calculate exam statistics
        $stats = [
            'total_sets' => $exam->examSets()->count(),
            'active_sets' => $exam->activeExamSets()->count(),
            'total_questions' => $exam->examSets()->withCount('questions')->get()->sum('questions_count'),
            'total_points' => DB::table('questions')
                ->join('exam_sets', 'questions.exam_set_id', '=', 'exam_sets.exam_set_id')
                ->where('exam_sets.exam_id', $exam->exam_id)
                ->where('questions.is_active', true)
                ->sum('questions.points'),
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
            'title' => 'required|string|max:255|unique:exams,title,' . $exam->exam_id . ',exam_id',
            'description' => 'nullable|string|max:1000',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'is_active' => 'nullable|boolean',
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
            $updateData = [
                'title' => $request->title,
                'description' => $request->description,
                'duration_minutes' => $request->duration_minutes,
            ];

            // Only update is_active if provided
            if ($request->has('is_active')) {
                $updateData['is_active'] = (bool) $request->is_active;
            }

            $exam->update($updateData);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Exam updated successfully!',
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

            // Check if exam has exam sets with questions
            $hasQuestions = $exam->examSets()->whereHas('questions')->exists();
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
     * Duplicate an exam with all its sets and questions.
     */
    public function duplicate($id)
    {
        try {
            $originalExam = Exam::with('examSets.questions.options')->findOrFail($id);

            DB::transaction(function () use ($originalExam) {
                // Create new exam
                $newExam = Exam::create([
                    'title' => $originalExam->title . ' (Copy)',
                    'description' => $originalExam->description,
                    'duration_minutes' => $originalExam->duration_minutes,
                    'is_active' => false, // Start as inactive
                ]);

                // Duplicate exam sets and questions
                foreach ($originalExam->examSets as $originalSet) {
                    $newSet = ExamSet::create([
                        'exam_id' => $newExam->exam_id,
                        'set_name' => $originalSet->set_name,
                        'description' => $originalSet->description,
                        'is_active' => $originalSet->is_active,
                    ]);

                    // Duplicate questions
                    foreach ($originalSet->questions as $originalQuestion) {
                        $newQuestion = $newSet->questions()->create([
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
     * Start sectioned exam interface
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
            $applicant = Applicant::with('examSet.exam')->findOrFail($applicantId);
            
            if (!$applicant->examSet) {
                return response()->json([
                    'success' => false,
                    'message' => 'No exam set assigned to this applicant.'
                ], 400);
            }

            // Get all questions grouped by type
            $questions = $applicant->examSet->activeQuestions()
                ->with('options')
                ->get()
                ->groupBy('question_type');

            // Check if there are any questions
            if ($questions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active questions found in your assigned exam set. Please contact the administrator.'
                ], 400);
            }

            // Initialize exam session
            $examSession = [
                'applicant_id' => $applicant->applicant_id,
                'exam_set_id' => $applicant->exam_set_id,
                'started_at' => now(),
                'duration_minutes' => $applicant->examSet->exam->duration_minutes ?? 30,
                'current_section' => 0,
                'sections_completed' => [],
                'answers' => []
            ];

            session(['exam_session' => $examSession]);

            return response()->json([
                'success' => true,
                'exam_session' => $examSession,
                'questions_by_type' => $questions,
                'applicant' => $applicant,
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
     * Get sectioned exam interface
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
            $applicant = Applicant::with('examSet.exam')->findOrFail($examSession['applicant_id']);
            
            // Get all questions grouped by type
            $questions = $applicant->examSet->activeQuestions()
                ->with('options')
                ->get()
                ->groupBy('question_type');

            // Check if there are any questions
            if ($questions->isEmpty()) {
                return redirect()->route('exam.pre-requirements')
                    ->with('error', 'No active questions found in your assigned exam set. Please contact the administrator.');
            }

            // Define section order and labels
            $sectionConfig = [
                'multiple_choice' => ['label' => 'Multiple Choice', 'icon' => 'MC'],
                'true_false' => ['label' => 'True/False', 'icon' => 'T/F'],
                'essay' => ['label' => 'Essay', 'icon' => 'Essay']
            ];

            // Filter out empty sections and prepare section data
            $sections = collect($sectionConfig)
                ->filter(function ($config, $type) use ($questions) {
                    return $questions->has($type) && $questions[$type]->count() > 0;
                })
                ->map(function ($config, $type) use ($questions) {
                    return array_merge($config, [
                        'type' => $type,
                        'questions' => $questions[$type],
                        'count' => $questions[$type]->count()
                    ]);
                })
                ->values();

            $totalQuestions = $questions->flatten()->count();
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