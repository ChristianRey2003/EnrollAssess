<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchoolYear;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use App\Services\Dashboard\BasicInfoAnalyticsService;
use Carbon\Carbon;

class SchoolYearController extends Controller
{
    /**
     * Switch the current school year
     */
    public function switch(Request $request)
    {
        $request->validate([
            'school_year_id' => 'required|exists:school_years,school_year_id',
        ]);

        $schoolYearId = $request->input('school_year_id');
        
        // Verify it's active
        $schoolYear = SchoolYear::where('school_year_id', $schoolYearId)
                                ->where('is_active', true)
                                ->first();

        if (!$schoolYear) {
            return redirect()->back()->with('error', 'Invalid school year selected.');
        }

        // Store in session
        session(['school_year_id' => $schoolYearId]);
        
        // Clear dashboard cache to force refresh
        try {
            $analyticsService = app(BasicInfoAnalyticsService::class);
            $analyticsService->clearCache();
        } catch (\Exception $e) {
            // Cache clearing failed, but continue
        }
        
        // Clear dashboard stats cache
        Cache::forget('dashboard_stats');
        Cache::forget('dashboard_stats_' . $schoolYearId);
        Cache::forget('dashboard_stats_all');

        return redirect()->back()->with('success', "Switched to {$schoolYear->name}");
    }

    /**
     * Display a listing of school years (for settings page)
     */
    public function index()
    {
        $schoolYears = SchoolYear::orderBy('start_date', 'desc')->get();
        return response()->json([
            'success' => true,
            'schoolYears' => $schoolYears->map(function ($sy) {
                return [
                    'school_year_id' => $sy->school_year_id,
                    'name' => $sy->name,
                    'start_date' => $sy->start_date->format('Y-m-d'),
                    'end_date' => $sy->end_date->format('Y-m-d'),
                    'is_current' => $sy->is_current,
                    'is_active' => $sy->is_active,
                    'created_at' => $sy->created_at->format('Y-m-d H:i:s'),
                ];
            }),
        ]);
    }

    /**
     * Store a newly created school year
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:school_years,name',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        try {
            $schoolYear = SchoolYear::create([
                'name' => $request->name,
                'start_date' => Carbon::parse($request->start_date),
                'end_date' => Carbon::parse($request->end_date),
                'is_current' => false,
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => "School year '{$schoolYear->name}' created successfully.",
                'schoolYear' => [
                    'school_year_id' => $schoolYear->school_year_id,
                    'name' => $schoolYear->name,
                    'start_date' => $schoolYear->start_date->format('Y-m-d'),
                    'end_date' => $schoolYear->end_date->format('Y-m-d'),
                    'is_current' => $schoolYear->is_current,
                    'is_active' => $schoolYear->is_active,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create school year: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified school year
     */
    public function update(Request $request, SchoolYear $schoolYear)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:school_years,name,' . $schoolYear->school_year_id . ',school_year_id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        try {
            $schoolYear->update([
                'name' => $request->name,
                'start_date' => Carbon::parse($request->start_date),
                'end_date' => Carbon::parse($request->end_date),
            ]);

            return response()->json([
                'success' => true,
                'message' => "School year '{$schoolYearModel->name}' updated successfully.",
                'schoolYear' => [
                    'school_year_id' => $schoolYearModel->school_year_id,
                    'name' => $schoolYearModel->name,
                    'start_date' => $schoolYearModel->start_date->format('Y-m-d'),
                    'end_date' => $schoolYearModel->end_date->format('Y-m-d'),
                    'is_current' => $schoolYearModel->is_current,
                    'is_active' => $schoolYearModel->is_active,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update school year: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified school year (soft delete by setting is_active to false)
     */
    public function destroy(SchoolYear $schoolYear)
    {
        try {
            // Don't allow deleting the current school year
            if ($schoolYear->is_current) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete the current school year. Please set another year as current first.',
                ], 400);
            }

            // Check if there are applicants assigned to this school year
            $applicantCount = $schoolYear->applicants()->count();
            if ($applicantCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete school year '{$schoolYear->name}' because it has {$applicantCount} applicant(s) assigned. Please reassign applicants first.",
                ], 400);
            }

            // Soft delete by setting is_active to false
            $schoolYear->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => "School year '{$schoolYear->name}' has been deactivated.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete school year: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Set a school year as the current one
     */
    public function setAsCurrent(SchoolYear $schoolYear)
    {
        try {
            if (!$schoolYear->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot set an inactive school year as current.',
                ], 400);
            }

            $schoolYear->setAsCurrent();

            // Update session if user is viewing admin area
            session(['school_year_id' => $schoolYear->school_year_id]);

            // Clear caches
            try {
                $analyticsService = app(BasicInfoAnalyticsService::class);
                $analyticsService->clearCache();
            } catch (\Exception $e) {
                // Cache clearing failed, but continue
            }
            
            Cache::forget('dashboard_stats');
            Cache::forget('dashboard_stats_' . $schoolYear->school_year_id);
            Cache::forget('dashboard_stats_all');

            return response()->json([
                'success' => true,
                'message' => "School year '{$schoolYear->name}' is now set as current.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to set school year as current: ' . $e->getMessage(),
            ], 500);
        }
    }
}
