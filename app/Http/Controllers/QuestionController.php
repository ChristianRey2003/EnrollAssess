<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Exam;
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
        $query = Question::with(['exam', 'options'])
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

        // Filter by exam
        if ($request->filled('exam_id')) {
            $query->where('exam_id', $request->exam_id);
        }

        $questions = $query->paginate(10);
        $exams = Exam::where('is_active', true)->get();

        // Get question counts by type for dashboard
        $questionStats = [
            'total' => Question::active()->count(),
            'multiple_choice' => Question::active()->byType('multiple_choice')->count(),
            'true_false' => Question::active()->byType('true_false')->count(),
            'short_answer' => Question::active()->byType('short_answer')->count(),
            'essay' => Question::active()->byType('essay')->count(),
        ];

        return view('admin.questions', compact('questions', 'exams', 'questionStats'));
    }

    /**
     * Show the form for creating a new question.
     */
    public function create()
    {
        $exams = Exam::where('is_active', true)->get();
        return view('admin.questions.create', compact('exams'));
    }

    /**
     * Store a newly created question in storage.
     */
    public function store(Request $request)
    {
        $validator = $this->validateQuestionData($request);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first()
                ]);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $question = null;
            DB::transaction(function () use ($request, &$question) {
                // Create the question
                $question = Question::create([
                    'exam_id' => $request->exam_id,
                    'question_text' => $request->question_text,
                    'question_type' => $request->question_type,
                    'points' => $request->points ?? 1,
                    'order_number' => $request->order_number ?? $this->getNextOrderNumber($request->exam_id),
                    'explanation' => $request->explanation,
                    'is_active' => $request->boolean('is_active', true),
                ]);

                // Create options based on question type
                $this->createQuestionOptions($question, $request);
            });

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Question created successfully!',
                    'data' => $question
                ]);
            }

            return redirect()->route('admin.questions')
                ->with('success', 'Question created successfully!');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create question: ' . $e->getMessage()
                ]);
            }
            return redirect()->back()
                ->with('error', 'Failed to create question. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified question.
     */
    public function show($id)
    {
        try {
            $question = Question::with(['exam', 'options'])->findOrFail($id);
            
            // If AJAX request, return JSON
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'question' => $question
                ]);
            }
            
            return view('admin.questions.show', compact('question'));
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Question not found'
                ], 404);
            }
            abort(404);
        }
    }

    /**
     * Show the form for editing the specified question.
     */
    public function edit($id)
    {
        $question = Question::with(['exam', 'options'])->findOrFail($id);
        $exams = Exam::where('is_active', true)->get();
        
        return view('admin.questions.create', compact('question', 'exams'));
    }

    /**
     * Update the specified question in storage.
     */
    public function update(Request $request, $id)
    {
        $question = Question::findOrFail($id);
        $validator = $this->validateQuestionData($request, $question);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first()
                ]);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::transaction(function () use ($request, $question) {
                // Update the question
                $question->update([
                    'exam_id' => $request->exam_id,
                    'question_text' => $request->question_text,
                    'question_type' => $request->question_type,
                    'points' => $request->points ?? 1,
                    'order_number' => $request->order_number ?? $question->order_number,
                    'explanation' => $request->explanation,
                ]);

                // Delete existing options and create new ones
                $question->options()->delete();
                $this->createQuestionOptions($question, $request);
            });

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Question updated successfully!',
                    'data' => $question
                ]);
            }

            return redirect()->route('admin.questions')
                ->with('success', 'Question updated successfully!');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update question: ' . $e->getMessage()
                ]);
            }
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
            'exam_id' => 'required|exists:exams,exam_id',
            'question_text' => 'required|string|min:10|max:2000',
            'question_type' => 'required|in:multiple_choice,true_false,short_answer,essay',
            'points' => 'nullable|integer|min:1|max:100',
            'explanation' => 'nullable|string|max:1000',
        ];

        // Add type-specific validation rules
        switch ($request->question_type) {
            case 'multiple_choice':
                // Handle both JSON string (AJAX) and array (form) formats
                if (is_string($request->options)) {
                    $rules['options'] = 'required|string';
                } else {
                    $rules['options'] = 'required|array|min:2|max:6';
                    $rules['options.*'] = 'required|string|max:500';
                }
                $rules['correct_option'] = 'nullable|integer|min:0|max:5';
                break;

            case 'true_false':
                $rules['tf_answer'] = 'nullable|in:true,false';
                $rules['correct_option'] = 'nullable|integer|in:0,1';
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
        // Handle JSON-encoded options from AJAX
        if ($request->has('options') && is_string($request->options)) {
            $options = json_decode($request->options, true);
            if (is_array($options)) {
                foreach ($options as $index => $option) {
                    QuestionOption::create([
                        'question_id' => $question->question_id,
                        'option_text' => $option['option_text'],
                        'is_correct' => $option['is_correct'] ?? false,
                        'order_number' => $index + 1,
                    ]);
                }
                return;
            }
        }
        
        switch ($request->question_type) {
            case 'multiple_choice':
                if ($request->has('options')) {
                    foreach ($request->options as $index => $optionText) {
                        if (empty(trim($optionText))) continue;
                        
                        QuestionOption::create([
                            'question_id' => $question->question_id,
                            'option_text' => trim($optionText),
                            'is_correct' => $index == $request->correct_option,
                            'order_number' => $index + 1,
                        ]);
                    }
                }
                break;

            case 'true_false':
                // Check if TF answer is in correct_option (from AJAX)
                $tfAnswer = $request->tf_answer ?? null;
                if ($request->has('correct_option')) {
                    $tfAnswer = $request->correct_option == 0 ? 'true' : 'false';
                }
                
                // Create True and False options
                QuestionOption::create([
                    'question_id' => $question->question_id,
                    'option_text' => 'True',
                    'is_correct' => $tfAnswer === 'true' || $tfAnswer === '0',
                    'order_number' => 1,
                ]);

                QuestionOption::create([
                    'question_id' => $question->question_id,
                    'option_text' => 'False',
                    'is_correct' => $tfAnswer === 'false' || $tfAnswer === '1',
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
     * Get the next order number for a question in an exam.
     */
    private function getNextOrderNumber($examId)
    {
        $maxOrder = Question::where('exam_id', $examId)->max('order_number');
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

    /**
     * Duplicate a question with all its options.
     */
    public function duplicate($id)
    {
        try {
            $originalQuestion = Question::with('options')->findOrFail($id);

            DB::transaction(function () use ($originalQuestion) {
                // Create new question
                $newQuestion = Question::create([
                    'exam_id' => $originalQuestion->exam_id,
                    'question_text' => $originalQuestion->question_text . ' (Copy)',
                    'question_type' => $originalQuestion->question_type,
                    'points' => $originalQuestion->points,
                    'order_number' => $this->getNextOrderNumber($originalQuestion->exam_id),
                    'explanation' => $originalQuestion->explanation,
                    'is_active' => false, // Start as inactive
                ]);

                // Copy question options
                foreach ($originalQuestion->options as $originalOption) {
                    QuestionOption::create([
                        'question_id' => $newQuestion->question_id,
                        'option_text' => $originalOption->option_text,
                        'is_correct' => $originalOption->is_correct,
                        'order_number' => $originalOption->order_number,
                    ]);
                }

                return $newQuestion;
            });

            return response()->json([
                'success' => true,
                'message' => 'Question duplicated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to duplicate question. Please try again.'
            ]);
        }
    }
}