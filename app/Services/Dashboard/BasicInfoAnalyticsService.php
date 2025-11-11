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
        $cacheKey = "dashboard_basic_info_analytics_v2_{$days}";
        
        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($days) {
            $dateFilter = $days > 0 ? now()->subDays($days) : null;
            
            return [
                'kpis' => $this->getKpis($dateFilter),
                'exam_scores_by_sex' => $this->getTopExamScoresBySex($dateFilter),
                'interview_scores_by_sex' => $this->getTopInterviewScoresBySex($dateFilter),
                'cities' => $this->getCityDistribution($dateFilter),
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
        $avgAge = (clone $baseQuery)->avg('age') ?? 0;
        
        // Top city/municipality - create fresh query
        $topCity = (clone $baseQuery)
            ->select('city_municipality', DB::raw('COUNT(*) as city_count'))
            ->whereNotNull('city_municipality')
            ->groupBy('city_municipality')
            ->orderByRaw('COUNT(*) DESC')
            ->first();
        
        // Female percentage - create fresh query
        $femaleCount = (clone $baseQuery)->where('sex', 'Female')->count();
        $femalePercentage = $totalCompleted > 0 ? round(($femaleCount / $totalCompleted) * 100, 1) : 0;
        
        return [
            'total_completed' => $totalCompleted,
            'avg_age' => round($avgAge, 1),
            'top_city' => $topCity->city_municipality ?? 'N/A',
            'female_percentage' => $femalePercentage,
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
     * Clear cache manually
     */
    public function clearCache(): void
    {
        $periods = [7, 30, 90, 0];
        
        foreach ($periods as $period) {
            Cache::forget("dashboard_basic_info_analytics_{$period}");
            Cache::forget("dashboard_basic_info_analytics_v2_{$period}");
        }
    }
}

