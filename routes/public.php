<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
|
| Routes accessible without authentication including exam interface,
| privacy consent, and public reports.
|
*/

// Welcome page - redirect to admin login (or dashboard if already logged in)
Route::get('/', function () {
    if (\Illuminate\Support\Facades\Auth::check()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('admin.login');
});

// Exam Interface - Sectioned Exam (with no-cache middleware and rate limiting)
Route::post('/exam/start', [App\Http\Controllers\ExamController::class, 'startExam'])->name('exam.start')->middleware(['no.cache', 'rate.limit:exam-submit']);
Route::get('/exam', [App\Http\Controllers\ExamController::class, 'getExamInterface'])->name('exam.interface')->middleware('no.cache');
Route::post('/exam/submit-section', [App\Http\Controllers\ExamController::class, 'submitSection'])->name('exam.submit-section')->middleware(['no.cache', 'rate.limit:exam-submit']);
Route::post('/exam/auto-save', [App\Http\Controllers\ExamController::class, 'autoSave'])->name('exam.auto-save')->middleware(['no.cache']);
Route::post('/exam/complete', [App\Http\Controllers\ExamSubmissionController::class, 'completeExam'])->name('exam.complete')->middleware(['no.cache', 'rate.limit:exam-submit']);

// Legacy routes for backward compatibility
Route::post('/exam/submit-answer', function () {
    return redirect('/exam')->with('success', 'Answer submitted (demo)');
})->name('exam.submit-answer');

// Exam Results
Route::get('/exam/results', [App\Http\Controllers\ExamResultsController::class, 'show'])->name('exam.results');

// Pre-Exam Requirements (replaces old privacy consent)
Route::get('/exam/pre-requirements', function (Illuminate\Http\Request $request) {
    $applicantId = $request->session()->get('applicant_id');
    
    if (!$applicantId) {
        return redirect()->route('applicant.login')
            ->with('error', 'Please verify your access code first.');
    }

    try {
        // Load applicant with access code and exam relationship
        $applicant = \App\Models\Applicant::with('accessCode.exam')->findOrFail($applicantId);
        
        // Check if applicant has an access code
        if (!$applicant->accessCode) {
            return redirect()->route('applicant.login')
                ->with('error', 'No access code found. Please contact the administrator.');
        }

        // Check if access code has already been used (check early)
        if ($applicant->accessCode->is_used) {
            return redirect()->route('applicant.login')
                ->with('error', 'This access code has already been used. You cannot retake the exam.');
        }

        // Get the currently active exam (simplified - no assignment needed)
        $exam = \App\Models\Exam::where('is_active', true)->first();
        
        if (!$exam) {
            return redirect()->route('applicant.login')
                ->with('error', 'No active exam is currently available. Please contact the administration office.');
        }

        // Check exam availability (timing window)
        if (!$exam->isAvailable()) {
            return redirect()->route('applicant.login')
                ->with('error', $exam->getAvailabilityMessage());
        }

        $totalQuestions = $exam->activeQuestions()->count();
        $duration = $exam->duration_minutes ?? 30;

        return view('exam.pre-requirements', compact('exam', 'totalQuestions', 'duration'));
    } catch (\Exception $e) {
        return redirect()->route('applicant.login')
            ->with('error', 'An error occurred. Please try again.');
    }
})->name('exam.pre-requirements');

// Basic Information Form (between pre-requirements and exam)
Route::get('/exam/basic-info', [App\Http\Controllers\BasicInfoController::class, 'showBasicInfoForm'])->name('exam.basic-info');
Route::post('/exam/basic-info', [App\Http\Controllers\BasicInfoController::class, 'storeBasicInfo'])->name('exam.basic-info.store');
Route::get('/api/cities-by-province/{province}', [App\Http\Controllers\BasicInfoController::class, 'getCitiesByProvince'])->name('api.cities-by-province');

// Exam Start Form (page before starting exam)
Route::get('/exam/start-form', function (Illuminate\Http\Request $request) {
    $applicantId = $request->session()->get('applicant_id');
    
    if (!$applicantId) {
        return redirect()->route('applicant.login')
            ->with('error', 'Please verify your access code first.');
    }

    try {
        $applicant = \App\Models\Applicant::with(['accessCode', 'basicInfo'])->findOrFail($applicantId);
        
        // Check if basic info is completed
        if (!$applicant->hasCompletedBasicInfo()) {
            return redirect()->route('exam.basic-info')
                ->with('error', 'Please complete the basic information form first.');
        }

        // Check if access code has already been used
        if ($applicant->accessCode && $applicant->accessCode->is_used) {
            return redirect()->route('applicant.login')
                ->with('error', 'This access code has already been used. You cannot retake the exam.');
        }

        $exam = \App\Models\Exam::where('is_active', true)->first();
        
        if (!$exam) {
            return redirect()->route('applicant.login')
                ->with('error', 'No active exam is currently available.');
        }

        if (!$exam->isAvailable()) {
            return redirect()->route('applicant.login')
                ->with('error', $exam->getAvailabilityMessage());
        }

        return view('exam.start-form', compact('applicant', 'exam'));
    } catch (\Exception $e) {
        return redirect()->route('applicant.login')
            ->with('error', 'An error occurred. Please try again.');
    }
})->name('exam.start.form');

// Privacy & Consent (legacy route - redirects to pre-requirements)
Route::get('/privacy/consent', function () {
    return redirect()->route('exam.pre-requirements');
})->name('privacy.consent');

// Public Reports
Route::get('/reports/pdf-preview', function () {
    return view('reports.pdf-preview');
})->name('reports.pdf-preview');

// Team Credits (Easter Egg)
Route::get('/credits', function () {
    return view('credits');
})->name('credits');
