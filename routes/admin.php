<?php

use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\DepartmentHeadController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExamSetController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SetsQuestionsController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| All routes for admin panel functionality including applicant management,
| exams, questions, interviews, and department head features.
| Requires authentication and appropriate role permissions.
|
*/

// Admin Dashboard - Main admin dashboard with stats
Route::get('/dashboard', function () {
    $stats = [
        'total_applicants' => \App\Models\Applicant::count(),
        'exam_completed' => \App\Models\Applicant::where('status', '!=', 'pending')->count(),
        'interviews_scheduled' => \App\Models\Interview::where('status', 'scheduled')->count(),
        'pending_reviews' => \App\Models\Applicant::where('status', 'exam-completed')->count(),
    ];
    
    $recent_applicants = \App\Models\Applicant::with(['examSet', 'accessCode'])
        ->latest()
        ->take(5)
        ->get();
    
    return view('admin.dashboard', compact('stats', 'recent_applicants'));
})->middleware('role:department-head,administrator')->name('dashboard');

// Applicant Management Routes
Route::prefix('applicants')->name('applicants.')->middleware('role:department-head,administrator')->group(function () {
    Route::get('/', [ApplicantController::class, 'index'])->name('index');
    Route::get('/create', [ApplicantController::class, 'create'])->name('create');
    Route::post('/', [ApplicantController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [ApplicantController::class, 'edit'])->name('edit');
    Route::put('/{id}', [ApplicantController::class, 'update'])->name('update');
    Route::delete('/{id}', [ApplicantController::class, 'destroy'])->name('destroy');
    
    // Direct import route for backward compatibility
    Route::get('/import', [ApplicantController::class, 'import'])->name('import');
    
    // Direct template download route for backward compatibility  
    Route::get('/download-template', [ApplicantController::class, 'downloadTemplate'])->name('download-template');
    
    // Bulk Operations
    Route::prefix('bulk')->name('bulk.')->group(function () {
        Route::get('/import', [ApplicantController::class, 'import'])->name('import');
        Route::post('/import', [ApplicantController::class, 'processImport'])->name('process-import');
        Route::post('/generate-access-codes', [ApplicantController::class, 'generateAccessCodes'])->name('generate-access-codes');
        Route::post('/assign-exam-sets', [ApplicantController::class, 'assignExamSets'])->name('assign-exam-sets');
    });
    
    // Export Operations
    Route::prefix('export')->name('export.')->group(function () {
        Route::get('/template', [ApplicantController::class, 'downloadTemplate'])->name('template');
        Route::get('/with-access-codes', [ApplicantController::class, 'exportWithAccessCodes'])->name('with-access-codes');
    });
    
    // Exam Set Assignment Routes
    Route::get('/assign-exam-sets', [ApplicantController::class, 'showExamSetAssignment'])->name('assign-exam-sets');
    Route::post('/assign-exam-sets', [ApplicantController::class, 'processExamSetAssignment'])->name('process-exam-sets');
    Route::get('/assignment-stats', [ApplicantController::class, 'getAssignmentStats'])->name('assignment-stats');

    // API Endpoints
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/eligible-for-interview', [ApplicantController::class, 'getEligibleForInterview'])->name('eligible-for-interview');
    });
    
    // Exam Results Route
    Route::get('/exam-results', [ApplicantController::class, 'examResults'])->name('exam-results');
    
    // Individual Applicant Detail Route
    Route::get('/{id}', [ApplicantController::class, 'show'])->name('show');
});



// Question Management Routes
Route::prefix('questions')->name('questions.')->middleware('role:department-head,administrator')->group(function () {
    Route::get('/', [QuestionController::class, 'index'])->name('index');
    Route::get('/create', [QuestionController::class, 'create'])->name('create');
    Route::post('/', [QuestionController::class, 'store'])->name('store');
    Route::get('/{id}', [QuestionController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [QuestionController::class, 'edit'])->name('edit');
    Route::put('/{id}', [QuestionController::class, 'update'])->name('update');
    Route::delete('/{id}', [QuestionController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/toggle-status', [QuestionController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('/{id}/duplicate', [QuestionController::class, 'duplicate'])->name('duplicate');
    Route::post('/reorder', [QuestionController::class, 'reorder'])->name('reorder');
});

// Sets & Questions Management Routes (Primary Interface)
Route::prefix('sets-questions')->name('sets-questions.')->middleware('role:department-head,administrator')->group(function () {
    Route::get('/', [SetsQuestionsController::class, 'index'])->name('index');
    Route::post('/new-semester', [SetsQuestionsController::class, 'newSemester'])->name('new-semester');
    Route::post('/{id}/publish', [SetsQuestionsController::class, 'publishExam'])->name('publish-exam');
    Route::get('/{id}/consistency-check', [SetsQuestionsController::class, 'consistencyCheck'])->name('consistency-check');
    Route::post('/archive-old', [SetsQuestionsController::class, 'archiveOldExams'])->name('archive-old');
});

// Simplified direct routes - no unnecessary redirects

// Backend CRUD Routes (for AJAX calls from the interface)
Route::prefix('exams')->name('exams.')->middleware('role:department-head,administrator')->group(function () {
    Route::post('/', [ExamController::class, 'store'])->name('store');
    Route::get('/{id}', [ExamController::class, 'show'])->name('show');
    Route::put('/{id}', [ExamController::class, 'update'])->name('update');
    Route::delete('/{id}', [ExamController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/toggle-status', [ExamController::class, 'toggleStatus'])->name('toggle-status');
});

// Exam Set Management Routes (for AJAX calls)
Route::prefix('exam-sets')->name('exam-sets.')->middleware('role:department-head,administrator')->group(function () {
    Route::post('/', [ExamSetController::class, 'store'])->name('store');
    Route::get('/{setId}', [ExamSetController::class, 'show'])->name('show');
    Route::put('/{setId}', [ExamSetController::class, 'update'])->name('update');
    Route::delete('/{setId}', [ExamSetController::class, 'destroy'])->name('destroy');
    Route::post('/{setId}/toggle-status', [ExamSetController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('/{setId}/duplicate', [ExamSetController::class, 'duplicate'])->name('duplicate');
    Route::post('/{setId}/reorder-questions', [ExamSetController::class, 'reorderQuestions'])->name('reorder-questions');
    Route::post('/{setId}/shuffle-questions', [ExamSetController::class, 'shuffleQuestions'])->name('shuffle-questions');
});

// Interview Management Routes
Route::prefix('interviews')->name('interviews.')->middleware('role:department-head,administrator')->group(function () {
    Route::get('/', [InterviewController::class, 'index'])->name('index');
    Route::get('/analytics', [InterviewController::class, 'analytics'])->name('analytics');
    Route::post('/schedule', [InterviewController::class, 'schedule'])->name('schedule');
    Route::post('/bulk-schedule', [InterviewController::class, 'bulkSchedule'])->name('bulk-schedule');
    Route::post('/bulk-assign-instructors', [InterviewController::class, 'bulkAssignToInstructors'])->name('bulk-assign-instructors');
    Route::put('/{interview}', [InterviewController::class, 'update'])->name('update');
    Route::post('/{interview}/cancel', [InterviewController::class, 'cancel'])->name('cancel');
    Route::get('/export', [InterviewController::class, 'export'])->name('export');
    
    // Admin Conduct Interview Routes
    Route::get('/{interview}/conduct', [InterviewController::class, 'adminConductForm'])->name('conduct');
    Route::post('/{interview}/conduct', [InterviewController::class, 'adminConductSubmit'])->name('conduct.submit');
    Route::post('/{interview}/claim', [InterviewController::class, 'adminClaimInterview'])->name('claim');
    Route::post('/{interview}/release', [InterviewController::class, 'adminReleaseInterview'])->name('release');
    
    // Department Head Pool Override Routes
    Route::prefix('pool')->name('pool.')->middleware('role:department-head')->group(function () {
        Route::get('/', [InterviewController::class, 'poolOverview'])->name('overview');
        Route::post('/{interview}/dh-claim', [InterviewController::class, 'dhClaimInterview'])->name('dh-claim');
        Route::post('/{interview}/dh-release', [InterviewController::class, 'dhReleaseInterview'])->name('dh-release');
        Route::post('/assign-to-instructor', [InterviewController::class, 'assignToInstructor'])->name('assign-to-instructor');
        Route::post('/set-priority', [InterviewController::class, 'setPriority'])->name('set-priority');
        Route::post('/bulk-assign', [InterviewController::class, 'bulkAssignToPool'])->name('bulk-assign');
        Route::get('/data', [InterviewController::class, 'getPoolData'])->name('data');
    });
});

// Reports
Route::get('/reports', [ReportsController::class, 'index'])
    ->middleware('role:department-head,administrator')
    ->name('reports');

// User Management (Department Head only)
Route::middleware(['role:department-head'])->prefix('users')->name('users.')->group(function () {
    Route::get('/', [UserManagementController::class, 'index'])->name('index');
    Route::get('/create', [UserManagementController::class, 'create'])->name('create');
    Route::post('/', [UserManagementController::class, 'store'])->name('store');
    Route::get('/{user}', [UserManagementController::class, 'show'])->name('show');
    Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
    Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
    Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
    Route::post('/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('reset-password');
    Route::post('/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('toggle-status');
    Route::get('/export/csv', [UserManagementController::class, 'export'])->name('export');
});

// Department Head Specific Features
Route::get('/department-head-dashboard', [DepartmentHeadController::class, 'dashboard'])
    ->middleware('role:department-head')
    ->name('department-head-dashboard');
Route::get('/interview-results', [DepartmentHeadController::class, 'interviewResults'])
    ->middleware('role:department-head,administrator')
    ->name('interview-results');
Route::get('/interview-detail/{interview}', [DepartmentHeadController::class, 'viewInterviewDetail'])
    ->middleware('role:department-head,administrator')
    ->name('interview-detail');
Route::post('/bulk-admission-decision', [DepartmentHeadController::class, 'bulkAdmissionDecision'])
    ->middleware('role:department-head,administrator')
    ->name('bulk-admission-decision');
Route::get('/analytics', [DepartmentHeadController::class, 'analytics'])
    ->middleware('role:department-head,administrator')
    ->name('analytics');
Route::get('/export-interview-results', [DepartmentHeadController::class, 'exportInterviewResults'])
    ->middleware('role:department-head,administrator')
    ->name('export-interview-results');

// Settings (placeholder)
Route::get('/settings', function () {
    return redirect('/admin/dashboard')->with('info', 'Settings page (demo)');
})->middleware('role:department-head,administrator')->name('settings');
