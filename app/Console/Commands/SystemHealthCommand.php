<?php

namespace App\Console\Commands;

use App\Services\CacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

/**
 * Check system health and connectivity
 * 
 * Verifies database, cache, queue, and other system components
 * are functioning properly for production monitoring.
 */
class SystemHealthCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:health {--detailed : Show detailed information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check system health (database, cache, queue, etc.)';

    /**
     * Cache service instance
     *
     * @var CacheService
     */
    protected CacheService $cacheService;

    /**
     * Health check results
     *
     * @var array
     */
    protected array $results = [];

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
        $this->info('🔍 Checking system health...');
        $this->newLine();
        
        $startTime = microtime(true);
        
        // Run health checks
        $this->checkDatabase();
        $this->checkCache();
        $this->checkQueue();
        $this->checkDiskSpace();
        $this->checkMemoryUsage();
        
        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);
        
        $this->displayResults($duration);
        
        // Log results
        Log::info('System health check completed', [
            'duration' => $duration,
            'results' => $this->results
        ]);
        
        // Return appropriate exit code
        $hasFailures = collect($this->results)->contains('status', '❌ Failed');
        return $hasFailures ? Command::FAILURE : Command::SUCCESS;
    }
    
    /**
     * Check database connectivity and performance
     *
     * @return void
     */
    protected function checkDatabase(): void
    {
        $this->info('Checking database...');
        
        try {
            $startTime = microtime(true);
            
            // Test basic connectivity
            DB::connection()->getPdo();
            
            // Test query performance
            $queryStart = microtime(true);
            $userCount = DB::table('users')->count();
            $queryTime = round((microtime(true) - $queryStart) * 1000, 2);
            
            $connectionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            $this->results['database'] = [
                'status' => '✅ OK',
                'connection_time' => "{$connectionTime}ms",
                'query_time' => "{$queryTime}ms",
                'user_count' => $userCount,
                'driver' => config('database.default'),
                'host' => config('database.connections.' . config('database.default') . '.host'),
            ];
            
        } catch (\Exception $e) {
            $this->results['database'] = [
                'status' => '❌ Failed',
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Check cache system
     *
     * @return void
     */
    protected function checkCache(): void
    {
        $this->info('Checking cache system...');
        
        try {
            $startTime = microtime(true);
            
            // Test cache write/read
            $testKey = 'health_check_' . time();
            $testValue = 'test_value_' . rand(1000, 9999);
            
            $this->cacheService->put($testKey, $testValue, 60);
            $retrievedValue = $this->cacheService->get($testKey);
            
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            
            if ($retrievedValue === $testValue) {
                $this->cacheService->forget($testKey);
                
                $stats = $this->cacheService->getStatistics();
                
                $this->results['cache'] = [
                    'status' => '✅ OK',
                    'response_time' => "{$responseTime}ms",
                    'driver' => $stats['driver'] ?? 'unknown',
                    'status_detail' => $stats['status'] ?? 'active',
                ];
            } else {
                throw new \Exception('Cache read/write test failed');
            }
            
        } catch (\Exception $e) {
            $this->results['cache'] = [
                'status' => '❌ Failed',
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Check queue system
     *
     * @return void
     */
    protected function checkQueue(): void
    {
        $this->info('Checking queue system...');
        
        try {
            $connection = config('queue.default');
            $queueSize = 0;
            
            if ($connection === 'redis') {
                // Check Redis queue
                $queueSize = Redis::llen('queues:default');
                $failedJobs = Redis::llen('queues:failed');
                
                $this->results['queue'] = [
                    'status' => '✅ OK',
                    'connection' => $connection,
                    'pending_jobs' => $queueSize,
                    'failed_jobs' => $failedJobs,
                ];
            } else {
                // Check database queue
                $queueSize = DB::table('jobs')->count();
                $failedJobs = DB::table('failed_jobs')->count();
                
                $this->results['queue'] = [
                    'status' => '✅ OK',
                    'connection' => $connection,
                    'pending_jobs' => $queueSize,
                    'failed_jobs' => $failedJobs,
                ];
            }
            
        } catch (\Exception $e) {
            $this->results['queue'] = [
                'status' => '❌ Failed',
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Check disk space
     *
     * @return void
     */
    protected function checkDiskSpace(): void
    {
        $this->info('Checking disk space...');
        
        try {
            $storagePath = storage_path();
            $freeBytes = disk_free_space($storagePath);
            $totalBytes = disk_total_space($storagePath);
            $usedBytes = $totalBytes - $freeBytes;
            
            $freeGB = round($freeBytes / (1024 * 1024 * 1024), 2);
            $totalGB = round($totalBytes / (1024 * 1024 * 1024), 2);
            $usedPercent = round(($usedBytes / $totalBytes) * 100, 1);
            
            $status = $usedPercent > 90 ? '⚠️ Warning' : '✅ OK';
            
            $this->results['disk'] = [
                'status' => $status,
                'free_space' => "{$freeGB} GB",
                'total_space' => "{$totalGB} GB",
                'used_percent' => "{$usedPercent}%",
                'path' => $storagePath,
            ];
            
        } catch (\Exception $e) {
            $this->results['disk'] = [
                'status' => '❌ Failed',
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Check memory usage
     *
     * @return void
     */
    protected function checkMemoryUsage(): void
    {
        $this->info('Checking memory usage...');
        
        try {
            $memoryUsage = memory_get_usage(true);
            $peakMemory = memory_get_peak_usage(true);
            $memoryLimit = ini_get('memory_limit');
            
            $memoryUsageMB = round($memoryUsage / (1024 * 1024), 2);
            $peakMemoryMB = round($peakMemory / (1024 * 1024), 2);
            
            $this->results['memory'] = [
                'status' => '✅ OK',
                'current_usage' => "{$memoryUsageMB} MB",
                'peak_usage' => "{$peakMemoryMB} MB",
                'limit' => $memoryLimit,
            ];
            
        } catch (\Exception $e) {
            $this->results['memory'] = [
                'status' => '❌ Failed',
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Display health check results
     *
     * @param float $duration
     * @return void
     */
    protected function displayResults(float $duration): void
    {
        $this->newLine();
        $this->info('📊 System Health Report');
        $this->newLine();
        
        // Basic status table
        $statusData = [];
        foreach ($this->results as $component => $data) {
            $statusData[] = [
                ucfirst($component),
                $data['status'],
                $data['error'] ?? 'N/A'
            ];
        }
        
        $this->table(
            ['Component', 'Status', 'Error'],
            $statusData
        );
        
        // Detailed information if requested
        if ($this->option('detailed')) {
            $this->newLine();
            $this->info('📋 Detailed Information');
            $this->newLine();
            
            foreach ($this->results as $component => $data) {
                $this->line("<fg=cyan>{$component}:</>");
                foreach ($data as $key => $value) {
                    if ($key !== 'status' && $key !== 'error') {
                        $this->line("  {$key}: {$value}");
                    }
                }
                $this->newLine();
            }
        }
        
        $this->info("⏱️  Total check time: {$duration} seconds");
        
        // Overall status
        $hasFailures = collect($this->results)->contains('status', '❌ Failed');
        $hasWarnings = collect($this->results)->contains('status', '⚠️ Warning');
        
        if ($hasFailures) {
            $this->error('❌ System health check failed - some components are not working properly');
        } elseif ($hasWarnings) {
            $this->warn('⚠️  System health check completed with warnings');
        } else {
            $this->info('✅ All systems are healthy');
        }
    }
}