<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Interview;
use App\Models\User;
use App\Services\InterviewPoolService;
use App\Mail\InterviewScheduleMail;
use App\Exports\InstructorReportExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class InstructorController extends Controller
{
    protected $interviewPoolService;

    public function __construct(InterviewPoolService $interviewPoolService)
    {
        $this->interviewPoolService = $interviewPoolService;
    }
    /**
     * Display the instructor dashboard
     */
    public function dashboard(Request $request)
    {
        $instructor = Auth::user();
        
        // Get assigned applicants (using assigned_instructor_id)
        $assignedApplicants = Applicant::where('assigned_instructor_id', $instructor->user_id)
            ->with(['latestInterview'])
            ->get();

        // Get statistics
        $stats = [
            'total_assigned' => $assignedApplicants->count(),
            'pending_interviews' => $assignedApplicants->whereIn('status', ['exam-completed'])->count(),
            'completed_interviews' => $assignedApplicants->where('status', 'interview-completed')->count(),
        ];

        // Determine sort order for upcoming interviews (default: oldest first)
        $upcomingSort = $request->get('upcoming_sort', 'asc') === 'desc' ? 'desc' : 'asc';

        // Get scheduled interviews for this instructor (regardless of whether the date is past or future)
        // so instructors can still see recently set interviews even if the date has passed.
        $upcomingInterviews = Interview::where('interviewer_id', $instructor->user_id)
            ->where('status', 'scheduled')
            ->whereNotNull('schedule_date')
            ->with('applicant')
            ->orderBy('schedule_date', $upcomingSort)
            ->get();

        // Recent activity (interviews in last 7 days)
        $recentInterviews = Interview::where('interviewer_id', $instructor->user_id)
                                  ->where('created_at', '>=', now()->subDays(7))
                                  ->with('applicant')
                                  ->orderBy('created_at', 'desc')
                                  ->take(5)
                                  ->get();

        return view('instructor.dashboard', compact(
            'instructor',
            'assignedApplicants', 
            'stats',
            'recentInterviews',
            'upcomingInterviews'
        ));
    }

    /**
     * Display assigned applicants list
     */
    public function applicants(Request $request)
    {
        $instructor = Auth::user();
        
        // Filter applicants by assigned_instructor_id for direct assignment
        $assignedApplicantsQuery = Applicant::where('assigned_instructor_id', $instructor->user_id)
            ->with(['latestInterview', 'interviews']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $assignedApplicantsQuery->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                      ->orWhere('middle_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email_address', 'like', "%{$search}%")
                      ->orWhere('application_no', 'like', "%{$search}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%{$search}%"]);
            });
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            
            if ($status === 'needs-scheduling') {
                // Filter for applicants that need scheduling: 
                // - Have an interview assigned to this instructor
                // - Interview doesn't have a schedule_date OR status is 'assigned'/'available'
                // - Applicant has completed the exam (status is exam-completed or interview-available)
                $assignedApplicantsQuery->whereHas('latestInterview', function($query) use ($instructor) {
                    $query->where('interviewer_id', $instructor->user_id)
                          ->where(function($q) {
                              // No schedule date set - needs scheduling
                              $q->whereNull('schedule_date')
                                // OR status is 'assigned' or 'available' - needs scheduling
                                ->orWhereIn('status', ['assigned', 'available']);
                          });
                })->where(function($query) {
                    $query->where('status', 'exam-completed')
                          ->orWhere('status', 'interview-available');
                });
            } else {
                $assignedApplicantsQuery->where('status', $status);
            }
        }

        $assignedApplicants = $assignedApplicantsQuery
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->appends($request->only(['search', 'status']));

        // Return JSON for AJAX pagination requests only
        if ($request->ajax() || ($request->header('Accept') && str_contains($request->header('Accept'), 'application/json'))) {
            $html = view('instructor.partials.applicants-table', [
                'assignedApplicants' => $assignedApplicants
            ])->render();

            return response()->json(['html' => $html]);
        }

        return view('instructor.applicants', compact('assignedApplicants'));
    }

    /**
     * Show interview form for specific applicant
     */
    public function showInterview($applicantId)
    {
        $instructor = Auth::user();
        
        $applicant = Applicant::with(['latestInterview'])->findOrFail($applicantId);
        
        // Check if instructor is assigned to this applicant via assigned_instructor_id
        if ($applicant->assigned_instructor_id !== $instructor->user_id) {
            abort(403, 'You are not assigned to interview this applicant.');
        }
        
        // Guard: Applicant must have completed the exam before interview
        if (method_exists($applicant, 'hasCompletedExam')) {
            if (!$applicant->hasCompletedExam()) {
                return redirect()->route('instructor.applicants')
                    ->with('warning', 'Applicant must complete the exam before conducting the interview.');
            }
        } else {
            if ($applicant->status !== 'exam-completed') {
                return redirect()->route('instructor.applicants')
                    ->with('warning', 'Applicant must complete the exam before conducting the interview.');
            }
        }
        
        // Guard: Check if applicant has already been interviewed by department head
        $hasDepartmentHeadInterview = Interview::where('applicant_id', $applicantId)
            ->where('status', 'completed')
            ->whereHas('interviewer', function($query) {
                $query->where('role', 'department-head');
            })
            ->exists();
        
        if ($hasDepartmentHeadInterview) {
            // Check if instructor has already completed their interview (allow viewing)
            $instructorInterview = Interview::where('applicant_id', $applicantId)
                ->where('interviewer_id', $instructor->user_id)
                ->where('status', 'completed')
                ->first();
            
            if (!$instructorInterview) {
                return redirect()->route('instructor.applicants')
                    ->with('warning', 'Cannot start interview. Applicant has already been interviewed by the department head.');
            }
        }
        
        // Get or create interview record
        $interview = Interview::firstOrCreate(
            [
                'applicant_id' => $applicantId,
                'interviewer_id' => $instructor->user_id
            ],
            [
                'status' => 'scheduled'
            ]
        );

        // If interview is completed, we're in edit mode
        $isEditMode = $interview->status === 'completed';

        return view('instructor.interview-form', compact('applicant', 'interview', 'isEditMode'));
    }

    /**
     * View detailed interview summary
     */
    public function viewInterviewSummary($applicantId)
    {
        $instructor = Auth::user();
        
        $applicant = Applicant::with(['latestInterview.interviewer', 'basicInfo'])->findOrFail($applicantId);
        
        // Check if instructor is assigned to this applicant
        if ($applicant->assigned_instructor_id !== $instructor->user_id) {
            abort(403, 'You are not assigned to view this applicant.');
        }
        
        // Get the latest interview for this applicant (any status) - this will have remarks
        $interview = Interview::where('applicant_id', $applicantId)
            ->orderBy('created_at', 'desc')
            ->first();
        
        // Check if exam is completed
        $hasCompletedExam = method_exists($applicant, 'hasCompletedExam') ? $applicant->hasCompletedExam() : ($applicant->status === 'exam-completed');
        
        // Load interviewer relationship if interview exists
        if ($interview && !$interview->relationLoaded('interviewer')) {
            $interview->load('interviewer');
        }
        
        // Calculate exam context
        $totalQuestions = 0;
        $correctAnswers = 0;
        if ($hasCompletedExam) {
            $totalQuestions = $applicant->results()->count();
            $correctAnswers = $applicant->results()->where('is_correct', true)->count();
        }
        
        return view('instructor.interview-summary', compact(
            'applicant', 
            'interview',  // Latest interview (any status) - used for remarks and display
            'totalQuestions', 
            'correctAnswers', 
            'hasCompletedExam'
        ));
    }

    /**
     * Submit interview evaluation with BSIT rubric
     */
    public function submitInterview(Request $request, $applicantId)
    {
        $instructor = Auth::user();
        
        $request->validate([
            // BSIT Rubric Criteria (8 criteria, 10 points each) - accepts any numeric value 0-10
            'communication_skills' => 'required|numeric|min:0|max:10',
            'motivation_interest' => 'required|numeric|min:0|max:10',
            'problem_solving_attitude' => 'required|numeric|min:0|max:10',
            'program_understanding' => 'required|numeric|min:0|max:10',
            'personality_attitude' => 'required|numeric|min:0|max:10',
            'it_background' => 'required|numeric|min:0|max:10',
            'willingness_to_learn' => 'required|numeric|min:0|max:10',
            'overall_impression' => 'required|numeric|min:0|max:10',
            
            // Overall Assessment
            'recommendation' => 'required|in:highly_recommended,recommended,conditional,not_recommended',
            'final_comments' => 'required|string|max:5000',
            
            // CARD/TOR GWA - required before submission (allowed range: 75-100)
            'card_tor_gwa' => 'required|numeric|min:75|max:100',
        ]);

        $interview = Interview::where('applicant_id', $applicantId)
                             ->where('interviewer_id', $instructor->user_id)
                             ->firstOrFail();

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
            'status' => 'completed',
        ]);

        // Update applicant status to interview-completed
        // Admission decision will be made by department head considering available slots
        $applicant = Applicant::findOrFail($applicantId);
        
        $applicant->update([
            'status' => 'interview-completed',
            'interview_score' => $totalScore,
            'card_tor_gwa' => $request->card_tor_gwa,
        ]);

        // Dispatch interview completed event
        \App\Helpers\BroadcastHelper::safeDispatch(new \App\Events\InterviewCompleted($interview->load(['applicant', 'interviewer'])));
        
        // Dispatch statistics update event
        $this->dispatchStatisticsUpdate();

        $totalPercent = round(($totalScore / 80) * 100);
        return redirect()->route('instructor.applicants')
                        ->with('success', 'Interview evaluation submitted successfully! Total Score: ' . $totalPercent . '/100');
    }

    /**
     * Display instructor's interview schedule
     */
    public function schedule()
    {
        $instructor = Auth::user();
        
        // Get interviews pending scheduling - only for applicants who have completed the exam
        $pendingScheduling = Interview::where('interviewer_id', $instructor->user_id)
            ->where(function ($q) {
                $q->whereNull('schedule_date')
                  ->orWhere('status', 'assigned');
            })
            ->whereHas('applicant', function($query) {
                // Only show applicants who have completed the exam
                $query->where('status', 'exam-completed');
            })
            ->with('applicant')
            ->get();

        return view('instructor.schedule', compact('pendingScheduling'));
    }

    /**
     * Display instructor's interview history
     */
    public function interviewHistory()
    {
        $instructor = Auth::user();
        
        $completedInterviews = Interview::where('interviewer_id', $instructor->user_id)
                                     ->where('status', 'completed')
                                     ->with(['applicant'])
                                     ->orderBy('schedule_date', 'desc')
                                     ->paginate(15);

        $statistics = [
            'total_completed' => $completedInterviews->total(),
            'average_score' => Interview::where('interviewer_id', $instructor->user_id)
                                     ->where('status', 'completed')
                                     ->avg('overall_score'),
            'recommended_count' => Interview::where('interviewer_id', $instructor->user_id)
                                         ->whereIn('recommendation', ['highly_recommended', 'recommended'])
                                         ->count(),
            'this_month' => Interview::where('interviewer_id', $instructor->user_id)
                                  ->where('status', 'completed')
                                  ->whereMonth('schedule_date', now()->month)
                                  ->count(),
        ];

        return view('instructor.interview-history', compact('completedInterviews', 'statistics'));
    }

    /**
     * Display evaluation guidelines and best practices
     */
    public function guidelines()
    {
        return view('instructor.guidelines');
    }

    /**
     * Show detailed applicant portfolio for instructor preparation
     */
    public function portfolio($applicantId)
    {
        $instructor = Auth::user();

        // Verify instructor is assigned to this applicant via assigned_instructor_id
        $applicant = Applicant::with([
            'accessCode',
            'results.question',
            'latestInterview'
        ])->findOrFail($applicantId);
        
        if ($applicant->assigned_instructor_id !== $instructor->user_id) {
            abort(403, 'You are not assigned to this applicant.');
        }

        // Compute basic exam stats
        $totalQuestions = $applicant->results->count();
        $correctAnswers = $applicant->results->where('is_correct', true)->count();
        // Use enrollassess_score first, then calculate from results, then fallback to exam_percentage
        $examPercentage = $applicant->enrollassess_score ?? ($totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : ($applicant->exam_percentage ?? 0));

        // Latest interview
        $latestInterview = $applicant->latestInterview;

        return view('instructor.applicant-portfolio', [
            'applicant' => $applicant,
            'latestInterview' => $latestInterview,
            'examStats' => [
                'total_questions' => $totalQuestions,
                'correct' => $correctAnswers,
                'percentage' => $examPercentage,
            ],
        ]);
    }

    /**
     * Interview Pool Methods
     */

    /**
     * Display available interviews in the pool
     */
    public function interviewPool(Request $request)
    {
        $filters = $request->only(['priority', 'search']);
        
        $availableInterviews = $this->interviewPoolService->getAvailableInterviews($filters);
        $myClaimedInterviews = $this->interviewPoolService->getUserClaimedInterviews(Auth::id());
        $poolStats = $this->interviewPoolService->getPoolStatistics();

        return view('instructor.interview-pool', compact(
            'availableInterviews',
            'myClaimedInterviews',
            'poolStats',
            'filters'
        ));
    }

    /**
     * Claim an interview from the pool
     */
    public function claimInterview(Request $request, $interviewId)
    {
        try {
            $interview = $this->interviewPoolService->claimInterview($interviewId, Auth::id());
            
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
     * Release a claimed interview back to the pool
     */
    public function releaseInterview(Request $request, $interviewId)
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
     * Get available interviews for AJAX updates
     */
    public function getAvailableInterviews(Request $request)
    {
        $filters = $request->only(['priority', 'search']);
        $availableInterviews = $this->interviewPoolService->getAvailableInterviews($filters);
        
        return response()->json([
            'interviews' => $availableInterviews,
            'count' => $availableInterviews->count()
        ]);
    }

    /**
     * Get my claimed interviews for AJAX updates
     */
    public function getMyClaimedInterviews()
    {
        $claimedInterviews = $this->interviewPoolService->getUserClaimedInterviews(Auth::id());
        
        return response()->json([
            'interviews' => $claimedInterviews,
            'count' => $claimedInterviews->count()
        ]);
    }

    /**
     * Schedule an individual interview
     */
    public function scheduleInterview(Request $request, $interviewId)
    {
        $instructor = Auth::user();
        
        $request->validate([
            'schedule_date' => 'required|date|after:now',
            'notes' => 'nullable|string|max:1000',
            'notify_email' => 'nullable|boolean',
        ]);

        $interview = Interview::with('applicant')->findOrFail($interviewId);
        
        // Verify instructor owns this interview
        if ($interview->interviewer_id !== $instructor->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this interview.'
            ], 403);
        }

        // Guard: Applicant must have completed the exam before scheduling
        $applicant = $interview->applicant;
        $hasCompletedExam = method_exists($applicant, 'hasCompletedExam') ? $applicant->hasCompletedExam() : ($applicant->status === 'exam-completed');
        if (!$hasCompletedExam) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot schedule interview. Applicant has not completed the exam.'
            ], 400);
        }

        // Guard: Check if applicant has already been interviewed by department head
        $hasDepartmentHeadInterview = Interview::where('applicant_id', $applicant->applicant_id)
            ->where('status', 'completed')
            ->whereHas('interviewer', function($query) {
                $query->where('role', 'department-head');
            })
            ->exists();
        
        if ($hasDepartmentHeadInterview) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot schedule interview. Applicant has already been interviewed by the department head.'
            ], 400);
        }

        // Guard: Respect interview window if defined
        $scheduleDate = \Carbon\Carbon::parse($request->schedule_date);
        if ($interview->interview_deadline_start && $scheduleDate->lt($interview->interview_deadline_start)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot schedule interview. Selected time is before the allowed interview window.'
            ], 400);
        }
        if ($interview->interview_deadline_end) {
            // Allow any time on the deadline end day (treat as end-of-day)
            $deadlineEnd = $interview->interview_deadline_end->copy()->endOfDay();
            if ($scheduleDate->gt($deadlineEnd)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot schedule interview. Selected time is beyond the allowed interview window.'
                ], 400);
            }
        }

        // Check for scheduling conflicts
        $conflict = Interview::where('interviewer_id', $instructor->user_id)
            ->where('interview_id', '!=', $interviewId)
            ->where('status', 'scheduled')
            ->whereNotNull('schedule_date')
            ->where(function($q) use ($scheduleDate) {
                $q->whereBetween('schedule_date', [
                    $scheduleDate->copy()->subMinutes(30),
                    $scheduleDate->copy()->addMinutes(30)
                ]);
            })
            ->exists();

        if ($conflict) {
            return response()->json([
                'success' => false,
                'message' => 'You have another interview scheduled within 30 minutes of this time.'
            ], 400);
        }

        // Update interview
        $interview->update([
            'schedule_date' => $request->schedule_date,
            'status' => 'scheduled',
            'notes' => $request->notes,
        ]);

        // Update applicant status
        $interview->applicant->update(['status' => 'interview-scheduled']);

        // Send email notification if requested
        $emailSent = false;
        if ($request->notify_email) {
            try {
                Mail::to($interview->applicant->email_address)->send(
                    new InterviewScheduleMail($interview->applicant, $interview)
                );
                $emailSent = true;
            } catch (\Exception $e) {
                \Log::error('Failed to send interview schedule email: ' . $e->getMessage());
            }
        }

        // Dispatch interview scheduled event
        \App\Helpers\BroadcastHelper::safeDispatch(new \App\Events\InterviewScheduled($interview->load(['applicant', 'interviewer'])));
        
        // Dispatch statistics update event
        $this->dispatchStatisticsUpdate();

        return response()->json([
            'success' => true,
            'message' => 'Interview scheduled successfully!',
            'email_sent' => $emailSent,
            'interview' => $interview->load('applicant')
        ]);
    }

    /**
     * Bulk schedule multiple interviews
     */
    public function bulkScheduleInterviews(Request $request)
    {
        $instructor = Auth::user();
        
        $request->validate([
            'interview_ids' => 'required|array|min:1',
            'interview_ids.*' => 'exists:interviews,interview_id',
            'schedule_date_start' => 'required|date|after:now',
            'time_interval' => 'required|integer|min:15|max:180',
            'notify_email' => 'nullable|boolean',
        ]);

        $scheduled = 0;
        $errors = [];
        $emailsSent = 0;

        DB::transaction(function () use ($request, $instructor, &$scheduled, &$errors, &$emailsSent) {
            $currentDateTime = \Carbon\Carbon::parse($request->schedule_date_start);
            
            foreach ($request->interview_ids as $interviewId) {
                try {
                    $interview = Interview::with('applicant')->findOrFail($interviewId);
                    
                    // Verify ownership
                    if ($interview->interviewer_id !== $instructor->user_id) {
                        $errors[] = "Interview #{$interviewId}: Not assigned to you";
                        continue;
                    }

                    // Guard: Applicant must have completed exam
                    $applicant = $interview->applicant;
                    $hasCompletedExam = method_exists($applicant, 'hasCompletedExam') ? $applicant->hasCompletedExam() : ($applicant->status === 'exam-completed');
                    if (!$hasCompletedExam) {
                        $errors[] = "Interview #{$interviewId}: Applicant has not completed the exam";
                        continue;
                    }

                    // Guard: Check if applicant has already been interviewed by department head
                    $hasDepartmentHeadInterview = Interview::where('applicant_id', $applicant->applicant_id)
                        ->where('status', 'completed')
                        ->whereHas('interviewer', function($query) {
                            $query->where('role', 'department-head');
                        })
                        ->exists();
                    
                    if ($hasDepartmentHeadInterview) {
                        $errors[] = "Interview #{$interviewId}: Applicant has already been interviewed by the department head";
                        continue;
                    }

                    // Guard: Respect interview window if defined
                    if ($interview->interview_deadline_start && $currentDateTime->lt($interview->interview_deadline_start)) {
                        $errors[] = "Interview #{$interviewId}: Scheduled time is before the allowed interview window";
                        continue;
                    }
                    if ($interview->interview_deadline_end) {
                        $deadlineEnd = $interview->interview_deadline_end->copy()->endOfDay();
                        if ($currentDateTime->gt($deadlineEnd)) {
                            $errors[] = "Interview #{$interviewId}: Scheduled time is beyond the allowed interview window";
                            continue;
                        }
                    }

                    // Check if already scheduled
                    if ($interview->status === 'scheduled' && $interview->schedule_date) {
                        $errors[] = "Interview #{$interviewId}: Already scheduled";
                        continue;
                    }

                    // Update interview
                    $interview->update([
                        'schedule_date' => $currentDateTime->format('Y-m-d H:i:s'),
                        'status' => 'scheduled',
                    ]);

                    // Update applicant status
                    $interview->applicant->update(['status' => 'interview-scheduled']);

                    // Send email if requested
                    if ($request->notify_email) {
                        try {
                            Mail::to($interview->applicant->email_address)->send(
                                new InterviewScheduleMail($interview->applicant, $interview)
                            );
                            $emailsSent++;
                        } catch (\Exception $e) {
                            \Log::error('Failed to send bulk schedule email: ' . $e->getMessage());
                        }
                    }

                    $scheduled++;
                    
                    // Increment time for next interview
                    $currentDateTime->addMinutes($request->time_interval);
                    
                } catch (\Exception $e) {
                    $errors[] = "Interview #{$interviewId}: " . $e->getMessage();
                }
            }
        });

        // Dispatch statistics update event after bulk operation
        if ($scheduled > 0) {
            $this->dispatchStatisticsUpdate();
        }

        return response()->json([
            'success' => true,
            'scheduled' => $scheduled,
            'errors' => $errors,
            'emails_sent' => $emailsSent,
            'message' => "Successfully scheduled {$scheduled} interview(s)."
        ]);
    }

    /**
     * Send interview notification email (reminder)
     */
    public function sendScheduleNotification($interviewId)
    {
        $instructor = Auth::user();
        $interview = Interview::findOrFail($interviewId);
        
        // Any instructor can send reminders (no ownership check needed)

        // Verify interview is scheduled
        if (!$interview->schedule_date) {
            return response()->json([
                'success' => false,
                'message' => 'Interview must be scheduled before sending reminder.'
            ], 400);
        }

        try {
            Mail::to($interview->applicant->email_address)->send(
                new InterviewScheduleMail($interview->applicant, $interview, true) // Pass true for isReminder
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Reminder email sent successfully!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send interview reminder: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email. Please try again.'
            ], 500);
        }
    }
    
    /**
     * Update remarks for an interview
     */
    public function updateRemarks(Request $request, $interviewId)
    {
        $instructor = Auth::user();
        
        $request->validate([
            'remarks' => 'nullable|string|max:5000',
        ]);

        $interview = Interview::findOrFail($interviewId);
        
        // Any instructor can add remarks (no ownership check needed)

        $interview->update([
            'remarks' => $request->remarks,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Remarks updated successfully!',
            'remarks' => $interview->remarks
        ]);
    }

    /**
     * Reschedule an existing interview
     */
    public function rescheduleInterview(Request $request, $interviewId)
    {
        $instructor = Auth::user();
        
        $request->validate([
            'schedule_date' => 'required|date|after:now',
            'notes' => 'nullable|string|max:1000',
            'notify_email' => 'nullable|boolean',
        ]);

        $interview = Interview::findOrFail($interviewId);
        
        // Verify instructor can reschedule (any instructor can reschedule)
        // No need to check interviewer_id since all instructors can reschedule
        
        // Check if interview is scheduled
        if (!$interview->schedule_date) {
            return response()->json([
                'success' => false,
                'message' => 'Interview must be scheduled before it can be rescheduled.'
            ], 400);
        }
        
        // Check if interview is completed
        if ($interview->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot reschedule a completed interview.'
            ], 400);
        }
        
        // Check time window constraints
        if ($interview->interview_deadline_start && $interview->interview_deadline_end) {
            $requestedDate = \Carbon\Carbon::parse($request->schedule_date);
            $deadlineStart = \Carbon\Carbon::parse($interview->interview_deadline_start);
            $deadlineEnd = \Carbon\Carbon::parse($interview->interview_deadline_end);
            
            if ($requestedDate->lt($deadlineStart) || $requestedDate->gt($deadlineEnd)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rescheduled date must be within the interview window: ' . 
                                $deadlineStart->format('M d, Y') . ' to ' . 
                                $deadlineEnd->format('M d, Y')
                ], 400);
            }
        }

        // Guard: Check if applicant has already been interviewed by department head
        $applicant = $interview->applicant;
        $hasDepartmentHeadInterview = Interview::where('applicant_id', $applicant->applicant_id)
            ->where('status', 'completed')
            ->whereHas('interviewer', function($query) {
                $query->where('role', 'department-head');
            })
            ->exists();
        
        if ($hasDepartmentHeadInterview) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot reschedule interview. Applicant has already been interviewed by the department head.'
            ], 400);
        }

        // Parse the schedule_date (format: YYYY-MM-DDTHH:MM)
        $scheduleDateTime = \Carbon\Carbon::parse($request->schedule_date);

        // Check for scheduling conflicts (optional - can be removed if not needed)
        // Note: Removed conflict check since any instructor can reschedule

        // Update interview
        $interview->update([
            'schedule_date' => $scheduleDateTime,
            'status' => 'scheduled',
            'notes' => $request->notes,
        ]);

        // Update applicant status
        $interview->applicant->update(['status' => 'interview-scheduled']);

        // Send email notification if requested
        $emailSent = false;
        if ($request->notify_email) {
            try {
                Mail::to($interview->applicant->email_address)->send(
                    new InterviewScheduleMail($interview->applicant, $interview)
                );
                $emailSent = true;
            } catch (\Exception $e) {
                \Log::error('Failed to send rescheduled interview email: ' . $e->getMessage());
            }
        }

        // Dispatch interview scheduled event (for rescheduling)
        \App\Helpers\BroadcastHelper::safeDispatch(new \App\Events\InterviewScheduled($interview->load(['applicant', 'instructor'])));

        return response()->json([
            'success' => true,
            'message' => 'Interview rescheduled successfully!',
            'email_sent' => $emailSent,
            'interview' => $interview->load('applicant')
        ]);
    }

    /**
     * Export applicants report as PDF
     */
    public function exportReport(Request $request)
    {
        $instructor = Auth::user();
        
        $request->validate([
            'report_type' => 'required|in:all,interviewed,not_interviewed',
        ]);

        try {
            // Get applicants based on current filters (if any)
            $applicants = null;
            if ($request->has('applicant_ids')) {
                $applicantIds = json_decode($request->applicant_ids, true);
                if (is_array($applicantIds) && count($applicantIds) > 0) {
                    $applicants = Applicant::whereIn('applicant_id', $applicantIds)
                        ->where('assigned_instructor_id', $instructor->user_id)
                        ->with(['basicInfo', 'latestInterview'])
                        ->get();
                }
            }

            $export = new InstructorReportExport($request->report_type, $applicants);
            $pdfContent = $export->export();

            $filename = $this->getReportFilename($request->report_type);

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
                
        } catch (\Exception $e) {
            \Log::error('Instructor report export failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get filename for report
     */
    protected function getReportFilename($reportType)
    {
        $filenames = [
            'all' => 'All_Assigned_Applicants',
            'interviewed' => 'Interviewed_Applicants',
            'not_interviewed' => 'Pending_Not_Interviewed_Applicants',
        ];

        $baseName = $filenames[$reportType] ?? 'Applicants_Report';
        return $baseName . '_' . now()->format('Y-m-d_His') . '.pdf';
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

        \App\Helpers\BroadcastHelper::safeDispatch(new \App\Events\StatisticsUpdated($stats));
    }
}
