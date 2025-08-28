<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Interview;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InterviewController extends Controller
{
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
                $q->where('full_name', 'like', '%' . $search . '%')
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
     * Bulk assign applicants to instructors for interviews
     */
    public function bulkAssignToInstructors(Request $request)
    {
        $request->validate([
            'applicant_ids' => 'required|array',
            'applicant_ids.*' => 'exists:applicants,applicant_id',
            'assignment_strategy' => 'required|in:balanced,manual',
            'assignments' => 'required_if:assignment_strategy,manual|array',
            'assignments.*.instructor_id' => 'required_if:assignment_strategy,manual|exists:users,user_id',
            'assignments.*.applicant_count' => 'required_if:assignment_strategy,manual|integer|min:1',
        ]);

        $assigned = 0;
        $errors = [];
        $instructors = User::where('role', 'instructor')->get();

        if ($instructors->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No instructors available for assignment.'
            ]);
        }

        DB::transaction(function () use ($request, &$assigned, &$errors, $instructors) {
            if ($request->assignment_strategy === 'balanced') {
                // Balanced distribution among all instructors
                $applicantIds = $request->applicant_ids;
                $instructorIndex = 0;
                
                foreach ($applicantIds as $applicantId) {
                    try {
                        // Check if already has interview
                        if (Interview::where('applicant_id', $applicantId)->exists()) {
                            $applicant = Applicant::find($applicantId);
                            $errors[] = "Interview already exists for {$applicant->full_name}";
                            continue;
                        }

                        $instructor = $instructors[$instructorIndex % $instructors->count()];

                        Interview::create([
                            'applicant_id' => $applicantId,
                            'interviewer_id' => $instructor->user_id,
                            'status' => 'assigned',
                            'schedule_date' => null, // To be scheduled later
                        ]);

                        // Update applicant status
                        Applicant::where('applicant_id', $applicantId)
                                 ->update(['status' => 'interview-assigned']);

                        $assigned++;
                        $instructorIndex++;

                    } catch (\Exception $e) {
                        $errors[] = "Failed to assign applicant ID {$applicantId}: " . $e->getMessage();
                    }
                }
            } else {
                // Manual assignment with specific counts
                foreach ($request->assignments as $assignment) {
                    $instructorId = $assignment['instructor_id'];
                    $requestedCount = $assignment['applicant_count'];
                    
                    $availableApplicants = collect($request->applicant_ids)
                        ->filter(function($applicantId) {
                            return !Interview::where('applicant_id', $applicantId)->exists();
                        })
                        ->take($requestedCount);

                    foreach ($availableApplicants as $applicantId) {
                        try {
                            Interview::create([
                                'applicant_id' => $applicantId,
                                'interviewer_id' => $instructorId,
                                'status' => 'assigned',
                                'schedule_date' => null,
                            ]);

                            Applicant::where('applicant_id', $applicantId)
                                     ->update(['status' => 'interview-assigned']);

                            $assigned++;

                            // Remove from available pool
                            $request->applicant_ids = array_diff($request->applicant_ids, [$applicantId]);

                        } catch (\Exception $e) {
                            $errors[] = "Failed to assign applicant ID {$applicantId}: " . $e->getMessage();
                        }
                    }
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Assigned {$assigned} applicants to instructors successfully.",
            'assigned' => $assigned,
            'errors' => $errors
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
}