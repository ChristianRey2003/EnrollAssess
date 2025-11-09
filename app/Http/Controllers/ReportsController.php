<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Exam;
use App\Models\Question;
use App\Models\AccessCode;
use App\Models\GeneratedReport;
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
     * Download a report
     */
    public function download($reportId)
    {
        $report = GeneratedReport::findOrFail($reportId);

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
}
