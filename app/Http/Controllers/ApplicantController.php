<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\AccessCode;
use App\Models\ExamSet;
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
            $query = Applicant::with(['examSet.exam', 'accessCode']);

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

            // Exam set filter
            if ($request->filled('exam_set_id')) {
                $query->where('exam_set_id', $request->get('exam_set_id'));
            }

            $applicants = $query->orderBy('created_at', 'desc')->paginate(20);

            // Statistics
            $stats = [
                'total' => Applicant::count(),
                'pending' => Applicant::where('status', 'pending')->count(),
                'exam_completed' => Applicant::where('status', '!=', 'pending')->count(),
                'with_access_codes' => Applicant::whereHas('accessCode')->count(),
                'without_access_codes' => Applicant::whereDoesntHave('accessCode')->count(),
            ];

            $examSets = ExamSet::with('exam')->where('is_active', true)->get();

            return view('admin.applicants.index', compact('applicants', 'stats', 'examSets'));
        } catch (Exception $e) {
            return back()->with('error', 'Failed to load applicants. Please try again.');
        }
    }

    /**
     * Show the form for creating a new applicant
     */
    public function create()
    {
        $examSets = ExamSet::with('exam')->where('is_active', true)->get();
        return view('admin.applicants.create', compact('examSets'));
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
            'exam_set_id' => 'nullable|exists:exam_sets,exam_set_id',
            'generate_access_code' => 'boolean',
        ]);

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
        });

        return redirect()->route('admin.applicants.index')
                        ->with('success', 'Applicant created successfully!');
    }

    /**
     * Display the specified applicant
     */
    public function show($id)
    {
        try {
            $applicant = Applicant::with(['examSet.exam', 'accessCode', 'latestInterview.interviewer', 'results.question'])
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
        $applicant = Applicant::with(['examSet', 'accessCode'])->findOrFail($id);
        $examSets = ExamSet::with('exam')->where('is_active', true)->get();
        
        return view('admin.applicants.create', compact('applicant', 'examSets'));
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
            'exam_set_id' => 'nullable|exists:exam_sets,exam_set_id',
            'status' => 'required|in:pending,exam-completed,interview-scheduled,interview-completed,admitted,rejected',
            'score' => 'nullable|numeric|min:0|max:9999.99',
            'verbal_description' => 'nullable|string|max:255',
        ]);

        $applicant->update($validated);

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
        $examSets = ExamSet::with('exam')->where('is_active', true)->get();
        return view('admin.applicants.import', compact('examSets'));
    }

    /**
     * Process bulk import from CSV
     */
    public function processImport(Request $request)
    {
        // Manual validation to ensure JSON response for AJAX
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
            'exam_set_id' => 'nullable|exists:exam_sets,exam_set_id',
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
                        
                        $applicantData['exam_set_id'] = $request->exam_set_id;

                        $applicant = Applicant::create($applicantData);

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
                    $applicant = Applicant::with('examSet.exam')->find($applicantId);
                    
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
                            Mail::to($applicant->email_address)->send(new AccessCodeMail($applicant, $accessCode));
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
     * Assign exam sets to applicants
     */
    public function assignExamSets(Request $request)
    {
        $request->validate([
            'applicant_ids' => 'required|array',
            'applicant_ids.*' => 'exists:applicants,applicant_id',
            'exam_set_id' => 'required|exists:exam_sets,exam_set_id',
            'assignment_strategy' => 'required|in:same,random',
        ]);

        $updated = 0;
        $examSets = [];

        if ($request->assignment_strategy === 'random') {
            // Get all active exam sets from the same exam
            $examSet = ExamSet::find($request->exam_set_id);
            $examSets = ExamSet::where('exam_id', $examSet->exam_id)
                              ->where('is_active', true)
                              ->pluck('exam_set_id')
                              ->toArray();
        }

        DB::transaction(function () use ($request, &$updated, $examSets) {
            foreach ($request->applicant_ids as $applicantId) {
                $examSetId = $request->exam_set_id;
                
                if ($request->assignment_strategy === 'random' && !empty($examSets)) {
                    $examSetId = $examSets[array_rand($examSets)];
                }

                Applicant::where('applicant_id', $applicantId)
                         ->update(['exam_set_id' => $examSetId]);
                
                $updated++;
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Updated {$updated} applicants successfully.",
            'updated' => $updated
        ]);
    }

    /**
     * Get applicants eligible for interview (API endpoint)
     */
    public function getEligibleForInterview()
    {
        $applicants = Applicant::where('status', 'exam-completed')
                              ->whereDoesntHave('interviews')
                              ->with(['examSet.exam'])
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
        $query = Applicant::with(['examSet.exam', 'accessCode']);

        // Apply filters if provided
        if ($request->has('exam_set_id') && $request->exam_set_id) {
            $query->where('exam_set_id', $request->exam_set_id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $applicants = $query->get();

        $csv = "Application No,Full Name,Email,Phone,Exam Set,Exam Title,Access Code,Status,Created At\n";

        foreach ($applicants as $applicant) {
            $examSet = $applicant->examSet ? $applicant->examSet->set_name : 'Not Assigned';
            $examTitle = $applicant->examSet && $applicant->examSet->exam ? $applicant->examSet->exam->title : 'Not Assigned';
            $accessCode = $applicant->accessCode ? $applicant->accessCode->code : 'Not Generated';
            
            $csv .= sprintf('"%s","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $applicant->application_no,
                $applicant->full_name,
                $applicant->email_address,
                $applicant->phone_number,
                $examSet,
                $examTitle,
                $accessCode,
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
     * Show exam set assignment interface
     */
    public function showExamSetAssignment(Request $request)
    {
        try {
            $query = Applicant::with(['examSet.exam', 'accessCode']);

            // Apply filters if provided
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

            if ($request->filled('status')) {
                $query->where('status', $request->get('status'));
            }

            if ($request->filled('exam_set_filter')) {
                if ($request->get('exam_set_filter') === 'unassigned') {
                    $query->whereNull('exam_set_id');
                } elseif ($request->get('exam_set_filter') === 'assigned') {
                    $query->whereNotNull('exam_set_id');
                } else {
                    $query->where('exam_set_id', $request->get('exam_set_filter'));
                }
            }

            $applicants = $query->orderBy('created_at', 'desc')->paginate(50);
            $examSets = ExamSet::with('exam')->where('is_active', true)->get();

            // Statistics for assignment overview
            $stats = [
                'total' => Applicant::count(),
                'assigned' => Applicant::whereNotNull('exam_set_id')->count(),
                'unassigned' => Applicant::whereNull('exam_set_id')->count(),
            ];

            // Distribution statistics by exam set
            $distribution = [];
            foreach ($examSets as $examSet) {
                $count = Applicant::where('exam_set_id', $examSet->exam_set_id)->count();
                $distribution[] = [
                    'exam_set' => $examSet,
                    'count' => $count,
                    'percentage' => $stats['total'] > 0 ? round(($count / $stats['total']) * 100, 1) : 0
                ];
            }

            // Add warning message if no exam sets exist
            if ($examSets->isEmpty()) {
                session()->flash('warning', 'No active exam sets found. Please create exam sets first before assigning them to applicants.');
            }

            return view('admin.applicants.assign-exam-sets', compact(
                'applicants', 'examSets', 'stats', 'distribution'
            ));
        } catch (Exception $e) {
            return back()->with('error', 'Failed to load assignment interface: ' . $e->getMessage());
        }
    }

    /**
     * Process smart exam set assignment
     */
    public function processExamSetAssignment(Request $request)
    {
        $request->validate([
            'applicant_ids' => 'required|array|min:1',
            'applicant_ids.*' => 'exists:applicants,applicant_id',
            'assignment_mode' => 'required|in:auto_distribute,manual_assign',
            'exam_set_id' => 'required_if:assignment_mode,manual_assign|nullable|exists:exam_sets,exam_set_id',
            'send_notifications' => 'boolean',
        ]);

        try {
            $applicantIds = $request->applicant_ids;
            $assignmentMode = $request->assignment_mode;
            $sendNotifications = $request->boolean('send_notifications', false);
            
            $assignments = [];
            $errors = [];
            $notificationsSent = 0;

            // Check if exam sets exist before starting transaction
            if ($assignmentMode === 'auto_distribute') {
                $examSets = ExamSet::where('is_active', true)->get();
                if ($examSets->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No active exam sets available for assignment. Please create exam sets first.'
                    ], 422);
                }
            } elseif ($assignmentMode === 'manual_assign') {
                $examSetId = $request->exam_set_id;
                if (!$examSetId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please select an exam set for assignment.'
                    ], 422);
                }
                
                $examSet = ExamSet::find($examSetId);
                if (!$examSet || !$examSet->is_active) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected exam set is not available or inactive.'
                    ], 422);
                }
            }

            DB::transaction(function () use (
                $applicantIds, $assignmentMode, $request, 
                &$assignments, &$errors, &$notificationsSent, $sendNotifications
            ) {
                if ($assignmentMode === 'auto_distribute') {
                    // Smart distribution algorithm
                    $examSets = ExamSet::where('is_active', true)
                                      ->orderBy('exam_set_id')
                                      ->get();

                    $totalApplicants = count($applicantIds);
                    $totalSets = $examSets->count();
                    $baseCount = intval($totalApplicants / $totalSets);
                    $remainder = $totalApplicants % $totalSets;

                    // Shuffle applicant IDs for random distribution
                    $shuffledIds = $applicantIds;
                    shuffle($shuffledIds);

                    $currentIndex = 0;
                    foreach ($examSets as $setIndex => $examSet) {
                        // Calculate how many students this set should get
                        $countForThisSet = $baseCount + ($setIndex < $remainder ? 1 : 0);
                        
                        for ($i = 0; $i < $countForThisSet && $currentIndex < $totalApplicants; $i++) {
                            $applicantId = $shuffledIds[$currentIndex];
                            
                            try {
                                $applicant = Applicant::with('examSet')->find($applicantId);
                                if ($applicant) {
                                    $applicant->update(['exam_set_id' => $examSet->exam_set_id]);
                                    $assignments[] = [
                                        'applicant' => $applicant,
                                        'exam_set' => $examSet,
                                        'previous_set' => $applicant->examSet ? $applicant->examSet->set_name : null
                                    ];
                                }
                            } catch (Exception $e) {
                                $errors[] = "Failed to assign applicant ID {$applicantId}: " . $e->getMessage();
                            }
                            
                            $currentIndex++;
                        }
                    }
                } else {
                    // Manual assignment to specific exam set
                    $examSet = ExamSet::find($request->exam_set_id);
                    
                    foreach ($applicantIds as $applicantId) {
                        try {
                            $applicant = Applicant::with('examSet')->find($applicantId);
                            if ($applicant) {
                                $previousSet = $applicant->examSet ? $applicant->examSet->set_name : null;
                                $applicant->update(['exam_set_id' => $examSet->exam_set_id]);
                                $assignments[] = [
                                    'applicant' => $applicant,
                                    'exam_set' => $examSet,
                                    'previous_set' => $previousSet
                                ];
                            }
                        } catch (Exception $e) {
                            $errors[] = "Failed to assign applicant ID {$applicantId}: " . $e->getMessage();
                        }
                    }
                }

                // Send email notifications if requested
                if ($sendNotifications) {
                    foreach ($assignments as $assignment) {
                        try {
                            $this->sendExamAssignmentNotification(
                                $assignment['applicant'], 
                                $assignment['exam_set']
                            );
                            $notificationsSent++;
                        } catch (Exception $e) {
                            $errors[] = "Assignment successful for {$assignment['applicant']->full_name} but email failed: " . $e->getMessage();
                        }
                    }
                }
            });

            $successCount = count($assignments);
            $message = "Successfully assigned {$successCount} applicant(s) to exam sets.";
            
            if ($sendNotifications && $notificationsSent > 0) {
                $message .= " Sent {$notificationsSent} email notifications.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'assignments' => $successCount,
                'notifications_sent' => $notificationsSent,
                'errors' => $errors
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Assignment failed: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Send exam assignment notification email
     */
    private function sendExamAssignmentNotification(Applicant $applicant, ExamSet $examSet)
    {
        if (!$applicant->email_address) {
            throw new Exception('No email address available for applicant.');
        }

        $emailData = [
            'applicant_name' => $applicant->full_name,
            'application_no' => $applicant->application_no,
            'exam_set_name' => $examSet->set_name,
            'exam_title' => $examSet->exam->title ?? 'BSIT Entrance Examination',
            'exam_date' => 'To be announced', // This should come from exam schedule
            'exam_time' => 'To be announced',
            'access_code' => $applicant->accessCode ? $applicant->accessCode->code : null,
            'instructions' => $this->getSeatingInstructions($examSet->set_name)
        ];

        // Send email using the professional template
        Mail::send('emails.exam-assignment', $emailData, function ($message) use ($applicant) {
            $message->to($applicant->email_address)
                    ->subject('BSIT Entrance Exam - Set Assignment Confirmation');
        });
    }

    /**
     * Build email content for exam assignment
     */
    private function buildExamAssignmentEmailContent(array $data): string
    {
        return "
        Dear {$data['applicant_name']},

        We are pleased to inform you that your exam set assignment has been confirmed for the BSIT Entrance Examination.

        EXAM DETAILS:
        - Application Number: {$data['application_no']}
        - Assigned Exam Set: {$data['exam_set_name']}
        - Exam Title: {$data['exam_title']}
        - Date: {$data['exam_date']}
        - Time: {$data['exam_time']}
        " . ($data['access_code'] ? "- Access Code: {$data['access_code']}" : '') . "

        SEATING INSTRUCTIONS:
        {$data['instructions']}

        IMPORTANT REMINDERS:
        - Arrive at the examination venue 30 minutes before the scheduled time
        - Bring a valid ID and your application form
        - Bring necessary writing materials (pen, pencil, eraser)
        - Mobile phones and electronic devices are not allowed during the exam
        - Follow all examination protocols and guidelines

        If you have any questions or concerns, please contact the admissions office.

        Good luck with your examination!

        Best regards,
        BSIT Admissions Office
        ";
    }

    /**
     * Get seating instructions based on exam set
     */
    private function getSeatingInstructions(string $setName): string
    {
        $instructions = [
            'A' => 'Please sit in the LEFT section of the examination room (Columns 1-3).',
            'B' => 'Please sit in the MIDDLE section of the examination room (Columns 4-6).',
            'C' => 'Please sit in the RIGHT section of the examination room (Columns 7-9).'
        ];

        return $instructions[$setName] ?? "Please follow the seating arrangement as directed by the examination proctor.";
    }

    /**
     * Get assignment statistics for dashboard
     */
    public function getAssignmentStats()
    {
        $stats = [
            'total_applicants' => Applicant::count(),
            'assigned' => Applicant::whereNotNull('exam_set_id')->count(),
            'unassigned' => Applicant::whereNull('exam_set_id')->count(),
        ];

        $examSets = ExamSet::with('exam')->where('is_active', true)->get();
        $distribution = [];
        
        foreach ($examSets as $examSet) {
            $count = Applicant::where('exam_set_id', $examSet->exam_set_id)->count();
            $distribution[] = [
                'set_name' => $examSet->set_name,
                'exam_title' => $examSet->exam->title ?? 'Unknown',
                'count' => $count,
                'percentage' => $stats['total_applicants'] > 0 
                    ? round(($count / $stats['total_applicants']) * 100, 1) 
                    : 0
            ];
        }

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'distribution' => $distribution
        ]);
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
            $query = Applicant::with(['examSet.exam', 'accessCode', 'latestInterview'])
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
}