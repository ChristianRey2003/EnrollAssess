<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExamSetController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\InstructorController;
use Illuminate\Support\Facades\Route;

// Welcome page - redirect to applicant login
Route::get('/', function () {
    return redirect()->route('applicant.login');
});

// Authentication routes
Route::get('/admin/login', [App\Http\Controllers\Auth\AdminAuthController::class, 'showLoginForm'])
    ->name('admin.login');

Route::post('/admin/login', [App\Http\Controllers\Auth\AdminAuthController::class, 'login'])
    ->name('admin.login.submit');

Route::post('/admin/logout', [App\Http\Controllers\Auth\AdminAuthController::class, 'logout'])
    ->name('admin.logout');

// Applicant authentication
Route::get('/applicant/login', [App\Http\Controllers\Auth\AdminAuthController::class, 'showApplicantLogin'])
    ->name('applicant.login');

Route::post('/applicant/verify', [App\Http\Controllers\Auth\AdminAuthController::class, 'verifyAccessCode'])
    ->name('applicant.verify');

// Protected Admin Routes (Department Head & Administrator only)
Route::middleware(['auth', 'role:department-head,administrator'])->prefix('admin')->name('admin.')->group(function () {
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

    Route::get('/questions', [QuestionController::class, 'index'])->name('questions');

    // Applicant management routes
    Route::get('/applicants', [ApplicantController::class, 'index'])->name('applicants');
    Route::get('/applicants/create', [ApplicantController::class, 'create'])->name('applicants.create');
    Route::post('/applicants', [ApplicantController::class, 'store'])->name('applicants.store');
    Route::get('/applicants/{id}', [ApplicantController::class, 'show'])->name('applicants.show');
    Route::get('/applicants/{id}/edit', [ApplicantController::class, 'edit'])->name('applicants.edit');
    Route::put('/applicants/{id}', [ApplicantController::class, 'update'])->name('applicants.update');
    Route::delete('/applicants/{id}', [ApplicantController::class, 'destroy'])->name('applicants.destroy');
    
    // Bulk applicant operations
    Route::get('/applicants-import', [ApplicantController::class, 'import'])->name('applicants.import');
    Route::post('/applicants-import', [ApplicantController::class, 'processImport'])->name('applicants.process-import');
    Route::get('/applicants/template/download', [ApplicantController::class, 'downloadTemplate'])->name('applicants.download-template');
    Route::post('/applicants/generate-access-codes', [ApplicantController::class, 'generateAccessCodes'])->name('applicants.generate-access-codes');
    Route::post('/applicants/assign-exam-sets', [ApplicantController::class, 'assignExamSets'])->name('applicants.assign-exam-sets');
    Route::get('/applicants/export/with-access-codes', [ApplicantController::class, 'exportWithAccessCodes'])->name('applicants.export-with-access-codes');

    Route::get('/reports', [ReportsController::class, 'index'])->name('reports');

    // Question management routes
    Route::get('/questions/create', [QuestionController::class, 'create'])->name('questions.create');
    Route::post('/questions/store', [QuestionController::class, 'store'])->name('questions.store');
    Route::get('/questions/{id}', [QuestionController::class, 'show'])->name('questions.show');
    Route::get('/questions/edit/{id}', [QuestionController::class, 'edit'])->name('questions.edit');
    Route::put('/questions/{id}', [QuestionController::class, 'update'])->name('questions.update');
    Route::delete('/questions/{id}', [QuestionController::class, 'destroy'])->name('questions.destroy');
    Route::post('/questions/{id}/toggle-status', [QuestionController::class, 'toggleStatus'])->name('questions.toggle-status');
    Route::post('/questions/reorder', [QuestionController::class, 'reorder'])->name('questions.reorder');

    // Exam management routes
    Route::get('/exams', [ExamController::class, 'index'])->name('exams.index');
    Route::get('/exams/create', [ExamController::class, 'create'])->name('exams.create');
    Route::post('/exams', [ExamController::class, 'store'])->name('exams.store');
    Route::get('/exams/{id}', [ExamController::class, 'show'])->name('exams.show');
    Route::get('/exams/{id}/edit', [ExamController::class, 'edit'])->name('exams.edit');
    Route::put('/exams/{id}', [ExamController::class, 'update'])->name('exams.update');
    Route::delete('/exams/{id}', [ExamController::class, 'destroy'])->name('exams.destroy');
    Route::post('/exams/{id}/toggle-status', [ExamController::class, 'toggleStatus'])->name('exams.toggle-status');
    Route::post('/exams/{id}/duplicate', [ExamController::class, 'duplicate'])->name('exams.duplicate');

    // Exam Set management routes
    Route::get('/exams/{examId}/sets', [ExamSetController::class, 'index'])->name('exam-sets.index');
    Route::get('/exams/{examId}/sets/create', [ExamSetController::class, 'create'])->name('exam-sets.create');
    Route::post('/exams/{examId}/sets', [ExamSetController::class, 'store'])->name('exam-sets.store');
    Route::get('/exams/{examId}/sets/{setId}', [ExamSetController::class, 'show'])->name('exam-sets.show');
    Route::get('/exams/{examId}/sets/{setId}/edit', [ExamSetController::class, 'edit'])->name('exam-sets.edit');
    Route::put('/exams/{examId}/sets/{setId}', [ExamSetController::class, 'update'])->name('exam-sets.update');
    Route::delete('/exams/{examId}/sets/{setId}', [ExamSetController::class, 'destroy'])->name('exam-sets.destroy');
    Route::post('/exams/{examId}/sets/{setId}/toggle-status', [ExamSetController::class, 'toggleStatus'])->name('exam-sets.toggle-status');
    Route::post('/exams/{examId}/sets/{setId}/duplicate', [ExamSetController::class, 'duplicate'])->name('exam-sets.duplicate');
    Route::get('/exams/{examId}/sets/{setId}/add-questions', [ExamSetController::class, 'addQuestions'])->name('exam-sets.add-questions');
    Route::post('/exams/{examId}/sets/{setId}/reorder-questions', [ExamSetController::class, 'reorderQuestions'])->name('exam-sets.reorder-questions');

    // Applicant detail route
    Route::get('/applicants/{id}', function ($id) {
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
    })->name('applicants.show');

    // User Management (RBAC)
    Route::get('/users', function () {
        return view('admin.users');
    })->name('users');

    // Settings
    Route::get('/settings', function () {
        return redirect('/admin/dashboard')->with('info', 'Settings page (demo)');
    })->name('settings');
});

// Protected Instructor Routes (Instructor role only)
Route::middleware(['auth', 'role:instructor'])->prefix('instructor')->name('instructor.')->group(function () {
    // Instructor Dashboard
    Route::get('/dashboard', [InstructorController::class, 'dashboard'])->name('dashboard');
    
    // Assigned Applicants
    Route::get('/applicants', [InstructorController::class, 'applicants'])->name('applicants');
    
    // Interview Management
    Route::get('/applicants/{applicant}/interview', [InstructorController::class, 'showInterview'])->name('interview.show');
    Route::post('/applicants/{applicant}/interview', [InstructorController::class, 'submitInterview'])->name('interview.submit');
});

// Exam interface route
Route::get('/exam', function () {
    return view('exam.interface');
})->name('exam.interface');

// Non-admin routes

// Exam results route
Route::get('/exam/results', function () {
    return view('exam.results');
})->name('exam.results');

// Data Privacy Consent Page (must be shown before exam)
Route::get('/privacy/consent', function () {
    return view('privacy.consent');
})->name('privacy.consent');

// Final PDF Report Preview
Route::get('/reports/pdf-preview', function () {
    return view('reports.pdf-preview');
})->name('reports.pdf-preview');

Route::post('/exam/submit-answer', function () {
    return redirect('/exam')->with('success', 'Answer submitted (demo)');
})->name('exam.submit-answer');

// Default Laravel auth routes removed - using custom EnrollAssess authentication system
// require __DIR__.'/auth.php';

// Redirect legacy routes to our custom login
Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

Route::get('/register', function () {
    return redirect()->route('admin.login')->with('error', 'Registration is not available. Please contact the administrator.');
})->name('register');

// Legacy logout route redirect to admin logout controller
Route::post('/logout', [App\Http\Controllers\Auth\AdminAuthController::class, 'logout'])
    ->name('logout');
