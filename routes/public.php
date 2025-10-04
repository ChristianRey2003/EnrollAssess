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
Route::get('/exam/pre-requirements', function () {
    return view('exam.pre-requirements');
})->name('exam.pre-requirements');

// Privacy & Consent (legacy route - redirects to pre-requirements)
Route::get('/privacy/consent', function () {
    return redirect()->route('exam.pre-requirements');
})->name('privacy.consent');

// Public Reports
Route::get('/reports/pdf-preview', function () {
    return view('reports.pdf-preview');
})->name('reports.pdf-preview');
