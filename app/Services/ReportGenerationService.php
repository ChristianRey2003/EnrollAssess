<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\GeneratedReport;
use App\Models\Interview;
use App\Models\Result;
use App\Models\Question;
use App\Models\Settings;
use App\Services\AdmissionScoringService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Exports\EVSUQualifiersExport;
use App\Exports\EVSUResultsExport;

class ReportGenerationService
{
    /**
     * Generate Final Applicant Ranking report
     */
    public function generateFinalRanking($filters, $userId)
    {
        \Log::info('Starting report generation', ['filters' => $filters, 'userId' => $userId]);
        
        $applicants = $this->getApplicantRankings($filters);
        \Log::info('Applicants retrieved', ['count' => $applicants->count()]);
        
        $data = [
            'applicants' => $applicants,
            'filters' => $filters,
            'totalApplicants' => $applicants->count(),
            'recommended' => $applicants->where('recommendation', 'recommended')->count(),
            'averageScore' => $applicants->avg('final_score') ?? 0,
            'generatedAt' => now()->format('F d, Y - g:i A'),
            'generatedBy' => auth()->user()->name ?? 'System',
            'signatures' => $this->getSignatureSettings(),
        ];

        \Log::info('Generating PDF with dompdf');
        try {
            $pdf = Pdf::loadView('reports.pdf.final-ranking', $data);
            $pdf->setPaper('A4', 'portrait');
            
            $filename = 'final_ranking_' . now()->format('Y-m-d_His') . '.pdf';
            $path = 'reports/' . $filename;
            
            \Log::info('Saving PDF to storage', ['path' => $path]);
            
            $output = $pdf->output();
            \Log::info('PDF generated', ['size' => strlen($output) . ' bytes']);
            
            $saved = Storage::put($path, $output);
            \Log::info('Storage::put result', ['saved' => $saved, 'exists' => Storage::exists($path)]);
            
            if (!Storage::exists($path)) {
                throw new \Exception('PDF file was not saved to storage at: ' . $path);
            }
        } catch (\Exception $e) {
            \Log::error('PDF generation failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
        
        \Log::info('PDF saved, creating database record');
        $report = $this->saveReportToDatabase(
            'final_ranking',
            'Final Applicant Ranking Report',
            $path,
            $filters,
            $userId,
            [
                'total_applicants' => $data['totalApplicants'],
                'recommended' => $data['recommended'],
                'average_score' => round($data['averageScore'] ?? 0, 2),
            ]
        );
        
        \Log::info('Report generation complete', ['report_id' => $report->id]);
        return $report;
    }

    /**
     * Generate Statistical Analysis report
     */
    public function generateStatisticalAnalysis($filters, $userId)
    {
        $applicants = $this->getApplicantRankings($filters);
        
        // Score distribution
        $scoreDistribution = [
            'excellent' => $applicants->filter(fn($a) => $a->final_score >= 90)->count(),
            'good' => $applicants->filter(fn($a) => $a->final_score >= 80 && $a->final_score < 90)->count(),
            'satisfactory' => $applicants->filter(fn($a) => $a->final_score >= 75 && $a->final_score < 80)->count(),
            'below_passing' => $applicants->filter(fn($a) => $a->final_score < 75)->count(),
        ];

        // Status distribution
        $statusDistribution = Applicant::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Calculate statistics
        $scores = $applicants->pluck('final_score')->filter();
        
        $data = [
            'applicants' => $applicants,
            'scoreDistribution' => $scoreDistribution,
            'statusDistribution' => $statusDistribution,
            'statistics' => [
                'total' => $applicants->count(),
                'average' => round($scores->avg(), 2),
                'median' => round($scores->median(), 2),
                'highest' => round($scores->max(), 2),
                'lowest' => round($scores->min(), 2),
                'pass_rate' => $applicants->count() > 0 
                    ? round(($applicants->filter(fn($a) => $a->final_score >= 75)->count() / $applicants->count()) * 100, 1) 
                    : 0,
            ],
            'filters' => $filters,
            'generatedAt' => now()->format('F d, Y - g:i A'),
            'generatedBy' => auth()->user()->name ?? 'System',
        ];

        $pdf = Pdf::loadView('reports.pdf.statistical-analysis', $data);
        $pdf->setPaper('A4', 'portrait');
        
        $filename = 'statistical_analysis_' . now()->format('Y-m-d_His') . '.pdf';
        $path = 'reports/' . $filename;
        
        Storage::put($path, $pdf->output());
        
        return $this->saveReportToDatabase(
            'statistical_analysis',
            'Statistical Analysis Report',
            $path,
            $filters,
            $userId,
            $data['statistics']
        );
    }

    /**
     * Generate Interview Summary report
     */
    public function generateInterviewSummary($filters, $userId)
    {
        $query = Interview::with(['applicant', 'interviewer'])
            ->whereHas('applicant', function($q) {
                $q->whereNotNull('interview_score');
            });

        // Apply filters
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['dateRange']) && $filters['dateRange'] !== 'all') {
            $query = $this->applyDateFilter($query, $filters['dateRange']);
        }

        $interviews = $query->orderBy('overall_score', 'desc')->get();

        // Calculate rubric averages
        $rubricAverages = [
            'communication_skills' => round($interviews->avg('communication_skills'), 2),
            'motivation_interest' => round($interviews->avg('motivation_interest'), 2),
            'problem_solving_attitude' => round($interviews->avg('problem_solving_attitude'), 2),
            'program_understanding' => round($interviews->avg('program_understanding'), 2),
            'personality_attitude' => round($interviews->avg('personality_attitude'), 2),
            'it_background' => round($interviews->avg('it_background'), 2),
            'willingness_to_learn' => round($interviews->avg('willingness_to_learn'), 2),
            'overall_impression' => round($interviews->avg('overall_impression'), 2),
        ];

        // Recommendation distribution
        $recommendationDistribution = $interviews->groupBy('recommendation')
            ->map(fn($group) => $group->count())
            ->toArray();

        $data = [
            'interviews' => $interviews,
            'rubricAverages' => $rubricAverages,
            'recommendationDistribution' => $recommendationDistribution,
            'statistics' => [
                'total' => $interviews->count(),
                'completed' => $interviews->where('status', 'completed')->count(),
                'average_score' => round($interviews->avg('overall_score'), 2),
                'highly_recommended' => $interviews->where('recommendation', 'highly_recommended')->count(),
                'recommended' => $interviews->where('recommendation', 'recommended')->count(),
                'conditional' => $interviews->where('recommendation', 'conditional')->count(),
                'not_recommended' => $interviews->where('recommendation', 'not_recommended')->count(),
            ],
            'filters' => $filters,
            'generatedAt' => now()->format('F d, Y - g:i A'),
            'generatedBy' => auth()->user()->name ?? 'System',
        ];

        $pdf = Pdf::loadView('reports.pdf.interview-summary', $data);
        $pdf->setPaper('A4', 'portrait');
        
        $filename = 'interview_summary_' . now()->format('Y-m-d_His') . '.pdf';
        $path = 'reports/' . $filename;
        
        Storage::put($path, $pdf->output());
        
        return $this->saveReportToDatabase(
            'interview_summary',
            'Interview Summary Report',
            $path,
            $filters,
            $userId,
            $data['statistics']
        );
    }

    /**
     * Get applicant rankings with calculated final scores
     */
    public function getApplicantRankings($filters)
    {
        $query = Applicant::query()
            ->with(['latestInterview', 'results'])
            ->where(function($q) {
                $q->whereNotNull('enrollassess_score')
                  ->orWhereNotNull('interview_score');
            });

        // Apply filters
        $query = $this->applyFilters($query, $filters);

        $scoringService = app(AdmissionScoringService::class);
        
        $applicants = $query->get()->map(function($applicant) use ($scoringService) {
            // Always use the scoring service for consistent calculation with school-year-specific weights
            // The service automatically uses the applicant's school_year_id to get the correct weights
            $rating = $scoringService->calculateOverallRating($applicant);
            $applicant->final_score = $rating['overall_rating'];
            
            // Determine recommendation based on overall rating
            if ($finalScore >= 75) {
                $applicant->recommendation = 'recommended';
            } elseif ($finalScore >= 70) {
                $applicant->recommendation = 'waitlisted';
            } else {
                $applicant->recommendation = 'not_recommended';
            }
            
            return $applicant;
        });

        // Sort based on filters
        $sortBy = $filters['sortBy'] ?? 'score-desc';
        
        return match($sortBy) {
            'score-desc' => $applicants->sortByDesc('final_score')->values(),
            'score-asc' => $applicants->sortBy('final_score')->values(),
            'name-asc' => $applicants->sortBy('last_name')->values(),
            'date-desc' => $applicants->sortByDesc('created_at')->values(),
            'date-asc' => $applicants->sortBy('created_at')->values(),
            default => $applicants->sortByDesc('final_score')->values(),
        };
    }

    /**
     * Apply filters to query
     */
    protected function applyFilters($query, $filters)
    {
        // Apply school year filter first
        $schoolYearId = session('school_year_id');
        if ($schoolYearId) {
            $query->where('school_year_id', $schoolYearId);
        }

        // Applicant status filter
        if (!empty($filters['applicantStatus']) && $filters['applicantStatus'] !== 'all') {
            switch ($filters['applicantStatus']) {
                case 'exam-completed':
                    $query->where('status', 'exam-completed');
                    break;
                case 'interview-pending':
                    $query->whereIn('status', ['exam-completed', 'interview-scheduled']);
                    break;
                default:
                    $query->where('status', $filters['applicantStatus']);
            }
        }

        // Score range filter (applied after retrieval since we calculate final_score)
        
        // Date range filter
        if (!empty($filters['dateRange']) && $filters['dateRange'] !== 'all') {
            $query = $this->applyDateFilter($query, $filters['dateRange'], $filters);
        }

        return $query;
    }

    /**
     * Apply date filter to query
     */
    protected function applyDateFilter($query, $dateRange, $filters = [])
    {
        switch ($dateRange) {
            case 'this-week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'this-month':
                $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                break;
            case 'last-month':
                $query->whereBetween('created_at', [
                    now()->subMonth()->startOfMonth(),
                    now()->subMonth()->endOfMonth()
                ]);
                break;
            case 'custom':
                if (!empty($filters['startDate']) && !empty($filters['endDate'])) {
                    $query->whereBetween('created_at', [
                        $filters['startDate'],
                        $filters['endDate']
                    ]);
                }
                break;
        }

        return $query;
    }

    /**
     * Save report to database
     */
    protected function saveReportToDatabase($type, $title, $filePath, $filters, $userId, $metadata = [])
    {
        $fileSize = Storage::size($filePath);
        
        // Determine school year ID
        $schoolYearId = session('school_year_id');
        
        // If not in session, check filters (for consistency)
        if (!$schoolYearId && isset($filters['school_year_id'])) {
            $schoolYearId = $filters['school_year_id'];
        }

        return GeneratedReport::create([
            'report_type' => $type,
            'title' => $title,
            'file_path' => $filePath,
            'filters_applied' => $filters,
            'generated_by' => $userId,
            'school_year_id' => $schoolYearId,
            'file_size' => $fileSize,
            'status' => 'completed',
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get preview data without generating PDF
     */
    public function getPreviewData($reportType, $filters)
    {
        switch ($reportType) {
            case 'final_ranking':
                $applicants = $this->getApplicantRankings($filters);
                $avgScore = $applicants->avg('final_score');
                
                return [
                    'total_applicants' => $applicants->count(),
                    'average_score' => $avgScore ? round($avgScore, 2) : 0,
                    'recommended' => $applicants->where('recommendation', 'recommended')->count(),
                    'top_applicants' => $applicants->take(5)->map(fn($a) => [
                        'name' => $a->full_name,
                        'score' => $a->final_score,
                    ])->toArray(),
                ];

            case 'statistical_analysis':
                $applicants = $this->getApplicantRankings($filters);
                $scores = $applicants->pluck('final_score')->filter();
                
                return [
                    'total' => $applicants->count(),
                    'average' => $scores->avg() ? round($scores->avg(), 2) : 0,
                    'median' => $scores->median() ? round($scores->median(), 2) : 0,
                    'pass_rate' => $applicants->count() > 0 
                        ? round(($applicants->filter(fn($a) => $a->final_score >= 75)->count() / $applicants->count()) * 100, 1) 
                        : 0,
                ];

            case 'interview_summary':
                $interviews = Interview::with('applicant')
                    ->whereNotNull('overall_score')
                    ->get();
                    
                $avgScore = $interviews->avg('overall_score');
                
                return [
                    'total' => $interviews->count(),
                    'average_score' => $avgScore ? round($avgScore, 2) : 0,
                    'highly_recommended' => $interviews->where('recommendation', 'highly_recommended')->count(),
                    'recommended' => $interviews->where('recommendation', 'recommended')->count(),
                ];

            default:
                return [];
        }
    }

    /**
     * Generate Qualifiers List report (Word document)
     * Get top N qualifiers based on overall rating, sorted alphabetically
     */
    public function generateQualifiersList($filters, $userId)
    {
        \Log::info('Starting qualifiers list generation', ['filters' => $filters, 'userId' => $userId]);

        // Get number of slots (required parameter)
        $slots = $filters['slots'] ?? 112;
        
        // Get all applicants with complete scores, filtered by school year
        $query = Applicant::query()
            ->whereNotNull('score')
            ->whereNotNull('card_tor_gwa')
            ->whereNotNull('enrollassess_score')
            ->whereNotNull('interview_score');
        
        // Apply school year filter
        $schoolYearId = session('school_year_id');
        if ($schoolYearId) {
            $query->where('school_year_id', $schoolYearId);
        }

        // Get applicants and filter those with all required scores
        $allApplicants = $query->get()->filter(function($applicant) {
            return $applicant->hasAllRequiredScores();
        });

        \Log::info('Applicants with all scores', ['count' => $allApplicants->count()]);

        // Sort by overall rating (descending - highest first)
        $sortedByRating = $allApplicants->sortByDesc(function($applicant) {
            $rating = $applicant->getOverallRating();
            return $rating ? $rating['overall_rating'] : 0;
        });

        // Take top N based on slots
        $topQualifiers = $sortedByRating->take($slots);

        // Sort alphabetically by last name (as per document note: "The list is arranged alphabetically")
        $qualifiers = $topQualifiers->sortBy('last_name')->values();

        \Log::info('Qualifiers selected and sorted', ['count' => $qualifiers->count()]);

        // Prepare export filters
        $exportFilters = [
            'campus' => $filters['campus'] ?? 'Ormoc/Computer Studies',
            'program_code' => $filters['program_code'] ?? 'BSIT',
            'program_description' => $filters['program_description'] ?? 'Bachelor of Science in Information Technology',
            'academic_year' => $filters['academic_year'] ?? (date('Y') . '-' . (date('Y') + 1)),
            'slots' => $slots,
        ];

        try {
            // Generate Word document
            $export = new EVSUQualifiersExport($qualifiers, $exportFilters);
            $tempFile = $export->export();

            // Read file content for storage
            $fileContent = file_get_contents($tempFile);
            
            // Generate filename
            $filename = 'EVSU_Qualifiers_List_' . 
                       str_replace(' ', '_', $exportFilters['program_code']) . '_' . 
                       now()->format('Y-m-d_His') . '.docx';
            
            $path = 'reports/' . $filename;

            // Save to storage
            Storage::put($path, $fileContent);

            // Clean up temp file
            @unlink($tempFile);

            \Log::info('Word document saved', ['path' => $path]);

        } catch (\Exception $e) {
            \Log::error('Word document generation failed', [
                'error' => $e->getMessage(), 
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        // Save to database
        $report = $this->saveReportToDatabase(
            'qualifiers_list',
            'List of Qualifiers Report',
            $path,
            $filters,
            $userId,
            [
                'total_qualifiers' => $qualifiers->count(),
                'slots' => $slots,
                'format' => 'docx',
            ]
        );

        \Log::info('Qualifiers list generation complete', ['report_id' => $report->id]);
        return $report;
    }

    /**
     * Generate Qualifiers List as PDF
     */
    public function generateQualifiersListPdf($filters, $userId)
    {
        \Log::info('Starting Qualifiers List PDF generation', ['filters' => $filters]);

        // Get number of slots (required)
        $slots = (int) ($filters['slots'] ?? 0);
        if ($slots <= 0) {
            throw new \InvalidArgumentException('Slots parameter is required and must be greater than 0');
        }

        // Get all applicants with complete scores
        $applicants = $this->getApplicantRankings($filters);
        
        // Filter to only include applicants with all required scores
        $completeApplicants = $applicants->filter(function ($applicant) {
            return $applicant->hasAllRequiredScores();
        });

        // Get top N qualifiers ordered by overall rating (desc)
        $topQualifiers = $completeApplicants->sortByDesc(function ($applicant) {
            $rating = $applicant->getOverallRating();
            return $rating ? $rating['overall_rating'] : 0;
        })->take($slots);

        // Sort qualifiers alphabetically by last name for final output
        $qualifiers = $topQualifiers->sortBy('last_name')->values();

        \Log::info('Qualifiers selected and sorted', ['count' => $qualifiers->count()]);

        // Prepare export filters
        $exportFilters = [
            'campus' => $filters['campus'] ?? 'Ormoc/Computer Studies',
            'program_code' => $filters['program_code'] ?? 'BSIT',
            'program_description' => $filters['program_description'] ?? 'Bachelor of Science in Information Technology',
            'academic_year' => $filters['academic_year'] ?? (date('Y') . '-' . (date('Y') + 1)),
            'slots' => $slots,
        ];

        try {
            // Generate PDF from template
            $export = new EVSUQualifiersExport($qualifiers, $exportFilters);
            $tempFile = $export->exportPdf();

            // Read file content for storage
            $fileContent = file_get_contents($tempFile);
            
            // Generate filename
            $filename = 'EVSU_Qualifiers_List_' . 
                       str_replace(' ', '_', $exportFilters['program_code']) . '_' . 
                       now()->format('Y-m-d_His') . '.pdf';
            
            $path = 'reports/' . $filename;

            // Save to storage
            Storage::put($path, $fileContent);

            // Clean up temp file
            @unlink($tempFile);

            \Log::info('Qualifiers PDF saved', ['path' => $path]);

        } catch (\Exception $e) {
            \Log::error('Qualifiers PDF generation failed', [
                'error' => $e->getMessage(), 
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        // Save to database
        $report = $this->saveReportToDatabase(
            'qualifiers_list_pdf',
            'List of Qualifiers Report (PDF)',
            $path,
            $filters,
            $userId,
            [
                'total_qualifiers' => $qualifiers->count(),
                'slots' => $slots,
                'format' => 'pdf',
            ]
        );

        \Log::info('Qualifiers PDF generation complete', ['report_id' => $report->id]);
        return $report;
    }

    /**
     * Generate EVSU Results as XLSX
     */
    public function generateEVSUResults($filters, $userId)
    {
        \Log::info('Starting EVSU Results XLSX generation', ['filters' => $filters]);

        // Build query with filters - FILTER AT DATABASE LEVEL FIRST
        $query = Applicant::with(['assignedInstructor', 'accessCode'])
            // Filter to only applicants with all required scores at database level
            ->whereNotNull('score')
            ->whereNotNull('card_tor_gwa')
            ->whereNotNull('enrollassess_score')
            ->whereNotNull('interview_score');

        // Apply filters using existing filter logic
        $this->applyFilters($query, $filters);

        // Get applicants - no need to filter in memory anymore
        $collection = $query->get();

        // Sort by overall rating (descending by default)
        $sort = $filters['sort'] ?? 'overall_desc';
        if ($sort === 'overall_asc') {
            $collection = $collection->sortBy(function($applicant) {
                $rating = $applicant->getOverallRating();
                return $rating ? $rating['overall_rating'] : 0;
            });
        } else {
            $collection = $collection->sortByDesc(function($applicant) {
                $rating = $applicant->getOverallRating();
                return $rating ? $rating['overall_rating'] : 0;
            });
        }

        // Apply limit if specified
        $limit = (int) ($filters['limit'] ?? 120);
        if ($limit > 0) {
            $collection = $collection->take($limit);
        }
        $applicants = $collection->values();

        // Prepare export filters
        $exportFilters = [
            'campus' => $filters['campus'] ?? 'Ormoc Campus',
            'college' => $filters['college'] ?? 'College',
            'department' => $filters['department'] ?? 'Department',
            'program_code' => $filters['program_code'] ?? 'BSIT',
            'program_description' => $filters['program_description'] ?? 'Bachelor of Science in Information Technology',
            'academic_year' => $filters['academic_year'] ?? (date('Y') . '-' . (date('Y') + 1)),
            'release_date' => $filters['release_date'] ?? now()->format('Y-m-d'),
        ];

        try {
            // Generate XLSX
            $export = new EVSUResultsExport($applicants, $exportFilters);
            $tempFile = $export->export();

            // Read file content for storage
            $fileContent = file_get_contents($tempFile);
            
            // Generate filename
            $filename = 'EVSU_Entrance_Results_' . 
                       str_replace(' ', '_', $exportFilters['program_code']) . '_' . 
                       now()->format('Y-m-d_His') . '.xlsx';
            
            $path = 'reports/' . $filename;

            // Save to storage
            Storage::put($path, $fileContent);

            // Clean up temp file
            @unlink($tempFile);

            \Log::info('EVSU Results XLSX saved', ['path' => $path]);

        } catch (\Exception $e) {
            \Log::error('EVSU Results XLSX generation failed', [
                'error' => $e->getMessage(), 
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        // Save to database
        $report = $this->saveReportToDatabase(
            'evsu_results',
            'EVSU Entrance Results Report',
            $path,
            $filters,
            $userId,
            [
                'total_applicants' => $applicants->count(),
                'format' => 'xlsx',
            ]
        );

        \Log::info('EVSU Results XLSX generation complete', ['report_id' => $report->id]);
        return $report;
    }

    /**
     * Generate EVSU Results as PDF
     */
    public function generateEVSUResultsPdf($filters, $userId)
    {
        \Log::info('Starting EVSU Results PDF generation', ['filters' => $filters]);

        // Build query with filters - FILTER AT DATABASE LEVEL FIRST
        $query = Applicant::with(['assignedInstructor', 'accessCode'])
            // Filter to only applicants with all required scores at database level
            ->whereNotNull('score')
            ->whereNotNull('card_tor_gwa')
            ->whereNotNull('enrollassess_score')
            ->whereNotNull('interview_score');

        // Apply filters using existing filter logic
        $this->applyFilters($query, $filters);

        // Get applicants - no need to filter in memory anymore
        $collection = $query->get();

        // Sort by overall rating (descending by default)
        $sort = $filters['sort'] ?? 'overall_desc';
        if ($sort === 'overall_asc') {
            $collection = $collection->sortBy(function($applicant) {
                $rating = $applicant->getOverallRating();
                return $rating ? $rating['overall_rating'] : 0;
            });
        } else {
            $collection = $collection->sortByDesc(function($applicant) {
                $rating = $applicant->getOverallRating();
                return $rating ? $rating['overall_rating'] : 0;
            });
        }

        // Apply limit if specified
        $limit = (int) ($filters['limit'] ?? 120);
        if ($limit > 0) {
            $collection = $collection->take($limit);
        }
        $applicants = $collection->values();

        // Prepare export filters
        $exportFilters = [
            'campus' => $filters['campus'] ?? 'Ormoc Campus',
            'college' => $filters['college'] ?? 'College',
            'department' => $filters['department'] ?? 'Department',
            'program_code' => $filters['program_code'] ?? 'BSIT',
            'program_description' => $filters['program_description'] ?? 'Bachelor of Science in Information Technology',
            'academic_year' => $filters['academic_year'] ?? (date('Y') . '-' . (date('Y') + 1)),
            'release_date' => $filters['release_date'] ?? now()->format('Y-m-d'),
        ];

        try {
            // Generate PDF from template
            $export = new EVSUResultsExport($applicants, $exportFilters);
            $tempFile = $export->exportPdf();

            // Read file content for storage
            $fileContent = file_get_contents($tempFile);
            
            // Generate filename
            $filename = 'EVSU_Entrance_Results_' . 
                       str_replace(' ', '_', $exportFilters['program_code']) . '_' . 
                       now()->format('Y-m-d_His') . '.pdf';
            
            $path = 'reports/' . $filename;

            // Save to storage
            Storage::put($path, $fileContent);

            // Clean up temp file
            @unlink($tempFile);

            \Log::info('EVSU Results PDF saved', ['path' => $path]);

        } catch (\Exception $e) {
            \Log::error('EVSU Results PDF generation failed', [
                'error' => $e->getMessage(), 
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        // Save to database
        $report = $this->saveReportToDatabase(
            'evsu_results_pdf',
            'EVSU Entrance Results Report (PDF)',
            $path,
            $filters,
            $userId,
            [
                'total_applicants' => $applicants->count(),
                'format' => 'pdf',
            ]
        );

        \Log::info('EVSU Results PDF generation complete', ['report_id' => $report->id]);
        return $report;
    }

    /**
     * Generate Geographic Performance Report (PDF)
     */
    public function generateGeographicPerformanceReport($filters, $userId)
    {
        \Log::info('Starting Geographic Performance Report generation', ['filters' => $filters, 'userId' => $userId]);
        
        // Get applicants with basic info
        $query = Applicant::with('basicInfo')
            ->whereHas('basicInfo');

        // Apply school year filter
        $schoolYearId = session('school_year_id');
        if ($schoolYearId) {
            $query->where('school_year_id', $schoolYearId);
        }
        
        // Apply status filter
        if (isset($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }
        
        // Apply province filter
        if (isset($filters['province']) && $filters['province'] !== 'all') {
            $query->whereHas('basicInfo', function($q) use ($filters) {
                $q->where('province', $filters['province']);
            });
        }
        
        $applicants = $query->get();
        
        // Group by city/municipality with performance data (primary grouping)
        $cityData = $applicants->groupBy(function($applicant) {
            return $applicant->basicInfo->city_municipality ?? 'Unknown';
        })->map(function($group, $city) {
            $examScores = $group->whereNotNull('enrollassess_score')->pluck('enrollassess_score');
            $overallRatings = $group->filter(function($applicant) {
                return $applicant->hasAllRequiredScores();
            })->map(function($applicant) {
                return $applicant->getOverallRatingValueAttribute();
            })->filter();
            
            return [
                'city' => $city,
                'count' => $group->count(),
                'avg_exam_score' => $examScores->isNotEmpty() ? round($examScores->average(), 2) : 0,
                'avg_overall_rating' => $overallRatings->isNotEmpty() ? round($overallRatings->average(), 2) : 0,
                'exam_completed' => $group->where('status', '!=', 'pending')->count(),
                'admitted' => $group->where('status', 'admitted')->count(),
                'province' => $group->first()->basicInfo->province ?? 'Unknown',
            ];
        })->sortByDesc('count')->values();
        
        // Group by province (secondary grouping for reference)
        $provinceData = $applicants->groupBy(function($applicant) {
            return $applicant->basicInfo->province ?? 'Unknown';
        })->map(function($group, $province) {
            $examScores = $group->whereNotNull('enrollassess_score')->pluck('enrollassess_score');
            
            return [
                'province' => $province,
                'count' => $group->count(),
                'avg_exam_score' => $examScores->isNotEmpty() ? round($examScores->average(), 2) : 0,
            ];
        })->sortByDesc('count')->values();
        
        $data = [
            'cityData' => $cityData,
            'provinceData' => $provinceData,
            'totalApplicants' => $applicants->count(),
            'totalCities' => $cityData->count(),
            'topCity' => $cityData->first(),
            'filters' => $filters,
            'generatedAt' => now()->format('F d, Y - g:i A'),
            'generatedBy' => auth()->user()->name ?? 'System',
        ];
        
        \Log::info('Generating Geographic Performance PDF');
        $pdf = Pdf::loadView('reports.pdf.geographic-performance', $data);
        $pdf->setPaper('A4', 'landscape');
        
        $filename = 'geographic_performance_' . now()->format('Y-m-d_His') . '.pdf';
        $path = 'reports/' . $filename;
        
        $output = $pdf->output();
        Storage::put($path, $output);
        
        $report = $this->saveReportToDatabase(
            'geographic_performance',
            'Geographic Performance Report - ' . now()->format('M d, Y'),
            $path,
            $filters,
            $userId,
            strlen($output)
        );
        
        \Log::info('Geographic Performance Report generation complete', ['report_id' => $report->id]);
        return $report;
    }

    /**
     * Generate Strand Distribution Report (PDF)
     */
    public function generateStrandDistributionReport($filters, $userId)
    {
        \Log::info('Starting Strand Distribution Report generation', ['filters' => $filters, 'userId' => $userId]);
        
        // Get applicants with basic info
        $query = Applicant::with('basicInfo')
            ->whereHas('basicInfo');

        // Apply school year filter
        $schoolYearId = session('school_year_id');
        if ($schoolYearId) {
            $query->where('school_year_id', $schoolYearId);
        }
        
        // Apply status filter
        if (isset($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }
        
        // Apply strand filter
        if (isset($filters['strand']) && $filters['strand'] !== 'all') {
            $query->whereHas('basicInfo', function($q) use ($filters) {
                $q->where('senior_high_school_strand', $filters['strand']);
            });
        }
        
        $applicants = $query->get();
        
        // Group by strand
        $strandData = $applicants->groupBy(function($applicant) {
            return $applicant->basicInfo->senior_high_school_strand ?? 'Unknown';
        })->map(function($group, $strand) {
            $examScores = $group->whereNotNull('enrollassess_score')->pluck('enrollassess_score');
            
            return [
                'strand' => $strand,
                'count' => $group->count(),
                'percentage' => 0, // Will be calculated below
                'avg_exam_score' => $examScores->isNotEmpty() ? round($examScores->average(), 2) : 0,
                'exam_completed' => $group->where('status', '!=', 'pending')->count(),
                'admitted' => $group->where('status', 'admitted')->count(),
            ];
        });
        
        // Calculate percentages
        $total = $applicants->count();
        $strandData = $strandData->map(function($item) use ($total) {
            $item['percentage'] = $total > 0 ? round(($item['count'] / $total) * 100, 2) : 0;
            return $item;
        })->sortByDesc('count')->values();
        
        // Get "Others" specifications
        $othersData = $applicants->filter(function($applicant) {
            return $applicant->basicInfo && 
                   $applicant->basicInfo->senior_high_school_strand === 'Others' && 
                   $applicant->basicInfo->senior_high_school_strand_other;
        })->pluck('basicInfo.senior_high_school_strand_other')
          ->countBy()
          ->map(function($count, $strand) {
              return ['strand' => $strand, 'count' => $count];
          })->sortByDesc('count')->take(10)->values();
        
        $data = [
            'strandData' => $strandData,
            'othersData' => $othersData,
            'totalApplicants' => $applicants->count(),
            'topStrand' => $strandData->first(),
            'filters' => $filters,
            'generatedAt' => now()->format('F d, Y - g:i A'),
            'generatedBy' => auth()->user()->name ?? 'System',
        ];
        
        \Log::info('Generating Strand Distribution PDF');
        $pdf = Pdf::loadView('reports.pdf.strand-distribution', $data);
        $pdf->setPaper('A4', 'portrait');
        
        $filename = 'strand_distribution_' . now()->format('Y-m-d_His') . '.pdf';
        $path = 'reports/' . $filename;
        
        $output = $pdf->output();
        Storage::put($path, $output);
        
        $report = $this->saveReportToDatabase(
            'strand_distribution',
            'Strand Distribution Report - ' . now()->format('M d, Y'),
            $path,
            $filters,
            $userId,
            strlen($output)
        );
        
        \Log::info('Strand Distribution Report generation complete', ['report_id' => $report->id]);
        return $report;
    }

    /**
     * Generate Demographic Overview Report (PDF)
     */
    public function generateDemographicOverviewReport($filters, $userId)
    {
        \Log::info('Starting Demographic Overview Report generation', ['filters' => $filters, 'userId' => $userId]);
        
        // Get applicants with basic info
        $query = Applicant::with('basicInfo')
            ->whereHas('basicInfo');

        // Apply school year filter
        $schoolYearId = session('school_year_id');
        if ($schoolYearId) {
            $query->where('school_year_id', $schoolYearId);
        }
        
        // Apply status filter
        if (isset($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }
        
        // Apply age range filter
        if (isset($filters['age_range']) && $filters['age_range'] !== 'all') {
            $ageRange = explode('-', $filters['age_range']);
            $query->whereHas('basicInfo', function($q) use ($ageRange) {
                if (count($ageRange) === 2) {
                    if ($ageRange[1] === '+') {
                        $q->where('age', '>=', (int)$ageRange[0]);
                    } else {
                        $q->whereBetween('age', [(int)$ageRange[0], (int)$ageRange[1]]);
                    }
                }
            });
        }
        
        $applicants = $query->get();
        
        // Gender distribution
        $genderData = $applicants->groupBy(function($applicant) {
            return $applicant->basicInfo->sex ?? 'Unknown';
        })->map(function($group, $gender) use ($applicants) {
            return [
                'gender' => $gender,
                'count' => $group->count(),
                'percentage' => round(($group->count() / $applicants->count()) * 100, 2),
            ];
        })->values();
        
        // Age distribution
        $ageData = $applicants->groupBy(function($applicant) {
            $age = $applicant->basicInfo->age ?? 0;
            if ($age >= 16 && $age <= 20) return '16-20';
            if ($age >= 21 && $age <= 25) return '21-25';
            if ($age >= 26 && $age <= 30) return '26-30';
            if ($age >= 31) return '31+';
            return 'Unknown';
        })->map(function($group, $range) use ($applicants) {
            return [
                'range' => $range,
                'count' => $group->count(),
                'percentage' => round(($group->count() / $applicants->count()) * 100, 2),
            ];
        })->sortBy('range')->values();
        
        // Civil status distribution
        $civilStatusData = $applicants->groupBy(function($applicant) {
            return $applicant->basicInfo->civil_status ?? 'Not Specified';
        })->map(function($group, $status) use ($applicants) {
            return [
                'status' => $status,
                'count' => $group->count(),
                'percentage' => round(($group->count() / $applicants->count()) * 100, 2),
            ];
        })->values();
        
        // Applicant type distribution
        $applicantTypeData = $applicants->groupBy(function($applicant) {
            return $applicant->basicInfo->applicant_type ?? 'Unknown';
        })->map(function($group, $type) use ($applicants) {
            return [
                'type' => $type,
                'count' => $group->count(),
                'percentage' => round(($group->count() / $applicants->count()) * 100, 2),
            ];
        })->values();
        
        // PWD statistics
        $pwdData = $applicants->groupBy(function($applicant) {
            return $applicant->basicInfo->is_pwd ?? 'Not Specified';
        })->map(function($group, $status) use ($applicants) {
            return [
                'status' => $status,
                'count' => $group->count(),
                'percentage' => round(($group->count() / $applicants->count()) * 100, 2),
            ];
        })->values();
        
        $data = [
            'genderData' => $genderData,
            'ageData' => $ageData,
            'civilStatusData' => $civilStatusData,
            'applicantTypeData' => $applicantTypeData,
            'pwdData' => $pwdData,
            'totalApplicants' => $applicants->count(),
            'averageAge' => $applicants->pluck('basicInfo.age')->filter()->average(),
            'filters' => $filters,
            'generatedAt' => now()->format('F d, Y - g:i A'),
            'generatedBy' => auth()->user()->name ?? 'System',
        ];
        
        \Log::info('Generating Demographic Overview PDF');
        $pdf = Pdf::loadView('reports.pdf.demographic-overview', $data);
        $pdf->setPaper('A4', 'portrait');
        
        $filename = 'demographic_overview_' . now()->format('Y-m-d_His') . '.pdf';
        $path = 'reports/' . $filename;
        
        $output = $pdf->output();
        Storage::put($path, $output);
        
        $report = $this->saveReportToDatabase(
            'demographic_overview',
            'Demographic Overview Report - ' . now()->format('M d, Y'),
            $path,
            $filters,
            $userId,
            strlen($output)
        );
        
        \Log::info('Demographic Overview Report generation complete', ['report_id' => $report->id]);
        return $report;
    }

    /**
     * Get signature settings from database
     */
    protected function getSignatureSettings()
    {
        return [
            'prepared_by_name' => Settings::getSetting('report_signature_prepared_by_name', 'JOSEPH JAYMEL S. MORPOS'),
            'prepared_by_title' => Settings::getSetting('report_signature_prepared_by_title', 'Head, Computer Studies Department'),
            'noted_name' => Settings::getSetting('report_signature_noted_name', 'DR. JEFFRY V. OCAY'),
            'noted_title' => Settings::getSetting('report_signature_noted_title', 'Director, Ormoc Campus'),
            'recommending_name' => Settings::getSetting('report_signature_recommending_name', 'LYDIA M. MORANTE, D.A.'),
            'recommending_title' => Settings::getSetting('report_signature_recommending_title', 'Vice President for Academic Affairs'),
            'approved_name' => Settings::getSetting('report_signature_approved_name', 'DENNIS C. DE PAZ, Ph.D.'),
            'approved_title' => Settings::getSetting('report_signature_approved_title', 'University President'),
        ];
    }
}

