<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Result;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Services\InterviewPoolService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ExamSubmissionController extends Controller
{
    protected $interviewPoolService;

    public function __construct(InterviewPoolService $interviewPoolService)
    {
        $this->interviewPoolService = $interviewPoolService;
    }

    /**
     * Complete the exam and calculate results
     */
    public function completeExam(Request $request)
    {
        $request->validate([
            'applicant_id' => 'required|exists:applicants,applicant_id',
            'answers' => 'required|array',
            'exam_session_id' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $applicantId = $request->applicant_id;
            $answers = $request->answers;
            
            $applicant = Applicant::findOrFail($applicantId);
            
            // Calculate score (using question bank from session)
            $scoreData = $this->calculateExamScore($answers);
            
            // Update applicant with exam results
            $applicant->update([
                'enrollassess_score' => $scoreData['total_score'],
                'status' => 'exam-completed',
                'exam_completed_at' => now(),
                'verbal_description' => $scoreData['verbal_description']
            ]);

            // Store detailed results
            $this->storeExamResults($applicantId, $answers, $scoreData);

            // **AUTO-ADD TO INTERVIEW POOL**
            $this->addToInterviewPool($applicantId, $scoreData['percentage']);

            DB::commit();

            Log::info("Exam completed for applicant {$applicantId} with score {$scoreData['percentage']}%");

            return response()->json([
                'success' => true,
                'message' => 'Exam completed successfully!',
                'score' => $scoreData['percentage'],
                'total_score' => $scoreData['total_score'],
                'max_score' => $scoreData['max_score'],
                'correct_answers' => $scoreData['correct_answers'],
                'total_questions' => $scoreData['total_questions'],
                'verbal_description' => $scoreData['verbal_description'],
                'redirect_url' => route('exam.results')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to complete exam: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete exam: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add applicant to interview pool after exam completion
     */
    private function addToInterviewPool($applicantId, $examPercentage)
    {
        try {
            // Use the interview pool service to add applicant with score-based priority
            $interview = $this->interviewPoolService->processExamCompletion($applicantId, $examPercentage);
            
            Log::info("Applicant {$applicantId} automatically added to interview pool with priority: {$interview->priority_level}");
            
            return $interview;
            
        } catch (\Exception $e) {
            // Log the error but don't fail the exam completion
            Log::error("Failed to add applicant {$applicantId} to interview pool: " . $e->getMessage());
        }
    }

    /**
     * Calculate exam score from answers
     */
    private function calculateExamScore($answers)
    {
        $totalScore = 0;
        $maxScore = 0;
        $correctAnswers = 0;
        $totalQuestions = count($answers);

        foreach ($answers as $questionId => $selectedAnswer) {
            $question = Question::with('options')->find($questionId);
            
            if (!$question) {
                continue;
            }

            $maxScore += $question->points ?? 1;

            // Handle different question types
            if ($question->question_type === 'essay') {
                // Essay questions need manual grading, for now give partial credit if answered
                if (!empty(trim($selectedAnswer))) {
                    $totalScore += ($question->points ?? 1) * 0.5; // 50% for answering
                    $correctAnswers++; // Count as answered
                }
            } else {
                // Multiple choice and true/false questions
                $correctOption = $question->options->where('is_correct', true)->first();
                
                // Handle both option_id and old format for backward compatibility
                $isCorrect = false;
                if ($correctOption) {
                    $isCorrect = ($selectedAnswer == $correctOption->option_id) || 
                               ($selectedAnswer == $correctOption->id);
                }
                
                if ($isCorrect) {
                    $totalScore += $question->points ?? 1;
                    $correctAnswers++;
                }
            }
        }

        $percentage = $maxScore > 0 ? round(($totalScore / $maxScore) * 100, 2) : 0;
        
        // Determine verbal description
        $verbalDescription = $this->getVerbalDescription($percentage);

        return [
            'total_score' => $totalScore,
            'max_score' => $maxScore,
            'percentage' => $percentage,
            'correct_answers' => $correctAnswers,
            'total_questions' => $totalQuestions,
            'verbal_description' => $verbalDescription
        ];
    }

    /**
     * Store detailed exam results
     */
    private function storeExamResults($applicantId, $answers, $scoreData, $autoSubmitted = false, $autoSubmitReason = null)
    {
        foreach ($answers as $questionId => $selectedAnswer) {
            $question = Question::with('options')->find($questionId);
            
            if (!$question) {
                continue;
            }

            if ($question->question_type === 'essay') {
                // Handle essay questions
                $isCorrect = !empty(trim($selectedAnswer)); // Consider answered as correct for now
                $pointsEarned = $isCorrect ? ($question->points ?? 1) * 0.5 : 0; // 50% for answering
                
                Result::create([
                    'applicant_id' => $applicantId,
                    'question_id' => $questionId,
                    'answer_text' => $selectedAnswer ?: 'No answer provided',
                    'is_correct' => $isCorrect,
                    'points_earned' => $pointsEarned,
                    'answered_at' => now(),
                ]);
            } else {
                // Handle multiple choice and true/false questions
                $correctOption = $question->options->where('is_correct', true)->first();
                $selectedOption = QuestionOption::find($selectedAnswer);
                
                // Handle both option_id and old format for backward compatibility
                $isCorrect = false;
                if ($correctOption) {
                    $isCorrect = ($selectedAnswer == $correctOption->option_id) || 
                               ($selectedAnswer == $correctOption->id);
                }

                Result::create([
                    'applicant_id' => $applicantId,
                    'question_id' => $questionId,
                    'selected_option_id' => $selectedAnswer,
                    'answer_text' => $selectedOption ? $selectedOption->option_text : 'No answer',
                    'is_correct' => $isCorrect,
                    'points_earned' => $isCorrect ? ($question->points ?? 1) : 0,
                    'answered_at' => now(),
                ]);
            }
        }
    }

    /**
     * Get verbal description based on percentage
     */
    private function getVerbalDescription($percentage)
    {
        if ($percentage >= 95) return 'Excellent';
        if ($percentage >= 85) return 'Very Good';
        if ($percentage >= 75) return 'Good';
        if ($percentage >= 65) return 'Satisfactory';
        if ($percentage >= 50) return 'Fair';
        return 'Needs Improvement';
    }
}
