<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\ExamSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    /**
     * Display a listing of the questions.
     */
    public function index(Request $request)
    {
        $query = Question::with(['examSet.exam', 'options'])
            ->active()
            ->ordered();

        // Search functionality
        if ($request->filled('search')) {
            $query->where('question_text', 'like', '%' . $request->search . '%');
        }

        // Filter by question type
        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        // Filter by exam set
        if ($request->filled('exam_set_id')) {
            $query->where('exam_set_id', $request->exam_set_id);
        }

        $questions = $query->paginate(10);
        $examSets = ExamSet::with('exam')->where('is_active', true)->get();

        // Get question counts by type for dashboard
        $questionStats = [
            'total' => Question::active()->count(),
            'multiple_choice' => Question::active()->byType('multiple_choice')->count(),
            'true_false' => Question::active()->byType('true_false')->count(),
            'short_answer' => Question::active()->byType('short_answer')->count(),
            'essay' => Question::active()->byType('essay')->count(),
        ];

        return view('admin.questions', compact('questions', 'examSets', 'questionStats'));
    }

    /**
     * Show the form for creating a new question.
     */
    public function create()
    {
        $examSets = ExamSet::with('exam')->where('is_active', true)->get();
        return view('admin.questions.create', compact('examSets'));
    }

    /**
     * Store a newly created question in storage.
     */
    public function store(Request $request)
    {
        $validator = $this->validateQuestionData($request);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::transaction(function () use ($request) {
                // Create the question
                $question = Question::create([
                    'exam_set_id' => $request->exam_set_id,
                    'question_text' => $request->question_text,
                    'question_type' => $request->question_type,
                    'points' => $request->points ?? 1,
                    'order_number' => $this->getNextOrderNumber($request->exam_set_id),
                    'explanation' => $request->explanation,
                    'is_active' => true,
                ]);

                // Create options based on question type
                $this->createQuestionOptions($question, $request);
            });

            return redirect()->route('admin.questions')
                ->with('success', 'Question created successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create question. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified question.
     */
    public function show(Question $question)
    {
        $question->load(['examSet.exam', 'options', 'results']);
        return view('admin.questions.show', compact('question'));
    }

    /**
     * Show the form for editing the specified question.
     */
    public function edit($id)
    {
        $question = Question::with(['examSet', 'options'])->findOrFail($id);
        $examSets = ExamSet::with('exam')->where('is_active', true)->get();
        
        return view('admin.questions.create', compact('question', 'examSets'));
    }

    /**
     * Update the specified question in storage.
     */
    public function update(Request $request, $id)
    {
        $question = Question::findOrFail($id);
        $validator = $this->validateQuestionData($request, $question);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::transaction(function () use ($request, $question) {
                // Update the question
                $question->update([
                    'exam_set_id' => $request->exam_set_id,
                    'question_text' => $request->question_text,
                    'question_type' => $request->question_type,
                    'points' => $request->points ?? 1,
                    'explanation' => $request->explanation,
                ]);

                // Delete existing options and create new ones
                $question->options()->delete();
                $this->createQuestionOptions($question, $request);
            });

            return redirect()->route('admin.questions')
                ->with('success', 'Question updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update question. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified question from storage.
     */
    public function destroy($id)
    {
        try {
            $question = Question::findOrFail($id);
            
            // Check if question has results (exam responses)
            if ($question->results()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete question that has been answered by applicants.'
                ]);
            }

            $question->delete();

            return response()->json([
                'success' => true,
                'message' => 'Question deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete question. Please try again.'
            ]);
        }
    }

    /**
     * Toggle question active status.
     */
    public function toggleStatus($id)
    {
        try {
            $question = Question::findOrFail($id);
            $question->update(['is_active' => !$question->is_active]);

            return response()->json([
                'success' => true,
                'is_active' => $question->is_active,
                'message' => 'Question status updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update question status.'
            ]);
        }
    }

    /**
     * Validate question data based on type.
     */
    private function validateQuestionData(Request $request, Question $question = null)
    {
        $rules = [
            'exam_set_id' => 'required|exists:exam_sets,exam_set_id',
            'question_text' => 'required|string|min:10|max:2000',
            'question_type' => 'required|in:multiple_choice,true_false,short_answer,essay',
            'points' => 'nullable|integer|min:1|max:100',
            'explanation' => 'nullable|string|max:1000',
        ];

        // Add type-specific validation rules
        switch ($request->question_type) {
            case 'multiple_choice':
                $rules['options'] = 'required|array|min:2|max:6';
                $rules['options.*'] = 'required|string|max:500';
                $rules['correct_answer'] = 'required|integer|min:0|max:5';
                break;

            case 'true_false':
                $rules['correct_answer'] = 'required|in:true,false';
                break;

            case 'short_answer':
                $rules['sample_answer'] = 'nullable|string|max:1000';
                break;

            case 'essay':
                $rules['sample_answer'] = 'nullable|string|max:2000';
                $rules['grading_criteria'] = 'nullable|string|max:1000';
                break;
        }

        return Validator::make($request->all(), $rules);
    }

    /**
     * Create question options based on question type.
     */
    private function createQuestionOptions(Question $question, Request $request)
    {
        switch ($request->question_type) {
            case 'multiple_choice':
                foreach ($request->options as $index => $optionText) {
                    if (empty(trim($optionText))) continue;
                    
                    QuestionOption::create([
                        'question_id' => $question->question_id,
                        'option_text' => trim($optionText),
                        'is_correct' => $index == $request->correct_answer,
                        'order_number' => $index + 1,
                    ]);
                }
                break;

            case 'true_false':
                // Create True and False options
                QuestionOption::create([
                    'question_id' => $question->question_id,
                    'option_text' => 'True',
                    'is_correct' => $request->correct_answer === 'true',
                    'order_number' => 1,
                ]);

                QuestionOption::create([
                    'question_id' => $question->question_id,
                    'option_text' => 'False',
                    'is_correct' => $request->correct_answer === 'false',
                    'order_number' => 2,
                ]);
                break;

            case 'short_answer':
            case 'essay':
                // For text-based questions, create a single "option" to store the sample answer
                if ($request->filled('sample_answer')) {
                    QuestionOption::create([
                        'question_id' => $question->question_id,
                        'option_text' => $request->sample_answer,
                        'is_correct' => true,
                        'order_number' => 1,
                    ]);
                }
                break;
        }
    }

    /**
     * Get the next order number for a question in an exam set.
     */
    private function getNextOrderNumber($examSetId)
    {
        $maxOrder = Question::where('exam_set_id', $examSetId)->max('order_number');
        return ($maxOrder ?? 0) + 1;
    }

    /**
     * Reorder questions within an exam set.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'questions' => 'required|array',
            'questions.*.id' => 'required|exists:questions,question_id',
            'questions.*.order' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($request) {
                foreach ($request->questions as $questionData) {
                    Question::where('question_id', $questionData['id'])
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