<?php

use App\Http\Controllers\AdminProfileController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentHeadController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SchoolYearController;
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

// School Year Management Routes
Route::prefix('school-year')->name('school-year.')->middleware('role:department-head,administrator')->group(function () {
    Route::post('/switch', [SchoolYearController::class, 'switch'])->name('switch');
});

// Admin Dashboard - Main admin dashboard with stats
Route::get('/dashboard', function (Illuminate\Http\Request $request) {
    $period = (int) $request->get('period', 30);
    
    // Get basic info analytics
    $analyticsService = app(\App\Services\Dashboard\BasicInfoAnalyticsService::class);
    $analytics = $analyticsService->getDashboardAnalytics($period);
    
    return view('admin.dashboard', compact('analytics', 'period'));
})->middleware('role:department-head,administrator')->name('dashboard');

// Routes requiring specific capabilities (Delegation or Role)
// MUST BE BEFORE the main applicants group to avoid matching {id} wildcard
Route::middleware(['auth', 'capability:applicants.assign'])->prefix('applicants')->name('applicants.')->group(function () {
    Route::get('/assign', [ApplicantController::class, 'assignPage'])->name('assign');
    Route::post('/bulk/assign-instructors', [ApplicantController::class, 'bulkAssignInstructors'])->name('bulk.assign-instructors');
});

// Applicant Management Routes
Route::prefix('applicants')->name('applicants.')->middleware(['role:department-head,administrator', 'capability:applicants.view'])->group(function () {
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
    
    // Dedicated Assignment Page (Moved to capability middleware group)
    // Route::get('/assign', [ApplicantController::class, 'assignPage'])->name('assign');
    
    // Bulk Operations (requires bulk_operations capability)
    Route::prefix('bulk')->name('bulk.')->middleware('capability:applicants.bulk_operations')->group(function () {
        Route::get('/import', [ApplicantController::class, 'import'])->name('import');
        Route::post('/import', [ApplicantController::class, 'processImport'])->name('process-import');
        Route::post('/generate-access-codes', [ApplicantController::class, 'generateAccessCodes'])->name('generate-access-codes');
        Route::post('/send-exam-notifications', [ApplicantController::class, 'sendExamNotifications'])->name('send-exam-notifications');
        Route::post('/delete', [ApplicantController::class, 'bulkDelete'])->name('delete');
    });
    
    // Archive Operations - DISABLED: Applicants are now filtered by school year instead of archiving
    // Route::post('/archive-all', [ApplicantController::class, 'archiveAll'])->name('archive-all');
    // Route::get('/archived-history', [ApplicantController::class, 'archivedHistory'])->name('archived-history');
    // Route::post('/restore-all', [ApplicantController::class, 'restoreAll'])->name('restore-all');
    // Route::post('/permanently-delete-all', [ApplicantController::class, 'permanentlyDeleteAll'])->name('permanently-delete-all');
    
    // Exam Assignment Routes
    Route::post('/assign-exam', [ApplicantController::class, 'assignExamToApplicants'])->name('assign-exam');
    Route::post('/{applicant}/assign-exam', [ApplicantController::class, 'assignExamToApplicant'])->name('assign-exam-single');
    
    // Export Operations
    Route::prefix('export')->name('export.')->group(function () {
        Route::get('/template', [ApplicantController::class, 'downloadTemplate'])->name('template');
        Route::get('/with-access-codes', [ApplicantController::class, 'exportWithAccessCodes'])->name('with-access-codes');
        Route::get('/evsu-results', [ApplicantController::class, 'exportEVSUResults'])->name('evsu-results');
        // Department Head Only: Export Access Codes PDF
        Route::get('/access-codes-pdf', [ApplicantController::class, 'exportAccessCodesPDF'])
            ->middleware('role:department-head')
            ->name('access-codes-pdf');
    });


    // API Endpoints
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/eligible-for-interview', [ApplicantController::class, 'getEligibleForInterview'])->name('eligible-for-interview');
    });
    
    // Exam Results Route
    Route::get('/exam-results', [ApplicantController::class, 'examResults'])->name('exam-results');
    
    // Exam Details Route
    Route::get('/{id}/exam-details', [ApplicantController::class, 'showExamDetails'])->name('exam-details');
    
    // Individual Applicant Detail Route
    Route::get('/{id}', [ApplicantController::class, 'show'])->name('show');
});



// Question Management Routes
Route::prefix('questions')->name('questions.')->middleware(['role:department-head,administrator,instructor', 'capability:questions.view'])->group(function () {
    Route::get('/', [QuestionController::class, 'index'])->name('index');
    Route::get('/{id}', [QuestionController::class, 'show'])->name('show');
    
    // Create requires questions.create
    Route::middleware('capability:questions.create')->group(function () {
        Route::get('/create', [QuestionController::class, 'create'])->name('create');
        Route::post('/', [QuestionController::class, 'store'])->name('store');
    });
    
    // Edit requires questions.edit
    Route::middleware('capability:questions.edit')->group(function () {
        Route::get('/{id}/edit', [QuestionController::class, 'edit'])->name('edit');
        Route::put('/{id}', [QuestionController::class, 'update'])->name('update');
        Route::post('/{id}/toggle-status', [QuestionController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{id}/duplicate', [QuestionController::class, 'duplicate'])->name('duplicate');
        Route::post('/reorder', [QuestionController::class, 'reorder'])->name('reorder');
    });
    
    // Delete requires questions.delete
    Route::delete('/{id}', [QuestionController::class, 'destroy'])->middleware('capability:questions.delete')->name('destroy');
});

// Sets & Questions Management Routes (Primary Interface)
Route::prefix('sets-questions')->name('sets-questions.')->middleware(['role:department-head,administrator,instructor', 'capability:questions.view'])->group(function () {
    Route::get('/', [SetsQuestionsController::class, 'index'])->name('index');
    Route::get('/{id}/consistency-check', [SetsQuestionsController::class, 'consistencyCheck'])->name('consistency-check');
    
    // Exam settings management requires questions.manage_exam_settings
    Route::middleware('capability:questions.manage_exam_settings')->group(function () {
        Route::post('/new-semester', [SetsQuestionsController::class, 'newSemester'])->name('new-semester');
        Route::post('/{id}/publish', [SetsQuestionsController::class, 'publishExam'])->name('publish-exam');
        Route::post('/archive-old', [SetsQuestionsController::class, 'archiveOldExams'])->name('archive-old');
        Route::post('/bulk/update-status', [SetsQuestionsController::class, 'bulkUpdateStatus'])->name('bulk-update-status');
        Route::post('/bulk/delete', [SetsQuestionsController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/bulk/duplicate', [SetsQuestionsController::class, 'bulkDuplicate'])->name('bulk-duplicate');
    });
    
    // Import requires questions.create
    Route::middleware('capability:questions.create')->group(function () {
        Route::get('/import/template', [SetsQuestionsController::class, 'downloadTemplate'])->name('import.template');
        Route::post('/import', [SetsQuestionsController::class, 'processImport'])->name('import');
    });
});

// Simplified direct routes - no unnecessary redirects

// Backend CRUD Routes (for AJAX calls from the interface)
Route::prefix('exams')->name('exams.')->middleware(['role:department-head,administrator,instructor', 'capability:questions.view'])->group(function () {
    Route::get('/{id}', [ExamController::class, 'show'])->name('show');
    
    // Exam settings management requires questions.manage_exam_settings
    Route::middleware('capability:questions.manage_exam_settings')->group(function () {
        Route::post('/', [ExamController::class, 'store'])->name('store');
        Route::put('/{id}', [ExamController::class, 'update'])->name('update');
        Route::delete('/{id}', [ExamController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle-status', [ExamController::class, 'toggleStatus'])->name('toggle-status');
    });
});


// Interview Management Routes
Route::prefix('interviews')->name('interviews.')->middleware('role:department-head,administrator')->group(function () {
    Route::get('/', [InterviewController::class, 'index'])->name('index');
    Route::get('/analytics', [InterviewController::class, 'analytics'])->name('analytics');
    Route::post('/schedule', [InterviewController::class, 'schedule'])->name('schedule');
    Route::put('/{interview}', [InterviewController::class, 'update'])->name('update');
    Route::post('/{interview}/cancel', [InterviewController::class, 'cancel'])->name('cancel');
    Route::get('/export', [InterviewController::class, 'export'])->name('export');
    
    // Admin Conduct Interview Routes
    Route::get('/{interview}/conduct', [InterviewController::class, 'adminConductForm'])->name('conduct');
    Route::post('/{interview}/conduct', [InterviewController::class, 'adminConductSubmit'])->name('conduct.submit');
    
    // Interview Detail Page
    Route::get('/{interview_id}', [InterviewController::class, 'show'])->name('show');
});

// Reports
Route::prefix('reports')->name('reports.')->middleware(['role:department-head,administrator,instructor', 'capability:reports.view'])->group(function () {
    Route::get('/', [ReportsController::class, 'index'])->name('index');
    Route::get('/history', [ReportsController::class, 'history'])->name('history');
    Route::get('/stats', [ReportsController::class, 'getStats'])->name('stats');
    Route::get('/signature-settings', [ReportsController::class, 'getSignatureSettings'])->name('signature-settings');
    
    // Generate/Export requires reports.generate
    Route::middleware('capability:reports.generate')->group(function () {
        Route::post('/generate', [ReportsController::class, 'generate'])->name('generate');
        Route::get('/{id}/download', [ReportsController::class, 'download'])->name('download');
        Route::post('/preview', [ReportsController::class, 'preview'])->name('preview');
    });
    
    // Archive management requires reports.manage_archive
    Route::middleware('capability:reports.manage_archive')->group(function () {
        Route::delete('/{id}', [ReportsController::class, 'destroy'])->name('destroy');
        Route::post('/archive-all', [ReportsController::class, 'archiveAll'])->name('archive-all');
        Route::get('/archived-history', [ReportsController::class, 'archivedHistory'])->name('archived-history');
        Route::post('/restore-all', [ReportsController::class, 'restoreAll'])->name('restore-all');
        Route::post('/permanently-delete-all', [ReportsController::class, 'permanentlyDeleteAll'])->name('permanently-delete-all');
        Route::post('/signature-settings', [ReportsController::class, 'updateSignatureSettings'])->name('update-signature-settings');
    });
});

// User Management (Department Head and Administrator)
Route::middleware(['role:department-head,administrator'])->prefix('users')->name('users.')->group(function () {
    Route::get('/', [UserManagementController::class, 'index'])->name('index');
    Route::get('/create', [UserManagementController::class, 'create'])->name('create');
    Route::post('/', [UserManagementController::class, 'store'])->name('store');
    Route::get('/{user}', [UserManagementController::class, 'show'])->name('show');
    Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
    Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
    Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
    Route::post('/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('reset-password');
    Route::post('/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('/{user}/send-credentials', [UserManagementController::class, 'sendCredentials'])->name('send-credentials');
    Route::get('/export/csv', [UserManagementController::class, 'export'])->name('export');
    
    // Delegation Routes
    Route::post('/{user}/delegate', [UserManagementController::class, 'delegate'])->name('delegate');
    Route::post('/{user}/revoke-delegation', [UserManagementController::class, 'revokeDelegation'])->name('revoke-delegation');
});



// Department Head Specific Features
Route::get('/department-head-dashboard', [DepartmentHeadController::class, 'dashboard'])
    ->middleware('role:department-head')
    ->name('department-head-dashboard');
Route::get('/interview-results', [DepartmentHeadController::class, 'interviewResults'])
    ->middleware('role:department-head,administrator')
    ->name('interview-results');

// Legacy interview detail route - 301 redirect to new route
Route::get('/interview-detail/{interview}', function ($interviewId) {
    return redirect()->route('admin.interviews.show', $interviewId, 301);
})->middleware('role:department-head,administrator')->name('interview-detail');

Route::post('/bulk-admission-decision', [DepartmentHeadController::class, 'bulkAdmissionDecision'])
    ->middleware('role:department-head,administrator')
    ->name('bulk-admission-decision');
Route::get('/interview-analytics', [DepartmentHeadController::class, 'analytics'])
    ->middleware('role:department-head,administrator')
    ->name('interview-analytics');
Route::get('/export-interview-results', [DepartmentHeadController::class, 'exportInterviewResults'])
    ->middleware('role:department-head,administrator')
    ->name('export-interview-results');

// Settings
Route::middleware(['role:department-head,administrator'])->prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [\App\Http\Controllers\SettingsController::class, 'index'])->name('index');
    Route::put('/', [\App\Http\Controllers\SettingsController::class, 'update'])->name('update');
    Route::post('/test-email', [\App\Http\Controllers\SettingsController::class, 'testEmail'])->name('test-email');
    Route::post('/reset', [\App\Http\Controllers\SettingsController::class, 'reset'])->name('reset');
    Route::get('/archived-questions', [\App\Http\Controllers\SettingsController::class, 'archivedQuestions'])->middleware('capability:questions.view')->name('archived-questions');
    Route::post('/restore-archived-questions', [\App\Http\Controllers\SettingsController::class, 'restoreArchivedQuestions'])->middleware('capability:questions.edit')->name('restore-archived-questions');
    
    // School Year Management Routes
    Route::prefix('school-years')->name('school-years.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SchoolYearController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\SchoolYearController::class, 'store'])->name('store');
        Route::put('/{schoolYear}', [\App\Http\Controllers\SchoolYearController::class, 'update'])->name('update');
        Route::delete('/{schoolYear}', [\App\Http\Controllers\SchoolYearController::class, 'destroy'])->name('destroy');
        Route::post('/{schoolYear}/set-current', [\App\Http\Controllers\SchoolYearController::class, 'setAsCurrent'])->name('set-current');
    });
});

// Profile Routes
Route::middleware(['role:department-head,administrator'])->prefix('profile')->name('profile.')->group(function () {
    Route::get('/edit', [AdminProfileController::class, 'edit'])->name('edit');
    Route::put('/update', [AdminProfileController::class, 'update'])->name('update');
    Route::post('/delete-picture', [AdminProfileController::class, 'deleteProfilePicture'])->name('delete-picture');
});

// Dashboard API Routes (Real-time)
Route::prefix('api/dashboard')->name('api.dashboard.')->middleware('role:department-head,administrator')->group(function () {
    Route::get('/stats', [DashboardController::class, 'getLiveStats'])->name('stats');
    Route::get('/activity', [DashboardController::class, 'getRecentActivity'])->name('activity');
    Route::get('/charts/{type}', [DashboardController::class, 'getChartData'])->name('charts');
    Route::get('/health', [DashboardController::class, 'getSystemHealth'])->name('health');
});

// Analytics Dashboard Routes (New Phase 2 Feature)
Route::prefix('analytics-dashboard')->name('analytics.')->middleware('role:department-head,administrator')->group(function () {
    Route::get('/', [AnalyticsController::class, 'index'])->name('index');
    Route::get('/score-distribution', [AnalyticsController::class, 'getScoreDistribution'])->name('score-distribution');
    Route::get('/performance-trends', [AnalyticsController::class, 'getPerformanceTrends'])->name('performance-trends');
    Route::get('/conversion-funnel', [AnalyticsController::class, 'getConversionFunnel'])->name('conversion-funnel');
    Route::get('/instructor-workload', [AnalyticsController::class, 'getInstructorWorkload'])->name('instructor-workload');
    Route::get('/time-to-completion', [AnalyticsController::class, 'getTimeToCompletion'])->name('time-to-completion');
    Route::get('/category-performance', [AnalyticsController::class, 'getCategoryPerformance'])->name('category-performance');
    Route::get('/export', [AnalyticsController::class, 'export'])->name('export');
});

// Audit Logs Routes (Superadmin only)
Route::prefix('audit-logs')->name('audit-logs.')->middleware('role:administrator')->group(function () {
    Route::get('/', [\App\Http\Controllers\AuditLogController::class, 'index'])->name('index');
});

// Notifications Routes
Route::prefix('notifications')->name('notifications.')->middleware('role:department-head,administrator,instructor')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    Route::delete('/', [NotificationController::class, 'clearAll'])->name('clear-all');
});
