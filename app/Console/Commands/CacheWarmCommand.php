<?php

namespace App\Console\Commands;

use App\Services\CacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Warm up frequently accessed cache
 * 
 * Pre-loads commonly accessed data into cache to improve
 * application performance and reduce database load.
 */
class CacheWarmCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:warm {--force : Force warm up even if cache is not empty}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm up frequently accessed cache to improve performance';

    /**
     * Cache service instance
     *
     * @var CacheService
     */
    protected CacheService $cacheService;

    /**
     * Create a new command instance.
     */
    public function __construct(CacheService $cacheService)
    {
        parent::__construct();
        $this->cacheService = $cacheService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting cache warm up...');
        
        $startTime = microtime(true);
        $results = [];
        
        try {
            // Warm up dashboard statistics
            $this->info('Warming up dashboard statistics...');
            $results['stats'] = $this->warmUpDashboardStats();
            
            // Warm up active exam sets
            $this->info('Warming up active exam sets...');
            $results['exams'] = $this->warmUpActiveExams();
            
            // Warm up user statistics
            $this->info('Warming up user statistics...');
            $results['users'] = $this->warmUpUserStats();
            
            // Warm up recent applicants
            $this->info('Warming up recent applicants...');
            $results['applicants'] = $this->warmUpRecentApplicants();
            
            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            
            $this->displayResults($results, $duration);
            
            Log::info('Cache warm up completed successfully', [
                'duration' => $duration,
                'results' => $results
            ]);
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Cache warm up failed: ' . $e->getMessage());
            Log::error('Cache warm up failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
    
    /**
     * Warm up dashboard statistics
     *
     * @return bool
     */
    protected function warmUpDashboardStats(): bool
    {
        try {
            $this->cacheService->cacheStats(function() {
                return [
                    'total_applicants' => \App\Models\Applicant::count(),
                    'exam_completed' => \App\Models\Applicant::where('status', '!=', 'pending')->count(),
                    'interviews_scheduled' => \App\Models\Interview::where('status', 'scheduled')->count(),
                    'pending_reviews' => \App\Models\Applicant::where('status', 'exam-completed')->count(),
                ];
            });
            
            return true;
        } catch (\Exception $e) {
            $this->warn("Failed to warm up dashboard stats: {$e->getMessage()}");
            return false;
        }
    }
    
    /**
     * Warm up active exam sets
     *
     * @return bool
     */
    protected function warmUpActiveExams(): bool
    {
        try {
            $examsKey = $this->cacheService->generateKey('exam', ['active']);
            $this->cacheService->remember($examsKey, function() {
                return \App\Models\Exam::where('is_active', true)->get();
            }, CacheService::LONG_TTL);
            
            return true;
        } catch (\Exception $e) {
            $this->warn("Failed to warm up active exams: {$e->getMessage()}");
            return false;
        }
    }
    
    /**
     * Warm up user statistics
     *
     * @return bool
     */
    protected function warmUpUserStats(): bool
    {
        try {
            $userStatsKey = $this->cacheService->generateKey('user', ['stats']);
            $this->cacheService->remember($userStatsKey, function() {
                return [
                    'total_users' => \App\Models\User::count(),
                    'department_heads' => \App\Models\User::where('role', 'department-head')->count(),
                    'instructors' => \App\Models\User::where('role', 'instructor')->count(),
                ];
            }, CacheService::LONG_TTL);
            
            return true;
        } catch (\Exception $e) {
            $this->warn("Failed to warm up user stats: {$e->getMessage()}");
            return false;
        }
    }
    
    /**
     * Warm up recent applicants
     *
     * @return bool
     */
    protected function warmUpRecentApplicants(): bool
    {
        try {
            $recentKey = $this->cacheService->generateKey('applicants', ['recent', 5]);
            $this->cacheService->remember($recentKey, function() {
                return \App\Models\Applicant::with(['assignedInstructor', 'accessCode'])
                    ->latest()
                    ->limit(5)
                    ->get();
            }, CacheService::SHORT_TTL);
            
            return true;
        } catch (\Exception $e) {
            $this->warn("Failed to warm up recent applicants: {$e->getMessage()}");
            return false;
        }
    }
    
    /**
     * Display warm up results
     *
     * @param array $results
     * @param float $duration
     * @return void
     */
    protected function displayResults(array $results, float $duration): void
    {
        $this->newLine();
        $this->info('Cache warm up completed!');
        $this->newLine();
        
        $this->table(
            ['Component', 'Status'],
            [
                ['Dashboard Statistics', $results['stats'] ? '✅ Success' : '❌ Failed'],
                ['Active Exam Sets', $results['exams'] ? '✅ Success' : '❌ Failed'],
                ['User Statistics', $results['users'] ? '✅ Success' : '❌ Failed'],
                ['Recent Applicants', $results['applicants'] ? '✅ Success' : '❌ Failed'],
            ]
        );
        
        $this->newLine();
        $this->info("Total time: {$duration} seconds");
        
        // Display cache statistics
        $cacheStats = $this->cacheService->getStatistics();
        $this->info("Cache driver: {$cacheStats['driver']}");
        
        if (isset($cacheStats['total_keys'])) {
            $this->info("Total cache keys: {$cacheStats['total_keys']}");
        }
    }
}