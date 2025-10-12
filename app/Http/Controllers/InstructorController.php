<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Interview;
use App\Models\User;
use App\Services\InterviewPoolService;
use App\Mail\InterviewScheduleMail;
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
    public function dashboard()
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
            'recommended' => $assignedApplicants->where('status', 'admitted')->count(),
        ];

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
            'recentInterviews'
        ));
    }

    /**
     * Display assigned applicants list
     */
    public function applicants()
    {
        $instructor = Auth::user();
        
        // Filter applicants by assigned_instructor_id for direct assignment
        $assignedApplicants = Applicant::where('assigned_instructor_id', $instructor->user_id)
            ->with(['latestInterview', 'interviews'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('instructor.applicants', compact('assignedApplicants'));
    }

    /**
     * Show interview form for specific applicant
     */
    public function showInterview($applicantId)
    {
        $instructor = Auth::user();
        
        $applicant = Applicant::findOrFail($applicantId);
        
        // Check if instructor is assigned to this applicant via assigned_instructor_id
        if ($applicant->assigned_instructor_id !== $instructor->user_id) {
            abort(403, 'You are not assigned to interview this applicant.');
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

        return view('instructor.interview-form', compact('applicant', 'interview'));
    }

    /**
     * Submit interview evaluation with enhanced rubrics
     */
    public function submitInterview(Request $request, $applicantId)
    {
        $instructor = Auth::user();
        
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
        ]);

        $interview = Interview::where('applicant_id', $applicantId)
                             ->where('interviewer_id', $instructor->user_id)
                             ->firstOrFail();

        // Calculate scores
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

        // Build update payload with only existing columns to avoid SQL errors
        $updateData = [
            // Individual rubric scores
            'rating_technical' => $technicalScore,
            'rating_communication' => $communicationScore,
            'rating_problem_solving' => $analyticalScore,
            'overall_score' => $percentage,
            'recommendation' => $request->recommendation,
            'notes' => $request->interview_notes,
            'schedule_date' => now(),
            'status' => 'completed',
        ];

        // Conditionally include optional columns if they exist
        if (Schema::hasColumn('interviews', 'rubric_scores')) {
            $updateData['rubric_scores'] = [
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
                ],
            ];
        }

        if (Schema::hasColumn('interviews', 'overall_rating')) {
            $updateData['overall_rating'] = $request->overall_rating;
        }

        if (Schema::hasColumn('interviews', 'strengths')) {
            $updateData['strengths'] = $request->strengths;
        }

        if (Schema::hasColumn('interviews', 'areas_improvement')) {
            $updateData['areas_improvement'] = $request->areas_improvement;
        }

        $interview->update($updateData);

        // Update applicant status and determine admission
        $applicant = Applicant::findOrFail($applicantId);
        $newStatus = 'interview-completed';
        
        // Auto-determine admission based on score and recommendation
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

        return redirect()->route('instructor.applicants')
                        ->with('success', 'Interview evaluation submitted successfully! Score: ' . $percentage . '%');
    }

    /**
     * Display instructor's interview schedule
     */
    public function schedule()
    {
        $instructor = Auth::user();
        
        // Get upcoming interviews with scheduled dates
        $upcomingInterviews = Interview::where('interviewer_id', $instructor->user_id)
            ->where('status', 'scheduled')
            ->whereNotNull('schedule_date')
            ->where('schedule_date', '>=', now())
            ->with('applicant')
            ->orderBy('schedule_date', 'asc')
            ->get();

        // Get interviews pending scheduling
        $pendingScheduling = Interview::where('interviewer_id', $instructor->user_id)
            ->where(function ($q) {
                $q->whereNull('schedule_date')
                  ->orWhere('status', 'assigned');
            })
            ->with('applicant')
            ->get();

        return view('instructor.schedule', compact('upcomingInterviews', 'pendingScheduling'));
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
        $examPercentage = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : ($applicant->exam_percentage ?? 0);

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

        $interview = Interview::findOrFail($interviewId);
        
        // Verify instructor owns this interview
        if ($interview->interviewer_id !== $instructor->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this interview.'
            ], 403);
        }

        // Check for scheduling conflicts
        $conflict = Interview::where('interviewer_id', $instructor->user_id)
            ->where('interview_id', '!=', $interviewId)
            ->where('status', 'scheduled')
            ->whereNotNull('schedule_date')
            ->where(function($q) use ($request) {
                $scheduleDate = \Carbon\Carbon::parse($request->schedule_date);
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
                    $interview = Interview::findOrFail($interviewId);
                    
                    // Verify ownership
                    if ($interview->interviewer_id !== $instructor->user_id) {
                        $errors[] = "Interview #{$interviewId}: Not assigned to you";
                        continue;
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

        return response()->json([
            'success' => true,
            'scheduled' => $scheduled,
            'errors' => $errors,
            'emails_sent' => $emailsSent,
            'message' => "Successfully scheduled {$scheduled} interview(s)."
        ]);
    }

    /**
     * Send interview notification email
     */
    public function sendScheduleNotification($interviewId)
    {
        $instructor = Auth::user();
        $interview = Interview::findOrFail($interviewId);
        
        // Verify ownership
        if ($interview->interviewer_id !== $instructor->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this interview.'
            ], 403);
        }

        // Verify interview is scheduled
        if (!$interview->schedule_date) {
            return response()->json([
                'success' => false,
                'message' => 'Interview must be scheduled before sending notification.'
            ], 400);
        }

        try {
            Mail::to($interview->applicant->email_address)->send(
                new InterviewScheduleMail($interview->applicant, $interview)
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Notification email sent successfully!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send interview notification: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email. Please try again.'
            ], 500);
        }
    }
}
