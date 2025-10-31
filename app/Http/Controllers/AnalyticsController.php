<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Interview;
use App\Models\Exam;
use App\Models\Result;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AnalyticsController extends Controller
{
    /**
     * Display the analytics dashboard
     */
    public function index()
    {
        $stats = $this->getOverviewStats();
        
        return view('admin.analytics', compact('stats'));
    }

    /**
     * Get overview statistics
     */
    private function getOverviewStats()
    {
        try {
            $totalApplicants = Applicant::count();
            $examCompleted = Applicant::where('status', '!=', 'pending')->count();
            $admitted = Applicant::where('status', 'admitted')->count();
            $rejected = Applicant::where('status', 'rejected')->count();

            // Calculate pass rate
            $passedCount = Applicant::whereIn('status', [
                'exam-completed', 'interview-scheduled', 'interview-completed', 'admitted'
            ])->count();
            $passRate = $totalApplicants > 0 ? round(($passedCount / $totalApplicants) * 100, 1) : 0;

            // Average scores - simplified to avoid potential issues
            $avgExamScore = 0;
            $avgInterviewScore = 0;
            
            try {
                $avgExamScore = Applicant::whereNotNull('enrollassess_score')->avg('enrollassess_score') ?? 0;
            } catch (Exception $e) {
                // Skip if column doesn't exist
            }
            
            try {
                $avgInterviewScore = Interview::where('status', 'completed')->avg('overall_score') ?? 0;
            } catch (Exception $e) {
                // Skip if column doesn't exist
            }

            return [
                'total_applicants' => $totalApplicants,
                'exam_completed' => $examCompleted,
                'admitted' => $admitted,
                'rejected' => $rejected,
                'pass_rate' => $passRate,
                'avg_exam_score' => round($avgExamScore, 1),
                'avg_interview_score' => round($avgInterviewScore, 1),
            ];
        } catch (Exception $e) {
            // Return basic stats if there are any issues
            return [
                'total_applicants' => 0,
                'exam_completed' => 0,
                'admitted' => 0,
                'rejected' => 0,
                'pass_rate' => 0,
                'avg_exam_score' => 0,
                'avg_interview_score' => 0,
            ];
        }
    }

    /**
     * Get score distribution data
     */
    public function getScoreDistribution(Request $request)
    {
        $type = $request->get('type', 'exam'); // exam or interview

        if ($type === 'exam') {
            $results = Applicant::select(
                    DB::raw('FLOOR(enrollassess_score / 10) * 10 as score_range'),
                    DB::raw('COUNT(*) as count')
                )
                ->whereNotNull('enrollassess_score')
                ->groupBy('score_range')
                ->orderBy('score_range')
                ->get();

            $labels = [];
            $data = [];

            foreach ($results as $result) {
                $range = $result->score_range;
                $labels[] = $range . '-' . ($range + 9) . '%';
                $data[] = $result->count;
            }
        } else {
            $results = Interview::select(
                    DB::raw('FLOOR(overall_score / 10) * 10 as score_range'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('status', 'completed')
                ->whereNotNull('overall_score')
                ->groupBy('score_range')
                ->orderBy('score_range')
                ->get();

            $labels = [];
            $data = [];

            foreach ($results as $result) {
                $range = $result->score_range;
                $labels[] = $range . '-' . ($range + 9);
                $data[] = $result->count;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => $type === 'exam' ? 'Exam Scores' : 'Interview Scores',
                        'data' => $data,
                        'backgroundColor' => 'rgba(128, 0, 32, 0.7)',
                        'borderColor' => 'rgb(128, 0, 32)',
                        'borderWidth' => 1,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Get performance trends over time
     */
    public function getPerformanceTrends(Request $request)
    {
        $period = $request->get('period', 30); // days
        $startDate = now()->subDays($period);

        // Get average scores per day
        $examScores = Applicant::select(
                DB::raw('DATE(updated_at) as date'),
                DB::raw('AVG(enrollassess_score) as avg_score')
            )
            ->whereNotNull('enrollassess_score')
            ->where('updated_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $interviewScores = Interview::select(
                DB::raw('DATE(updated_at) as date'),
                DB::raw('AVG(overall_score) as avg_score')
            )
            ->where('status', 'completed')
            ->where('updated_at', '>=', $startDate)
            ->whereNotNull('overall_score')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $examData = [];
        $interviewData = [];

        $current = $startDate->copy();
        $examByDate = $examScores->keyBy('date');
        $interviewByDate = $interviewScores->keyBy('date');

        while ($current <= now()) {
            $dateStr = $current->format('Y-m-d');
            $labels[] = $current->format('M d');
            $examData[] = $examByDate->get($dateStr)?->avg_score ? round($examByDate->get($dateStr)->avg_score, 1) : null;
            $interviewData[] = $interviewByDate->get($dateStr)?->avg_score ? round($interviewByDate->get($dateStr)->avg_score, 1) : null;
            $current->addDay();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Avg Exam Score',
                        'data' => $examData,
                        'borderColor' => 'rgb(128, 0, 32)',
                        'backgroundColor' => 'rgba(128, 0, 32, 0.1)',
                        'tension' => 0.4,
                        'spanGaps' => true,
                    ],
                    [
                        'label' => 'Avg Interview Score',
                        'data' => $interviewData,
                        'borderColor' => 'rgb(255, 215, 0)',
                        'backgroundColor' => 'rgba(255, 215, 0, 0.1)',
                        'tension' => 0.4,
                        'spanGaps' => true,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Get conversion funnel data
     */
    public function getConversionFunnel()
    {
        $total = Applicant::count();
        $examCompleted = Applicant::where('status', '!=', 'pending')->count();
        $interviewScheduled = Applicant::whereIn('status', [
            'interview-scheduled', 'interview-completed', 'admitted'
        ])->count();
        $interviewCompleted = Applicant::whereIn('status', [
            'interview-completed', 'admitted'
        ])->count();
        $admitted = Applicant::where('status', 'admitted')->count();

        $data = [
            ['stage' => 'Applied', 'count' => $total, 'percentage' => 100],
            ['stage' => 'Exam Completed', 'count' => $examCompleted, 'percentage' => $total > 0 ? round(($examCompleted / $total) * 100, 1) : 0],
            ['stage' => 'Interview Scheduled', 'count' => $interviewScheduled, 'percentage' => $total > 0 ? round(($interviewScheduled / $total) * 100, 1) : 0],
            ['stage' => 'Interview Completed', 'count' => $interviewCompleted, 'percentage' => $total > 0 ? round(($interviewCompleted / $total) * 100, 1) : 0],
            ['stage' => 'Admitted', 'count' => $admitted, 'percentage' => $total > 0 ? round(($admitted / $total) * 100, 1) : 0],
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get instructor workload data
     */
    public function getInstructorWorkload()
    {
        $workload = Interview::select(
                'interviewer_id',
                DB::raw('COUNT(*) as total_interviews'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed'),
                DB::raw('SUM(CASE WHEN status = "scheduled" THEN 1 ELSE 0 END) as scheduled'),
                DB::raw('AVG(CASE WHEN status = "completed" AND overall_score IS NOT NULL THEN overall_score END) as avg_score')
            )
            ->with('interviewer:user_id,name')
            ->whereNotNull('interviewer_id')
            ->groupBy('interviewer_id')
            ->get();

        $labels = [];
        $totalData = [];
        $completedData = [];
        $scheduledData = [];

        foreach ($workload as $item) {
            $labels[] = $item->interviewer->name ?? 'Unknown';
            $totalData[] = $item->total_interviews;
            $completedData[] = $item->completed;
            $scheduledData[] = $item->scheduled;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Total Interviews',
                        'data' => $totalData,
                        'backgroundColor' => 'rgba(128, 0, 32, 0.7)',
                    ],
                    [
                        'label' => 'Completed',
                        'data' => $completedData,
                        'backgroundColor' => 'rgba(34, 197, 94, 0.7)',
                    ],
                    [
                        'label' => 'Scheduled',
                        'data' => $scheduledData,
                        'backgroundColor' => 'rgba(255, 215, 0, 0.7)',
                    ],
                ],
            ],
            'details' => $workload->map(function ($item) {
                return [
                    'instructor' => $item->interviewer->name ?? 'Unknown',
                    'total' => $item->total_interviews,
                    'completed' => $item->completed,
                    'scheduled' => $item->scheduled,
                    'avg_score' => $item->avg_score ? round($item->avg_score, 1) : null,
                ];
            }),
        ]);
    }

    /**
     * Get time-to-completion metrics
     */
    public function getTimeToCompletion()
    {
        $applicants = Applicant::select(
                'id',
                'created_at',
                'updated_at',
                'status',
                DB::raw('TIMESTAMPDIFF(DAY, created_at, updated_at) as days_to_complete')
            )
            ->where('status', '!=', 'pending')
            ->get();

        $avgDays = $applicants->avg('days_to_complete') ?? 0;
        $medianDays = $applicants->median('days_to_complete') ?? 0;

        // Group by days
        $distribution = $applicants->groupBy(function ($item) {
            $days = $item->days_to_complete;
            if ($days <= 1) return '0-1 day';
            if ($days <= 3) return '2-3 days';
            if ($days <= 7) return '4-7 days';
            if ($days <= 14) return '8-14 days';
            return '15+ days';
        })->map(fn ($group) => $group->count());

        $labels = ['0-1 day', '2-3 days', '4-7 days', '8-14 days', '15+ days'];
        $data = [];

        foreach ($labels as $label) {
            $data[] = $distribution->get($label, 0);
        }

        return response()->json([
            'success' => true,
            'metrics' => [
                'average_days' => round($avgDays, 1),
                'median_days' => round($medianDays, 1),
            ],
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Number of Applicants',
                        'data' => $data,
                        'backgroundColor' => 'rgba(128, 0, 32, 0.7)',
                        'borderColor' => 'rgb(128, 0, 32)',
                        'borderWidth' => 1,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Get question category performance
     */
    public function getCategoryPerformance()
    {
        // This would require tracking question categories in results
        // For now, return placeholder data
        return response()->json([
            'success' => true,
            'message' => 'Category performance tracking will be available in a future update',
            'data' => [
                'labels' => [],
                'datasets' => [],
            ],
        ]);
    }

    /**
     * Export analytics data
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'overview');

        // Implement export logic based on type
        return response()->json([
            'success' => true,
            'message' => 'Export functionality coming soon',
        ]);
    }
}

