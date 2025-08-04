<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Applicant login route
Route::get('/applicant/login', function () {
    return view('auth.applicant-login');
})->name('applicant.login');

// Admin routes for viewing the new UI pages
Route::get('/admin/login', function () {
    return view('auth.admin-login');
})->name('admin.login');

Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');

Route::get('/admin/questions', function () {
    return view('admin.questions');
})->name('admin.questions');

Route::get('/admin/applicants', function () {
    return view('admin.applicants');
})->name('admin.applicants');

Route::get('/admin/reports', function () {
    return view('admin.reports');
})->name('admin.reports');

// Exam interface route
Route::get('/exam', function () {
    return view('exam.interface');
})->name('exam.interface');

// Additional routes for form handling (placeholder routes for demo)
Route::get('/admin/questions/create', function () {
    return view('admin.questions.create');
})->name('admin.questions.create');

Route::post('/admin/questions/store', function () {
    return redirect('/admin/questions')->with('success', 'Question created successfully (demo)');
})->name('admin.questions.store');

Route::get('/admin/questions/edit/{id}', function ($id) {
    return view('admin.questions.create', ['question' => (object)['id' => $id]]);
})->name('admin.questions.edit');

Route::put('/admin/questions/{id}', function ($id) {
    return redirect('/admin/questions')->with('success', "Question #{$id} updated successfully (demo)");
})->name('admin.questions.update');

// Exam results route
Route::get('/exam/results', function () {
    return view('exam.results');
})->name('exam.results');

Route::get('/admin/applicants/{id}', function ($id) {
    return view('admin.applicants.show', ['applicant' => (object)['id' => $id, 'name' => 'John Doe']]);
})->name('admin.applicants.show');

Route::get('/admin/settings', function () {
    return redirect('/admin/dashboard')->with('info', 'Settings page (demo)');
})->name('admin.settings');

Route::post('/exam/submit-answer', function () {
    return redirect('/exam')->with('success', 'Answer submitted (demo)');
})->name('exam.submit-answer');

// Login/logout routes for demo
Route::post('/login', function () {
    return redirect('/admin/dashboard')->with('success', 'Logged in successfully (demo)');
})->name('login');

Route::post('/logout', function () {
    return redirect('/admin/login')->with('success', 'Logged out successfully (demo)');
})->name('logout');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
