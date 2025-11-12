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

        // Return JSON for AJAX pagination requests only
        if ($request->ajax() && $request->header('Accept') && str_contains($request->header('Accept'), 'application/json')) {
            return response()->json([
                'interviews' => $interviews->items(),
                'pagination' => [
                    'current_page' => $interviews->currentPage(),
                    'last_page' => $interviews->lastPage(),
                    'per_page' => $interviews->perPage(),
                    'total' => $interviews->total(),
                    'from' => $interviews->firstItem(),
                    'to' => $interviews->lastItem(),
                ],
                'pagination_html' => $interviews->hasPages() ? $interviews->appends($request->query())->links()->render() : '',
            ]);
        }

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

        // Guard: Applicant must have completed the exam
        $applicant = Applicant::findOrFail($request->applicant_id);
        $hasCompletedExam = method_exists($applicant, 'hasCompletedExam') ? $applicant->hasCompletedExam() : ($applicant->status === 'exam-completed');
        if (!$hasCompletedExam) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot schedule interview. Applicant has not completed the exam.'
            ], 400);
        }

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

        // Dispatch interview scheduled event
        \App\Events\InterviewScheduled::dispatch($interview->load(['applicant', 'interviewer']));
        
        // Dispatch statistics update event
        $this->dispatchStatisticsUpdate();

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
                    // Guard: Applicant must have completed exam
                    $applicant = Applicant::findOrFail($applicantId);
                    $hasCompletedExam = method_exists($applicant, 'hasCompletedExam') ? $applicant->hasCompletedExam() : ($applicant->status === 'exam-completed');
                    if (!$hasCompletedExam) {
                        $errors[] = "Cannot schedule for applicant #{$applicantId}: exam not completed";
                        continue;
                    }

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

        // Dispatch statistics update event after bulk operation
        if ($scheduled > 0) {
            $this->dispatchStatisticsUpdate();
        }

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
        if ($interview->status === 'completed' && $interview->interviewer_id !== $user->user_id) {
            // Allow viewing completed interviews
            $isEditMode = false;
            return view('admin.interviews.conduct', compact('interview', 'applicant', 'isEditMode'));
        }

        // Check soft lock - if claimed by someone else within timeout period
        if ($interview->claimed_by && 
            $interview->claimed_by !== $user->user_id && 
            $interview->claimed_at && 
            $interview->claimed_at->gt(now()->subHour())) { // 1 hour timeout
            
            $claimedBy = User::find($interview->claimed_by);
            return redirect()->route('admin.interviews.index')
                    ->with('warning', "This interview is currently being conducted by {$claimedBy->full_name}. Please try again later.");
        }

        // Soft claim the interview if not already claimed by current user
        if ($interview->claimed_by !== $user->user_id) {
            $interview->update([
                'claimed_by' => $user->user_id,
                'claimed_at' => now(),
            ]);
        }

        // Allow editing if current user is the interviewer and interview is completed
        $isEditMode = ($interview->status === 'completed' && $interview->interviewer_id === $user->user_id);

        return view('admin.interviews.conduct', compact('interview', 'applicant', 'isEditMode'));
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

        // Validation - BSIT rubric criteria
        $request->validate([
            // BSIT Rubric Criteria (8 criteria, 10 points each)
            'communication_skills' => 'required|integer|min:0|max:10',
            'motivation_interest' => 'required|integer|min:0|max:10',
            'problem_solving_attitude' => 'required|integer|min:0|max:10',
            'program_understanding' => 'required|integer|min:0|max:10',
            'personality_attitude' => 'required|integer|min:0|max:10',
            'it_background' => 'required|integer|min:0|max:10',
            'willingness_to_learn' => 'required|integer|min:0|max:10',
            'overall_impression' => 'required|integer|min:0|max:10',
            
            // Overall Assessment
            'recommendation' => 'required|in:highly_recommended,recommended,conditional,not_recommended',
            'final_comments' => 'required|string|max:5000',
            'action' => 'required|in:save_draft,submit_final',
            
            // CARD/TOR GWA - required before submission
            'card_tor_gwa' => 'required|numeric|min:0|max:100',
        ]);

        // Calculate overall score (sum of 8 criteria = 80 points total)
        // Recommendation is categorical and does not add points
        $totalScore = $request->communication_skills + 
                     $request->motivation_interest + 
                     $request->problem_solving_attitude + 
                     $request->program_understanding + 
                     $request->personality_attitude + 
                     $request->it_background + 
                     $request->willingness_to_learn + 
                     $request->overall_impression;

        DB::transaction(function () use ($request, $interview, $user, $totalScore) {
            
            // Update interview record with BSIT rubric scores
            $interview->update([
                // BSIT Rubric Criteria
                'communication_skills' => $request->communication_skills,
                'motivation_interest' => $request->motivation_interest,
                'problem_solving_attitude' => $request->problem_solving_attitude,
                'program_understanding' => $request->program_understanding,
                'personality_attitude' => $request->personality_attitude,
                'it_background' => $request->it_background,
                'willingness_to_learn' => $request->willingness_to_learn,
                'overall_impression' => $request->overall_impression,
                
                // Overall Assessment
                'overall_score' => $totalScore,
                'recommendation' => $request->recommendation,
                
                // Written Feedback
                'final_comments' => $request->final_comments,
                
                // Interview Metadata
                'schedule_date' => $interview->schedule_date ?? now(),
                'interviewer_id' => $user->user_id, // Admin becomes the interviewer
                'claimed_by' => $user->user_id,
                'status' => $request->action === 'submit_final' ? 'completed' : 'scheduled',
            ]);

            // Only update applicant status if submitting final (not draft)
            // Admission decision will be made by department head considering available slots
            if ($request->action === 'submit_final') {
                $applicant = $interview->applicant;
                
                $applicant->update([
                    'status' => 'interview-completed',
                    'interview_score' => $totalScore,
                    'card_tor_gwa' => $request->card_tor_gwa,
                ]);
            } else {
                // Also save GWA for drafts (it's per-applicant, not per-interview)
                $interview->applicant->update([
                    'card_tor_gwa' => $request->card_tor_gwa,
                ]);
            }
        });

        $percent = round(($totalScore / 80) * 100);
        $message = $request->action === 'submit_final' 
            ? "Interview evaluation submitted successfully! Total Score: {$percent}/100"
            : "Interview draft saved successfully! Total Score: {$percent}/100";

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

    /**
     * Show detailed interview information
     */
    public function show($interviewId)
    {
        // Find interview with all necessary relationships
        $interview = Interview::with([
            'applicant.assignedInstructor',
            'interviewer',
            'claimedBy'
        ])->find($interviewId);

        // Graceful 404 handling
        if (!$interview) {
            return redirect()->route('admin.interviews.index')
                ->with('error', 'Interview not found. It may have been deleted or the ID is invalid.');
        }

        // Check if applicant exists
        if (!$interview->applicant) {
            return redirect()->route('admin.interviews.index')
                ->with('error', 'Applicant not found for this interview.');
        }

        $applicant = $interview->applicant;

        // Calculate exam context (correct/total)
        $totalQuestions = $applicant->results()->count();
        $correctAnswers = $applicant->results()->where('is_correct', true)->count();

        return view('admin.interviews.show', compact('interview', 'applicant', 'totalQuestions', 'correctAnswers'));
    }

    /**
     * Dispatch statistics update event
     */
    protected function dispatchStatisticsUpdate()
    {
        $stats = [
            'total' => \App\Models\Applicant::count(),
            'pending' => \App\Models\Applicant::where('status', 'pending')->count(),
            'exam_completed' => \App\Models\Applicant::where('status', 'exam-completed')->count(),
            'interview_scheduled' => \App\Models\Applicant::where('status', 'interview-scheduled')->count(),
            'interview_completed' => \App\Models\Applicant::where('status', 'interview-completed')->count(),
            'admitted' => \App\Models\Applicant::where('status', 'admitted')->count(),
            'rejected' => \App\Models\Applicant::where('status', 'rejected')->count(),
            'with_access_codes' => \App\Models\Applicant::whereHas('accessCode')->count(),
            'without_access_codes' => \App\Models\Applicant::whereDoesntHave('accessCode')->count(),
        ];

        \App\Events\StatisticsUpdated::dispatch($stats);
    }
}