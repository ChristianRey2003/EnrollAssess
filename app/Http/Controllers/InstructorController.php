<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Interview;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstructorController extends Controller
{
    /**
     * Display the instructor dashboard
     */
    public function dashboard()
    {
        $instructor = Auth::user();
        
        // Get assigned applicants for interviews
        $assignedApplicants = Applicant::whereHas('interviews', function($query) use ($instructor) {
            $query->where('interviewer_id', $instructor->user_id);
        })->with(['examSet.exam', 'interviews' => function($query) use ($instructor) {
            $query->where('interviewer_id', $instructor->user_id);
        }])->get();

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
        
        $applicants = Applicant::whereHas('interviews', function($query) use ($instructor) {
            $query->where('interviewer_id', $instructor->user_id);
        })->with(['examSet.exam', 'interviews' => function($query) use ($instructor) {
            $query->where('interviewer_id', $instructor->user_id);
        }])->orderBy('created_at', 'desc')->paginate(15);

        return view('instructor.applicants', compact('applicants'));
    }

    /**
     * Show interview form for specific applicant
     */
    public function showInterview($applicantId)
    {
        $instructor = Auth::user();
        
        $applicant = Applicant::with(['examSet.exam'])->findOrFail($applicantId);
        
        // Check if instructor is assigned to this applicant
        $interview = Interview::where('applicant_id', $applicantId)
                             ->where('interviewer_id', $instructor->user_id)
                             ->first();
        
        if (!$interview) {
            abort(403, 'You are not assigned to interview this applicant.');
        }

        return view('instructor.interview', compact('applicant', 'interview'));
    }

    /**
     * Submit interview evaluation
     */
    public function submitInterview(Request $request, $applicantId)
    {
        $instructor = Auth::user();
        
        $request->validate([
            'technical_score' => 'required|numeric|min:0|max:100',
            'communication_score' => 'required|numeric|min:0|max:100', 
            'problem_solving_score' => 'required|numeric|min:0|max:100',
            'overall_rating' => 'required|in:excellent,good,satisfactory,needs_improvement,poor',
            'recommendation' => 'required|in:highly_recommended,recommended,conditional,not_recommended',
            'notes' => 'nullable|string|max:2000',
        ]);

        $interview = Interview::where('applicant_id', $applicantId)
                             ->where('interviewer_id', $instructor->user_id)
                             ->firstOrFail();

        // Update interview record
        $interview->update([
            'technical_score' => $request->technical_score,
            'communication_score' => $request->communication_score,
            'problem_solving_score' => $request->problem_solving_score,
            'overall_rating' => $request->overall_rating,
            'recommendation' => $request->recommendation,
            'notes' => $request->notes,
            'interview_date' => now(),
        ]);

        // Update applicant status
        $applicant = Applicant::findOrFail($applicantId);
        $applicant->update([
            'status' => 'interview-completed'
        ]);

        return redirect()->route('instructor.applicants')
                        ->with('success', 'Interview evaluation submitted successfully!');
    }
}
