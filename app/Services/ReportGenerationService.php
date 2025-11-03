<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\GeneratedReport;
use App\Models\Interview;
use App\Models\Result;
use App\Models\Question;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Exports\EVSUQualifiersExport;

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

        $applicants = $query->get()->map(function($applicant) {
            // Calculate final score: 60% exam + 40% interview
            $examScore = $applicant->enrollassess_score ?? 0;
            $interviewScore = $applicant->interview_score ?? 0;
            
            $finalScore = ($examScore * 0.6) + ($interviewScore * 0.4);
            
            $applicant->final_score = round($finalScore, 2);
            $applicant->exam_score_weighted = round($examScore * 0.6, 2);
            $applicant->interview_score_weighted = round($interviewScore * 0.4, 2);
            
            // Determine recommendation
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

        return GeneratedReport::create([
            'report_type' => $type,
            'title' => $title,
            'file_path' => $filePath,
            'filters_applied' => $filters,
            'generated_by' => $userId,
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
        
        // Get all applicants with complete scores
        $query = Applicant::query()
            ->whereNotNull('score')
            ->whereNotNull('card_tor_gwa')
            ->whereNotNull('enrollassess_score')
            ->whereNotNull('interview_score');

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
}

