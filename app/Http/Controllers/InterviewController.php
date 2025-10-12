<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Interview;
use App\Models\User;
use App\Services\InterviewPoolService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InterviewController extends Controller
{
    protected $interviewPoolService;

    public function __construct(InterviewPoolService $interviewPoolService)
    {
        $this->interviewPoolService = $interviewPoolService;
    }
    /**
     * Display interview management dashboard
     */
    public function index(Request $request)
    {
        $query = Interview::with(['applicant', 'interviewer']);

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('applicant', function($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%')
                  ->orWhere('email_address', 'like', '%' . $search . '%');
            })->orWhereHas('interviewer', function($q) use ($search) {
                $q->where('full_name', 'like', '%' . $search . '%');
            });
        }

        // Status filter
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Interviewer filter
        if ($request->has('interviewer_id') && $request->interviewer_id != '') {
            $query->where('interviewer_id', $request->interviewer_id);
        }

        $interviews = $query->orderBy('schedule_date', 'desc')->paginate(20);

        // Statistics
        $stats = [
            'total' => Interview::count(),
            'scheduled' => Interview::where('status', 'scheduled')->count(),
            'completed' => Interview::where('status', 'completed')->count(),
            'pending_assignment' => Applicant::where('status', 'exam-completed')
                                           ->whereDoesntHave('interviews')->count(),
            // Interview pool deprecated: mirror pending_assignment for compatibility
            'pool_available' => Applicant::where('status', 'exam-completed')
                                           ->whereDoesntHave('interviews')->count(),
        ];

        // Available instructors
        $instructors = User::where('role', 'instructor')->get();

        return view('admin.interviews.index', compact('interviews', 'stats', 'instructors'));
    }

    /**
     * Schedule interview for applicant
     */
    public function schedule(Request $request)
    {
        $request->validate([
            'applicant_id' => 'required|exists:applicants,applicant_id',
            'interviewer_id' => 'required|exists:users,user_id',
            'schedule_date' => 'required|date|after:now',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check if interview already exists
        $existingInterview = Interview::where('applicant_id', $request->applicant_id)->first();
        
        if ($existingInterview) {
            return response()->json([
                'success' => false,
                'message' => 'Interview already scheduled for this applicant.'
            ]);
        }

        // Create interview
        $interview = Interview::create([
            'applicant_id' => $request->applicant_id,
            'interviewer_id' => $request->interviewer_id,
            'schedule_date' => $request->schedule_date,
            'status' => 'scheduled',
            'notes' => $request->notes,
        ]);

        // Update applicant status
        Applicant::where('applicant_id', $request->applicant_id)
                 ->update(['status' => 'interview-scheduled']);

        return response()->json([
            'success' => true,
            'message' => 'Interview scheduled successfully!',
            'interview' => $interview
        ]);
    }


    /**
     * Bulk schedule interviews
     */
    public function bulkSchedule(Request $request)
    {
        $request->validate([
            'applicant_ids' => 'required|array',
            'applicant_ids.*' => 'exists:applicants,applicant_id',
            'assignment_strategy' => 'required|in:balanced,specific,random',
            'interviewer_id' => 'required_if:assignment_strategy,specific|exists:users,user_id',
            'schedule_date_start' => 'required|date|after:now',
            'time_slots' => 'required|array',
            'interview_duration' => 'required|integer|min:15|max:180', // minutes
        ]);

        $scheduled = 0;
        $errors = [];
        $instructors = User::where('role', 'instructor')->pluck('user_id')->toArray();

        DB::transaction(function () use ($request, &$scheduled, &$errors, $instructors) {
            $currentDate = $request->schedule_date_start;
            $timeSlotIndex = 0;
            
            foreach ($request->applicant_ids as $applicantId) {
                try {
                    // Check if already has interview
                    if (Interview::where('applicant_id', $applicantId)->exists()) {
                        $applicant = Applicant::find($applicantId);
                        $errors[] = "Interview already exists for {$applicant->full_name}";
                        continue;
                    }

                    // Determine interviewer based on strategy
                    $interviewerId = match($request->assignment_strategy) {
                        'specific' => $request->interviewer_id,
                        'balanced' => $instructors[$scheduled % count($instructors)],
                        'random' => $instructors[array_rand($instructors)],
                    };

                    // Get time slot
                    $timeSlot = $request->time_slots[$timeSlotIndex % count($request->time_slots)];
                    $scheduleDateTime = $currentDate . ' ' . $timeSlot;

                    // Create interview
                    Interview::create([
                        'applicant_id' => $applicantId,
                        'interviewer_id' => $interviewerId,
                        'schedule_date' => $scheduleDateTime,
                        'status' => 'scheduled',
                    ]);

                    // Update applicant status
                    Applicant::where('applicant_id', $applicantId)
                             ->update(['status' => 'interview-scheduled']);

                    $scheduled++;
                    $timeSlotIndex++;

                    // Move to next day if all time slots used
                    if ($timeSlotIndex % count($request->time_slots) === 0) {
                        $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
                    }

                } catch (\Exception $e) {
                    $errors[] = "Failed to schedule for applicant ID {$applicantId}: " . $e->getMessage();
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Scheduled {$scheduled} interviews successfully.",
            'scheduled' => $scheduled,
            'errors' => $errors
        ]);
    }

    /**
     * Update interview schedule
     */
    public function update(Request $request, Interview $interview)
    {
        $request->validate([
            'schedule_date' => 'required|date',
            'status' => 'required|in:scheduled,completed,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        $interview->update([
            'schedule_date' => $request->schedule_date,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        // Update applicant status based on interview status
        $applicantStatus = match($request->status) {
            'scheduled' => 'interview-scheduled',
            'completed' => 'interview-completed',
            'cancelled' => 'exam-completed', // Back to exam completed
        };

        $interview->applicant->update(['status' => $applicantStatus]);

        return response()->json([
            'success' => true,
            'message' => 'Interview updated successfully!'
        ]);
    }

    /**
     * Cancel interview
     */
    public function cancel(Interview $interview)
    {
        $interview->update(['status' => 'cancelled']);
        $interview->applicant->update(['status' => 'exam-completed']);

        return response()->json([
            'success' => true,
            'message' => 'Interview cancelled successfully!'
        ]);
    }

    /**
     * Interview analytics and reports
     */
    public function analytics()
    {
        $analytics = [
            // Interview completion rates
            'completion_rate' => [
                'scheduled' => Interview::where('status', 'scheduled')->count(),
                'completed' => Interview::where('status', 'completed')->count(),
                'cancelled' => Interview::where('status', 'cancelled')->count(),
            ],
            
            // Instructor performance
            'instructor_performance' => User::where('role', 'instructor')
                ->withCount(['interviews', 'completedInterviews'])
                ->get(),
            
            // Interview outcomes
            'outcomes' => Interview::where('status', 'completed')
                ->selectRaw('recommendation, COUNT(*) as count')
                ->groupBy('recommendation')
                ->pluck('count', 'recommendation'),
            
            // Average ratings
            'average_ratings' => [
                'technical' => Interview::where('status', 'completed')->avg('rating_technical'),
                'communication' => Interview::where('status', 'completed')->avg('rating_communication'),
                'problem_solving' => Interview::where('status', 'completed')->avg('rating_problem_solving'),
                'overall' => Interview::where('status', 'completed')->avg('overall_score'),
            ],
            
            // Recent trends (last 30 days)
            'recent_trends' => Interview::where('created_at', '>=', now()->subDays(30))
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];

        return view('admin.interviews.analytics', compact('analytics'));
    }

    /**
     * Export interviews data
     */
    public function export(Request $request)
    {
        $query = Interview::with(['applicant', 'interviewer']);

        // Apply filters if provided
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && $request->date_from != '') {
            $query->where('schedule_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $query->where('schedule_date', '<=', $request->date_to);
        }

        $interviews = $query->get();

        $filename = 'interviews_export_' . now()->format('Y_m_d_H_i_s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($interviews) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'Interview ID',
                'Applicant Name',
                'Applicant Email',
                'Interviewer',
                'Schedule Date',
                'Status',
                'Technical Rating',
                'Communication Rating',
                'Problem Solving Rating',
                'Overall Score',
                'Recommendation',
                'Notes'
            ]);

            // CSV Data
            foreach ($interviews as $interview) {
                fputcsv($file, [
                    $interview->interview_id,
                    $interview->applicant->full_name ?? '',
                    $interview->applicant->email_address ?? '',
                    $interview->interviewer->full_name ?? '',
                    $interview->schedule_date ? $interview->schedule_date->format('Y-m-d H:i') : '',
                    ucfirst($interview->status),
                    $interview->rating_technical ?? '',
                    $interview->rating_communication ?? '',
                    $interview->rating_problem_solving ?? '',
                    $interview->overall_score ?? '',
                    ucfirst(str_replace('_', ' ', $interview->recommendation ?? '')),
                    $interview->notes ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Department Head Interview Pool Override Methods
     */

    /**
     * Get interview pool overview for Department Head
     */
    public function poolOverview(Request $request)
    {
        $filters = $request->only(['priority', 'search', 'status']);
        
        // Get all interviews (available, claimed, assigned)
        $availableInterviews = $this->interviewPoolService->getAvailableInterviews($filters);
        $claimedInterviews = Interview::with(['applicant', 'claimedBy'])
            ->claimed()
            ->get();
        
        $poolStats = $this->interviewPoolService->getPoolStatistics();
        
        // Get all instructors for assignment
        $instructors = User::where('role', 'instructor')->get();
        
        return view('admin.interviews.pool-overview', compact(
            'availableInterviews',
            'claimedInterviews', 
            'poolStats',
            'instructors',
            'filters'
        ));
    }

    /**
     * Department Head claims an interview for themselves
     */
    public function dhClaimInterview(Request $request, $interviewId)
    {
        try {
            $interview = $this->interviewPoolService->claimInterview($interviewId, Auth::id());
            
            return response()->json([
                'success' => true,
                'message' => 'Interview claimed by Department Head successfully!',
                'interview' => $interview
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Department Head assigns interview to specific instructor (override)
     */
    public function assignToInstructor(Request $request)
    {
        $request->validate([
            'interview_id' => 'required|exists:interviews,interview_id',
            'instructor_id' => 'required|exists:users,user_id',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $interview = $this->interviewPoolService->assignInterviewToInstructor(
                $request->interview_id,
                $request->instructor_id,
                $request->notes
            );
            
            $instructor = User::find($request->instructor_id);
            
            return response()->json([
                'success' => true,
                'message' => "Interview assigned to {$instructor->full_name} successfully!",
                'interview' => $interview
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Department Head overrides and releases any claimed interview
     */
    public function dhReleaseInterview(Request $request, $interviewId)
    {
        try {
            $interview = $this->interviewPoolService->releaseInterview($interviewId, Auth::id());
            
            return response()->json([
                'success' => true,
                'message' => 'Interview released back to pool successfully!',
                'interview' => $interview
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Set interview priority (Department Head only)
     */
    public function setPriority(Request $request)
    {
        $request->validate([
            'interview_id' => 'required|exists:interviews,interview_id',
            'priority' => 'required|in:high,medium,low'
        ]);

        try {
            $interview = $this->interviewPoolService->setInterviewPriority(
                $request->interview_id,
                $request->priority
            );
            
            return response()->json([
                'success' => true,
                'message' => "Interview priority set to {$request->priority} successfully!",
                'interview' => $interview
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }


    /**
     * Get real-time pool data for AJAX updates
     */
    public function getPoolData(Request $request)
    {
        $filters = $request->only(['priority', 'search', 'status']);
        
        return response()->json([
            'available_interviews' => $this->interviewPoolService->getAvailableInterviews($filters),
            'claimed_interviews' => Interview::with(['applicant', 'claimedBy'])
                ->claimed()
                ->get(),
            'pool_stats' => $this->interviewPoolService->getPoolStatistics()
        ]);
    }

    /**
     * Admin Conduct Interview Methods
     */

    /**
     * Show admin interview conduct form
     */
    public function adminConductForm(Interview $interview)
    {
        $user = Auth::user();
        
        // Authorization check - only department-head or administrator
        if (!in_array($user->role, ['department-head', 'administrator'])) {
            abort(403, 'You are not authorized to conduct interviews.');
        }

        // Load interview with applicant data
        $interview->load(['applicant']);
        $applicant = $interview->applicant;

        // Check if interview is already completed by someone else
        if ($interview->status === 'completed' && $interview->claimed_by !== $user->user_id) {
            return redirect()->route('admin.interviews.index')
                    ->with('error', 'This interview has already been completed by another evaluator.');
        }

        // Check soft lock - if claimed by someone else within timeout period
        if ($interview->claimed_by && 
            $interview->claimed_by !== $user->user_id && 
            !$interview->isClaimedTooLong(1)) { // 1 hour timeout
            
            $claimedBy = User::find($interview->claimed_by);
            return redirect()->route('admin.interviews.index')
                    ->with('warning', "This interview is currently being conducted by {$claimedBy->full_name}. Please try again later.");
        }

        // Soft claim the interview if not already claimed by current user
        if ($interview->claimed_by !== $user->user_id) {
            $interview->update([
                'claimed_by' => $user->user_id,
                'claimed_at' => now(),
                'status' => $interview->status === 'available' ? 'claimed' : $interview->status
            ]);
        }

        return view('admin.interviews.conduct', compact('interview', 'applicant'));
    }

    /**
     * Submit admin interview evaluation
     */
    public function adminConductSubmit(Request $request, Interview $interview)
    {
        $user = Auth::user();
        
        // Authorization check
        if (!in_array($user->role, ['department-head', 'administrator'])) {
            abort(403, 'You are not authorized to conduct interviews.');
        }

        // Verify claim ownership
        if ($interview->claimed_by !== $user->user_id) {
            return redirect()->route('admin.interviews.index')
                    ->with('error', 'You cannot submit an evaluation for an interview you do not have claimed.');
        }

        // Validation - reuse same rules as instructor
        $request->validate([
            // Technical Skills (40 points max)
            'technical_programming' => 'required|numeric|min:0|max:10',
            'technical_problem_solving' => 'required|numeric|min:0|max:10',
            'technical_algorithms' => 'required|numeric|min:0|max:10',
            'technical_system_design' => 'required|numeric|min:0|max:10',
            
            // Communication Skills (30 points max)
            'communication_clarity' => 'required|numeric|min:0|max:10',
            'communication_listening' => 'required|numeric|min:0|max:10',
            'communication_confidence' => 'required|numeric|min:0|max:10',
            
            // Analytical Thinking (30 points max)
            'analytical_critical_thinking' => 'required|numeric|min:0|max:10',
            'analytical_creativity' => 'required|numeric|min:0|max:10',
            'analytical_attention_detail' => 'required|numeric|min:0|max:10',
            
            // Overall Assessment
            'overall_rating' => 'required|in:excellent,very_good,good,satisfactory,needs_improvement',
            'recommendation' => 'required|in:highly_recommended,recommended,conditional,not_recommended',
            'strengths' => 'required|string|max:1000',
            'areas_improvement' => 'required|string|max:1000',
            'interview_notes' => 'nullable|string|max:2000',
            'action' => 'required|in:save_draft,submit_final'
        ]);

        // Calculate scores using same logic as instructor
        $technicalScore = $request->technical_programming + 
                         $request->technical_problem_solving + 
                         $request->technical_algorithms + 
                         $request->technical_system_design;
        
        $communicationScore = $request->communication_clarity + 
                             $request->communication_listening + 
                             $request->communication_confidence;
        
        $analyticalScore = $request->analytical_critical_thinking + 
                          $request->analytical_creativity + 
                          $request->analytical_attention_detail;
        
        $totalScore = $technicalScore + $communicationScore + $analyticalScore;
        $percentage = round(($totalScore / 100) * 100, 2);

        DB::transaction(function () use ($request, $interview, $user, $technicalScore, $communicationScore, $analyticalScore, $totalScore, $percentage) {
            
            // Update interview record with detailed rubrics
            $interview->update([
                // Individual rubric scores
                'rating_technical' => $technicalScore,
                'rating_communication' => $communicationScore,
                'rating_problem_solving' => $analyticalScore,
                'overall_score' => $percentage,
                
                // Detailed breakdown (store as JSON)
                'rubric_scores' => json_encode([
                    'technical' => [
                        'programming' => $request->technical_programming,
                        'problem_solving' => $request->technical_problem_solving,
                        'algorithms' => $request->technical_algorithms,
                        'system_design' => $request->technical_system_design,
                    ],
                    'communication' => [
                        'clarity' => $request->communication_clarity,
                        'listening' => $request->communication_listening,
                        'confidence' => $request->communication_confidence,
                    ],
                    'analytical' => [
                        'critical_thinking' => $request->analytical_critical_thinking,
                        'creativity' => $request->analytical_creativity,
                        'attention_detail' => $request->analytical_attention_detail,
                    ]
                ]),
                
                'overall_rating' => $request->overall_rating,
                'recommendation' => $request->recommendation,
                'strengths' => $request->strengths,
                'areas_improvement' => $request->areas_improvement,
                'notes' => $request->interview_notes,
                'schedule_date' => $interview->schedule_date ?? now(),
                
                // Track evaluator details
                'interviewer_id' => $user->user_id, // Admin becomes the interviewer
                'claimed_by' => $user->user_id,
                'status' => $request->action === 'submit_final' ? 'completed' : 'in-progress',
            ]);

            // Only update applicant status if submitting final (not draft)
            if ($request->action === 'submit_final') {
                $applicant = $interview->applicant;
                $newStatus = 'interview-completed';
                
                // Auto-determine admission based on score and recommendation (same logic as instructor)
                if ($percentage >= 75 && in_array($request->recommendation, ['highly_recommended', 'recommended'])) {
                    $newStatus = 'admitted';
                } elseif ($percentage < 50 || $request->recommendation === 'not_recommended') {
                    $newStatus = 'rejected';
                }
                
                $applicant->update([
                    'status' => $newStatus,
                    'final_score' => $percentage,
                    'admission_decision_date' => now(),
                ]);
            }
        });

        $message = $request->action === 'submit_final' 
            ? "Interview evaluation submitted successfully! Score: {$percentage}%"
            : "Interview draft saved successfully! Score: {$percentage}%";

        return redirect()->route('admin.interviews.index')
                        ->with('success', $message);
    }

    /**
     * Admin claim interview (explicit)
     */
    public function adminClaimInterview(Request $request, Interview $interview)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['department-head', 'administrator'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to claim interviews.'
            ], 403);
        }

        try {
            // Check if already claimed by someone else within timeout
            if ($interview->claimed_by && 
                $interview->claimed_by !== $user->user_id && 
                !$interview->isClaimedTooLong(1)) {
                
                $claimedBy = User::find($interview->claimed_by);
                return response()->json([
                    'success' => false,
                    'message' => "Interview is currently claimed by {$claimedBy->full_name}."
                ], 400);
            }

            $interview->update([
                'claimed_by' => $user->user_id,
                'claimed_at' => now(),
                'status' => 'claimed',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Interview claimed successfully!',
                'interview' => $interview
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Admin release interview claim
     */
    public function adminReleaseInterview(Request $request, Interview $interview)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['department-head', 'administrator'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to release interviews.'
            ], 403);
        }

        try {
            // Department Head can release any interview, Administrator can only release their own
            if ($user->role === 'administrator' && $interview->claimed_by !== $user->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only release interviews you have claimed.'
                ], 403);
            }

            $interview->update([
                'claimed_by' => null,
                'claimed_at' => null,
                'status' => 'available',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Interview released successfully!',
                'interview' => $interview
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}