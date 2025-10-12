<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Question;
use Exception;

/**
 * Question Selection Service
 * 
 * Handles per-student random question selection from the question bank.
 * Each student gets a unique set of questions selected on-the-fly when they start the exam.
 */
class QuestionSelectionService
{
    /**
     * Select random questions for a student based on exam configuration
     * Uses applicant_id as seed to ensure consistent selection per student per exam.
     * 
     * @param Exam $exam
     * @param int $applicantId
     * @return \Illuminate\Support\Collection<Question>
     * @throws Exception
     */
    public function selectQuestionsForApplicant(Exam $exam, int $applicantId)
    {
        if (!$exam->total_items) {
            throw new Exception('Exam must have total_items configured.');
        }

        // Validate quotas
        $errors = $exam->validateQuotas();
        if (!empty($errors)) {
            throw new Exception('Exam configuration invalid: ' . implode(', ', $errors));
        }

        // Use applicant_id and exam_id as seed for consistent randomization
        $seed = $exam->exam_id * 1000000 + $applicantId;
        mt_srand($seed);

        $selected = collect();

        // Select MCQ questions if quota specified
        if ($exam->mcq_quota > 0) {
            $mcqQuestions = $exam->multipleChoiceQuestions()
                ->inRandomOrder()
                ->limit($exam->mcq_quota)
                ->get();

            if ($mcqQuestions->count() < $exam->mcq_quota) {
                throw new Exception(
                    "Insufficient MCQ questions. Need {$exam->mcq_quota}, found {$mcqQuestions->count()}."
                );
            }

            $selected = $selected->merge($mcqQuestions);
        }

        // Select T/F questions if quota specified
        if ($exam->tf_quota > 0) {
            $tfQuestions = $exam->trueFalseQuestions()
                ->inRandomOrder()
                ->limit($exam->tf_quota)
                ->get();

            if ($tfQuestions->count() < $exam->tf_quota) {
                throw new Exception(
                    "Insufficient T/F questions. Need {$exam->tf_quota}, found {$tfQuestions->count()}."
                );
            }

            $selected = $selected->merge($tfQuestions);
        }

        // Shuffle to mix question types
        $shuffled = $selected->shuffle();

        // Reset random seed
        mt_srand();

        return $shuffled;
    }

    /**
     * Validate exam has enough questions for quotas
     * 
     * @param Exam $exam
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validateExamConfiguration(Exam $exam): array
    {
        $errors = [];

        if (!$exam->total_items) {
            $errors[] = 'Exam must have total_items configured.';
        }

        // Validate quotas
        $quotaErrors = $exam->validateQuotas();
        $errors = array_merge($errors, $quotaErrors);

        // Check available questions
        if (!$exam->hasEnoughQuestions()) {
            $errors[] = "Not enough questions in question bank. MCQ: {$exam->mcq_count}/{$exam->mcq_quota}, T/F: {$exam->tf_count}/{$exam->tf_quota}";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Get shuffled options for an MCQ question (per student)
     * Uses applicant_id and question_id as seed for consistent option order.
     * 
     * @param Question $question
     * @param int $applicantId
     * @return \Illuminate\Support\Collection
     */
    public function getShuffledOptions(Question $question, int $applicantId)
    {
        if (!$question->isMultipleChoice()) {
            return collect();
        }

        // Use question_id and applicant_id as seed
        $seed = $question->question_id * 1000000 + $applicantId;
        mt_srand($seed);

        $shuffled = $question->options->shuffle();

        // Reset seed
        mt_srand();

        return $shuffled;
    }

    /**
     * Get question bank statistics for an exam
     * 
     * @param Exam $exam
     * @return array
     */
    public function getQuestionBankStats(Exam $exam): array
    {
        return [
            'total_questions' => $exam->total_questions,
            'mcq_available' => $exam->mcq_count,
            'tf_available' => $exam->tf_count,
            'mcq_required' => $exam->mcq_quota ?? 0,
            'tf_required' => $exam->tf_quota ?? 0,
            'total_required' => $exam->total_items,
            'has_enough' => $exam->hasEnoughQuestions(),
        ];
    }
}
