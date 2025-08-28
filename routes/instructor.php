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
Route::get('/dashboard', [InstructorController::class, 'dashboard'])->name('dashboard');

// Assigned Applicants
Route::get('/applicants', [InstructorController::class, 'applicants'])->name('applicants');

// Interview Management
Route::prefix('interview')->name('interview.')->group(function () {
    Route::get('/applicants/{applicant}', [InstructorController::class, 'showInterview'])->name('show');
    Route::post('/applicants/{applicant}', [InstructorController::class, 'submitInterview'])->name('submit');
});

// Applicant Portfolio
Route::get('/applicants/{applicant}/portfolio', [InstructorController::class, 'portfolio'])->name('applicant.portfolio');

// Schedule Management
Route::prefix('schedule')->name('schedule.')->group(function () {
    Route::get('/', [InstructorController::class, 'schedule'])->name('index');
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
Route::get('/interview-history', [InstructorController::class, 'interviewHistory'])->name('interview-history');

// Guidelines
Route::get('/guidelines', [InstructorController::class, 'guidelines'])->name('guidelines');
