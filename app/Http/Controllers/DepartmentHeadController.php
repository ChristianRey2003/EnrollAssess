<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Interview;
use App\Models\User;
use App\Http\Requests\BulkAdmissionDecisionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentHeadController extends Controller
{
    /**
     * Department Head dashboard with interview results overview
     */
    public function dashboard()
    {
        // Overall statistics
        $stats = [
            'total_applicants' => Applicant::count(),
            'interview_assigned' => Applicant::where('status', 'interview-assigned')->count(),
            'interview_completed' => Applicant::where('status', 'interview-completed')->count(),
            'admitted' => Applicant::where('status', 'admitted')->count(),
            'rejected' => Applicant::where('status', 'rejected')->count(),
            'pending_decision' => Applicant::where('status', 'interview-completed')->count(),
        ];

        // Recent interview submissions (last 7 days)
        $recentInterviews = Interview::where('status', 'completed')
                                   ->where('updated_at', '>=', now()->subDays(7))
                                   ->with(['applicant', 'interviewer'])
                                   ->orderBy('updated_at', 'desc')
                                   ->take(10)
                                   ->get();

        // Instructor performance summary
        $instructorPerformance = User::where('role', 'instructor')
                                   ->withCount([
                                       'interviews',
                                       'completedInterviews'
                                   ])
                                   ->get();

        // Score distribution
        $scoreDistribution = Interview::where('status', 'completed')
                                    ->selectRaw('
                                        CASE 
                                            WHEN overall_score >= 90 THEN "Excellent (90-100)"
                                            WHEN overall_score >= 80 THEN "Very Good (80-89)"
                                            WHEN overall_score >= 70 THEN "Good (70-79)"
                                            WHEN overall_score >= 60 THEN "Satisfactory (60-69)"
                                            ELSE "Needs Improvement (<60)"
                                        END as score_range,
                                        COUNT(*) as count
                                    ')
                                    ->groupBy('score_range')
                                    ->pluck('count', 'score_range');

        return view('admin.department-head.dashboard', compact(
            'stats',
            'recentInterviews',
            'instructorPerformance',
            'scoreDistribution'
        ));
    }

    /**
     * View all interview results with detailed breakdown
     */
    public function interviewResults(Request $request)
    {
        $query = Interview::with(['applicant', 'interviewer'])
                         ->where('status', 'completed');

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('applicant', function($q) use ($search) {
                $q->where('full_name', 'like', '%' . $search . '%')
                  ->orWhere('email_address', 'like', '%' . $search . '%');
            });
        }

        // Filter by interviewer
        if ($request->has('interviewer_id') && $request->interviewer_id != '') {
            $query->where('interviewer_id', $request->interviewer_id);
        }

        // Filter by recommendation
        if ($request->has('recommendation') && $request->recommendation != '') {
            $query->where('recommendation', $request->recommendation);
        }

        // Filter by score range
        if ($request->has('score_range') && $request->score_range != '') {
            switch ($request->score_range) {
                case 'excellent':
                    $query->where('overall_score', '>=', 90);
                    break;
                case 'very_good':
                    $query->whereBetween('overall_score', [80, 89]);
                    break;
                case 'good':
                    $query->whereBetween('overall_score', [70, 79]);
                    break;
                case 'satisfactory':
                    $query->whereBetween('overall_score', [60, 69]);
                    break;
                case 'needs_improvement':
                    $query->where('overall_score', '<', 60);
                    break;
            }
        }

        $interviews = $query->orderBy('updated_at', 'desc')->paginate(20);

        // Get instructors for filter dropdown
        $instructors = User::where('role', 'instructor')->get();

        return view('admin.department-head.interview-results', compact(
            'interviews',
            'instructors'
        ));
    }

    /**
     * View detailed interview result for specific applicant
     */
    public function viewInterviewDetail($interviewId)
    {
        $interview = Interview::with(['applicant', 'interviewer'])
                             ->where('status', 'completed')
                             ->findOrFail($interviewId);

        // Decode rubric scores
        $rubricScores = json_decode($interview->rubric_scores, true);

        return view('admin.department-head.interview-detail', compact(
            'interview',
            'rubricScores'
        ));
    }

    /**
     * Bulk admission decision with enhanced validation and error handling
     */
    public function bulkAdmissionDecision(BulkAdmissionDecisionRequest $request)
    {
        // Validation is handled by the FormRequest

        $updated = 0;

        foreach ($request->interview_ids as $interviewId) {
            $interview = Interview::find($interviewId);
            if ($interview && $interview->status === 'completed') {
                $applicant = $interview->applicant;
                
                $newStatus = match($request->decision) {
                    'admit' => 'admitted',
                    'reject' => 'rejected',
                    'pending' => 'interview-completed',
                };

                $applicant->update([
                    'status' => $newStatus,
                    'admission_decision_date' => now(),
                    'decision_made_by' => Auth::id(),
                ]);

                $updated++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Updated admission status for {$updated} applicants.",
            'updated' => $updated
        ]);
    }

    /**
     * Export interview results
     */
    public function exportInterviewResults(Request $request)
    {
        $query = Interview::with(['applicant', 'interviewer'])
                         ->where('status', 'completed');

        // Apply filters
        if ($request->has('interviewer_id') && $request->interviewer_id != '') {
            $query->where('interviewer_id', $request->interviewer_id);
        }

        if ($request->has('recommendation') && $request->recommendation != '') {
            $query->where('recommendation', $request->recommendation);
        }

        $interviews = $query->get();

        $filename = 'interview_results_' . now()->format('Y_m_d_H_i_s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($interviews) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'Applicant Name',
                'Email',
                'Interviewer',
                'Interview Date',
                'Technical Score (/40)',
                'Communication Score (/30)',
                'Analytical Score (/30)',
                'Overall Score (%)',
                'Overall Rating',
                'Recommendation',
                'Applicant Status',
                'Strengths',
                'Areas for Improvement',
                'Interview Notes'
            ]);

            // CSV Data
            foreach ($interviews as $interview) {
                fputcsv($file, [
                    $interview->applicant->full_name ?? '',
                    $interview->applicant->email_address ?? '',
                    $interview->interviewer->full_name ?? '',
                    $interview->schedule_date ? $interview->schedule_date->format('Y-m-d H:i') : '',
                    $interview->rating_technical ?? '',
                    $interview->rating_communication ?? '',
                    $interview->rating_problem_solving ?? '',
                    $interview->overall_score ?? '',
                    ucfirst(str_replace('_', ' ', $interview->overall_rating ?? '')),
                    ucfirst(str_replace('_', ' ', $interview->recommendation ?? '')),
                    ucfirst(str_replace('-', ' ', $interview->applicant->status ?? '')),
                    $interview->strengths ?? '',
                    $interview->areas_improvement ?? '',
                    $interview->notes ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Interview analytics for department head
     */
    public function analytics()
    {
        $analytics = [
            // Overall completion rates
            'completion_stats' => [
                'total_interviews' => Interview::count(),
                'completed' => Interview::where('status', 'completed')->count(),
                'pending' => Interview::where('status', 'assigned')->count(),
            ],

            // Score averages by category
            'score_averages' => [
                'technical' => Interview::where('status', 'completed')->avg('rating_technical'),
                'communication' => Interview::where('status', 'completed')->avg('rating_communication'),
                'analytical' => Interview::where('status', 'completed')->avg('rating_problem_solving'),
                'overall' => Interview::where('status', 'completed')->avg('overall_score'),
            ],

            // Recommendation breakdown
            'recommendations' => Interview::where('status', 'completed')
                ->selectRaw('recommendation, COUNT(*) as count')
                ->groupBy('recommendation')
                ->pluck('count', 'recommendation'),

            // Admission outcomes
            'admission_outcomes' => [
                'admitted' => Applicant::where('status', 'admitted')->count(),
                'rejected' => Applicant::where('status', 'rejected')->count(),
                'pending' => Applicant::where('status', 'interview-completed')->count(),
            ],

            // Monthly trends
            'monthly_trends' => Interview::where('status', 'completed')
                ->where('schedule_date', '>=', now()->subMonths(6))
                ->selectRaw('DATE_FORMAT(schedule_date, "%Y-%m") as month, COUNT(*) as count, AVG(overall_score) as avg_score')
                ->groupBy('month')
                ->orderBy('month')
                ->get(),

            // Instructor performance comparison
            'instructor_comparison' => User::where('role', 'instructor')
                ->with(['completedInterviews' => function($query) {
                    $query->select('interviewer_id', 
                                 \DB::raw('COUNT(*) as total_interviews'),
                                 \DB::raw('AVG(overall_score) as avg_score'),
                                 \DB::raw('COUNT(CASE WHEN recommendation IN ("highly_recommended", "recommended") THEN 1 END) as positive_recommendations'));
                }])
                ->get(),
        ];

        return view('admin.department-head.analytics', compact('analytics'));
    }
}