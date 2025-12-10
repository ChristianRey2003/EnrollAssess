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
     * Cached settings to avoid repeated database queries
     * Key format: school_year_id or 'global'
     */
    protected static $cachedWeights = [];

    /**
     * Get scoring weights for a specific school year (cached per request)
     * 
     * @param int|null $schoolYearId School year ID (null for global/default)
     * @return array
     */
    protected function getScoringWeights($schoolYearId = null): array
    {
        $cacheKey = $schoolYearId ?? 'global';
        
        if (!isset(self::$cachedWeights[$cacheKey])) {
            self::$cachedWeights[$cacheKey] = \App\Models\Settings::getScoringWeights($schoolYearId);
        }
        
        return self::$cachedWeights[$cacheKey];
    }

    /**
     * Calculate the overall admission rating for an applicant
     * 
     * Formula: Overall = UEE + (GWA × GWA_weight) + (Interview × Interview_weight) + (SkillTest × SkillTest_weight)
     * 
     * Note: UEE is already weighted based on its percentage, so it's calculated from raw percentage.
     * 
     * @param Applicant $applicant
     * @return array ['overall_rating' => float, 'components' => array]
     */
    public function calculateOverallRating(Applicant $applicant): array
    {
        // Get school year ID from applicant (fallback to null for global/default weights)
        $schoolYearId = $applicant->school_year_id ?? null;
        
        // Get dynamic weights from settings for this school year (cached to avoid repeated queries)
        $weights = $this->getScoringWeights($schoolYearId);
        $ueeWeightPercent = $weights['uee'];
        $gwaWeightPercent = $weights['gwa'];
        $interviewWeightPercent = $weights['interview'];
        $skillTestWeightPercent = $weights['skilltest'];
        
        // Convert percentages to decimals for calculation
        $ueeWeight = $ueeWeightPercent / 100.0;
        $gwaWeight = $gwaWeightPercent / 100.0;
        $interviewWeight = $interviewWeightPercent / 100.0;
        $skillTestWeight = $skillTestWeightPercent / 100.0;
        
        // Get raw scores
        // UEE score is stored as a percentage (0-100) in the database
        // Based on ApplicantController validation: 'score' => 'nullable|numeric|min:0|max:100'
        $ueeRawPercentage = (float) ($applicant->score ?? 0);
        
        // Other scores are already percentages (0-100)
        $gwaRaw = (float) ($applicant->card_tor_gwa ?? 0);
        $skillTestRaw = (float) ($applicant->enrollassess_score ?? 0);  // EnrollAssess Exam
        $interviewRaw = (float) ($applicant->interview_score ?? 0);
        
        // Calculate weighted values
        $ueeWeighted = $ueeRawPercentage * $ueeWeight;
        $gwaWeighted = $gwaRaw * $gwaWeight;
        $interviewWeighted = $interviewRaw * $interviewWeight;
        $skillTestWeighted = $skillTestRaw * $skillTestWeight;

        // Overall = UEE(weighted) + GWA(weighted) + Interview(weighted) + SkillTest(weighted)
        $overallRating = $ueeWeighted + $gwaWeighted + $interviewWeighted + $skillTestWeighted;
        
        return [
            'overall_rating' => round($overallRating, 2),
            'components' => [
                'uee' => [
                    'raw' => round($ueeRawPercentage, 2),         // Calculated for display (0-100)
                    'weighted' => round($ueeWeighted, 2),   // Weighted value
                    'weight' => $ueeWeightPercent,
                ],
                'gwa' => [
                    'raw' => round($gwaRaw, 2),         // 0-100
                    'weighted' => round($gwaWeighted, 2),   // Weighted value
                    'weight' => $gwaWeightPercent,
                ],
                'interview' => [
                    'raw' => round($interviewRaw, 2),      // 0-100
                    'weighted' => round($interviewWeighted, 2), // Weighted value
                    'weight' => $interviewWeightPercent,
                ],
                'skill_test' => [
                    'raw' => round($skillTestRaw, 2),      // 0-100
                    'weighted' => round($skillTestWeighted, 2), // Weighted value
                    'weight' => $skillTestWeightPercent,
                ],
                'interview_skill_combined' => [
                    'raw' => round(($interviewRaw + $skillTestRaw) / 2.0, 2), // Average for display
                    'weighted' => round($interviewWeighted + $skillTestWeighted, 2), // Combined weighted
                    'weight' => $interviewWeightPercent + $skillTestWeightPercent,
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

