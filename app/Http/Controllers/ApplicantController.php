<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\AccessCode;
use App\Models\Exam;
use App\Models\User;
use App\Models\Interview;
use App\Services\ApplicantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Exception;

/**
 * Applicant Management Controller
 * 
 * Handles CRUD operations for applicants, access code generation,
 * exam set assignment, and bulk operations.
 */
class ApplicantController extends BaseController
{
    /**
     * Applicant service instance
     */
    protected ?ApplicantService $applicantService = null;

    /**
     * Constructor - inject dependencies
     */
    public function __construct()
    {
        // Service will be instantiated when needed to avoid circular dependencies
    }

    /**
     * Display a listing of applicants
     *
     * @param Request $request
     * @return View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        try {
            $query = Applicant::with(['assignedInstructor', 'accessCode', 'accessCode.exam']);

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('middle_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email_address', 'like', "%{$search}%")
                      ->orWhere('application_no', 'like', "%{$search}%")
                      ->orWhere('preferred_course', 'like', "%{$search}%");
                });
            }

            // Status filter
            if ($request->filled('status')) {
                $query->where('status', $request->get('status'));
            }

            // Instructor filter
            if ($request->filled('instructor_id')) {
                if ($request->instructor_id === 'unassigned') {
                    $query->whereNull('assigned_instructor_id');
                } else {
                    $query->where('assigned_instructor_id', $request->get('instructor_id'));
                }
            }

            $applicants = $query->orderBy('created_at', 'desc')->paginate(20);

            // Statistics
            $stats = [
                'total' => Applicant::count(),
                'pending' => Applicant::where('status', 'pending')->count(),
                'exam_completed' => Applicant::where('status', '!=', 'pending')->count(),
                'with_access_codes' => Applicant::whereHas('accessCode')->count(),
                'without_access_codes' => Applicant::whereDoesntHave('accessCode')->count(),
                'assigned_to_instructor' => Applicant::whereNotNull('assigned_instructor_id')->count(),
                'unassigned_instructor' => Applicant::whereNull('assigned_instructor_id')->count(),
            ];

            $instructors = User::where('role', 'instructor')->get();

            $exams = Exam::where('is_active', true)->get();
            
            return view('admin.applicants.index', compact('applicants', 'stats', 'instructors', 'exams'));
        } catch (Exception $e) {
            \Log::error('Applicants index failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to load applicants. Please try again. Error: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new applicant
     */
    public function create()
    {
        $instructors = User::where('role', 'instructor')->get();
        return view('admin.applicants.create', compact('instructors'));
    }


    /**
     * Store a newly created applicant
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'preferred_course' => 'nullable|string|max:255',
            'email_address' => 'required|email|unique:applicants,email_address',
            'phone_number' => 'nullable|string|max:20',
            'assigned_instructor_id' => 'nullable|exists:users,user_id',
            'score' => 'nullable|numeric|min:0|max:100',
            'status' => 'nullable|in:pending,exam-completed,interview-scheduled,interview-completed,admitted,rejected',
            'verbal_description' => 'nullable|string|max:255',
            'generate_access_code' => 'boolean',
        ]);

        try {
            DB::transaction(function () use ($validated, $request) {
                // Generate application number
                $validated['application_no'] = Applicant::generateApplicationNumber();

                // Create applicant
                $applicant = Applicant::create($validated);

                // Generate access code if requested
                if ($request->boolean('generate_access_code')) {
                    AccessCode::createForApplicant(
                        $applicant->applicant_id,
                        'BSIT',
                        8,
                        72 // 72 hours expiration
                    );
                }

                // Create interview record if instructor assigned
                if ($request->filled('assigned_instructor_id')) {
                    Interview::create([
                        'applicant_id' => $applicant->applicant_id,
                        'interviewer_id' => $request->assigned_instructor_id,
                        'status' => 'scheduled',
                    ]);
                }
            });

            return redirect()->route('admin.applicants.index')
                            ->with('success', 'Applicant created successfully!');
        } catch (\Exception $e) {
            \Log::error('Applicant creation failed: ' . $e->getMessage());
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Failed to create applicant: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified applicant
     */
    public function show($id)
    {
        try {
            $applicant = Applicant::with(['assignedInstructor', 'accessCode', 'latestInterview.interviewer', 'results.question'])
                ->findOrFail($id);
            
            // Calculate additional data for the view (simplified from route logic)
            $applicant->name = $applicant->full_name;
            $applicant->email = $applicant->email_address;
            $applicant->phone = $applicant->phone_number;
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
                
                // Category scores (demo data)
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
            $applicant->interview_date = $interview && $interview->schedule_date ? $interview->schedule_date->format('Y-m-d') : null;
            $applicant->interview_time = $interview && $interview->schedule_date ? $interview->schedule_date->format('H:i') : null;
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
            
            if ($interview && $interview->status === 'scheduled' && $interview->schedule_date) {
                $applicant->timeline[] = ['date' => $interview->schedule_date->format('M d, Y'), 'time' => $interview->schedule_date->format('g:i A'), 'event' => 'Interview scheduled with ' . ($interview->interviewer->full_name ?? 'Dr. Smith'), 'type' => 'interview'];
            }
            
            return view('admin.applicants.show', compact('applicant'));
        } catch (Exception $e) {
            return back()->with('error', 'Applicant not found.');
        }
    }

    /**
     * Show the form for editing the specified applicant
     */
    public function edit($id)
    {
        $applicant = Applicant::with(['assignedInstructor', 'accessCode'])->findOrFail($id);
        $instructors = User::where('role', 'instructor')->get();
        
        return view('admin.applicants.create', compact('applicant', 'instructors'));
    }

    /**
     * Update the specified applicant
     */
    public function update(Request $request, $id)
    {
        $applicant = Applicant::findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'preferred_course' => 'nullable|string|max:255',
            'email_address' => 'required|email|unique:applicants,email_address,' . $id . ',applicant_id',
            'phone_number' => 'nullable|string|max:20',
            'assigned_instructor_id' => 'nullable|exists:users,user_id',
            'status' => 'required|in:pending,exam-completed,interview-scheduled,interview-completed,admitted,rejected',
            'score' => 'nullable|numeric|min:0|max:9999.99',
            'verbal_description' => 'nullable|string|max:255',
        ]);

        $oldInstructorId = $applicant->assigned_instructor_id;
        $applicant->update($validated);

        // If instructor assignment changed, update or create interview record
        if ($request->filled('assigned_instructor_id') && $oldInstructorId != $request->assigned_instructor_id) {
            $interview = $applicant->latestInterview;
            if ($interview) {
                $interview->update(['interviewer_id' => $request->assigned_instructor_id]);
            } else {
                Interview::create([
                    'applicant_id' => $applicant->applicant_id,
                    'interviewer_id' => $request->assigned_instructor_id,
                    'status' => 'scheduled',
                ]);
            }
        }

        return redirect()->route('admin.applicants.index')
                        ->with('success', 'Applicant updated successfully!');
    }

    /**
     * Remove the specified applicant
     */
    public function destroy($id)
    {
        $applicant = Applicant::findOrFail($id);
        $applicant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Applicant deleted successfully!'
        ]);
    }

    /**
     * Show bulk import interface
     */
    public function import()
    {
        $instructors = User::where('role', 'instructor')->get();
        return view('admin.applicants.import', compact('instructors'));
    }

    /**
     * Process bulk import from CSV
     */
    public function processImport(Request $request)
    {
        // Manual validation to ensure JSON response for AJAX
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
            'assigned_instructor_id' => 'nullable|exists:users,user_id',
            // accept '1'/'0', 'true'/'false', true/false
            'generate_access_codes' => 'nullable|in:1,0,true,false,TRUE,FALSE',
            'access_code_expiry_hours' => 'nullable|integer|min:1|max:8760', // Max 365 days
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        try {
            $file = $request->file('csv_file');
            $csvContent = file_get_contents($file->getPathname());
            $lines = explode("\n", $csvContent);
            
            if (count($lines) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'CSV file must contain at least a header row and one data row.'
                ], 422);
            }

            // Parse header
            // Normalize header: strip BOM, trim whitespace
            $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $lines[0]);
            $header = array_map(function($h){ return trim($h, " \t\n\r\0\x0B\"'"); }, str_getcsv($firstLine));
            
            // Header mapping for official template
            $headerMapping = [
                // Official template headers
                'First Name' => 'first_name',
                'Middle Name' => 'middle_name', 
                'Last Name' => 'last_name',
                'Preferred Course' => 'preferred_course',
                'E-mail' => 'email_address',
                'Contact #' => 'phone_number',
                'Applicant No.' => 'application_no',
                'Weighted Exam Percentage (60%)' => 'score',
                'Weighted Exam % (60%)' => 'score',
                'Verbal Description' => 'verbal_description',
                // Legacy support
                'first_name' => 'first_name',
                'middle_name' => 'middle_name',
                'last_name' => 'last_name', 
                'preferred_course' => 'preferred_course',
                'email_address' => 'email_address',
                'phone_number' => 'phone_number',
                'application_no' => 'application_no',
                'score' => 'score',
                'verbal_description' => 'verbal_description',
            ];
            
            $importResults = [
                'total' => 0,
                'successful' => 0,
                'failed' => 0,
                'errors' => [],
                'imported_applicants' => [],
            ];

            DB::transaction(function () use ($lines, $header, $headerMapping, $request, &$importResults) {
                for ($i = 1; $i < count($lines); $i++) {
                    $line = trim($lines[$i]);
                    if (empty($line)) continue;

                    $importResults['total']++;
                    $lineNumber = $i + 1;

                    try {
                        $data = str_getcsv($line);
                        $rawRecord = array_combine($header, $data);
                        
                        // Map headers to internal field names
                        $record = [];
                        foreach ($rawRecord as $csvHeader => $value) {
                            $mappedField = $headerMapping[$csvHeader] ?? null;
                            if ($mappedField) {
                                $record[$mappedField] = trim($value);
                            }
                        }

                        // Validate required fields
                        $validator = Validator::make($record, [
                            'first_name' => 'required|string|max:255',
                            'middle_name' => 'nullable|string|max:255',
                            'last_name' => 'required|string|max:255',
                            'preferred_course' => 'nullable|string|max:255',
                            'email_address' => 'required|email|unique:applicants,email_address',
                            'phone_number' => 'nullable|string|max:20',
                            'application_no' => 'nullable|string|max:50',
                            'score' => 'nullable|numeric|min:0|max:100',
                            'verbal_description' => 'nullable|string|max:255',
                        ]);

                        if ($validator->fails()) {
                            $importResults['failed']++;
                            $importResults['errors'][] = "Line {$lineNumber}: " . implode(', ', $validator->errors()->all());
                            continue;
                        }

                        // Create applicant
                        $applicantData = $validator->validated();
                        
                        // Generate application number only if not provided in CSV
                        if (empty($applicantData['application_no'])) {
                            $applicantData['application_no'] = Applicant::generateApplicationNumber();
                        }
                        
                        $applicantData['assigned_instructor_id'] = $request->assigned_instructor_id;

                        $applicant = Applicant::create($applicantData);

                        // Create interview record if instructor assigned
                        if ($request->filled('assigned_instructor_id')) {
                            Interview::create([
                                'applicant_id' => $applicant->applicant_id,
                                'interviewer_id' => $request->assigned_instructor_id,
                                'status' => 'scheduled',
                            ]);
                        }

                        // Generate access code if requested
                        if (filter_var($request->input('generate_access_codes'), FILTER_VALIDATE_BOOLEAN)) {
                            $expiryHours = (int) ($request->access_code_expiry_hours ?? 72);
                            $accessCode = AccessCode::createForApplicant(
                                $applicant->applicant_id,
                                'BSIT',
                                8,
                                $expiryHours
                            );
                            $applicant->access_code = $accessCode->code;
                        }

                        $importResults['successful']++;
                        $importResults['imported_applicants'][] = $applicant;

                    } catch (Exception $e) {
                        $importResults['failed']++;
                        $importResults['errors'][] = "Line {$lineNumber}: " . $e->getMessage();
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => "Import completed! {$importResults['successful']} applicants imported successfully.",
                'results' => $importResults
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Download CSV template
     */
    public function downloadTemplate()
    {
        $csv = "No.,Applicant No.,Preferred Course,Last Name,First Name,Middle Name,E-mail,Contact #,Weighted Exam % (60%),Verbal Description\n";
        $csv .= "1,0-25-9-00001-0001,BSIT,ABRIL,GABRIEL,LOMACO,gabriel.1000abril@gmail.com,9513693169,,\n";
        $csv .= "2,0-25-9-00002-0002,BSIT,ALBARICO,DANIELLE ANGELO,ARREZA,danielleangelo.albarico@gmail.com,9090855732,,\n";
        $csv .= "3,0-25-9-00003-0003,BSIT,ALCALA,AERO JADE,GORDON,aerojade.alcala@gmail.com,9129751059,,\n";

        return response($csv)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="applicants_import_template.csv"');
    }

    /**
     * Generate access codes for applicants without them
     */
    public function generateAccessCodes(Request $request)
    {
        $request->validate([
            'applicant_ids' => 'required|array',
            'applicant_ids.*' => 'exists:applicants,applicant_id',
            'expiry_hours' => 'nullable|integer|min:1|max:720',
            'send_email' => 'boolean',
        ]);

        $generated = 0;
        $emailsSent = 0;
        $errors = [];
        $sendEmail = $request->boolean('send_email', false);

        DB::transaction(function () use ($request, &$generated, &$emailsSent, &$errors, $sendEmail) {
            foreach ($request->applicant_ids as $applicantId) {
                try {
                    $applicant = Applicant::with('assignedInstructor')->find($applicantId);
                    
                    // Check if applicant already has an access code
                    if ($applicant->accessCode) {
                        $errors[] = "Applicant {$applicant->full_name} already has an access code";
                        continue;
                    }

                    // Create access code
                    $accessCode = AccessCode::createForApplicant(
                        $applicantId,
                        'BSIT',
                        8,
                        $request->expiry_hours ?? 72
                    );

                    $generated++;

                    // Send email if requested
                    if ($sendEmail && $applicant->email_address) {
                        try {
                            Mail::to($applicant->email_address)->send(new \App\Mail\AccessCodeMail($applicant, $accessCode));
                            $emailsSent++;
                        } catch (Exception $e) {
                            $errors[] = "Code generated for {$applicant->full_name} but email failed: " . $e->getMessage();
                        }
                    }

                } catch (Exception $e) {
                    $errors[] = "Failed to generate code for applicant ID {$applicantId}: " . $e->getMessage();
                }
            }
        });

        $message = "Generated {$generated} access codes successfully.";
        if ($sendEmail) {
            $message .= " Sent {$emailsSent} email notifications.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'generated' => $generated,
            'emails_sent' => $emailsSent,
            'errors' => $errors
        ]);
    }

    /**
     * Show dedicated assignment page
     */
    public function assignPage(Request $request)
    {
        // Get all instructors
        $instructors = User::where('role', 'instructor')
            ->orderBy('full_name')
            ->get();

        // Build applicants query with filters
        $query = Applicant::with(['assignedInstructor', 'accessCode']);

        // Search filter
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email_address', 'like', "%{$search}%")
                  ->orWhere('application_no', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Assignment filter
        if ($request->filled('assigned')) {
            if ($request->assigned === 'unassigned') {
                $query->whereNull('assigned_instructor_id');
            } elseif ($request->assigned === 'assigned') {
                $query->whereNotNull('assigned_instructor_id');
            }
        }

        // Course filter
        if ($request->filled('course')) {
            $query->where('preferred_course', $request->course);
        }

        // Paginate results
        $applicants = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.applicants.assign', compact('applicants', 'instructors'));
    }

    /**
     * Bulk assign instructors to applicants
     */
    public function bulkAssignInstructors(Request $request)
    {
        $request->validate([
            'applicant_ids' => 'required|array',
            'applicant_ids.*' => 'exists:applicants,applicant_id',
            'instructor_id' => 'required|exists:users,user_id',
            'notify_email' => 'nullable|boolean',
            'note' => 'nullable|string|max:500',
        ]);

        $updated = 0;
        $interviewsCreated = 0;
        $emailsSent = 0;

        DB::transaction(function () use ($request, &$updated, &$interviewsCreated, &$emailsSent) {
            $instructor = User::findOrFail($request->instructor_id);
            
            foreach ($request->applicant_ids as $applicantId) {
                $applicant = Applicant::findOrFail($applicantId);
                
                // Update instructor assignment
                $applicant->update(['assigned_instructor_id' => $request->instructor_id]);
                $updated++;

                // Create or update interview record
                $interview = $applicant->latestInterview;
                if ($interview) {
                    $interview->update(['interviewer_id' => $request->instructor_id]);
                } else {
                    Interview::create([
                        'applicant_id' => $applicantId,
                        'interviewer_id' => $request->instructor_id,
                        'status' => 'scheduled',
                    ]);
                    $interviewsCreated++;
                }

                // Send email notification if requested
                if ($request->notify_email) {
                    try {
                        Mail::to($applicant->email_address)->send(
                            new \App\Mail\InterviewInvitationMail($applicant, $instructor, $request->note)
                        );
                        $emailsSent++;
                    } catch (\Exception $e) {
                        // Log error but continue processing
                        \Log::error("Failed to send email to {$applicant->email_address}: " . $e->getMessage());
                    }
                }
            }
        });

        $message = "Assigned {$updated} applicants to instructor successfully.";
        if ($request->notify_email) {
            $message .= " Sent {$emailsSent} email notifications.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'updated' => $updated,
            'interviews_created' => $interviewsCreated,
            'emails_sent' => $emailsSent
        ]);
    }

    /**
     * Get applicants eligible for interview (API endpoint)
     */
    public function getEligibleForInterview()
    {
        $applicants = Applicant::where('status', 'exam-completed')
                              ->whereDoesntHave('interviews')
                              ->with(['assignedInstructor'])
                              ->get();

        return response()->json([
            'success' => true,
            'applicants' => $applicants
        ]);
    }

    /**
     * Export applicants with access codes
     */
    public function exportWithAccessCodes(Request $request)
    {
        $query = Applicant::with(['assignedInstructor', 'accessCode', 'accessCode.exam']);

        // Apply filters if provided
        if ($request->has('instructor_id') && $request->instructor_id) {
            if ($request->instructor_id === 'unassigned') {
                $query->whereNull('assigned_instructor_id');
            } else {
                $query->where('assigned_instructor_id', $request->instructor_id);
            }
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $applicants = $query->get();

        $csv = "Application No,Full Name,Email,Phone,Assigned Instructor,Access Code,Assigned Exam,Status,Created At\n";

        foreach ($applicants as $applicant) {
            $instructor = $applicant->assignedInstructor ? $applicant->assignedInstructor->full_name : 'Not Assigned';
            $accessCode = $applicant->accessCode ? $applicant->accessCode->code : 'No Access Code';
            
            // Determine assigned exam
            $assignedExam = 'No Access Code';
            if ($applicant->accessCode) {
                if ($applicant->accessCode->exam_id && $applicant->accessCode->exam) {
                    $assignedExam = $applicant->accessCode->exam->title;
                } else {
                    $assignedExam = 'No Exam Assigned';
                }
            }
            
            $csv .= sprintf('"%s","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $applicant->application_no,
                $applicant->full_name,
                $applicant->email_address,
                $applicant->phone_number,
                $instructor,
                $accessCode,
                $assignedExam,
                ucfirst($applicant->status),
                $applicant->created_at->format('Y-m-d H:i:s')
            );
        }

        $filename = 'applicants_with_access_codes_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response($csv)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }


    /**
     * Display exam results page with EnrollAssess and interview scores
     *
     * @param Request $request
     * @return View
     */
    public function examResults(Request $request)
    {
        try {
            $query = Applicant::with(['assignedInstructor', 'accessCode', 'latestInterview'])
                ->whereNotNull('enrollassess_score'); // Only show applicants who completed EnrollAssess exam

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('application_no', 'like', "%{$search}%")
                      ->orWhere('email_address', 'like', "%{$search}%");
                });
            }

            // Status filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Course filter
            if ($request->filled('course')) {
                $query->where('preferred_course', $request->course);
            }

            // Score range filter for EnrollAssess scores
            if ($request->filled('score_min')) {
                $query->where('enrollassess_score', '>=', $request->score_min);
            }
            if ($request->filled('score_max')) {
                $query->where('enrollassess_score', '<=', $request->score_max);
            }

            // Interview score range filter
            if ($request->filled('interview_score_min')) {
                $query->where('interview_score', '>=', $request->interview_score_min);
            }
            if ($request->filled('interview_score_max')) {
                $query->where('interview_score', '<=', $request->interview_score_max);
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            $allowedSorts = ['created_at', 'first_name', 'last_name', 'enrollassess_score', 'interview_score', 'status'];
            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $applicants = $query->paginate(20);

            // Get filter options
            $courses = Applicant::select('preferred_course')
                ->distinct()
                ->whereNotNull('preferred_course')
                ->pluck('preferred_course')
                ->sort();

            $statuses = [
                'exam-completed',
                'interview-available',
                'interview-claimed',
                'interview-scheduled',
                'interview-completed',
                'admitted',
                'rejected'
            ];

            // Statistics
            $stats = [
                'total_with_scores' => Applicant::whereNotNull('enrollassess_score')->count(),
                'with_interview_scores' => Applicant::whereNotNull('interview_score')->count(),
                'average_enrollassess' => round(Applicant::whereNotNull('enrollassess_score')->avg('enrollassess_score'), 2),
                'average_interview' => round(Applicant::whereNotNull('interview_score')->avg('interview_score'), 2),
            ];

            return view('admin.applicants.exam-results', compact(
                'applicants',
                'courses', 
                'statuses',
                'stats'
            ));

        } catch (Exception $e) {
            return redirect()->route('admin.applicants.index')
                ->with('error', 'Failed to load exam results: ' . $e->getMessage());
        }
    }

    /**
     * Send exam notifications to selected applicants
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendExamNotifications(Request $request): JsonResponse
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'applicant_ids' => 'required|array|min:1',
                'applicant_ids.*' => 'exists:applicants,applicant_id',
                'exam_date' => 'nullable|string|max:255',
                'exam_time' => 'nullable|string|max:255',
                'exam_venue' => 'nullable|string|max:500',
                'special_instructions' => 'nullable|string|max:2000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first()
                ], 422);
            }

            $applicantIds = $request->applicant_ids;
            $examDate = $request->exam_date ?? 'To Be Announced';
            $examTime = $request->exam_time ?? 'To Be Announced';
            $examVenue = $request->exam_venue ?? 'To Be Announced';
            $specialInstructions = $request->special_instructions;

            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            // Get applicants with their access codes
            $applicants = Applicant::with(['accessCode'])
                ->whereIn('applicant_id', $applicantIds)
                ->get();

            foreach ($applicants as $applicant) {
                try {
                    // Check if applicant has an access code
                    if (!$applicant->accessCode || !$applicant->accessCode->code) {
                        $errors[] = "Applicant {$applicant->full_name} does not have an access code.";
                        $failedCount++;
                        continue;
                    }

                    // Check if applicant has an email address
                    if (!$applicant->email_address) {
                        $errors[] = "Applicant {$applicant->full_name} does not have an email address.";
                        $failedCount++;
                        continue;
                    }

                    // Send email notification
                    Mail::to($applicant->email_address)
                        ->send(new \App\Mail\ExamNotificationMail(
                            $applicant,
                            $applicant->accessCode->code,
                            $examDate,
                            $examTime,
                            $examVenue,
                            $specialInstructions
                        ));

                    $successCount++;
                } catch (Exception $e) {
                    $errors[] = "Failed to send email to {$applicant->full_name}: {$e->getMessage()}";
                    $failedCount++;
                }
            }

            // Prepare response message
            $message = "Email notifications sent successfully to {$successCount} applicant(s).";
            
            if ($failedCount > 0) {
                $message .= " {$failedCount} failed.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'success_count' => $successCount,
                    'failed_count' => $failedCount,
                    'errors' => $errors
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email notifications: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk assign exam to applicants (via access codes)
     */
    public function assignExamToApplicants(Request $request)
    {
        $request->validate([
            'applicant_ids' => 'required|array|min:1',
            'applicant_ids.*' => 'exists:applicants,applicant_id',
            'exam_id' => 'required|exists:exams,exam_id',
        ]);

        $assigned = 0;
        $skipped = 0;
        $errors = [];

        DB::transaction(function () use ($request, &$assigned, &$skipped, &$errors) {
            $exam = Exam::findOrFail($request->exam_id);

            foreach ($request->applicant_ids as $applicantId) {
                try {
                    $applicant = Applicant::with('accessCode')->find($applicantId);
                    
                    // Check if applicant has an access code
                    if (!$applicant->accessCode) {
                        $errors[] = "Applicant {$applicant->full_name} has no access code";
                        $skipped++;
                        continue;
                    }

                    // Update access code with exam_id
                    $applicant->accessCode->update([
                        'exam_id' => $request->exam_id
                    ]);

                    $assigned++;

                } catch (Exception $e) {
                    $errors[] = "Failed for applicant ID {$applicantId}: " . $e->getMessage();
                    $skipped++;
                }
            }
        });

        $message = "Exam assigned to {$assigned} applicant(s) successfully.";
        if ($skipped > 0) {
            $message .= " {$skipped} applicant(s) skipped (no access code or error).";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'assigned' => $assigned,
            'skipped' => $skipped,
            'errors' => $errors
        ]);
    }

    /**
     * Assign exam to a single applicant (via access code)
     */
    public function assignExamToApplicant(Request $request, $applicantId)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,exam_id',
        ]);

        try {
            $applicant = Applicant::with('accessCode')->findOrFail($applicantId);
            
            // Check if applicant has an access code
            if (!$applicant->accessCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'This applicant does not have an access code. Please generate one first.'
                ], 400);
            }

            $exam = Exam::findOrFail($request->exam_id);

            // Update access code with exam_id
            $applicant->accessCode->update([
                'exam_id' => $request->exam_id
            ]);

            return response()->json([
                'success' => true,
                'message' => "Exam '{$exam->title}' assigned to {$applicant->full_name} successfully.",
                'exam' => $exam
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign exam: ' . $e->getMessage()
            ], 500);
        }
    }
}