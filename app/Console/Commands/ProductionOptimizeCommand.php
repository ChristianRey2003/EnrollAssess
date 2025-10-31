<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

/**
 * Run all production optimization commands
 * 
 * Executes a series of commands to optimize the application
 * for production deployment including caching, route optimization,
 * and view compilation.
 */
class ProductionOptimizeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'production:optimize {--force : Force optimization even if already cached}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run all production optimization commands';

    /**
     * Optimization steps
     *
     * @var array
     */
    protected array $steps = [
        'config:cache' => 'Cache configuration files',
        'route:cache' => 'Cache application routes',
        'view:cache' => 'Cache application views',
        'event:cache' => 'Cache application events',
        'cache:warm' => 'Warm up application cache',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Starting production optimization...');
        $this->newLine();
        
        $startTime = microtime(true);
        $results = [];
        
        foreach ($this->steps as $command => $description) {
            $this->info("Running: {$description}");
            
            try {
                $stepStartTime = microtime(true);
                
                $exitCode = Artisan::call($command, [
                    '--force' => $this->option('force')
                ]);
                
                $stepDuration = round((microtime(true) - $stepStartTime) * 1000, 2);
                
                if ($exitCode === 0) {
                    $this->line("  ✅ {$description} completed ({$stepDuration}ms)");
                    $results[$command] = [
                        'status' => 'success',
                        'duration' => $stepDuration,
                        'output' => Artisan::output()
                    ];
                } else {
                    $this->error("  ❌ {$description} failed");
                    $results[$command] = [
                        'status' => 'failed',
                        'duration' => $stepDuration,
                        'output' => Artisan::output()
                    ];
                }
                
            } catch (\Exception $e) {
                $this->error("  ❌ {$description} failed: {$e->getMessage()}");
                $results[$command] = [
                    'status' => 'error',
                    'duration' => 0,
                    'error' => $e->getMessage()
                ];
            }
            
            $this->newLine();
        }
        
        $endTime = microtime(true);
        $totalDuration = round($endTime - $startTime, 2);
        
        $this->displayResults($results, $totalDuration);
        
        // Log results
        Log::info('Production optimization completed', [
            'duration' => $totalDuration,
            'results' => $results
        ]);
        
        // Return appropriate exit code
        $hasFailures = collect($results)->contains('status', 'failed') || 
                      collect($results)->contains('status', 'error');
        
        return $hasFailures ? Command::FAILURE : Command::SUCCESS;
    }
    
    /**
     * Display optimization results
     *
     * @param array $results
     * @param float $totalDuration
     * @return void
     */
    protected function displayResults(array $results, float $totalDuration): void
    {
        $this->info('📊 Optimization Results');
        $this->newLine();
        
        $tableData = [];
        foreach ($results as $command => $data) {
            $status = match($data['status']) {
                'success' => '✅ Success',
                'failed' => '❌ Failed',
                'error' => '💥 Error',
                default => '❓ Unknown'
            };
            
            $tableData[] = [
                $command,
                $status,
                $data['duration'] . 'ms',
                $data['error'] ?? 'N/A'
            ];
        }
        
        $this->table(
            ['Command', 'Status', 'Duration', 'Error'],
            $tableData
        );
        
        $this->newLine();
        $this->info("⏱️  Total optimization time: {$totalDuration} seconds");
        
        // Overall status
        $successCount = collect($results)->where('status', 'success')->count();
        $totalCount = count($results);
        
        if ($successCount === $totalCount) {
            $this->info('✅ All optimizations completed successfully');
        } else {
            $failedCount = $totalCount - $successCount;
            $this->warn("⚠️  {$successCount}/{$totalCount} optimizations successful, {$failedCount} failed");
        }
        
        // Additional recommendations
        $this->newLine();
        $this->info('💡 Additional Recommendations:');
        $this->line('  • Run "php artisan system:health" to verify system status');
        $this->line('  • Check server configuration for optimal performance');
        $this->line('  • Monitor application performance after deployment');
        $this->line('  • Set up log monitoring and error tracking');
    }
}