<?php

use App\Http\Controllers\Auth\AdminAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| Custom authentication routes for the EnrollAssess system.
| Includes admin and applicant authentication flows.
|
*/

// Admin Authentication
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
});

// Applicant Authentication
Route::prefix('applicant')->name('applicant.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showApplicantLogin'])->name('login');
    Route::post('/verify', [AdminAuthController::class, 'verifyAccessCode'])->name('verify');
});

// Legacy Route Redirects
Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

Route::get('/register', function () {
    return redirect()->route('admin.login')->with('error', 'Registration is not available. Please contact the administrator.');
})->name('register');

// Legacy logout route redirect
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');