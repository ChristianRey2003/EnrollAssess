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

// Welcome page - redirect to applicant login
Route::get('/', function () {
    return redirect()->route('applicant.login');
});

// Exam Interface - Sectioned Exam
Route::post('/exam/start', [App\Http\Controllers\ExamController::class, 'startExam'])->name('exam.start');
Route::get('/exam', [App\Http\Controllers\ExamController::class, 'getExamInterface'])->name('exam.interface');
Route::post('/exam/submit-section', [App\Http\Controllers\ExamController::class, 'submitSection'])->name('exam.submit-section');
Route::post('/exam/complete', [App\Http\Controllers\ExamSubmissionController::class, 'completeExam'])->name('exam.complete');

// Legacy routes for backward compatibility
Route::post('/exam/submit-answer', function () {
    return redirect('/exam')->with('success', 'Answer submitted (demo)');
})->name('exam.submit-answer');

// Exam Results
Route::get('/exam/results', function () {
    return view('exam.results');
})->name('exam.results');

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

        // Check if exam is assigned to the access code
        if (!$applicant->accessCode->exam_id || !$applicant->accessCode->exam) {
            return redirect()->route('applicant.login')
                ->with('error', 'No exam assigned. Please contact the administrator.');
        }

        $exam = $applicant->accessCode->exam;

        // Check if access code has already been used
        if ($applicant->accessCode->is_used) {
            return redirect()->route('applicant.login')
                ->with('error', 'This access code has already been used. You cannot retake the exam.');
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

// Privacy & Consent (legacy route - redirects to pre-requirements)
Route::get('/privacy/consent', function () {
    return redirect()->route('exam.pre-requirements');
})->name('privacy.consent');

// Public Reports
Route::get('/reports/pdf-preview', function () {
    return view('reports.pdf-preview');
})->name('reports.pdf-preview');
