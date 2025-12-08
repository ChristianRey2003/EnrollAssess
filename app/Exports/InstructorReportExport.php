<?php

namespace App\Exports;

use App\Models\Applicant;
use App\Models\Interview;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class InstructorReportExport
{
    protected $instructor;
    protected $reportType;
    protected $applicants;

    public function __construct($reportType, $applicants = null)
    {
        $this->instructor = Auth::user();
        $this->reportType = $reportType;
        $this->applicants = $applicants;
    }

    /**
     * Generate PDF report
     */
    public function export()
    {
        $applicants = $this->getApplicants();
        
        $data = [
            'instructor' => $this->instructor,
            'applicants' => $applicants,
            'reportType' => $this->reportType,
            'generatedAt' => now()->format('F d, Y g:i A'),
            'reportTitle' => $this->getReportTitle(),
        ];

        $view = $this->getViewName();
        
        return Pdf::loadView($view, $data)
            ->setPaper('a4', 'landscape')
            ->output();
    }

    /**
     * Get applicants based on report type
     */
    protected function getApplicants()
    {
        if ($this->applicants) {
            return $this->applicants;
        }

        $instructorId = $this->instructor->user_id;
        
        $query = Applicant::where('assigned_instructor_id', $instructorId)
            ->with(['basicInfo', 'latestInterview']);

        switch ($this->reportType) {
            case 'all':
                return $query->with(['interviews'])->orderBy('created_at', 'desc')->get();
                
            case 'interviewed':
                return $query->with(['interviews' => function($q) {
                    $q->where('status', 'completed');
                }])->whereHas('interviews', function($q) {
                    $q->where('status', 'completed');
                })->orderBy('created_at', 'desc')->get();
                
            case 'not_interviewed':
                return $query->with(['interviews'])->whereDoesntHave('interviews', function($q) {
                    $q->where('status', 'completed');
                })->orderBy('created_at', 'desc')->get();
                
            default:
                return collect();
        }
    }

    /**
     * Get report title
     */
    protected function getReportTitle()
    {
        $titles = [
            'all' => 'All Assigned Applicants',
            'interviewed' => 'Interviewed Applicants',
            'not_interviewed' => 'Pending/Not Interviewed Applicants',
        ];

        return $titles[$this->reportType] ?? 'Applicants Report';
    }

    /**
     * Get view name for PDF
     */
    protected function getViewName()
    {
        return 'reports.instructor.' . $this->reportType;
    }
}

