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

// Exam Interface
Route::get('/exam', function () {
    return view('exam.interface');
})->name('exam.interface');

Route::post('/exam/submit-answer', function () {
    return redirect('/exam')->with('success', 'Answer submitted (demo)');
})->name('exam.submit-answer');

// Exam Results
Route::get('/exam/results', function () {
    return view('exam.results');
})->name('exam.results');

// Privacy & Consent
Route::get('/privacy/consent', function () {
    return view('privacy.consent');
})->name('privacy.consent');

// Public Reports
Route::get('/reports/pdf-preview', function () {
    return view('reports.pdf-preview');
})->name('reports.pdf-preview');
