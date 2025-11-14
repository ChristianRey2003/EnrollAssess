<?php

namespace App\Services;

use App\Models\Applicant;

/**
 * AdmissionScoringService
 * 
 * Centralized service for calculating admission scores using weighted averages.
 * 
 * Formula: FinalGrade = UEE + (GWA × 0.3) + (Interview × 0.05) + (SkillTest × 0.05)
 * 
 * Components:
 * - University Entrance Examination (UEE): already weighted (0-60), use as-is
 * - CARD/TOR GWA: raw percentage (0-100) × 0.3
 * - Interview: raw percentage (0-100) × 0.05
 * - Skill Test (EnrollAssess Exam): raw percentage (0-100) × 0.05
 */
class AdmissionScoringService
{
    /**
     * Calculate the overall admission rating for an applicant
     * 
     * Formula: Overall = UEE + (0.30 × GWA%) + (0.05 × Interview%) + (0.05 × SkillTest%)
     * 
     * Note: UEE is already weighted (0-60 range), so it's used as-is without multiplication.
     * 
     * @param Applicant $applicant
     * @return array ['overall_rating' => float, 'components' => array]
     */
    public function calculateOverallRating(Applicant $applicant): array
    {
        // UEE is already weighted (0-60 range), use as-is
        $ueeWeighted = (float) ($applicant->score ?? 0);
        
        // GWA, Interview, and SkillTest are raw percentages (0-100)
        $gwaRaw = (float) ($applicant->card_tor_gwa ?? 0);
        $skillTestRaw = (float) ($applicant->enrollassess_score ?? 0);  // EnrollAssess Exam
        $interviewRaw = (float) ($applicant->interview_score ?? 0);

        // Apply weights: GWA (30%), Interview (5%), SkillTest (5%)
        // UEE is already weighted, so no multiplication needed
        $gwaWeighted = $gwaRaw * 0.30;
        $interviewWeighted = $interviewRaw * 0.05;
        $skillTestWeighted = $skillTestRaw * 0.05;

        // Overall = UEE(already weighted 0-60) + GWA(30%) + Interview(5%) + SkillTest(5%)
        $overallRating = $ueeWeighted + $gwaWeighted + $interviewWeighted + $skillTestWeighted;
        
        // Calculate UEE raw percentage for display (reverse calculation: weighted / 0.6)
        $ueeRaw = $ueeWeighted > 0 ? ($ueeWeighted / 0.60) : 0;
        
        return [
            'overall_rating' => round($overallRating, 2),
            'components' => [
                'uee' => [
                    'raw' => round($ueeRaw, 2),         // Calculated for display (0-100)
                    'weighted' => round($ueeWeighted, 2),   // Already weighted (0-60)
                    'weight' => 60,
                ],
                'gwa' => [
                    'raw' => round($gwaRaw, 2),         // 0-100
                    'weighted' => round($gwaWeighted, 2),   // 0-30
                    'weight' => 30,
                ],
                'interview' => [
                    'raw' => round($interviewRaw, 2),      // 0-100
                    'weighted' => round($interviewWeighted, 2), // 0-5
                    'weight' => 5,
                ],
                'skill_test' => [
                    'raw' => round($skillTestRaw, 2),      // 0-100
                    'weighted' => round($skillTestWeighted, 2), // 0-5
                    'weight' => 5,
                ],
                'interview_skill_combined' => [
                    'raw' => round(($interviewRaw + $skillTestRaw) / 2.0, 2), // Average for display
                    'weighted' => round($interviewWeighted + $skillTestWeighted, 2), // 0-10
                    'weight' => 10,
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

