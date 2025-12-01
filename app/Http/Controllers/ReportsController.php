<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Exam;
use App\Models\Question;
use App\Models\AccessCode;
use App\Models\GeneratedReport;
use App\Models\Settings;
use App\Services\ReportGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportsController extends Controller
{
    protected $reportService;

    public function __construct(ReportGenerationService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Apply school year filter to query if needed
     */
    protected function applySchoolYearFilter($query)
    {
        $schoolYearId = session('school_year_id');
        if ($schoolYearId) {
            $query->forSchoolYear($schoolYearId);
        }
        return $query;
    }

    /**
     * Display the reports dashboard
     */
    public function index(Request $request)
    {
        $schoolYearId = session('school_year_id');
        
        // Get delegation info if user is accessing via delegation
        $delegation = null;
        $isDelegated = false;
        if (auth()->check() && auth()->user()->role === 'instructor') {
            // Check for any reports-related delegation (granular capabilities)
            $delegation = auth()->user()->delegatedPermissions()
                ->whereIn('permission', ['reports.view', 'reports.generate', 'reports.manage_archive', 'view_reports'])
                ->where('status', 'active')
                ->where(function($q) {
                    $q->whereNull('starts_at')
                      ->orWhere('starts_at', '<=', now());
                })
                ->first();
            
            if ($delegation && !$delegation->isExpired()) {
                $isDelegated = true;
            }
        }
        
        // Overall statistics (filtered by school year)
        $applicantQuery = Applicant::query();
        $this->applySchoolYearFilter($applicantQuery);
        
        $totalApplicants = (clone $applicantQuery)->count();
        $examCompleted = (clone $applicantQuery)->where('status', 'exam-completed')->count();
        $admitted = (clone $applicantQuery)->where('status', 'admitted')->count();
        $rejected = (clone $applicantQuery)->where('status', 'rejected')->count();

        // Calculate pass rate
        $passedCount = (clone $applicantQuery)->whereIn('status', [
            'exam-completed', 'interview-scheduled', 'interview-completed', 'admitted'
        ])->count();
        $passRate = $totalApplicants > 0 ? round(($passedCount / $totalApplicants) * 100, 1) : 0;

        // Access code statistics (filtered by school year through applicants)
        $accessCodeQuery = AccessCode::query();
        if ($schoolYearId) {
            $accessCodeQuery->whereHas('applicant', function($q) use ($schoolYearId) {
                $q->where('school_year_id', $schoolYearId);
            });
        }
        $accessCodesGenerated = (clone $accessCodeQuery)->count();
        $accessCodesUsed = (clone $accessCodeQuery)->where('is_used', true)->count();

        // Exam statistics
        $totalExams = Exam::count();
        $activeExams = Exam::where('is_active', true)->count();
        $totalQuestions = Question::count();

        // Status distribution (filtered by school year)
        $statusQuery = Applicant::selectRaw('status, COUNT(*) as count');
        if ($schoolYearId) {
            $statusQuery->where('school_year_id', $schoolYearId);
        }
        $statusDistribution = $statusQuery->groupBy('status')
                                      ->pluck('count', 'status')
                                      ->toArray();

        // Recent activity (last 7 days, filtered by school year)
        $recentQuery = Applicant::where('created_at', '>=', now()->subDays(7));
        if ($schoolYearId) {
            $recentQuery->where('school_year_id', $schoolYearId);
        }
        $recentApplicants = $recentQuery->count();

        // Exam Results Data
        try {
            $query = Applicant::with(['assignedInstructor', 'accessCode', 'latestInterview'])
                ->whereNotNull('enrollassess_score'); // Only show applicants who completed EnrollAssess exam
            
            // Apply school year filter
            $this->applySchoolYearFilter($query);

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('application_no', 'like', "%{$search}%")
                      ->orWhere('email_address', 'like', "%{$search}%");
                });
            }

            // Status filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Sorting - Default to newest exam completions first
            $sortBy = $request->get('sort_by', 'exam_completed_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            $allowedSorts = [
                'created_at',
                'updated_at',
                'application_no',
                'first_name',
                'last_name',
                'email_address',
                'enrollassess_score',
                'interview_score',
                'score',
                'card_tor_gwa',
                'status',
                'exam_completed_at',
            ];
            
            // Handle overall_rating sorting (calculated field)
            if ($sortBy === 'overall_rating') {
                // Handle overall_rating sorting (calculated field)
                $collection = $query->get();
                $sorted = $collection->sortBy(function($applicant) {
                    $rating = $applicant->getOverallRating();
                    return $rating ? $rating['overall_rating'] : 0;
                }, SORT_REGULAR, $sortOrder === 'desc');
                
                $page = $request->get('page', 1);
                $perPage = 20;
                $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                    $sorted->forPage($page, $perPage),
                    $sorted->count(),
                    $perPage,
                    $page,
                    ['path' => $request->url(), 'query' => $request->query()]
                );
                $applicants = $paginator;
            } elseif (in_array($sortBy, $allowedSorts)) {
                // Handle exam_completed_at with NULLS LAST for proper newest-to-oldest sorting
                if ($sortBy === 'exam_completed_at') {
                    if ($sortOrder === 'desc') {
                        // Newest first: non-null values descending, then nulls (MySQL compatible)
                        $query->orderByRaw('ISNULL(exam_completed_at), exam_completed_at DESC');
                    } else {
                        // Oldest first: non-null values ascending, then nulls (MySQL compatible)
                        $query->orderByRaw('ISNULL(exam_completed_at), exam_completed_at ASC');
                    }
                } else {
                    $query->orderBy($sortBy, $sortOrder);
                }
                $applicants = $query->paginate(20);
            } else {
                // Default fallback: newest exam completions first (MySQL compatible)
                $query->orderByRaw('ISNULL(exam_completed_at), exam_completed_at DESC');
                $applicants = $query->paginate(20);
            }

            $statuses = [
                'exam-completed',
                'interview-completed'
            ];

            // Statistics - 4 most important metrics (filtered by school year)
            $scoringService = app(\App\Services\AdmissionScoringService::class);
            $allApplicantsQuery = Applicant::whereNotNull('enrollassess_score');
            $this->applySchoolYearFilter($allApplicantsQuery);
            $allApplicants = $allApplicantsQuery->get();
            
            $qualifiersCount = $allApplicants->filter(function($applicant) use ($scoringService) {
                return $scoringService->hasAllRequiredScores($applicant);
            })->count();
            
            $overallRatings = $allApplicants->map(function($applicant) {
                $rating = $applicant->getOverallRating();
                return $rating ? $rating['overall_rating'] : null;
            })->filter()->values();
            
            $statsQuery = Applicant::query();
            $this->applySchoolYearFilter($statsQuery);
            
            $stats = [
                'qualifiers_count' => $qualifiersCount,
                'average_overall' => $overallRatings->count() > 0 ? round($overallRatings->avg(), 2) : 0,
                'average_uee' => round((clone $statsQuery)->whereNotNull('score')->avg('score') ?? 0, 2),
                'average_gwa' => round((clone $statsQuery)->whereNotNull('card_tor_gwa')->avg('card_tor_gwa') ?? 0, 2),
            ];

            // Return JSON for AJAX pagination requests only
            if ($request->ajax() && $request->header('Accept') && str_contains($request->header('Accept'), 'application/json')) {
                return response()->json([
                    'applicants' => $applicants->items(),
                    'pagination' => [
                        'current_page' => $applicants->currentPage(),
                        'last_page' => $applicants->lastPage(),
                        'per_page' => $applicants->perPage(),
                        'total' => $applicants->total(),
                        'from' => $applicants->firstItem(),
                        'to' => $applicants->lastItem(),
                    ],
                    'pagination_html' => $applicants->hasPages() ? $applicants->onEachSide(2)->appends($request->query())->links()->render() : '',
                ]);
            }
        } catch (\Exception $e) {
            $applicants = collect([])->paginate(20);
            $statuses = [];
            $stats = [
                'qualifiers_count' => 0,
                'average_overall' => 0,
                'average_uee' => 0,
                'average_gwa' => 0,
            ];
        }

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
            'recentApplicants',
            'applicants',
            'statuses',
            'applicants',
            'statuses',
            'stats',
            'delegation',
            'isDelegated'
        ));
    }

    /**
     * Generate a report
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:final_ranking,statistical_analysis,interview_summary,qualifiers_list,qualifiers_list_pdf,evsu_results,evsu_results_pdf,geographic_performance,strand_distribution,demographic_overview',
            'filters' => 'nullable|array',
        ]);

        try {
            $reportType = $validated['type'];
            $filters = $validated['filters'] ?? [];
            $userId = auth()->id();

            // Validate slots for qualifiers_list
            if (in_array($reportType, ['qualifiers_list', 'qualifiers_list_pdf'])) {
                $request->validate([
                    'filters.slots' => 'required|integer|min:1|max:500',
                ]);
            }

            $report = match($reportType) {
                'final_ranking' => $this->reportService->generateFinalRanking($filters, $userId),
                'statistical_analysis' => $this->reportService->generateStatisticalAnalysis($filters, $userId),
                'interview_summary' => $this->reportService->generateInterviewSummary($filters, $userId),
                'qualifiers_list' => $this->reportService->generateQualifiersList($filters, $userId),
                'qualifiers_list_pdf' => $this->reportService->generateQualifiersListPdf($filters, $userId),
                'evsu_results' => $this->reportService->generateEVSUResults($filters, $userId),
                'evsu_results_pdf' => $this->reportService->generateEVSUResultsPdf($filters, $userId),
                'geographic_performance' => $this->reportService->generateGeographicPerformanceReport($filters, $userId),
                'strand_distribution' => $this->reportService->generateStrandDistributionReport($filters, $userId),
                'demographic_overview' => $this->reportService->generateDemographicOverviewReport($filters, $userId),
            };

            return response()->json([
                'success' => true,
                'message' => 'Report generated successfully!',
                'report' => [
                    'id' => $report->id,
                    'type' => $report->readable_type,
                    'title' => $report->title,
                    'file_size' => $report->formatted_file_size,
                    'created_at' => $report->created_at->format('M d, Y g:i A'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download a report (including archived reports)
     */
    public function download($reportId)
    {
        $report = GeneratedReport::withTrashed()->findOrFail($reportId);

        if (!$report->fileExists()) {
            return back()->with('error', 'Report file not found. The file may have been deleted or moved.');
        }

        try {
            // Get the file path using Storage facade to respect configured disk root
            $filePath = Storage::path($report->file_path);
            $filename = basename($report->file_path);
            
            // Force download with proper headers to prompt save dialog
            return response()->download($filePath, $filename, [
                'Content-Type' => $this->getContentType($filename),
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to download report: ' . $e->getMessage());
        }
    }

    /**
     * Get content type based on file extension
     */
    private function getContentType($filename)
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        
        return match(strtolower($extension)) {
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'doc' => 'application/msword',
            'xls' => 'application/vnd.ms-excel',
            default => 'application/octet-stream',
        };
    }

    /**
     * Preview report data
     */
    public function preview(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:final_ranking,statistical_analysis,interview_summary,qualifiers_list,qualifiers_list_pdf,evsu_results,evsu_results_pdf',
            'filters' => 'nullable|array',
        ]);

        try {
            $data = $this->reportService->getPreviewData(
                $validated['type'],
                $validated['filters'] ?? []
            );

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate preview: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get report history
     */
    public function history()
    {
        $reports = GeneratedReport::with('generatedBy')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Transform paginator items safely
        $items = $reports->getCollection()->map(function ($report) {
            return [
                'id' => $report->id,
                'type' => $report->readable_type,
                'title' => $report->title,
                'generated_by' => $report->generatedBy->name ?? 'Unknown',
                'created_at' => $report->created_at->format('M d, Y g:i A'),
                'file_size' => $report->formatted_file_size,
                'filters' => $report->filters_applied,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'reports' => $items,
            'pagination' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'total' => $reports->total(),
            ],
        ]);
    }

    /**
     * Delete a report
     */
    public function destroy($reportId)
    {
        $report = GeneratedReport::findOrFail($reportId);

        // Delete file from storage
        if ($report->fileExists()) {
            Storage::delete($report->file_path);
        }

        // Delete database record
        $report->delete();

        return response()->json([
            'success' => true,
            'message' => 'Report deleted successfully.',
        ]);
    }

    
    /**
     * Get statistics for all active reports (for confirmation modal)
     */
    public function getStats()
    {
        $reports = GeneratedReport::get();
        
        $totalCount = $reports->count();
        $totalFileSize = $reports->sum('file_size');
        
        // Format total file size
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $totalFileSize;
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        $formattedSize = round($bytes, 2) . ' ' . $units[$i];

        return response()->json([
            'success' => true,
            'total_count' => $totalCount,
            'total_file_size' => $totalFileSize,
            'formatted_file_size' => $formattedSize,
        ]);
    }

    /**
     * Archive all reports (soft delete)
     */
    public function archiveAll()
    {
        try {
            $reports = GeneratedReport::get();
            $count = $reports->count();
            
            if ($count === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No reports to archive.',
                ], 400);
            }

            // Soft delete all reports
            GeneratedReport::query()->delete();

            return response()->json([
                'success' => true,
                'message' => "Successfully archived {$count} report(s).",
                'archived_count' => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to archive reports: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get archived reports
     */
    public function archivedHistory(Request $request)
    {
        \Log::info('Archived History Request', ['all' => $request->all(), 'school_year_id' => $request->school_year_id]);
        $query = GeneratedReport::onlyTrashed()
            ->with('generatedBy')
            ->orderBy('deleted_at', 'desc');

        // Apply school year filter
        if ($request->has('school_year_id') && $request->school_year_id !== 'all') {
            $query->where('school_year_id', $request->school_year_id);
        }

        $reports = $query->paginate(10);

        // Transform paginator items safely
        $items = $reports->getCollection()->map(function ($report) {
            return [
                'id' => $report->id,
                'type' => $report->readable_type,
                'title' => $report->title,
                'generated_by' => $report->generatedBy->name ?? 'Unknown',
                'created_at' => $report->created_at->format('M d, Y g:i A'),
                'deleted_at' => $report->deleted_at->format('M d, Y g:i A'),
                'file_size' => $report->formatted_file_size,
                'filters' => $report->filters_applied,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'reports' => $items,
            'pagination' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'total' => $reports->total(),
            ],
            'debug_filters' => [
                'received_school_year_id' => $request->input('school_year_id'),
                'has_filter' => $request->has('school_year_id'),
                'is_not_all' => $request->input('school_year_id') !== 'all',
            ]
        ]);
    }

    /**
     * Restore all archived reports
     */
    public function restoreAll()
    {
        try {
            $archivedCount = GeneratedReport::onlyTrashed()->count();
            
            if ($archivedCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No archived reports to restore.',
                ], 400);
            }

            // Restore all archived reports
            GeneratedReport::onlyTrashed()->restore();

            return response()->json([
                'success' => true,
                'message' => "Successfully restored {$archivedCount} report(s).",
                'restored_count' => $archivedCount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore reports: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Permanently delete all archived reports
     */
    public function permanentlyDeleteAll()
    {
        try {
            $archivedReports = GeneratedReport::onlyTrashed()->get();
            $count = $archivedReports->count();
            
            if ($count === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No archived reports to delete.',
                ], 400);
            }

            // Delete files from storage
            foreach ($archivedReports as $report) {
                if ($report->fileExists()) {
                    Storage::delete($report->file_path);
                }
            }

            // Permanently delete from database
            GeneratedReport::onlyTrashed()->forceDelete();

            return response()->json([
                'success' => true,
                'message' => "Successfully permanently deleted {$count} report(s).",
                'deleted_count' => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to permanently delete reports: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get report signature settings
     */
    public function getSignatureSettings()
    {
        $settings = [
            'control_no' => Settings::getSetting('report_control_no', 'EVSU- SASO-F-131'),
            'revision_no' => Settings::getSetting('report_revision_no', '0'),
            'prepared_by_name' => Settings::getSetting('report_signature_prepared_by_name', 'JOSEPH JAYMEL S. MORPOS'),
            'prepared_by_title' => Settings::getSetting('report_signature_prepared_by_title', 'Head, Computer Studies Department'),
            'noted_name' => Settings::getSetting('report_signature_noted_name', 'DR. JEFFRY V. OCAY'),
            'noted_title' => Settings::getSetting('report_signature_noted_title', 'Director, Ormoc Campus'),
            'recommending_name' => Settings::getSetting('report_signature_recommending_name', 'LYDIA M. MORANTE, D.A.'),
            'recommending_title' => Settings::getSetting('report_signature_recommending_title', 'Vice President for Academic Affairs'),
            'approved_name' => Settings::getSetting('report_signature_approved_name', 'DENNIS C. DE PAZ, Ph.D.'),
            'approved_title' => Settings::getSetting('report_signature_approved_title', 'University President'),
        ];

        return response()->json([
            'success' => true,
            'settings' => $settings,
        ]);
    }

    /**
     * Update report signature settings
     */
    public function updateSignatureSettings(Request $request)
    {
        $validated = $request->validate([
            'control_no' => 'nullable|string|max:255',
            'revision_no' => 'nullable|string|max:50',
            'prepared_by_name' => 'nullable|string|max:255',
            'prepared_by_title' => 'nullable|string|max:255',
            'noted_name' => 'nullable|string|max:255',
            'noted_title' => 'nullable|string|max:255',
            'recommending_name' => 'nullable|string|max:255',
            'recommending_title' => 'nullable|string|max:255',
            'approved_name' => 'nullable|string|max:255',
            'approved_title' => 'nullable|string|max:255',
        ]);

        try {
            Settings::setSetting('report_control_no', $validated['control_no'] ?? '', 'reports');
            Settings::setSetting('report_revision_no', $validated['revision_no'] ?? '', 'reports');
            Settings::setSetting('report_signature_prepared_by_name', $validated['prepared_by_name'] ?? '', 'reports');
            Settings::setSetting('report_signature_prepared_by_title', $validated['prepared_by_title'] ?? '', 'reports');
            Settings::setSetting('report_signature_noted_name', $validated['noted_name'] ?? '', 'reports');
            Settings::setSetting('report_signature_noted_title', $validated['noted_title'] ?? '', 'reports');
            Settings::setSetting('report_signature_recommending_name', $validated['recommending_name'] ?? '', 'reports');
            Settings::setSetting('report_signature_recommending_title', $validated['recommending_title'] ?? '', 'reports');
            Settings::setSetting('report_signature_approved_name', $validated['approved_name'] ?? '', 'reports');
            Settings::setSetting('report_signature_approved_title', $validated['approved_title'] ?? '', 'reports');

            // Clear cache
            Settings::clearCache();

            return response()->json([
                'success' => true,
                'message' => 'Signature settings updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update signature settings: ' . $e->getMessage(),
            ], 500);
        }
    }
}
