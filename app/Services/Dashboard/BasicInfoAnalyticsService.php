<?php

namespace App\Services\Dashboard;

use App\Models\Applicant;
use App\Models\ApplicantBasicInfo;
use App\Models\Interview;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BasicInfoAnalyticsService
{
    /**
     * Get all dashboard analytics in one call
     */
    public function getDashboardAnalytics(int $days = 30): array
    {
        $cacheKey = "dashboard_basic_info_analytics_v3_{$days}";
        
        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($days) {
            $dateFilter = $days > 0 ? now()->subDays($days) : null;
            
            return [
                'kpis' => $this->getKpis($dateFilter),
                'exam_scores_by_sex' => $this->getTopExamScoresBySex($dateFilter),
                'interview_scores_by_sex' => $this->getTopInterviewScoresBySex($dateFilter),
                'cities' => $this->getCityDistribution($dateFilter),
                'violations' => $this->getViolationDistribution($dateFilter),
                'status_distribution' => $this->getStatusDistribution($dateFilter),
            ];
        });
    }

    /**
     * Get key performance indicators
     */
    public function getKpis($dateFilter = null): array
    {
        $baseQuery = ApplicantBasicInfo::query();
        
        if ($dateFilter) {
            $baseQuery->where('completed_at', '>=', $dateFilter);
        }
        
        $totalCompleted = (clone $baseQuery)->whereNotNull('completed_at')->count();
        
        // Top city/municipality - create fresh query
        $topCity = (clone $baseQuery)
            ->select('city_municipality', DB::raw('COUNT(*) as city_count'))
            ->whereNotNull('city_municipality')
            ->groupBy('city_municipality')
            ->orderByRaw('COUNT(*) DESC')
            ->first();
        
        // Male and Female counts - count ALL applicants in the system (not filtered by date)
        // Join with applicants table to ensure we're counting actual applicants
        $maleCount = ApplicantBasicInfo::join('applicants', 'applicant_basic_infos.applicant_id', '=', 'applicants.applicant_id')
            ->where('applicant_basic_infos.sex', 'Male')
            ->count();
        
        $femaleCount = ApplicantBasicInfo::join('applicants', 'applicant_basic_infos.applicant_id', '=', 'applicants.applicant_id')
            ->where('applicant_basic_infos.sex', 'Female')
            ->count();
        
        return [
            'total_completed' => $totalCompleted,
            'male_count' => $maleCount,
            'top_city' => $topCity->city_municipality ?? 'N/A',
            'female_count' => $femaleCount,
        ];
    }

    /**
     * Get top exam scores segmented by sex
     */
    public function getTopExamScoresBySex($dateFilter = null): array
    {
        $query = Applicant::query()
            ->join('applicant_basic_infos as abi', 'abi.applicant_id', '=', 'applicants.applicant_id')
            ->select('abi.sex', DB::raw('MAX(applicants.enrollassess_score) as max_score'))
            ->whereNotNull('abi.sex')
            ->whereNotNull('applicants.enrollassess_score');
        
        if ($dateFilter) {
            $query->whereNotNull('applicants.exam_completed_at')
                ->where('applicants.exam_completed_at', '>=', $dateFilter);
        }
        
        $results = $query->groupBy('abi.sex')->get();
        $sexes = ['Male', 'Female'];
        $colors = [
            'Male' => '#3B82F6',
            'Female' => '#EC4899',
        ];
        
        $data = [];
        $backgroundColors = [];
        foreach ($sexes as $sex) {
            $score = (float) ($results->firstWhere('sex', $sex)->max_score ?? 0);
            $data[] = round($score, 2);
            $backgroundColors[] = $colors[$sex];
        }
        
        return [
            'labels' => $sexes,
            'data' => $data,
            'colors' => $backgroundColors,
            'headline' => $this->buildComparisonHeadline($data, $sexes, 'exam'),
        ];
    }

    /**
     * Get top interview scores segmented by sex
     */
    public function getTopInterviewScoresBySex($dateFilter = null): array
    {
        $query = Interview::query()
            ->join('applicant_basic_infos as abi', 'abi.applicant_id', '=', 'interviews.applicant_id')
            ->select('abi.sex', DB::raw('MAX(interviews.overall_score) as max_score'))
            ->whereNotNull('abi.sex')
            ->whereNotNull('interviews.overall_score')
            ->where('interviews.status', 'completed');
        
        if ($dateFilter) {
            $query->where('interviews.updated_at', '>=', $dateFilter);
        }
        
        $results = $query->groupBy('abi.sex')->get();
        $sexes = ['Male', 'Female'];
        $colors = [
            'Male' => '#3B82F6',
            'Female' => '#EC4899',
        ];
        
        $data = [];
        $backgroundColors = [];
        foreach ($sexes as $sex) {
            $score = (float) ($results->firstWhere('sex', $sex)->max_score ?? 0);
            $data[] = round($score, 2);
            $backgroundColors[] = $colors[$sex];
        }
        
        return [
            'labels' => $sexes,
            'data' => $data,
            'colors' => $backgroundColors,
            'headline' => $this->buildComparisonHeadline($data, $sexes, 'interview'),
        ];
    }

    /**
     * Get top cities by applicant residence
     */
    public function getCityDistribution($dateFilter = null, int $limit = 10): array
    {
        $query = ApplicantBasicInfo::query();
        
        if ($dateFilter) {
            $query->where('completed_at', '>=', $dateFilter);
        }
        
        $allCities = $query->select('city_municipality as city', DB::raw('COUNT(*) as count'))
            ->whereNotNull('city_municipality')
            ->groupBy('city')
            ->orderByDesc('count')
            ->get();
        
        $topCities = $allCities->take($limit);
        $othersCount = $allCities->skip($limit)->sum('count');
        
        $labels = $topCities->pluck('city')->toArray();
        $data = $topCities->pluck('count')->toArray();
        
        if ($othersCount > 0) {
            $labels[] = 'Others';
            $data[] = $othersCount;
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
            'color' => '#1D4ED8',
        ];
    }
    
    /**
     * Build a simple comparison headline for two-value datasets
     */
    protected function buildComparisonHeadline(array $data, array $labels, string $context): string
    {
        $maleIndex = array_search('Male', $labels, true);
        $femaleIndex = array_search('Female', $labels, true);
        
        $maleScore = $maleIndex !== false ? $data[$maleIndex] : 0;
        $femaleScore = $femaleIndex !== false ? $data[$femaleIndex] : 0;
        
        if ($maleScore <= 0 && $femaleScore <= 0) {
            return ucfirst($context) . ' scores are not available yet.';
        }
        
        if (abs($maleScore - $femaleScore) < 0.01) {
            $score = number_format($maleScore, 2);
            return "Male and Female applicants share the same top " . $context . " score ({$score}).";
        }
        
        if ($maleScore > $femaleScore) {
            return "Male applicants currently hold the higher top " . $context . " score (" . number_format($maleScore, 2) . ").";
        }
        
        return "Female applicants currently hold the higher top " . $context . " score (" . number_format($femaleScore, 2) . ").";
    }
    
    /**
     * Get violation distribution
     */
    public function getViolationDistribution($dateFilter = null): array
    {
        $query = Applicant::query()
            ->whereNotNull('exam_completed_at');
        
        if ($dateFilter) {
            $query->where('exam_completed_at', '>=', $dateFilter);
        }
        
        // Count students by violation count categories
        $clean = (clone $query)->where('violation_count', 0)->count();
        $minor = (clone $query)->whereBetween('violation_count', [1, 2])->count();
        $moderate = (clone $query)->whereBetween('violation_count', [3, 4])->count();
        $flagged = (clone $query)->where('violation_count', 5)->count();
        
        $labels = ['Clean (0)', 'Minor (1-2)', 'Moderate (3-4)', 'Flagged (5)'];
        $data = [$clean, $minor, $moderate, $flagged];
        $colors = [
            '#10B981', // Green for clean
            '#F59E0B', // Amber for minor
            '#EF4444', // Red for moderate
            '#DC2626', // Dark red for flagged
        ];
        
        $total = array_sum($data);
        $headline = $total > 0 
            ? sprintf(
                '%d students completed exams. %d (%.1f%%) were flagged with 5 violations.',
                $total,
                $flagged,
                ($flagged / $total) * 100
            )
            : 'No exam completion data available yet.';
        
        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => $colors,
            'headline' => $headline,
            'total' => $total,
            'flagged' => $flagged,
        ];
    }
    
    /**
     * Get status distribution
     */
    public function getStatusDistribution($dateFilter = null): array
    {
        // Build base query
        $baseQuery = Applicant::query();
        
        if ($dateFilter) {
            $baseQuery->where('created_at', '>=', $dateFilter);
        }
        
        // Count applicants by status - get all statuses that exist in database
        $statusCounts = (clone $baseQuery)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        // Map status codes to display labels
        $statusLabels = [
            'pending' => 'Pending',
            'exam-completed' => 'Exam Completed',
            'interview-available' => 'Interview Available',
            'interview-claimed' => 'Interview Claimed',
            'interview-scheduled' => 'Interview Scheduled',
            'interview-completed' => 'Interview Completed',
            'admitted' => 'Admitted',
            'rejected' => 'Rejected',
        ];
        
        // Define colors for each status
        $statusColors = [
            'pending' => '#9CA3AF', // Gray
            'exam-completed' => '#3B82F6', // Blue
            'interview-available' => '#10B981', // Green
            'interview-claimed' => '#F59E0B', // Amber
            'interview-scheduled' => '#8B5CF6', // Purple
            'interview-completed' => '#06B6D4', // Cyan
            'admitted' => '#10B981', // Green
            'rejected' => '#EF4444', // Red
        ];
        
        // Build arrays for chart (only include statuses that have data)
        $labels = [];
        $data = [];
        $colors = [];
        
        foreach ($statusLabels as $status => $label) {
            if (isset($statusCounts[$status]) && $statusCounts[$status] > 0) {
                $labels[] = $label;
                $data[] = $statusCounts[$status];
                $colors[] = $statusColors[$status] ?? '#9CA3AF';
            }
        }
        
        $total = array_sum($data);
        $admittedCount = $statusCounts['admitted'] ?? 0;
        $rejectedCount = $statusCounts['rejected'] ?? 0;
        
        $headline = $total > 0 
            ? sprintf(
                '%d total applicants. %d admitted, %d rejected.',
                $total,
                $admittedCount,
                $rejectedCount
            )
            : 'No applicant data available yet.';
        
        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => $colors,
            'headline' => $headline,
            'total' => $total,
        ];
    }
    
    /**
     * Clear cache manually
     */
    public function clearCache(): void
    {
        $periods = [7, 30, 90, 0];
        
        foreach ($periods as $period) {
            Cache::forget("dashboard_basic_info_analytics_{$period}");
            Cache::forget("dashboard_basic_info_analytics_v2_{$period}");
            Cache::forget("dashboard_basic_info_analytics_v3_{$period}");
        }
    }
}

