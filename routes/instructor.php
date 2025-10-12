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

// Interview Scheduling Routes
Route::prefix('interviews')->name('interviews.')->middleware('role:instructor')->group(function () {
    Route::post('/{interview}/schedule', [InstructorController::class, 'scheduleInterview'])->name('schedule');
    Route::post('/bulk-schedule', [InstructorController::class, 'bulkScheduleInterviews'])->name('bulk-schedule');
    Route::post('/{interview}/send-notification', [InstructorController::class, 'sendScheduleNotification'])->name('send-notification');
    Route::post('/{interview}/reschedule', [InstructorController::class, 'rescheduleInterview'])->name('reschedule');
});