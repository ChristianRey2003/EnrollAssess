<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rate limiting middleware for public routes
 * 
 * Provides protection against abuse and DDoS attacks on public-facing
 * routes like applicant login, exam submission, and access code validation.
 */
class RateLimitPublicRoutes
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $type = 'default'): Response
    {
        $key = $this->resolveRequestSignature($request, $type);
        
        $maxAttempts = $this->getMaxAttempts($type);
        $decayMinutes = $this->getDecayMinutes($type);
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Too many attempts. Please try again in {$seconds} seconds.",
                    'retry_after' => $seconds
                ], 429);
            }
            
            return back()->with('error', "Too many attempts. Please try again in {$seconds} seconds.");
        }
        
        RateLimiter::hit($key, $decayMinutes * 60);
        
        return $next($request);
    }
    
    /**
     * Resolve the request signature for rate limiting
     *
     * @param Request $request
     * @param string $type
     * @return string
     */
    protected function resolveRequestSignature(Request $request, string $type): string
    {
        // Use IP address for public routes
        $ip = $request->ip();
        
        // For access code verification, also include the access code to prevent
        // brute force attacks on specific codes
        if ($type === 'access-code' && $request->has('access_code')) {
            return "rate_limit:{$type}:{$ip}:" . md5($request->input('access_code'));
        }
        
        return "rate_limit:{$type}:{$ip}";
    }
    
    /**
     * Get maximum attempts for the given type
     *
     * @param string $type
     * @return int
     */
    protected function getMaxAttempts(string $type): int
    {
        return match($type) {
            'login' => 5,           // 5 attempts per minute for login
            'exam-submit' => 3,     // 3 attempts per minute for exam submission
            'access-code' => 10,    // 10 attempts per minute for access code validation
            'default' => 10,        // 10 attempts per minute for other routes
        };
    }
    
    /**
     * Get decay minutes for the given type
     *
     * @param string $type
     * @return int
     */
    protected function getDecayMinutes(string $type): int
    {
        return match($type) {
            'login' => 1,           // 1 minute decay for login
            'exam-submit' => 1,     // 1 minute decay for exam submission
            'access-code' => 1,     // 1 minute decay for access code validation
            'default' => 1,         // 1 minute decay for other routes
        };
    }
}


