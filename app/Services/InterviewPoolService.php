<?php

namespace App\Services;

use App\Models\Interview;
use App\Models\Applicant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InterviewPoolService
{
    /**
     * Add an applicant to the interview pool after exam completion
     */
    public function addApplicantToPool($applicantId, $priority = 'medium')
    {
        try {
            DB::beginTransaction();

            // Check if applicant exists and has completed exam
            $applicant = Applicant::find($applicantId);
            if (!$applicant || $applicant->status !== 'exam-completed') {
                throw new \Exception('Applicant not found or has not completed exam');
            }

            // Check if interview already exists for this applicant
            $existingInterview = Interview::where('applicant_id', $applicantId)->first();
            if ($existingInterview) {
                // Update existing interview to be available in pool
                $existingInterview->update([
                    'status' => 'available',
                    'priority_level' => $priority,
                    'dh_override' => false,
                    'claimed_by' => null,
                    'claimed_at' => null,
                    'interviewer_id' => null
                ]);
                
                $interview = $existingInterview;
            } else {
                // Create new interview in pool
                $interview = Interview::addToPool($applicantId, $priority);
            }

            // Update applicant status
            $applicant->update(['status' => 'interview-available']);

            DB::commit();

            Log::info("Applicant {$applicantId} added to interview pool with priority {$priority}");

            return $interview;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to add applicant {$applicantId} to interview pool: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get available interviews in the pool
     */
    public function getAvailableInterviews($filters = [])
    {
        $query = Interview::with(['applicant', 'claimedBy'])
            ->availableInPool();

        // Apply filters
        if (isset($filters['priority']) && $filters['priority']) {
            $query->where('priority_level', $filters['priority']);
        }

        if (isset($filters['search']) && $filters['search']) {
            $search = $filters['search'];
            $query->whereHas('applicant', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Order by priority (high first) and created date
        $query->orderByRaw("FIELD(priority_level, 'high', 'medium', 'low')")
              ->orderBy('created_at', 'asc');

        return $query->get();
    }

    /**
     * Claim an interview for a user
     */
    public function claimInterview($interviewId, $userId)
    {
        try {
            DB::beginTransaction();

            $interview = Interview::find($interviewId);
            if (!$interview) {
                throw new \Exception('Interview not found');
            }

            if (!$interview->isAvailableForClaiming()) {
                throw new \Exception('Interview is not available for claiming');
            }

            // Check if user exists and has appropriate role
            $user = User::find($userId);
            if (!$user || !in_array($user->role, ['instructor', 'department-head'])) {
                throw new \Exception('User not authorized to conduct interviews');
            }

            // Claim the interview
            $interview->claimForUser($userId);

            // Update applicant status
            $interview->applicant->update(['status' => 'interview-claimed']);

            DB::commit();

            Log::info("Interview {$interviewId} claimed by user {$userId}");

            return $interview->fresh(['applicant', 'claimedBy']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to claim interview {$interviewId} for user {$userId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Release a claimed interview back to the pool
     */
    public function releaseInterview($interviewId, $userId)
    {
        try {
            DB::beginTransaction();

            $interview = Interview::find($interviewId);
            if (!$interview) {
                throw new \Exception('Interview not found');
            }

            // Check if user can release this interview
            if ($interview->claimed_by != $userId) {
                $user = User::find($userId);
                if (!$user || $user->role !== 'department-head') {
                    throw new \Exception('You can only release interviews you have claimed');
                }
            }

            // Release the interview
            $interview->releaseToPool();

            // Update applicant status
            $interview->applicant->update(['status' => 'interview-available']);

            DB::commit();

            Log::info("Interview {$interviewId} released back to pool by user {$userId}");

            return $interview->fresh(['applicant']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to release interview {$interviewId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Department Head assigns interview to specific instructor
     */
    public function assignInterviewToInstructor($interviewId, $instructorId, $notes = null)
    {
        try {
            DB::beginTransaction();

            $interview = Interview::find($interviewId);
            if (!$interview) {
                throw new \Exception('Interview not found');
            }

            // Check if instructor exists and has instructor role
            $instructor = User::find($instructorId);
            if (!$instructor || !in_array($instructor->role, ['instructor', 'department-head'])) {
                throw new \Exception('Instructor not found or invalid role');
            }

            // Assign the interview
            $interview->assignToInstructor($instructorId, $notes);

            // Update applicant status
            $interview->applicant->update(['status' => 'interview-assigned']);

            DB::commit();

            Log::info("Interview {$interviewId} assigned to instructor {$instructorId} by department head");

            return $interview->fresh(['applicant', 'interviewer']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to assign interview {$interviewId} to instructor {$instructorId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get claimed interviews for a specific user
     */
    public function getUserClaimedInterviews($userId)
    {
        return Interview::with(['applicant'])
            ->claimedBy($userId)
            ->whereIn('status', ['claimed', 'assigned'])
            ->orderBy('claimed_at', 'desc')
            ->get();
    }

    /**
     * Get pool statistics
     */
    public function getPoolStatistics()
    {
        return [
            'total_available' => Interview::availableInPool()->count(),
            'total_claimed' => Interview::claimed()->count(),
            'high_priority' => Interview::availableInPool()->highPriority()->count(),
            'medium_priority' => Interview::availableInPool()->byPriority('medium')->count(),
            'low_priority' => Interview::availableInPool()->byPriority('low')->count(),
            'claimed_by_dh' => Interview::claimed()
                ->whereHas('claimedBy', function($q) {
                    $q->where('role', 'department-head');
                })->count(),
            'claimed_by_instructors' => Interview::claimed()
                ->whereHas('claimedBy', function($q) {
                    $q->where('role', 'instructor');
                })->count(),
        ];
    }

    /**
     * Set interview priority (Department Head only)
     */
    public function setInterviewPriority($interviewId, $priority)
    {
        $interview = Interview::find($interviewId);
        if (!$interview) {
            throw new \Exception('Interview not found');
        }

        if (!in_array($priority, ['high', 'medium', 'low'])) {
            throw new \Exception('Invalid priority level');
        }

        $interview->setPriority($priority);

        Log::info("Interview {$interviewId} priority set to {$priority}");

        return $interview;
    }

    /**
     * Process exam completion and automatically add to pool
     */
    public function processExamCompletion($applicantId, $examScore = null)
    {
        try {
            // Determine priority based on exam score
            $priority = 'medium'; // default
            
            if ($examScore !== null) {
                if ($examScore >= 85) {
                    $priority = 'high';
                } elseif ($examScore >= 75) {
                    $priority = 'medium';
                } else {
                    $priority = 'low';
                }
            }

            return $this->addApplicantToPool($applicantId, $priority);

        } catch (\Exception $e) {
            Log::error("Failed to process exam completion for applicant {$applicantId}: " . $e->getMessage());
            throw $e;
        }
    }


    /**
     * Clean up stale claimed interviews (optional timeout handling)
     */
    public function cleanupStaleClaimedInterviews($timeoutHours = 2)
    {
        $staleInterviews = Interview::claimed()
            ->where('claimed_at', '<', now()->subHours($timeoutHours))
            ->get();

        $releasedCount = 0;
        foreach ($staleInterviews as $interview) {
            try {
                $interview->releaseToPool();
                $interview->applicant->update(['status' => 'interview-available']);
                $releasedCount++;
                
                Log::info("Released stale interview {$interview->interview_id} claimed by user {$interview->claimed_by}");
            } catch (\Exception $e) {
                Log::error("Failed to release stale interview {$interview->interview_id}: " . $e->getMessage());
            }
        }

        return $releasedCount;
    }
}
