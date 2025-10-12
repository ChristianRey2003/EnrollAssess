<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Centralized caching service for application-wide cache management
 * 
 * Provides standardized caching patterns, cache key management,
 * and cache invalidation strategies.
 */
class CacheService
{
    /**
     * Cache TTL constants (in seconds)
     */
    const VERY_SHORT_TTL = 300;    // 5 minutes
    const SHORT_TTL = 900;         // 15 minutes
    const MEDIUM_TTL = 3600;       // 1 hour
    const LONG_TTL = 86400;        // 24 hours
    const VERY_LONG_TTL = 604800;  // 1 week

    /**
     * Cache key prefixes
     */
    const PREFIX_STATS = 'stats';
    const PREFIX_APPLICANT = 'applicant';
    const PREFIX_EXAM = 'exam';
    const PREFIX_QUESTION = 'question';
    const PREFIX_USER = 'user';
    const PREFIX_INTERVIEW = 'interview';

    /**
     * Remember a value in cache with automatic key generation
     *
     * @param string $key
     * @param callable $callback
     * @param int $ttl
     * @param array $tags
     * @return mixed
     */
    public function remember(string $key, callable $callback, int $ttl = self::MEDIUM_TTL, array $tags = [])
    {
        try {
            return Cache::remember($key, $ttl, $callback);
        } catch (\Exception $e) {
            Log::warning('Cache remember failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            
            // Fall back to direct execution if cache fails
            return $callback();
        }
    }

    /**
     * Store value in cache
     *
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return bool
     */
    public function put(string $key, $value, int $ttl = self::MEDIUM_TTL): bool
    {
        try {
            return Cache::put($key, $value, $ttl);
        } catch (\Exception $e) {
            Log::warning('Cache put failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get value from cache
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        try {
            return Cache::get($key, $default);
        } catch (\Exception $e) {
            Log::warning('Cache get failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return $default;
        }
    }

    /**
     * Forget (delete) cache key
     *
     * @param string $key
     * @return bool
     */
    public function forget(string $key): bool
    {
        try {
            return Cache::forget($key);
        } catch (\Exception $e) {
            Log::warning('Cache forget failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Flush cache by pattern
     *
     * @param string $pattern
     * @return bool
     */
    public function flushByPattern(string $pattern): bool
    {
        try {
            $keys = Cache::getRedis()->keys($pattern);
            if (!empty($keys)) {
                return Cache::getRedis()->del($keys) > 0;
            }
            return true;
        } catch (\Exception $e) {
            Log::warning('Cache flush by pattern failed', [
                'pattern' => $pattern,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Generate standardized cache key
     *
     * @param string $prefix
     * @param array $params
     * @return string
     */
    public function generateKey(string $prefix, array $params = []): string
    {
        $key = $prefix;
        
        if (!empty($params)) {
            $key .= ':' . implode(':', array_map(function($param) {
                return is_array($param) ? md5(serialize($param)) : (string)$param;
            }, $params));
        }
        
        return $key;
    }

    /**
     * Cache application statistics
     *
     * @param callable $callback
     * @return mixed
     */
    public function cacheStats(callable $callback)
    {
        $key = $this->generateKey(self::PREFIX_STATS, ['dashboard']);
        return $this->remember($key, $callback, self::SHORT_TTL);
    }

    /**
     * Cache applicant data
     *
     * @param int $applicantId
     * @param callable $callback
     * @return mixed
     */
    public function cacheApplicant(int $applicantId, callable $callback)
    {
        $key = $this->generateKey(self::PREFIX_APPLICANT, [$applicantId]);
        return $this->remember($key, $callback, self::MEDIUM_TTL);
    }

    /**
     * Cache exam data
     *
     * @param int $examId
     * @param callable $callback
     * @return mixed
     */
    public function cacheExam(int $examId, callable $callback)
    {
        $key = $this->generateKey(self::PREFIX_EXAM, [$examId]);
        return $this->remember($key, $callback, self::LONG_TTL);
    }

    /**
     * Cache paginated query results
     *
     * @param string $baseKey
     * @param array $queryParams
     * @param callable $callback
     * @param int $ttl
     * @return mixed
     */
    public function cachePaginatedQuery(string $baseKey, array $queryParams, callable $callback, int $ttl = self::SHORT_TTL)
    {
        // Create cache key including pagination and filter parameters
        $cacheParams = [
            'page' => $queryParams['page'] ?? 1,
            'per_page' => $queryParams['per_page'] ?? 15,
            'search' => $queryParams['search'] ?? '',
            'status' => $queryParams['status'] ?? '',
            'filters' => md5(serialize($queryParams))
        ];
        
        $key = $this->generateKey($baseKey, $cacheParams);
        return $this->remember($key, $callback, $ttl);
    }

    /**
     * Invalidate related cache keys when data changes
     *
     * @param string $type
     * @param int|null $id
     * @return bool
     */
    public function invalidateRelated(string $type, ?int $id = null): bool
    {
        $patterns = [];
        
        switch ($type) {
            case 'applicant':
                $patterns[] = self::PREFIX_APPLICANT . ':*';
                $patterns[] = self::PREFIX_STATS . ':*';
                if ($id) {
                    $patterns[] = self::PREFIX_APPLICANT . ':' . $id . ':*';
                }
                break;
                
            case 'exam':
                $patterns[] = self::PREFIX_EXAM . ':*';
                $patterns[] = self::PREFIX_QUESTION . ':*';
                break;
                
            case 'interview':
                $patterns[] = self::PREFIX_INTERVIEW . ':*';
                $patterns[] = self::PREFIX_STATS . ':*';
                if ($id) {
                    $patterns[] = self::PREFIX_INTERVIEW . ':' . $id . ':*';
                }
                break;
                
            case 'stats':
                $patterns[] = self::PREFIX_STATS . ':*';
                break;
        }
        
        $success = true;
        foreach ($patterns as $pattern) {
            if (!$this->flushByPattern($pattern)) {
                $success = false;
            }
        }
        
        return $success;
    }

    /**
     * Warm up commonly accessed cache
     *
     * @return array
     */
    public function warmUpCache(): array
    {
        $results = [
            'stats' => false,
            'active_exams' => false,
            'user_counts' => false
        ];
        
        try {
            // Warm up dashboard statistics
            $results['stats'] = $this->cacheStats(function() {
                return [
                    'total_applicants' => \App\Models\Applicant::count(),
                    'exam_completed' => \App\Models\Applicant::where('status', '!=', 'pending')->count(),
                    'interviews_scheduled' => \App\Models\Interview::where('status', 'scheduled')->count(),
                    'pending_reviews' => \App\Models\Applicant::where('status', 'exam-completed')->count(),
                ];
            });
            
            // Warm up active exam sets
            $examsKey = $this->generateKey(self::PREFIX_EXAM, ['active']);
            $results['active_exams'] = $this->remember($examsKey, function() {
                return \App\Models\Exam::where('is_active', true)->get();
            }, self::LONG_TTL);
            
            // Warm up user statistics
            $userStatsKey = $this->generateKey(self::PREFIX_USER, ['stats']);
            $results['user_counts'] = $this->remember($userStatsKey, function() {
                return [
                    'total_users' => \App\Models\User::count(),
                    'department_heads' => \App\Models\User::where('role', 'department-head')->count(),
                    'instructors' => \App\Models\User::where('role', 'instructor')->count(),
                ];
            }, self::LONG_TTL);
            
        } catch (\Exception $e) {
            Log::error('Cache warm up failed', [
                'error' => $e->getMessage()
            ]);
        }
        
        return $results;
    }

    /**
     * Get cache statistics
     *
     * @return array
     */
    public function getStatistics(): array
    {
        try {
            $redis = Cache::getRedis();
            
            return [
                'total_keys' => count($redis->keys('*')),
                'memory_usage' => $redis->info('memory')['used_memory_human'] ?? 'Unknown',
                'hit_rate' => $redis->info('stats')['keyspace_hit_rate'] ?? 'Unknown',
                'connected_clients' => $redis->info('clients')['connected_clients'] ?? 0,
            ];
        } catch (\Exception $e) {
            Log::warning('Failed to get cache statistics', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'total_keys' => 'Unknown',
                'memory_usage' => 'Unknown',
                'hit_rate' => 'Unknown',
                'connected_clients' => 'Unknown',
            ];
        }
    }
}
