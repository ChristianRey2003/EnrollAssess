<?php

use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\DepartmentHeadController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExamSetController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ReportsController;
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

// Admin Dashboard
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
})->name('dashboard');

// Applicant Management Routes
Route::prefix('applicants')->name('applicants.')->group(function () {
    Route::get('/', [ApplicantController::class, 'index'])->name('index');
    Route::get('/create', [ApplicantController::class, 'create'])->name('create');
    Route::post('/', [ApplicantController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [ApplicantController::class, 'edit'])->name('edit');
    Route::put('/{id}', [ApplicantController::class, 'update'])->name('update');
    Route::delete('/{id}', [ApplicantController::class, 'destroy'])->name('destroy');
    
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
    
    // API Endpoints
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/eligible-for-interview', [ApplicantController::class, 'getEligibleForInterview'])->name('eligible-for-interview');
    });
    
    // Individual Applicant Detail Route (with complex logic)
    Route::get('/{id}', function ($id) {
        $applicant = \App\Models\Applicant::with(['examSet', 'accessCode', 'latestInterview.interviewer', 'results.question'])
            ->findOrFail($id);
        
        // Calculate additional data for the view
        $applicant->name = $applicant->full_name;
        $applicant->email = $applicant->email_address;
        $applicant->phone = $applicant->phone_number;
        $applicant->education = $applicant->education_background;
        $applicant->overall_status = ucfirst(str_replace('-', ' ', $applicant->status));
        $applicant->student_id = $applicant->application_no;
        
        // Exam data
        $applicant->exam_completed = $applicant->hasCompletedExam();
        $applicant->exam_score = $applicant->exam_percentage ?? $applicant->score ?? 0;
        
        // Get exam results if available
        if ($applicant->results->count() > 0) {
            $totalQuestions = $applicant->results->count();
            $correctAnswers = $applicant->results->where('is_correct', true)->count();
            
            $applicant->correct_answers = $correctAnswers;
            $applicant->total_questions = $totalQuestions;
            $applicant->exam_duration = '24 minutes 30 seconds';
            
            // Category scores (simplified for demo)
            $applicant->category_scores = [
                ['name' => 'Programming Logic', 'score' => 90, 'correct' => 9, 'total' => 10],
                ['name' => 'Mathematics', 'score' => 85, 'correct' => 4, 'total' => 5],
                ['name' => 'Problem Solving', 'score' => 80, 'correct' => 3, 'total' => 4],
                ['name' => 'Computer Fundamentals', 'score' => 85, 'correct' => 3, 'total' => 4],
                ['name' => 'English Proficiency', 'score' => 88, 'correct' => 3, 'total' => 3]
            ];
        } else {
            $applicant->correct_answers = 0;
            $applicant->total_questions = 20;
            $applicant->category_scores = [];
        }
        
        // Interview data
        $interview = $applicant->latestInterview;
        $applicant->interview_status = $interview ? $interview->status : 'not-scheduled';
        $applicant->interview_date = $interview ? $interview->schedule_date->format('Y-m-d') : null;
        $applicant->interview_time = $interview ? $interview->schedule_date->format('H:i') : null;
        $applicant->interviewer = $interview ? 'dr-' . strtolower(str_replace(' ', '-', $interview->interviewer->full_name ?? 'smith')) : 'dr-smith';
        $applicant->private_notes = $interview ? $interview->notes : 'No interview notes available.';
        $applicant->final_recommendation = $interview ? $interview->recommendation : 'pending';
        
        // Timeline
        $applicant->timeline = [
            ['date' => $applicant->created_at->format('M d, Y'), 'time' => $applicant->created_at->format('g:i A'), 'event' => 'Application submitted successfully', 'type' => 'application'],
            ['date' => $applicant->created_at->addDays(2)->format('M d, Y'), 'time' => '2:15 PM', 'event' => 'Documents verified and approved', 'type' => 'update'],
        ];
        
        if ($applicant->exam_completed_at) {
            $applicant->timeline[] = ['date' => $applicant->exam_completed_at->format('M d, Y'), 'time' => $applicant->exam_completed_at->format('g:i A'), 'event' => 'Entrance exam completed with ' . $applicant->exam_score . '% score', 'type' => 'exam'];
        }
        
        if ($interview && $interview->status === 'scheduled') {
            $applicant->timeline[] = ['date' => $interview->schedule_date->format('M d, Y'), 'time' => $interview->schedule_date->format('g:i A'), 'event' => 'Interview scheduled with ' . ($interview->interviewer->full_name ?? 'Dr. Smith'), 'type' => 'interview'];
        }
        
        return view('admin.applicants.show', compact('applicant'));
    })->name('show');
});



// Question Management Routes
Route::prefix('questions')->name('questions.')->group(function () {
    Route::get('/', [QuestionController::class, 'index'])->name('index');
    Route::get('/create', [QuestionController::class, 'create'])->name('create');
    Route::post('/', [QuestionController::class, 'store'])->name('store');
    Route::get('/{id}', [QuestionController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [QuestionController::class, 'edit'])->name('edit');
    Route::put('/{id}', [QuestionController::class, 'update'])->name('update');
    Route::delete('/{id}', [QuestionController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/toggle-status', [QuestionController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('/reorder', [QuestionController::class, 'reorder'])->name('reorder');
});

// Exam Management Routes
Route::prefix('exams')->name('exams.')->group(function () {
    Route::get('/', [ExamController::class, 'index'])->name('index');
    Route::get('/create', [ExamController::class, 'create'])->name('create');
    Route::post('/', [ExamController::class, 'store'])->name('store');
    Route::get('/{id}', [ExamController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [ExamController::class, 'edit'])->name('edit');
    Route::put('/{id}', [ExamController::class, 'update'])->name('update');
    Route::delete('/{id}', [ExamController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/toggle-status', [ExamController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('/{id}/duplicate', [ExamController::class, 'duplicate'])->name('duplicate');
    
});

// Exam Set Management Routes (standalone with exam context)
Route::prefix('exam-sets')->name('exam-sets.')->group(function () {
    Route::get('/{examId}', [ExamSetController::class, 'index'])->name('index');
    Route::get('/{examId}/create', [ExamSetController::class, 'create'])->name('create');
    Route::post('/{examId}', [ExamSetController::class, 'store'])->name('store');
    Route::get('/{examId}/{setId}', [ExamSetController::class, 'show'])->name('show');
    Route::get('/{examId}/{setId}/edit', [ExamSetController::class, 'edit'])->name('edit');
    Route::put('/{examId}/{setId}', [ExamSetController::class, 'update'])->name('update');
    Route::delete('/{examId}/{setId}', [ExamSetController::class, 'destroy'])->name('destroy');
    Route::post('/{examId}/{setId}/toggle-status', [ExamSetController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('/{examId}/{setId}/duplicate', [ExamSetController::class, 'duplicate'])->name('duplicate');
    Route::get('/{examId}/{setId}/add-questions', [ExamSetController::class, 'addQuestions'])->name('add-questions');
    Route::post('/{examId}/{setId}/reorder-questions', [ExamSetController::class, 'reorderQuestions'])->name('reorder-questions');
});

// Interview Management Routes
Route::prefix('interviews')->name('interviews.')->group(function () {
    Route::get('/', [InterviewController::class, 'index'])->name('index');
    Route::get('/analytics', [InterviewController::class, 'analytics'])->name('analytics');
    Route::post('/schedule', [InterviewController::class, 'schedule'])->name('schedule');
    Route::post('/bulk-schedule', [InterviewController::class, 'bulkSchedule'])->name('bulk-schedule');
    Route::post('/bulk-assign-instructors', [InterviewController::class, 'bulkAssignToInstructors'])->name('bulk-assign-instructors');
    Route::put('/{interview}', [InterviewController::class, 'update'])->name('update');
    Route::post('/{interview}/cancel', [InterviewController::class, 'cancel'])->name('cancel');
    Route::get('/export', [InterviewController::class, 'export'])->name('export');
});

// Reports
Route::get('/reports', [ReportsController::class, 'index'])->name('reports');

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
Route::get('/interview-results', [DepartmentHeadController::class, 'interviewResults'])->name('interview-results');
Route::get('/interview-detail/{interview}', [DepartmentHeadController::class, 'viewInterviewDetail'])->name('interview-detail');
Route::post('/bulk-admission-decision', [DepartmentHeadController::class, 'bulkAdmissionDecision'])->name('bulk-admission-decision');
Route::get('/analytics', [DepartmentHeadController::class, 'analytics'])->name('analytics');
Route::get('/export-interview-results', [DepartmentHeadController::class, 'exportInterviewResults'])->name('export-interview-results');

// Settings (placeholder)
Route::get('/settings', function () {
    return redirect('/admin/dashboard')->with('info', 'Settings page (demo)');
})->name('settings');
