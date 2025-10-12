<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\AccessCode;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Service class for applicant management operations
 * 
 * Handles business logic for applicant CRUD operations,
 * access code generation, exam set assignment, and bulk operations.
 */
class ApplicantService
{
    protected ?CacheService $cacheService = null;
    protected ?QueryOptimizationService $queryService = null;

    public function __construct(CacheService $cacheService, QueryOptimizationService $queryService)
    {
        $this->cacheService = $cacheService;
        $this->queryService = $queryService;
    }
    /**
     * Get paginated applicants with filters and search
     *
     * @param Request $request
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedApplicants(Request $request, int $perPage = 20): LengthAwarePaginator
    {
        $filters = [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'page' => $request->get('page', 1),
            'per_page' => $perPage
        ];

        return $this->queryService->getOptimizedApplicants($filters, $perPage);
    }

    /**
     * Get applicant statistics
     *
     * @return array
     */
    public function getApplicantStatistics(): array
    {
        return $this->queryService->getDashboardStatistics();
    }

    /**
     * Create a new applicant
     *
     * @param array $data
     * @return Applicant
     */
    public function createApplicant(array $data): Applicant
    {
        return DB::transaction(function () use ($data) {
            // Generate unique application number
            $data['application_no'] = $this->generateApplicationNumber();
            
            // Set default status
            $data['status'] = $data['status'] ?? 'pending';
            
            $applicant = Applicant::create($data);
            
            // Invalidate related cache
            $this->cacheService->invalidateRelated('applicant');
            
            return $applicant;
        });
    }

    /**
     * Update an existing applicant
     *
     * @param Applicant $applicant
     * @param array $data
     * @return Applicant
     */
    public function updateApplicant(Applicant $applicant, array $data): Applicant
    {
        return DB::transaction(function () use ($applicant, $data) {
            $applicant->update($data);
            
            // Invalidate related cache
            $this->cacheService->invalidateRelated('applicant', $applicant->applicant_id);
            
            return $applicant->refresh();
        });
    }

    /**
     * Delete an applicant and related data
     *
     * @param Applicant $applicant
     * @return bool
     */
    public function deleteApplicant(Applicant $applicant): bool
    {
        return DB::transaction(function () use ($applicant) {
            $applicantId = $applicant->applicant_id;
            
            // Delete related access codes
            $applicant->accessCode()?->delete();
            
            // Delete exam results
            $applicant->results()?->delete();
            
            // Delete interviews
            $applicant->interviews()?->delete();
            
            $deleted = $applicant->delete();
            
            if ($deleted) {
                // Invalidate related cache
                $this->cacheService->invalidateRelated('applicant', $applicantId);
            }
            
            return $deleted;
        });
    }

    /**
     * Generate access codes for applicants
     *
     * @param array $applicantIds
     * @param bool $regenerate
     * @return array
     */
    public function generateAccessCodes(array $applicantIds, bool $regenerate = false): array
    {
        $results = [
            'generated' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        DB::transaction(function () use ($applicantIds, $regenerate, &$results) {
            foreach ($applicantIds as $applicantId) {
                try {
                    $applicant = Applicant::findOrFail($applicantId);
                    
                    // Skip if already has access code and not regenerating
                    if ($applicant->accessCode && !$regenerate) {
                        $results['skipped']++;
                        continue;
                    }

                    // Delete existing access code if regenerating
                    if ($regenerate && $applicant->accessCode) {
                        $applicant->accessCode->delete();
                    }

                    // Generate new access code
                    $accessCode = AccessCode::create([
                        'applicant_id' => $applicant->id,
                        'code' => $this->generateUniqueAccessCode(),
                        'is_used' => false,
                        'expires_at' => now()->addDays(30),
                    ]);

                    $results['generated']++;

                } catch (\Exception $e) {
                    $results['errors'][] = "Error for applicant ID {$applicantId}: " . $e->getMessage();
                    Log::error('Access code generation error', [
                        'applicant_id' => $applicantId,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });

        return $results;
    }

    /**
     * Assign instructor to applicants
     *
     * @param array $applicantIds
     * @param int $instructorId
     * @return array
     */
    public function assignInstructor(array $applicantIds, int $instructorId): array
    {
        $results = [
            'assigned' => 0,
            'errors' => []
        ];

        DB::transaction(function () use ($applicantIds, $instructorId, &$results) {
            foreach ($applicantIds as $applicantId) {
                try {
                    $applicant = Applicant::findOrFail($applicantId);
                    $applicant->update(['assigned_instructor_id' => $instructorId]);
                    $results['assigned']++;

                } catch (\Exception $e) {
                    $results['errors'][] = "Error for applicant ID {$applicantId}: " . $e->getMessage();
                    Log::error('Instructor assignment error', [
                        'applicant_id' => $applicantId,
                        'instructor_id' => $instructorId,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });

        return $results;
    }

    /**
     * Get applicants eligible for interview
     *
     * @return Collection
     */
    public function getEligibleForInterview(): Collection
    {
        return Applicant::where('status', 'exam-completed')
            ->whereDoesntHave('interviews', function ($query) {
                $query->whereIn('status', ['scheduled', 'completed']);
            })
            ->with(['assignedInstructor'])
            ->get();
    }

    /**
     * Generate unique application number
     *
     * @return string
     */
    private function generateApplicationNumber(): string
    {
        $year = date('Y');
        $prefix = "APP{$year}";
        
        do {
            $number = $prefix . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Applicant::where('application_no', $number)->exists());

        return $number;
    }

    /**
     * Generate unique access code
     *
     * @return string
     */
    private function generateUniqueAccessCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (AccessCode::where('code', $code)->exists());

        return $code;
    }

    /**
     * Import applicants from CSV data
     *
     * @param array $csvData
     * @return array
     */
    public function importFromCsv(array $csvData): array
    {
        $results = [
            'imported' => 0,
            'errors' => [],
            'skipped' => 0
        ];

        DB::transaction(function () use ($csvData, &$results) {
            foreach ($csvData as $index => $row) {
                try {
                    $rowNumber = $index + 2; // Account for header row

                    // Validate required fields
                    if (empty($row['first_name']) || empty($row['last_name']) || empty($row['email_address'])) {
                        $results['errors'][] = "Row {$rowNumber}: Missing required fields (first_name, last_name, email_address)";
                        continue;
                    }

                    // Check for duplicate email
                    if (Applicant::where('email_address', $row['email_address'])->exists()) {
                        $results['skipped']++;
                        continue;
                    }

                    // Create applicant
                    $this->createApplicant([
                        'first_name' => $row['first_name'],
                        'middle_name' => $row['middle_name'] ?? null,
                        'last_name' => $row['last_name'],
                        'preferred_course' => $row['preferred_course'] ?? null,
                        'email_address' => $row['email_address'],
                        'phone_number' => $row['phone_number'] ?? null,
                        'status' => 'pending'
                    ]);

                    $results['imported']++;

                } catch (\Exception $e) {
                    $results['errors'][] = "Row {$rowNumber}: " . $e->getMessage();
                    Log::error('CSV import error', [
                        'row' => $index,
                        'data' => $row,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });

        return $results;
    }
}
