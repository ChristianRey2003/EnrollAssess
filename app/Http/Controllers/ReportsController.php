<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Exam;
use App\Models\ExamSet;
use App\Models\Question;
use App\Models\AccessCode;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    /**
     * Display the reports dashboard
     */
    public function index()
    {
        // Overall statistics
        $totalApplicants = Applicant::count();
        $examCompleted = Applicant::where('status', 'exam-completed')->count();
        $admitted = Applicant::where('status', 'admitted')->count();
        $rejected = Applicant::where('status', 'rejected')->count();

        // Calculate pass rate
        $passedCount = Applicant::whereIn('status', [
            'exam-completed', 'interview-scheduled', 'interview-completed', 'admitted'
        ])->count();
        $passRate = $totalApplicants > 0 ? round(($passedCount / $totalApplicants) * 100, 1) : 0;

        // Access code statistics
        $accessCodesGenerated = AccessCode::count();
        $accessCodesUsed = AccessCode::where('is_used', true)->count();

        // Exam statistics
        $totalExams = Exam::count();
        $activeExams = Exam::where('is_active', true)->count();
        $totalQuestions = Question::count();

        // Status distribution
        $statusDistribution = Applicant::selectRaw('status, COUNT(*) as count')
                                      ->groupBy('status')
                                      ->pluck('count', 'status')
                                      ->toArray();

        // Recent activity (last 7 days)
        $recentApplicants = Applicant::where('created_at', '>=', now()->subDays(7))->count();

        return view('admin.reports', compact(
            'totalApplicants',
            'examCompleted', 
            'admitted',
            'rejected',
            'passRate',
            'accessCodesGenerated',
            'accessCodesUsed',
            'totalExams',
            'activeExams',
            'totalQuestions',
            'statusDistribution',
            'recentApplicants'
        ));
    }
}
