<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required. Please log in.'
                ], 401);
            }
            return redirect()->route('admin.login');
        }

        $user = Auth::user();

        // Check if user has any of the required roles
        if (!in_array($user->role, $roles)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. You do not have permission to access this area.'
                ], 403);
            }
            abort(403, 'Access denied. You do not have permission to access this area.');
        }

        return $next($request);
    }
}
