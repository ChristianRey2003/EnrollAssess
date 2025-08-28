<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Performance monitoring middleware
 * 
 * Tracks request performance metrics including response time,
 * memory usage, and database query counts.
 */
class PerformanceMonitoringMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip monitoring for non-essential routes
        if ($this->shouldSkipMonitoring($request)) {
            return $next($request);
        }

        // Start monitoring
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        // Enable query logging if configured
        if (config('performance.database.optimization.enable_query_log', false)) {
            DB::enableQueryLog();
        }

        // Process request
        $response = $next($request);

        // Calculate metrics
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $metrics = [
            'route' => $request->route()?->getName() ?? $request->path(),
            'method' => $request->method(),
            'response_time_ms' => round(($endTime - $startTime) * 1000, 2),
            'memory_usage_mb' => round(($endMemory - $startMemory) / 1024 / 1024, 2),
            'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'status_code' => $response->getStatusCode(),
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'timestamp' => now()->toISOString(),
        ];

        // Add query metrics if enabled
        if (DB::getQueryLog()) {
            $queryLog = DB::getQueryLog();
            $metrics['query_count'] = count($queryLog);
            $metrics['total_query_time_ms'] = round(
                array_sum(array_column($queryLog, 'time')), 2
            );
            
            // Log slow queries
            $slowQueries = array_filter($queryLog, function($query) {
                return $query['time'] > config('performance.database.optimization.slow_query_threshold', 1000);
            });
            
            if (!empty($slowQueries)) {
                $metrics['slow_queries'] = count($slowQueries);
                Log::warning('Slow queries detected', [
                    'route' => $metrics['route'],
                    'slow_queries' => $slowQueries
                ]);
            }
        }

        // Check performance thresholds
        $this->checkPerformanceThresholds($metrics, $request);

        // Log performance metrics
        $this->logPerformanceMetrics($metrics);

        // Add performance headers for debugging
        if (config('app.debug')) {
            $response->headers->set('X-Response-Time', $metrics['response_time_ms'] . 'ms');
            $response->headers->set('X-Memory-Usage', $metrics['memory_usage_mb'] . 'MB');
            
            if (isset($metrics['query_count'])) {
                $response->headers->set('X-Query-Count', $metrics['query_count']);
            }
        }

        return $response;
    }

    /**
     * Determine if monitoring should be skipped for this request
     *
     * @param Request $request
     * @return bool
     */
    protected function shouldSkipMonitoring(Request $request): bool
    {
        // Skip monitoring if disabled
        if (!config('performance.monitoring.enabled', true)) {
            return true;
        }

        // Skip for asset requests
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)$/i', $request->path())) {
            return true;
        }

        // Skip for monitoring endpoints to avoid recursion
        $skipRoutes = [
            'horizon.*',
            'telescope.*',
            '_debugbar.*',
        ];

        $routeName = $request->route()?->getName();
        if ($routeName) {
            foreach ($skipRoutes as $pattern) {
                if (fnmatch($pattern, $routeName)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if metrics exceed performance thresholds
     *
     * @param array $metrics
     * @param Request $request
     * @return void
     */
    protected function checkPerformanceThresholds(array $metrics, Request $request): void
    {
        $thresholds = config('performance.monitoring.thresholds', []);
        $alerts = [];

        // Check response time
        if (isset($thresholds['max_response_time']) && 
            $metrics['response_time_ms'] > $thresholds['max_response_time']) {
            $alerts[] = "Slow response time: {$metrics['response_time_ms']}ms (threshold: {$thresholds['max_response_time']}ms)";
        }

        // Check memory usage
        if (isset($thresholds['max_memory_usage']) && 
            $metrics['memory_usage_mb'] > $thresholds['max_memory_usage']) {
            $alerts[] = "High memory usage: {$metrics['memory_usage_mb']}MB (threshold: {$thresholds['max_memory_usage']}MB)";
        }

        // Check query count
        if (isset($metrics['query_count'], $thresholds['max_database_queries']) && 
            $metrics['query_count'] > $thresholds['max_database_queries']) {
            $alerts[] = "High query count: {$metrics['query_count']} (threshold: {$thresholds['max_database_queries']})";
        }

        // Log alerts if any
        if (!empty($alerts)) {
            Log::warning('Performance threshold exceeded', [
                'route' => $metrics['route'],
                'alerts' => $alerts,
                'metrics' => $metrics,
                'request_data' => [
                    'url' => $request->fullUrl(),
                    'user_agent' => $request->userAgent(),
                ]
            ]);
        }
    }

    /**
     * Log performance metrics
     *
     * @param array $metrics
     * @return void
     */
    protected function logPerformanceMetrics(array $metrics): void
    {
        // Log to performance channel if configured
        Log::channel('performance')->info('Request metrics', $metrics);
        
        // Store metrics for reporting (you could extend this to use a metrics database)
        // This is a simple implementation - you might want to use a time-series database
        // like InfluxDB or store in a separate metrics table for production use
    }
}
