<?php

namespace App\Services;

use App\Models\Applicant;

/**
 * AdmissionScoringService
 * 
 * Centralized service for calculating admission scores based on:
 * - University Entrance Examination (UEE): 60%
 * - CARD/TOR GWA: 30%
 * - Interview/Skill Test (EnrollAssess Exam + Interview): 10%
 */
class AdmissionScoringService
{
    /**
     * Calculate the overall admission rating for an applicant
     * 
     * Formula: Overall = (0.60 × UEE%) + (0.30 × GWA%) + (0.10 × Interview/Skill%)
     * 
     * @param Applicant $applicant
     * @return array ['overall_rating' => float, 'components' => array]
     */
    public function calculateOverallRating(Applicant $applicant): array
    {
        // UEE and GWA are ALREADY weighted:
        // - UEE: 0–60 (already the 60% contribution)
        // - GWA: 0–30 (already the 30% contribution)
        $ueeWeighted = (float) ($applicant->score ?? 0);
        $gwaWeighted = (float) ($applicant->card_tor_gwa ?? 0);

        // EnrollAssess and Interview are raw percentages (0–100)
        $examPercentage = (float) ($applicant->enrollassess_score ?? 0);
        $interviewPercentage = (float) ($applicant->interview_score ?? 0);

        // Interview/Skill (10%) = average(exam%, interview%) × 0.10 (0–10)
        $interviewSkillPercentage = ($examPercentage + $interviewPercentage) / 2.0;
        $interviewSkillWeighted = $interviewSkillPercentage * 0.10;

        // Overall = UEE(0–60) + GWA(0–30) + Interview/Skill(0–10)
        $overallRating = $ueeWeighted + $gwaWeighted + $interviewSkillWeighted;
        
        return [
            'overall_rating' => round($overallRating, 2),
            'components' => [
                'uee' => [
                    'raw' => round($ueeWeighted, 2),   // already weighted
                    'weighted' => round($ueeWeighted, 2),
                    'weight' => 60,
                ],
                'gwa' => [
                    'raw' => round($gwaWeighted, 2),    // already weighted
                    'weighted' => round($gwaWeighted, 2),
                    'weight' => 30,
                ],
                'interview_skill' => [
                    'raw' => round($interviewSkillPercentage, 2), // 0–100
                    'weighted' => round($interviewSkillWeighted, 2),
                    'weight' => 10,
                    'breakdown' => [
                        'exam' => round($examPercentage, 2),        // 0–100
                        'interview' => round($interviewPercentage, 2),
                    ],
                ],
            ],
        ];
    }
    
    /**
     * Check if all required scores are available for overall rating calculation
     * 
     * @param Applicant $applicant
     * @return bool
     */
    public function hasAllRequiredScores(Applicant $applicant): bool
    {
        return !is_null($applicant->score) &&
               !is_null($applicant->card_tor_gwa) &&
               !is_null($applicant->enrollassess_score) &&
               !is_null($applicant->interview_score);
    }
    
    /**
     * Get missing score components
     * 
     * @param Applicant $applicant
     * @return array
     */
    public function getMissingScores(Applicant $applicant): array
    {
        $missing = [];
        
        if (is_null($applicant->score)) {
            $missing[] = 'University Entrance Examination';
        }
        
        if (is_null($applicant->card_tor_gwa)) {
            $missing[] = 'CARD/TOR GWA';
        }
        
        if (is_null($applicant->enrollassess_score)) {
            $missing[] = 'EnrollAssess Exam';
        }
        
        if (is_null($applicant->interview_score)) {
            $missing[] = 'Interview Evaluation';
        }
        
        return $missing;
    }
    
    /**
     * Get verbal description based on overall rating
     * 
     * @param float $overallRating
     * @return string
     */
    public function getVerbalDescription(float $overallRating): string
    {
        if ($overallRating >= 95) return 'Outstanding';
        if ($overallRating >= 90) return 'Excellent';
        if ($overallRating >= 85) return 'Very Good';
        if ($overallRating >= 80) return 'Good';
        if ($overallRating >= 75) return 'Satisfactory';
        if ($overallRating >= 70) return 'Fair';
        if ($overallRating >= 60) return 'Conditional';
        return 'Below Standards';
    }
    
    /**
     * Determine if applicant passes based on overall rating
     * 
     * @param float $overallRating
     * @param float $passingGrade Default 70
     * @return bool
     */
    public function isPassing(float $overallRating, float $passingGrade = 70.0): bool
    {
        return $overallRating >= $passingGrade;
    }
}

