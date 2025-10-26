<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Interview;
use App\Models\Exam;
use App\Models\AccessCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get live dashboard statistics
     */
    public function getLiveStats()
    {
        // Cache for 30 seconds to reduce database load
        $stats = Cache::remember('dashboard_stats', 30, function () {
            return [
                'total_applicants' => Applicant::count(),
                'exam_completed' => Applicant::where('status', '!=', 'pending')->count(),
                'interviews_scheduled' => Interview::where('status', 'scheduled')->count(),
                'pending_reviews' => Applicant::where('status', 'exam-completed')->count(),
                'admitted' => Applicant::where('status', 'admitted')->count(),
                'rejected' => Applicant::where('status', 'rejected')->count(),
                'interviews_completed' => Interview::where('status', 'completed')->count(),
                'active_exams' => Exam::where('is_active', true)->count(),
            ];
        });

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'timestamp' => now()->format('M d, Y g:i A'),
        ]);
    }

    /**
     * Get recent activity feed
     */
    public function getRecentActivity(Request $request)
    {
        $limit = $request->get('limit', 10);

        $recentApplicants = Applicant::with(['assignedInstructor', 'accessCode'])
            ->latest()
            ->take($limit)
            ->get()
            ->map(function ($applicant) {
                return [
                    'id' => $applicant->id,
                    'type' => 'applicant',
                    'name' => $applicant->first_name . ' ' . $applicant->last_name,
                    'email' => $applicant->email,
                    'status' => $applicant->status,
                    'created_at' => $applicant->created_at->format('M d, Y g:i A'),
                    'time_ago' => $applicant->created_at->diffForHumans(),
                ];
            });

        $recentInterviews = Interview::with(['applicant', 'instructor'])
            ->latest()
            ->take($limit)
            ->get()
            ->map(function ($interview) {
                return [
                    'id' => $interview->id,
                    'type' => 'interview',
                    'applicant_name' => $interview->applicant->first_name . ' ' . $interview->applicant->last_name,
                    'instructor_name' => $interview->instructor->name ?? 'Not assigned',
                    'status' => $interview->status,
                    'scheduled_at' => $interview->scheduled_at?->format('M d, Y g:i A'),
                    'created_at' => $interview->created_at->format('M d, Y g:i A'),
                    'time_ago' => $interview->created_at->diffForHumans(),
                ];
            });

        // Merge and sort by created_at
        $activities = $recentApplicants->concat($recentInterviews)
            ->sortByDesc('created_at')
            ->take($limit)
            ->values();

        return response()->json([
            'success' => true,
            'activities' => $activities,
        ]);
    }

    /**
     * Get chart data for trends
     */
    public function getChartData(Request $request)
    {
        $type = $request->get('type', 'applicants');
        $period = $request->get('period', '30'); // days

        $data = match($type) {
            'applicants' => $this->getApplicantTrends($period),
            'exams' => $this->getExamTrends($period),
            'interviews' => $this->getInterviewTrends($period),
            'status_distribution' => $this->getStatusDistribution(),
            default => ['labels' => [], 'datasets' => []],
        };

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get applicant trend data
     */
    private function getApplicantTrends($days)
    {
        $startDate = now()->subDays($days);

        $applicants = Applicant::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $data = [];

        // Fill in missing dates with 0
        $current = $startDate->copy();
        $applicantsByDate = $applicants->keyBy('date');

        while ($current <= now()) {
            $dateStr = $current->format('Y-m-d');
            $labels[] = $current->format('M d');
            $data[] = $applicantsByDate->get($dateStr)?->count ?? 0;
            $current->addDay();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'New Applicants',
                    'data' => $data,
                    'borderColor' => 'rgb(128, 0, 32)',
                    'backgroundColor' => 'rgba(128, 0, 32, 0.1)',
                    'tension' => 0.4,
                ],
            ],
        ];
    }

    /**
     * Get exam completion trend data
     */
    private function getExamTrends($days)
    {
        $startDate = now()->subDays($days);

        $completions = Applicant::select(
                DB::raw('DATE(updated_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('status', '!=', 'pending')
            ->where('updated_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $data = [];

        $current = $startDate->copy();
        $completionsByDate = $completions->keyBy('date');

        while ($current <= now()) {
            $dateStr = $current->format('Y-m-d');
            $labels[] = $current->format('M d');
            $data[] = $completionsByDate->get($dateStr)?->count ?? 0;
            $current->addDay();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Exams Completed',
                    'data' => $data,
                    'borderColor' => 'rgb(255, 215, 0)',
                    'backgroundColor' => 'rgba(255, 215, 0, 0.1)',
                    'tension' => 0.4,
                ],
            ],
        ];
    }

    /**
     * Get interview trend data
     */
    private function getInterviewTrends($days)
    {
        $startDate = now()->subDays($days);

        $scheduled = Interview::select(
                DB::raw('DATE(scheduled_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('scheduled_at', '>=', $startDate)
            ->where('status', 'scheduled')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $completed = Interview::select(
                DB::raw('DATE(updated_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('updated_at', '>=', $startDate)
            ->where('status', 'completed')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $scheduledData = [];
        $completedData = [];

        $current = $startDate->copy();
        $scheduledByDate = $scheduled->keyBy('date');
        $completedByDate = $completed->keyBy('date');

        while ($current <= now()) {
            $dateStr = $current->format('Y-m-d');
            $labels[] = $current->format('M d');
            $scheduledData[] = $scheduledByDate->get($dateStr)?->count ?? 0;
            $completedData[] = $completedByDate->get($dateStr)?->count ?? 0;
            $current->addDay();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Interviews Scheduled',
                    'data' => $scheduledData,
                    'borderColor' => 'rgb(128, 0, 32)',
                    'backgroundColor' => 'rgba(128, 0, 32, 0.1)',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Interviews Completed',
                    'data' => $completedData,
                    'borderColor' => 'rgb(255, 215, 0)',
                    'backgroundColor' => 'rgba(255, 215, 0, 0.1)',
                    'tension' => 0.4,
                ],
            ],
        ];
    }

    /**
     * Get status distribution data
     */
    private function getStatusDistribution()
    {
        $distribution = Applicant::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        $statusLabels = [
            'pending' => 'Pending',
            'exam-completed' => 'Exam Completed',
            'interview-scheduled' => 'Interview Scheduled',
            'interview-completed' => 'Interview Completed',
            'admitted' => 'Admitted',
            'rejected' => 'Rejected',
        ];

        $labels = [];
        $data = [];
        $backgroundColor = [
            'rgb(128, 0, 32)',
            'rgb(255, 215, 0)',
            'rgb(75, 85, 99)',
            'rgb(59, 130, 246)',
            'rgb(34, 197, 94)',
            'rgb(239, 68, 68)',
        ];

        foreach ($distribution as $index => $item) {
            $labels[] = $statusLabels[$item->status] ?? ucfirst($item->status);
            $data[] = $item->count;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Applicants by Status',
                    'data' => $data,
                    'backgroundColor' => array_slice($backgroundColor, 0, count($data)),
                ],
            ],
        ];
    }

    /**
     * Get system health metrics
     */
    public function getSystemHealth()
    {
        $health = [
            'database' => $this->checkDatabaseHealth(),
            'cache' => $this->checkCacheHealth(),
            'storage' => $this->checkStorageHealth(),
        ];

        return response()->json([
            'success' => true,
            'health' => $health,
            'overall_status' => collect($health)->every(fn($item) => $item['status'] === 'healthy') ? 'healthy' : 'warning',
        ]);
    }

    private function checkDatabaseHealth()
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'healthy', 'message' => 'Database connection active'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Database connection failed'];
        }
    }

    private function checkCacheHealth()
    {
        try {
            Cache::put('health_check', 'ok', 10);
            $value = Cache::get('health_check');
            return ['status' => $value === 'ok' ? 'healthy' : 'warning', 'message' => 'Cache working'];
        } catch (\Exception $e) {
            return ['status' => 'warning', 'message' => 'Cache not available'];
        }
    }

    private function checkStorageHealth()
    {
        try {
            $diskSpace = disk_free_space(storage_path());
            $totalSpace = disk_total_space(storage_path());
            $usedPercentage = (($totalSpace - $diskSpace) / $totalSpace) * 100;

            return [
                'status' => $usedPercentage < 90 ? 'healthy' : 'warning',
                'message' => 'Disk usage: ' . number_format($usedPercentage, 1) . '%',
                'free_space_gb' => number_format($diskSpace / 1024 / 1024 / 1024, 2),
            ];
        } catch (\Exception $e) {
            return ['status' => 'warning', 'message' => 'Could not check storage'];
        }
    }
}

