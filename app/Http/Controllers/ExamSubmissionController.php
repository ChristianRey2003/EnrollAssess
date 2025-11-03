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
use Illuminate\Support\Str;

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
            
            // Get exam session to retrieve assigned question IDs
            $examSession = Session::get('exam_session');
            if (!$examSession || empty($examSession['question_ids'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Exam session expired or invalid. Please contact the administrator.'
                ], 400);
            }
            
            // Generate unique attempt token for this exam submission
            $attemptToken = Str::uuid()->toString();
            
            // Calculate score (using only assigned questions from session)
            $scoreData = $this->calculateExamScore($answers, $examSession['question_ids']);
            
            // Update applicant with exam results
            $applicant->update([
                'enrollassess_score' => $scoreData['percentage'],
                'status' => 'exam-completed',
                'exam_completed_at' => now(),
                'verbal_description' => $scoreData['verbal_description']
            ]);

            // Store detailed results (only for assigned questions) with attempt token
            $this->storeExamResults($applicantId, $answers, $scoreData, $examSession['question_ids'], false, null, $attemptToken);
            
            // Store attempt token in session for results page
            session(['exam_attempt_token' => $attemptToken]);

            // **MARK ACCESS CODE AS USED**
            $accessCode = $applicant->accessCode;
            if ($accessCode && !$accessCode->is_used) {
                $accessCode->markAsUsed();
                Log::info("Access code {$accessCode->code} marked as used for applicant {$applicantId}");
            }

            // **AUTO-ADD TO INTERVIEW POOL**
            $this->addToInterviewPool($applicantId, $scoreData['percentage']);

            // Clear exam session after successful submission
            Session::forget('exam_session');

            DB::commit();

            // Dispatch exam completed event
            \App\Events\ExamCompleted::dispatch($applicant->fresh(), $scoreData['percentage']);
            
            // Dispatch statistics update event
            $this->dispatchStatisticsUpdate();

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
     * Calculate exam score from answers (only for assigned questions)
     */
    private function calculateExamScore($answers, $assignedQuestionIds)
    {
        $totalScore = 0;
        $maxScore = 0;
        $correctAnswers = 0;
        
        // Filter answers to only include assigned questions
        $assignedQuestionIds = collect($assignedQuestionIds);
        $filteredAnswers = array_filter($answers, function($answer, $questionId) use ($assignedQuestionIds) {
            return $assignedQuestionIds->contains($questionId);
        }, ARRAY_FILTER_USE_BOTH);
        
        $totalQuestions = $assignedQuestionIds->count();

        foreach ($filteredAnswers as $questionId => $selectedAnswer) {
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
            } elseif ($question->question_type === 'true_false') {
                // True/False questions: accept either option IDs or synthetic values (true_*/false_*)
                $isCorrect = false;

                // Prefer option-based evaluation if option ID provided
                $selectedOption = null;
                if (is_scalar($selectedAnswer)) {
                    $selectedOption = QuestionOption::where('question_id', $question->question_id)
                        ->where('option_id', $selectedAnswer)
                        ->first();
                }

                if ($selectedOption) {
                    $isCorrect = (bool) $selectedOption->is_correct;
                } else {
                    // Fallback to synthetic value handling
                    if (is_string($selectedAnswer)) {
                        if (strpos($selectedAnswer, 'true_') === 0) {
                            $isCorrect = ($question->correct_answer === true);
                        } elseif (strpos($selectedAnswer, 'false_') === 0) {
                            $isCorrect = ($question->correct_answer === false);
                        }
                    }
                }

                if ($isCorrect) {
                    $totalScore += $question->points ?? 1;
                    $correctAnswers++;
                }
            } else {
                // Multiple choice questions
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
     * Store detailed exam results (only for assigned questions)
     */
    private function storeExamResults($applicantId, $answers, $scoreData, $assignedQuestionIds, $autoSubmitted = false, $autoSubmitReason = null, $attemptToken = null)
    {
        // Filter answers to only include assigned questions
        $assignedQuestionIds = collect($assignedQuestionIds);
        
        foreach ($answers as $questionId => $selectedAnswer) {
            // Skip if question is not in assigned list
            if (!$assignedQuestionIds->contains($questionId)) {
                continue;
            }
            
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
                    'attempt_token' => $attemptToken,
                ]);
            } elseif ($question->question_type === 'true_false') {
                // Handle true/false questions: accept both option IDs and synthetic values
                $isCorrect = false;
                $answerText = 'No answer';
                $selectedOptionId = null;

                // Try option-based selection first
                $selectedOption = null;
                if (is_scalar($selectedAnswer)) {
                    $selectedOption = QuestionOption::where('question_id', $question->question_id)
                        ->where('option_id', $selectedAnswer)
                        ->first();
                }

                if ($selectedOption) {
                    $selectedOptionId = $selectedOption->option_id;
                    $answerText = in_array(strtolower($selectedOption->option_text), ['true', 't', 'yes']) ? 'True' : 'False';
                    $isCorrect = (bool) $selectedOption->is_correct;
                } else {
                    // Fallback to synthetic pattern
                    if (is_string($selectedAnswer)) {
                        if (strpos($selectedAnswer, 'true_') === 0) {
                            $answerText = 'True';
                            $isCorrect = ($question->correct_answer === true);
                        } elseif (strpos($selectedAnswer, 'false_') === 0) {
                            $answerText = 'False';
                            $isCorrect = ($question->correct_answer === false);
                        }
                    }
                }

                Result::create([
                    'applicant_id' => $applicantId,
                    'question_id' => $questionId,
                    'selected_option_id' => $selectedOptionId, // store option if provided
                    'answer_text' => $answerText,
                    'is_correct' => $isCorrect,
                    'points_earned' => $isCorrect ? ($question->points ?? 1) : 0,
                    'answered_at' => now(),
                    'attempt_token' => $attemptToken,
                ]);
            } else {
                // Handle multiple choice questions
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
                    'attempt_token' => $attemptToken,
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

    /**
     * Dispatch statistics update event
     */
    protected function dispatchStatisticsUpdate()
    {
        $stats = [
            'total' => Applicant::count(),
            'pending' => Applicant::where('status', 'pending')->count(),
            'exam_completed' => Applicant::where('status', 'exam-completed')->count(),
            'interview_scheduled' => Applicant::where('status', 'interview-scheduled')->count(),
            'interview_completed' => Applicant::where('status', 'interview-completed')->count(),
            'admitted' => Applicant::where('status', 'admitted')->count(),
            'rejected' => Applicant::where('status', 'rejected')->count(),
            'with_access_codes' => Applicant::whereHas('accessCode')->count(),
            'without_access_codes' => Applicant::whereDoesntHave('accessCode')->count(),
        ];

        \App\Events\StatisticsUpdated::dispatch($stats);
    }
}
