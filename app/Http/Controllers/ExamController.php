<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamSet;
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
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $exam = Exam::create([
                'title' => $request->title,
                'description' => $request->description,
                'duration_minutes' => $request->duration_minutes,
                'is_active' => true,
            ]);

            return redirect()->route('admin.exams.show', $exam->exam_id)
                ->with('success', 'Exam created successfully!');

        } catch (\Exception $e) {
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
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $exam->update([
                'title' => $request->title,
                'description' => $request->description,
                'duration_minutes' => $request->duration_minutes,
            ]);

            return redirect()->route('admin.exams.show', $exam->exam_id)
                ->with('success', 'Exam updated successfully!');

        } catch (\Exception $e) {
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
}