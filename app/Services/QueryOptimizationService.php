<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\Exam;
use App\Models\Interview;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Query optimization service for complex database operations
 * 
 * Provides optimized queries with proper eager loading,
 * subquery optimization, and performance-focused methods.
 */
class QueryOptimizationService
{
    protected CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Get optimized applicants with minimal N+1 queries
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getOptimizedApplicants(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->cacheService->cachePaginatedQuery(
            'applicants:paginated',
            $filters,
            function() use ($filters, $perPage) {
                $query = Applicant::query()
                    ->select([
                        'applicants.*',
                        'users.first_name as instructor_first_name',
                        'users.last_name as instructor_last_name',
                        'access_codes.code as access_code',
                        'access_codes.is_used as code_used'
                    ])
                    ->leftJoin('users', 'applicants.assigned_instructor_id', '=', 'users.user_id')
                    ->leftJoin('access_codes', 'applicants.applicant_id', '=', 'access_codes.applicant_id');

                // Apply filters efficiently
                $this->applyApplicantFilters($query, $filters);

                return $query->orderBy('applicants.created_at', 'desc')
                           ->paginate($perPage);
            },
            CacheService::SHORT_TTL
        );
    }

    /**
     * Get applicant with all related data in single optimized query
     *
     * @param int $applicantId
     * @return Applicant|null
     */
    public function getApplicantWithRelations(int $applicantId): ?Applicant
    {
        return $this->cacheService->cacheApplicant($applicantId, function() use ($applicantId) {
            return Applicant::with([
                'accessCode:applicant_id,code,is_used,expires_at',
                'accessCode.exam:exam_id,title,duration_minutes',
                'assignedInstructor:user_id,first_name,last_name,email',
                'latestInterview:interview_id,applicant_id,interviewer_id,status,schedule_date,overall_score,recommendation',
                'latestInterview.interviewer:user_id,first_name,last_name',
                'results:result_id,applicant_id,question_id,is_correct,points_earned',
                'results.question:question_id,question_text,points'
            ])->find($applicantId);
        });
    }

    /**
     * Get dashboard statistics with optimized queries
     *
     * @return array
     */
    public function getDashboardStatistics(): array
    {
        return $this->cacheService->cacheStats(function() {
            // Use single query with conditional aggregation
            $stats = DB::select("
                SELECT 
                    COUNT(*) as total_applicants,
                    COUNT(CASE WHEN status != 'pending' THEN 1 END) as exam_completed,
                    COUNT(CASE WHEN status = 'exam-completed' THEN 1 END) as pending_reviews,
                    COUNT(CASE WHEN assigned_instructor_id IS NOT NULL THEN 1 END) as with_instructors,
                    AVG(CASE WHEN score IS NOT NULL THEN score END) as average_score
                FROM applicants
            ")[0];

            // Get interview statistics in separate optimized query
            $interviewStats = DB::select("
                SELECT 
                    COUNT(CASE WHEN status = 'scheduled' THEN 1 END) as interviews_scheduled,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as interviews_completed
                FROM interviews
            ")[0];

            return [
                'total_applicants' => $stats->total_applicants,
                'exam_completed' => $stats->exam_completed,
                'pending_reviews' => $stats->pending_reviews,
                'with_instructors' => $stats->with_instructors,
                'average_score' => round($stats->average_score ?? 0, 2),
                'interviews_scheduled' => $interviewStats->interviews_scheduled,
                'interviews_completed' => $interviewStats->interviews_completed,
            ];
        });
    }

    /**
     * Get recent applicants with optimized eager loading
     *
     * @param int $limit
     * @return Collection
     */
    public function getRecentApplicants(int $limit = 5): Collection
    {
        $cacheKey = $this->cacheService->generateKey('applicants:recent', [$limit]);
        
        return $this->cacheService->remember($cacheKey, function() use ($limit) {
            return Applicant::select([
                'applicant_id',
                'first_name',
                'middle_name',
                'last_name',
                'email_address',
                'status',
                'assigned_instructor_id',
                'score',
                'created_at'
            ])
            ->with([
                'assignedInstructor:user_id,first_name,last_name',
                'accessCode:applicant_id,code,is_used'
            ])
            ->latest()
            ->limit($limit)
            ->get();
        }, CacheService::SHORT_TTL);
    }

    /**
     * Get exam performance analytics with optimized queries
     *
     * @param int|null $examSetId
     * @return array
     */
    public function getExamPerformanceAnalytics(?int $examSetId = null): array
    {
        $cacheKey = $this->cacheService->generateKey('analytics:exam', [$examSetId]);
        
        return $this->cacheService->remember($cacheKey, function() use ($examSetId) {
            $query = "
                SELECT 
                    es.set_name,
                    COUNT(a.applicant_id) as total_applicants,
                    AVG(a.score) as average_score,
                    MAX(a.score) as highest_score,
                    MIN(a.score) as lowest_score,
                    COUNT(CASE WHEN a.score >= 70 THEN 1 END) as passed_count,
                    COUNT(CASE WHEN a.status = 'exam-completed' THEN 1 END) as completed_count
                FROM exam_sets es
                LEFT JOIN applicants a ON es.exam_set_id = a.exam_set_id
                WHERE es.is_active = 1
            ";
            
            if ($examSetId) {
                $query .= " AND es.exam_set_id = ?";
                $results = DB::select($query, [$examSetId]);
            } else {
                $query .= " GROUP BY es.exam_set_id, es.set_name";
                $results = DB::select($query);
            }
            
            return array_map(function($row) {
                return [
                    'set_name' => $row->set_name,
                    'total_applicants' => $row->total_applicants,
                    'average_score' => round($row->average_score ?? 0, 2),
                    'highest_score' => $row->highest_score ?? 0,
                    'lowest_score' => $row->lowest_score ?? 0,
                    'passed_count' => $row->passed_count,
                    'completed_count' => $row->completed_count,
                    'pass_rate' => $row->total_applicants > 0 
                        ? round(($row->passed_count / $row->total_applicants) * 100, 2) 
                        : 0
                ];
            }, $results);
        }, CacheService::MEDIUM_TTL);
    }

    /**
     * Get interview statistics with optimized aggregation
     *
     * @return array
     */
    public function getInterviewStatistics(): array
    {
        $cacheKey = $this->cacheService->generateKey('analytics:interviews');
        
        return $this->cacheService->remember($cacheKey, function() {
            $stats = DB::select("
                SELECT 
                    COUNT(*) as total_interviews,
                    COUNT(CASE WHEN status = 'scheduled' THEN 1 END) as scheduled,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled,
                    AVG(CASE WHEN overall_score IS NOT NULL THEN overall_score END) as average_score,
                    COUNT(CASE WHEN recommendation = 'recommended' THEN 1 END) as recommended,
                    COUNT(CASE WHEN recommendation = 'not-recommended' THEN 1 END) as not_recommended
                FROM interviews
            ")[0];
            
            return [
                'total_interviews' => $stats->total_interviews,
                'scheduled' => $stats->scheduled,
                'completed' => $stats->completed,
                'cancelled' => $stats->cancelled,
                'average_score' => round($stats->average_score ?? 0, 2),
                'recommended' => $stats->recommended,
                'not_recommended' => $stats->not_recommended,
                'recommendation_rate' => $stats->completed > 0 
                    ? round(($stats->recommended / $stats->completed) * 100, 2) 
                    : 0
            ];
        }, CacheService::MEDIUM_TTL);
    }

    /**
     * Get questions with options in optimized way
     *
     * @param int $examId
     * @return Collection
     */
    public function getQuestionsWithOptions(int $examId): Collection
    {
        $cacheKey = $this->cacheService->generateKey('questions:with_options', [$examId]);
        
        return $this->cacheService->remember($cacheKey, function() use ($examId) {
            return Question::with([
                'options:option_id,question_id,option_text,is_correct,order_number'
            ])
            ->where('exam_id', $examId)
            ->where('is_active', true)
            ->orderBy('order_number')
            ->get([
                'question_id',
                'exam_id',
                'question_text',
                'question_type',
                'correct_answer',
                'points',
                'order_number'
            ]);
        }, CacheService::LONG_TTL);
    }

    /**
     * Apply filters to applicant query efficiently
     *
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    protected function applyApplicantFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('applicants.first_name', 'like', "%{$search}%")
                  ->orWhere('applicants.middle_name', 'like', "%{$search}%")
                  ->orWhere('applicants.last_name', 'like', "%{$search}%")
                  ->orWhere('applicants.email_address', 'like', "%{$search}%")
                  ->orWhere('applicants.application_no', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('applicants.status', $filters['status']);
        }

        if (!empty($filters['assigned_instructor_id'])) {
            $query->where('applicants.assigned_instructor_id', $filters['assigned_instructor_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('applicants.created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('applicants.created_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    /**
     * Bulk update applicant statuses efficiently
     *
     * @param array $applicantIds
     * @param string $status
     * @return int
     */
    public function bulkUpdateApplicantStatus(array $applicantIds, string $status): int
    {
        $updated = DB::table('applicants')
            ->whereIn('applicant_id', $applicantIds)
            ->update([
                'status' => $status,
                'updated_at' => now()
            ]);
            
        // Invalidate related cache
        $this->cacheService->invalidateRelated('applicant');
        
        return $updated;
    }

    /**
     * Get applicants eligible for interview with optimized query
     *
     * @return Collection
     */
    public function getEligibleForInterview(): Collection
    {
        $cacheKey = $this->cacheService->generateKey('applicants:eligible_interview');
        
        return $this->cacheService->remember($cacheKey, function() {
            return Applicant::select([
                'applicants.applicant_id',
                'applicants.first_name',
                'applicants.middle_name',
                'applicants.last_name',
                'applicants.email_address',
                'applicants.score',
                'applicants.assigned_instructor_id'
            ])
            ->leftJoin('interviews', function($join) {
                $join->on('applicants.applicant_id', '=', 'interviews.applicant_id')
                     ->whereIn('interviews.status', ['scheduled', 'completed']);
            })
            ->where('applicants.status', 'exam-completed')
            ->whereNull('interviews.interview_id') // No existing interview
            ->orderBy('applicants.score', 'desc')
            ->get();
        }, CacheService::SHORT_TTL);
    }

    /**
     * Get system performance metrics
     *
     * @return array
     */
    public function getPerformanceMetrics(): array
    {
        return [
            'database' => $this->getDatabaseMetrics(),
            'cache' => $this->cacheService->getStatistics(),
            'query_counts' => $this->getQueryCounts()
        ];
    }

    /**
     * Get database performance metrics
     *
     * @return array
     */
    protected function getDatabaseMetrics(): array
    {
        try {
            // Get table sizes and row counts
            $tables = DB::select("
                SELECT 
                    table_name,
                    table_rows,
                    data_length,
                    index_length
                FROM information_schema.tables 
                WHERE table_schema = DATABASE()
            ");
            
            $metrics = [];
            foreach ($tables as $table) {
                $metrics[$table->table_name] = [
                    'rows' => $table->table_rows,
                    'data_size' => $this->formatBytes($table->data_length),
                    'index_size' => $this->formatBytes($table->index_length)
                ];
            }
            
            return $metrics;
        } catch (\Exception $e) {
            return ['error' => 'Unable to fetch database metrics'];
        }
    }

    /**
     * Get query execution counts
     *
     * @return array
     */
    protected function getQueryCounts(): array
    {
        try {
            return [
                'total_queries' => DB::getQueryLog() ? count(DB::getQueryLog()) : 0,
                'total_connections' => 1, // Current connection
            ];
        } catch (\Exception $e) {
            return ['error' => 'Unable to fetch query metrics'];
        }
    }

    /**
     * Format bytes to human readable format
     *
     * @param int $bytes
     * @return string
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        
        return sprintf('%.2f %s', $bytes / pow(1024, $factor), $units[$factor]);
    }
}
