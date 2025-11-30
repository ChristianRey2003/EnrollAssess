<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\SchoolYear;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;

class SetSchoolYear
{
    /**
     * Handle an incoming request.
     * Sets the current school year in session and shares it with all views.
     * Excludes settings, question bank, and users routes from filtering.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Routes that should NOT be filtered by school year
        $excludedRoutes = [
            'admin.settings',
            'admin.users',
        ];

        $currentRoute = $request->route()->getName() ?? '';
        
        // Check if current route should be excluded
        $shouldExclude = false;
        foreach ($excludedRoutes as $excludedPrefix) {
            if (str_starts_with($currentRoute, $excludedPrefix)) {
                $shouldExclude = true;
                break;
            }
        }

        // Get all active school years for dropdown (only if table exists)
        $schoolYears = collect([]);
        try {
            if (Schema::hasTable('school_years')) {
                // Only get active school years, ordered by start_date descending (most recent first)
                $schoolYears = SchoolYear::where('is_active', true)
                                        ->orderBy('start_date', 'desc')
                                        ->get();
            }
        } catch (\Exception $e) {
            // Table doesn't exist yet or other error - use empty collection
            $schoolYears = collect([]);
        }

        // Determine current school year
        $schoolYearId = null;
        
        // Check if school_year_id is in query string (for direct links)
        if ($request->has('school_year_id')) {
            $schoolYearId = $request->get('school_year_id');
            // Validate it exists (only if table exists)
            try {
                if (Schema::hasTable('school_years')) {
                    if (!SchoolYear::where('school_year_id', $schoolYearId)->where('is_active', true)->exists()) {
                        $schoolYearId = null;
                    }
                } else {
                    $schoolYearId = null;
                }
            } catch (\Exception $e) {
                $schoolYearId = null;
            }
        }
        
        // If not in query, check session
        if (!$schoolYearId) {
            $schoolYearId = session('school_year_id');
            // Validate session school year still exists and is active
            if ($schoolYearId) {
                try {
                    if (Schema::hasTable('school_years')) {
                        if (!SchoolYear::where('school_year_id', $schoolYearId)->where('is_active', true)->exists()) {
                            $schoolYearId = null; // Session has invalid school year, reset it
                        }
                    }
                } catch (\Exception $e) {
                    $schoolYearId = null;
                }
            }
        }
        
        // If still no school year, get the current one from database (only if table exists)
        if (!$schoolYearId) {
            try {
                if (Schema::hasTable('school_years')) {
                    $currentSchoolYear = SchoolYear::getCurrent();
                    if (!$currentSchoolYear) {
                        // If no current year set, get the most recent active one
                        $currentSchoolYear = SchoolYear::where('is_active', true)
                                                      ->orderBy('start_date', 'desc')
                                                      ->first();
                    }
                    $schoolYearId = $currentSchoolYear ? $currentSchoolYear->school_year_id : null;
                }
            } catch (\Exception $e) {
                // Table doesn't exist yet - skip
            }
        }
        
        // Store in session (always update to ensure it's set)
        if ($schoolYearId) {
            session(['school_year_id' => $schoolYearId]);
        }

        // Get the current school year object
        $currentSchoolYear = null;
        if ($schoolYearId) {
            try {
                if (Schema::hasTable('school_years')) {
                    $currentSchoolYear = SchoolYear::find($schoolYearId);
                }
            } catch (\Exception $e) {
                // Table doesn't exist yet - skip
            }
        }

        // Share with all views (for dropdown and filtering)
        View::share('schoolYears', $schoolYears);
        View::share('currentSchoolYear', $currentSchoolYear);
        View::share('currentSchoolYearId', $schoolYearId);
        View::share('shouldFilterBySchoolYear', !$shouldExclude);

        return $next($request);
    }
}
