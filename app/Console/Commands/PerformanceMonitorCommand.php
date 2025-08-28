<?php

namespace App\Console\Commands;

use App\Services\CacheService;
use App\Services\QueryOptimizationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Performance monitoring command for system health checks
 * 
 * Monitors database performance, cache effectiveness,
 * and overall system metrics.
 */
class PerformanceMonitorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrollassess:monitor 
                            {--report : Generate detailed performance report}
                            {--warmup : Warm up application cache}
                            {--clear-cache : Clear all application cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor system performance and manage cache';

    protected CacheService $cacheService;
    protected QueryOptimizationService $queryService;

    /**
     * Create a new command instance.
     */
    public function __construct(CacheService $cacheService, QueryOptimizationService $queryService)
    {
        parent::__construct();
        $this->cacheService = $cacheService;
        $this->queryService = $queryService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('clear-cache')) {
            return $this->clearCache();
        }

        if ($this->option('warmup')) {
            return $this->warmUpCache();
        }

        if ($this->option('report')) {
            return $this->generateReport();
        }

        $this->showQuickStatus();
    }

    /**
     * Show quick system status
     */
    protected function showQuickStatus(): void
    {
        $this->info('EnrollAssess Performance Monitor');
        $this->newLine();

        // Basic health checks
        $this->line('🔍 <comment>Quick Health Check</comment>');
        
        // Database connectivity
        try {
            DB::connection()->getPdo();
            $this->line('✅ Database: Connected');
        } catch (\Exception $e) {
            $this->line('❌ Database: Failed to connect');
            $this->error('   Error: ' . $e->getMessage());
        }

        // Cache connectivity
        try {
            $this->cacheService->put('health_check', 'ok', 60);
            $value = $this->cacheService->get('health_check');
            if ($value === 'ok') {
                $this->line('✅ Cache: Working');
            } else {
                $this->line('❌ Cache: Not responding correctly');
            }
        } catch (\Exception $e) {
            $this->line('❌ Cache: Failed');
            $this->error('   Error: ' . $e->getMessage());
        }

        // Quick stats
        try {
            $stats = $this->queryService->getDashboardStatistics();
            $this->newLine();
            $this->line('📊 <comment>Quick Stats</comment>');
            $this->line("   Total Applicants: {$stats['total_applicants']}");
            $this->line("   Exam Completed: {$stats['exam_completed']}");
            $this->line("   Interviews Scheduled: {$stats['interviews_scheduled']}");
        } catch (\Exception $e) {
            $this->line('❌ Unable to fetch statistics');
        }

        $this->newLine();
        $this->comment('Use --report for detailed analysis, --warmup to warm cache, or --clear-cache to clear cache.');
    }

    /**
     * Generate detailed performance report
     */
    protected function generateReport(): int
    {
        $this->info('🔍 Generating Performance Report...');
        $this->newLine();

        try {
            // Get performance metrics
            $metrics = $this->queryService->getPerformanceMetrics();
            
            $this->displayDatabaseMetrics($metrics['database'] ?? []);
            $this->displayCacheMetrics($metrics['cache'] ?? []);
            $this->displayQueryMetrics($metrics['query_counts'] ?? []);
            
            // Log the report
            Log::info('Performance report generated', $metrics);
            
            $this->newLine();
            $this->info('✅ Performance report generated successfully');
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to generate performance report: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Display database metrics
     */
    protected function displayDatabaseMetrics(array $metrics): void
    {
        $this->line('🗄️  <comment>Database Metrics</comment>');
        
        if (empty($metrics)) {
            $this->line('   Unable to fetch database metrics');
            return;
        }

        $totalRows = 0;
        foreach ($metrics as $table => $data) {
            if (is_array($data) && isset($data['rows'])) {
                $totalRows += $data['rows'];
                $this->line("   {$table}: {$data['rows']} rows");
            }
        }
        
        $this->line("   <info>Total Records: {$totalRows}</info>");
        $this->newLine();
    }

    /**
     * Display cache metrics
     */
    protected function displayCacheMetrics(array $metrics): void
    {
        $this->line('💾 <comment>Cache Metrics</comment>');
        
        foreach ($metrics as $key => $value) {
            $this->line("   " . ucfirst(str_replace('_', ' ', $key)) . ": {$value}");
        }
        
        $this->newLine();
    }

    /**
     * Display query metrics
     */
    protected function displayQueryMetrics(array $metrics): void
    {
        $this->line('⚡ <comment>Query Metrics</comment>');
        
        foreach ($metrics as $key => $value) {
            $this->line("   " . ucfirst(str_replace('_', ' ', $key)) . ": {$value}");
        }
        
        $this->newLine();
    }

    /**
     * Warm up application cache
     */
    protected function warmUpCache(): int
    {
        $this->info('🔥 Warming up application cache...');
        
        try {
            $results = $this->cacheService->warmUpCache();
            
            foreach ($results as $key => $success) {
                if ($success) {
                    $this->line("✅ {$key}: Cached successfully");
                } else {
                    $this->line("❌ {$key}: Failed to cache");
                }
            }
            
            $this->newLine();
            $this->info('✅ Cache warm up completed');
            
        } catch (\Exception $e) {
            $this->error('❌ Cache warm up failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Clear application cache
     */
    protected function clearCache(): int
    {
        $this->info('🧹 Clearing application cache...');
        
        try {
            // Clear different cache types
            $cleared = 0;
            
            $patterns = [
                'stats:*',
                'applicant:*', 
                'exam:*',
                'question:*',
                'user:*',
                'interview:*',
                'analytics:*'
            ];
            
            foreach ($patterns as $pattern) {
                if ($this->cacheService->flushByPattern($pattern)) {
                    $cleared++;
                }
            }
            
            $this->line("✅ Cleared {$cleared} cache pattern(s)");
            $this->newLine();
            $this->info('✅ Cache cleared successfully');
            
        } catch (\Exception $e) {
            $this->error('❌ Cache clearing failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
