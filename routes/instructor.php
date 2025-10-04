<?php

use App\Http\Controllers\InstructorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Instructor Routes
|--------------------------------------------------------------------------
|
| All routes for instructor portal functionality including interview
| management, applicant assignment, and schedule management.
| Requires authentication with instructor role.
|
*/

// Instructor Dashboard
Route::get('/dashboard', [InstructorController::class, 'dashboard'])
    ->middleware('role:instructor')
    ->name('dashboard');

// Assigned Applicants
Route::get('/applicants', [InstructorController::class, 'applicants'])
    ->middleware('role:instructor')
    ->name('applicants');

// Interview Management
Route::prefix('interview')->name('interview.')->middleware('role:instructor')->group(function () {
    Route::get('/applicants/{applicant}', [InstructorController::class, 'showInterview'])->name('show');
    Route::post('/applicants/{applicant}', [InstructorController::class, 'submitInterview'])->name('submit');
});

// Applicant Portfolio
Route::get('/applicants/{applicant}/portfolio', [InstructorController::class, 'portfolio'])
    ->middleware('role:instructor')
    ->name('applicant.portfolio');

// Schedule Management
Route::get('/schedule', [InstructorController::class, 'schedule'])
    ->middleware('role:instructor')
    ->name('schedule');

Route::prefix('schedule')->name('schedule.')->middleware('role:instructor')->group(function () {
    Route::post('/update/{interview}', function(\Illuminate\Http\Request $request, \App\Models\Interview $interview) {
        $request->validate([
            'schedule_date' => 'required|date|after:now',
            'notes' => 'nullable|string|max:1000',
        ]);

        $interview->update([
            'schedule_date' => $request->schedule_date,
            'status' => 'scheduled',
            'notes' => $request->notes,
        ]);

        $interview->applicant->update(['status' => 'interview-scheduled']);

        return response()->json(['success' => true]);
    })->name('update');
});

// Interview History
Route::get('/interview-history', [InstructorController::class, 'interviewHistory'])
    ->middleware('role:instructor')
    ->name('interview-history');

// Guidelines
Route::get('/guidelines', [InstructorController::class, 'guidelines'])
    ->middleware('role:instructor')
    ->name('guidelines');

// Interview Pool Routes
Route::prefix('interview-pool')->name('interview-pool.')->middleware('role:instructor,department-head')->group(function () {
    Route::get('/', [InstructorController::class, 'interviewPool'])->name('index');
    Route::post('/{interview}/claim', [InstructorController::class, 'claimInterview'])->name('claim');
    Route::post('/{interview}/release', [InstructorController::class, 'releaseInterview'])->name('release');
    Route::get('/available', [InstructorController::class, 'getAvailableInterviews'])->name('available');
    Route::get('/my-claimed', [InstructorController::class, 'getMyClaimedInterviews'])->name('my-claimed');
});