<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamSet;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExamSetController extends Controller
{
    /**
     * Display a listing of exam sets for a specific exam.
     */
    public function index($examId, Request $request)
    {
        $exam = Exam::findOrFail($examId);
        
        $query = $exam->examSets()->with(['questions' => function($q) {
            $q->where('is_active', true);
        }]);

        // Search functionality
        if ($request->filled('search')) {
            $query->where('set_name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $examSets = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.exam-sets.index', compact('exam', 'examSets'));
    }

    /**
     * Show the form for creating a new exam set.
     */
    public function create($examId)
    {
        $exam = Exam::findOrFail($examId);
        $existingSets = $exam->examSets()->get();
        
        return view('admin.exam-sets.create', compact('exam', 'existingSets'));
    }

    /**
     * Store a newly created exam set in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|exists:exams,exam_id',
            'set_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        // Check for unique set name within the exam
        $validator->after(function ($validator) use ($request) {
            $exists = ExamSet::where('exam_id', $request->exam_id)
                            ->where('set_name', $request->set_name)
                            ->exists();
            if ($exists) {
                $validator->errors()->add('set_name', 'A set with this name already exists for this exam.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $validator->errors()->first()
            ]);
        }

        try {
            $examSet = ExamSet::create([
                'exam_id' => $request->exam_id,
                'set_name' => $request->set_name,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active', false),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Exam set created successfully!',
                'data' => $examSet
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create exam set: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified exam set.
     */
    public function show($examId, $setId)
    {
        $exam = Exam::findOrFail($examId);
        $examSet = $exam->examSets()->with(['questions.options' => function($q) {
            $q->orderBy('order_number');
        }])->findOrFail($setId);

        // Get statistics
        $stats = [
            'total_questions' => $examSet->questions()->count(),
            'active_questions' => $examSet->activeQuestions()->count(),
            'total_points' => $examSet->total_points,
            'question_types' => $examSet->questions()
                ->select('question_type', DB::raw('count(*) as count'))
                ->groupBy('question_type')
                ->pluck('count', 'question_type')
                ->toArray(),
        ];

        return view('admin.exam-sets.show', compact('exam', 'examSet', 'stats'));
    }

    /**
     * Show the form for editing the specified exam set.
     */
    public function edit($examId, $setId)
    {
        $exam = Exam::findOrFail($examId);
        $examSet = $exam->examSets()->findOrFail($setId);
        
        return view('admin.exam-sets.edit', compact('exam', 'examSet'));
    }

    /**
     * Update the specified exam set in storage.
     */
    public function update(Request $request, $examId, $setId)
    {
        $exam = Exam::findOrFail($examId);
        $examSet = $exam->examSets()->findOrFail($setId);

        $validator = Validator::make($request->all(), [
            'set_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        // Check for unique set name within the exam (excluding current set)
        $validator->after(function ($validator) use ($request, $examId, $setId) {
            $exists = ExamSet::where('exam_id', $examId)
                            ->where('set_name', $request->set_name)
                            ->where('exam_set_id', '!=', $setId)
                            ->exists();
            if ($exists) {
                $validator->errors()->add('set_name', 'A set with this name already exists for this exam.');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $examSet->update([
                'set_name' => $request->set_name,
                'description' => $request->description,
            ]);

            return redirect()->route('admin.exam-sets.show', [$exam->exam_id, $examSet->exam_set_id])
                ->with('success', 'Exam set updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update exam set. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified exam set from storage.
     */
    public function destroy($setId)
    {
        try {
            $examSet = ExamSet::findOrFail($setId);

            // Check if exam set has questions
            if ($examSet->questions()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete exam set that contains questions. Please delete all questions first.'
                ]);
            }

            $examSet->delete();

            return response()->json([
                'success' => true,
                'message' => 'Exam set deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete exam set. Please try again.'
            ]);
        }
    }

    /**
     * Toggle exam set active status.
     */
    public function toggleStatus($setId)
    {
        try {
            $examSet = ExamSet::findOrFail($setId);
            $examSet->update(['is_active' => !$examSet->is_active]);

            return response()->json([
                'success' => true,
                'is_active' => $examSet->is_active,
                'message' => 'Exam set status updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update exam set status.'
            ]);
        }
    }

    /**
     * Duplicate an exam set with all its questions.
     */
    public function duplicate($setId)
    {
        try {
            $originalSet = ExamSet::with('questions.options')->findOrFail($setId);

            $newSet = DB::transaction(function () use ($originalSet) {
                // Generate unique name to prevent collisions
                $baseName = $originalSet->set_name . ' (Copy)';
                $newSetName = $this->generateUniqueSetName($originalSet->exam_id, $baseName);

                // Create new exam set
                $newSet = ExamSet::create([
                    'exam_id' => $originalSet->exam_id,
                    'set_name' => $newSetName,
                    'description' => $originalSet->description,
                    'is_active' => false, // Start as draft
                ]);

                // Copy questions with preserved order, types, points, and explanations
                $this->copyQuestionsToSet($originalSet, $newSet);

                return $newSet;
            });

            return response()->json([
                'success' => true,
                'message' => 'Exam set duplicated successfully!',
                'data' => $newSet
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to duplicate exam set: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Shuffle questions in an exam set.
     */
    public function shuffleQuestions($setId)
    {
        try {
            $examSet = ExamSet::with('questions')->findOrFail($setId);
            
            if ($examSet->questions->count() === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No questions to shuffle in this set.'
                ]);
            }

            $previousOrder = null;
            
            DB::transaction(function () use ($examSet, &$previousOrder) {
                $questions = $examSet->questions;
                
                // Store previous order for undo functionality
                $previousOrder = $questions->map(function($question) {
                    return [
                        'id' => $question->question_id,
                        'order' => $question->order_number
                    ];
                })->toArray();
                
                // Shuffle and reassign order numbers
                $shuffledQuestions = $questions->shuffle();
                $newOrder = 1;
                
                foreach ($shuffledQuestions as $question) {
                    Question::where('question_id', $question->question_id)
                        ->update(['order_number' => $newOrder]);
                    $newOrder++;
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Questions shuffled successfully!',
                'previous_order' => ['questions' => $previousOrder]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to shuffle questions: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Add questions from the question bank to this exam set.
     */
    public function addQuestions($examId, $setId)
    {
        $exam = Exam::findOrFail($examId);
        $examSet = $exam->examSets()->findOrFail($setId);
        
        // Get available questions (not in this set)
        $availableQuestions = Question::with(['examSet.exam', 'options'])
            ->where('exam_set_id', '!=', $setId)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.exam-sets.add-questions', compact('exam', 'examSet', 'availableQuestions'));
    }

    /**
     * Copy questions from one set to another.
     */
    private function copyQuestionsToSet($sourceSet, $targetSet)
    {
        foreach ($sourceSet->questions as $originalQuestion) {
            $newQuestion = Question::create([
                'exam_set_id' => $targetSet->exam_set_id,
                'question_text' => $originalQuestion->question_text,
                'question_type' => $originalQuestion->question_type,
                'points' => $originalQuestion->points,
                'order_number' => $originalQuestion->order_number,
                'explanation' => $originalQuestion->explanation,
                'is_active' => false, // Start as draft for review
            ]);

            // Copy question options with preserved order
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
     * Generate a unique set name to prevent collisions.
     */
    private function generateUniqueSetName($examId, $baseName)
    {
        $counter = 1;
        $newName = $baseName;
        
        while (ExamSet::where('exam_id', $examId)->where('set_name', $newName)->exists()) {
            $counter++;
            $newName = $baseName . ' (' . $counter . ')';
        }
        
        return $newName;
    }

    /**
     * Reorder questions within an exam set.
     */
    public function reorderQuestions(Request $request, $setId)
    {
        $request->validate([
            'questions' => 'required|array',
            'questions.*.id' => 'required|exists:questions,question_id',
            'questions.*.order' => 'required|integer|min:1',
        ]);

        try {
            $examSet = ExamSet::findOrFail($setId);

            DB::transaction(function () use ($request, $examSet) {
                foreach ($request->questions as $questionData) {
                    Question::where('question_id', $questionData['id'])
                        ->where('exam_set_id', $examSet->exam_set_id)
                        ->update(['order_number' => $questionData['order']]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Questions reordered successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder questions.'
            ]);
        }
    }
}