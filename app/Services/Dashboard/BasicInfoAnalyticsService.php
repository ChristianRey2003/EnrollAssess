<?php

namespace App\Services\Dashboard;

use App\Models\Applicant;
use App\Models\ApplicantBasicInfo;
use App\Models\Interview;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BasicInfoAnalyticsService
{
    /**
     * Apply school year filter to query if needed
     */
    protected function applySchoolYearFilter($query)
    {
        $schoolYearId = session('school_year_id');
        if ($schoolYearId) {
            $query->forSchoolYear($schoolYearId);
        }
        return $query;
    }

    /**
     * Get all dashboard analytics in one call
     */
    public function getDashboardAnalytics(int $days = 30): array
    {
        $schoolYearId = session('school_year_id');
        $cacheKey = "dashboard_basic_info_analytics_v4_{$days}_sy_{$schoolYearId}";
        
        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($days) {
            $dateFilter = $days > 0 ? now()->subDays($days) : null;
            
            return [
                'kpis' => $this->getKpis($dateFilter),
                'exam_scores_by_sex' => $this->getTopExamScoresBySex($dateFilter),
                'interview_scores_by_sex' => $this->getTopInterviewScoresBySex($dateFilter),
                'cities' => $this->getCityDistribution($dateFilter),
                'violations' => $this->getViolationDistribution($dateFilter),
                'status_distribution' => $this->getStatusDistribution($dateFilter),
                'pending_interview_distribution' => $this->getPendingInterviewDistribution($dateFilter),
            ];
        });
    }

    /**
     * Get key performance indicators
     */
    public function getKpis($dateFilter = null): array
    {
        // KPIs should reflect overall progress, independent of the dashboard period filter
        $baseQuery = Applicant::query();
        $this->applySchoolYearFilter($baseQuery);

        $totalApplicants = (clone $baseQuery)->count();

        $finishedExamStatuses = [
            'exam-completed',
            'interview-available',
            'interview-claimed',
            'interview-scheduled',
            'interview-completed',
            'admitted',
            'rejected',
        ];
        $finishedExam = (clone $baseQuery)->whereIn('status', $finishedExamStatuses)->count();

        $interviewCompleted = (clone $baseQuery)->whereIn('status', [
            'interview-completed',
            'admitted',
            'rejected',
        ])->count();

        $fullyScreened = $interviewCompleted;

        return [
            'total_applicants' => $totalApplicants,
            'finished_exam' => $finishedExam,
            'interview_completed' => $interviewCompleted,
            'fully_screened' => $fullyScreened,
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
        
        $this->applySchoolYearFilter($query);
        
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
        $schoolYearId = session('school_year_id');
        
        $query = Interview::query()
            ->join('applicant_basic_infos as abi', 'abi.applicant_id', '=', 'interviews.applicant_id')
            ->select('abi.sex', DB::raw('MAX(interviews.overall_score) as max_score'))
            ->whereNotNull('abi.sex')
            ->whereNotNull('interviews.overall_score')
            ->where('interviews.status', 'completed');
        
        // Filter by school year through applicants
        if ($schoolYearId) {
            $query->whereHas('applicant', function($q) use ($schoolYearId) {
                $q->where('school_year_id', $schoolYearId);
            });
        }
        
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
        $schoolYearId = session('school_year_id');
        
        $query = ApplicantBasicInfo::query();
        
        // Filter by school year through applicants
        if ($schoolYearId) {
            $query->whereHas('applicant', function($q) use ($schoolYearId) {
                $q->where('school_year_id', $schoolYearId);
            });
        }
        
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
        
        $this->applySchoolYearFilter($query);
        
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
        $this->applySchoolYearFilter($baseQuery);
        
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
     * Get pending vs interview distribution overall and by instructor
     */
    public function getPendingInterviewDistribution($dateFilter = null): array
    {
        $baseQuery = Applicant::query();
        $this->applySchoolYearFilter($baseQuery);

        $pendingStatuses = ['pending'];
        $interviewStatuses = ['interview-completed', 'admitted', 'rejected'];

        $overallPending = (clone $baseQuery)->whereIn('status', $pendingStatuses)->count();
        $overallInterview = (clone $baseQuery)->whereIn('status', $interviewStatuses)->count();

        $overall = [
            'labels' => ['Pending Applicants', 'Interview Completed'],
            'data' => [$overallPending, $overallInterview],
            'colors' => ['#9CA3AF', '#06B6D4'],
            'headline' => sprintf(
                'Across all instructors: %d pending, %d interview completed.',
                $overallPending,
                $overallInterview
            ),
        ];

        $instructorCounts = (clone $baseQuery)
            ->whereNotNull('assigned_instructor_id')
            ->select(
                'assigned_instructor_id',
                DB::raw("SUM(CASE WHEN status IN ('pending') THEN 1 ELSE 0 END) as pending_count"),
                DB::raw("SUM(CASE WHEN status IN ('interview-completed','admitted','rejected') THEN 1 ELSE 0 END) as interview_count")
            )
            ->groupBy('assigned_instructor_id')
            ->get();

        if ($instructorCounts->isEmpty()) {
            return [
                'overall' => $overall,
                'options' => [],
                'datasets' => [],
            ];
        }

        $instructorIds = $instructorCounts->pluck('assigned_instructor_id')->unique()->values();
        $instructorNames = User::whereIn('user_id', $instructorIds)
            ->get()
            ->mapWithKeys(function ($user) {
                $displayName = $user->full_name
                    ?? $user->name
                    ?? $user->username
                    ?? "Instructor {$user->user_id}";
                return [$user->user_id => $displayName];
            });

        $options = [];
        $datasets = [];

        foreach ($instructorCounts as $row) {
            $name = $instructorNames[$row->assigned_instructor_id] ?? "Instructor {$row->assigned_instructor_id}";
            $options[] = [
                'id' => (string) $row->assigned_instructor_id,
                'name' => $name,
            ];

            $pendingCount = (int) $row->pending_count;
            $interviewCount = (int) $row->interview_count;
            $headline = sprintf(
                '%s: %d pending, %d interview completed.',
                $name,
                $pendingCount,
                $interviewCount
            );

            $datasets[(string) $row->assigned_instructor_id] = [
                'labels' => ['Pending Applicants', 'Interview Completed'],
                'data' => [$pendingCount, $interviewCount],
                'colors' => ['#9CA3AF', '#06B6D4'],
                'headline' => $headline,
                'name' => $name,
            ];
        }

        // Sort options alphabetically by instructor name
        usort($options, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return [
            'overall' => $overall,
            'options' => $options,
            'datasets' => $datasets,
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
            Cache::forget("dashboard_basic_info_analytics_v4_{$period}");
        }
    }
}

