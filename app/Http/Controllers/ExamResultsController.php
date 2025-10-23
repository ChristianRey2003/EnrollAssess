<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Applicant;
use App\Models\Result;

class ExamResultsController extends Controller
{
    /**
     * Display exam results for the applicant
     */
    public function show(Request $request)
    {
        $applicantId = $request->session()->get('applicant_id');
        
        if (!$applicantId) {
            return redirect()->route('applicant.login')
                ->with('error', 'Please verify your access code first.');
        }

        try {
            $applicant = Applicant::findOrFail($applicantId);
            
            // Get attempt token from session if available
            $attemptToken = $request->session()->get('exam_attempt_token');
            $stats = Result::getExamStats($applicantId, $attemptToken);
            
            return view('exam.results', compact('applicant', 'stats'));
        } catch (\Exception $e) {
            return redirect()->route('applicant.login')
                ->with('error', 'Unable to retrieve exam results. Please contact the administrator.');
        }
    }
}

