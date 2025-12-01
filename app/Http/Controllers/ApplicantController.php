<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\AccessCode;
use App\Models\Exam;
use App\Models\User;
use App\Models\Interview;
use App\Models\ExamSchedule;
use App\Services\ApplicantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
     * Apply school year filter to query if needed
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applySchoolYearFilter($query)
    {
        $currentRoute = request()->route()->getName() ?? '';
        
        // Routes that should NOT be filtered by school year
        $excludedRoutes = [
            'admin.settings',
            'admin.questions',
            'admin.users',
        ];
        
        // Check if current route should be excluded
        $shouldExclude = false;
        foreach ($excludedRoutes as $excludedPrefix) {
            if (str_starts_with($currentRoute, $excludedPrefix)) {
                $shouldExclude = true;
                break;
            }
        }
        
        // Apply filter if not excluded
        if (!$shouldExclude) {
            $schoolYearId = session('school_year_id');
            if ($schoolYearId) {
                $query->forSchoolYear($schoolYearId);
            }
        }
        
        return $query;
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
            // Exclude archived applicants by default
            $query = Applicant::with(['assignedInstructor', 'accessCode', 'accessCode.exam', 'latestExamSchedule']);
            
            // Apply school year filter
            $this->applySchoolYearFilter($query);

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

            // Meaningful Statistics (exclude archived applicants, filter by school year)
            $statsQuery = Applicant::query();
            $this->applySchoolYearFilter($statsQuery);

            // Compute qualified count based on overall admission score (overall_rating_value)
            // Threshold: overall score >= 75
            $qualifiedCount = (clone $statsQuery)
                ->get()
                ->filter(function (Applicant $applicant) {
                    return $applicant->overall_rating_value !== null
                        && $applicant->overall_rating_value >= 75;
                })
                ->count();
            
            $stats = [
                'total_applicants' => (clone $statsQuery)->count(),
                'exam_completed' => (clone $statsQuery)->where('status', '!=', 'pending')->whereNotNull('enrollassess_score')->count(),
                'interview_completed' => (clone $statsQuery)->whereIn('status', [
                    'interview-completed',
                    'admitted',
                    'rejected',
                ])->count(),
                'qualified' => $qualifiedCount,
            ];

            $instructors = User::where('role', 'instructor')->get();

            $exams = Exam::where('is_active', true)->get();
            
            // Return JSON for AJAX pagination requests only
            if ($request->ajax() && $request->header('Accept') && str_contains($request->header('Accept'), 'application/json')) {
                // Map applicants to include exam schedule data
                $applicantsData = $applicants->getCollection()->map(function($applicant) {
                    $data = $applicant->toArray();
                    
                    // Include exam schedule data
                    if ($applicant->latestExamSchedule) {
                        $schedule = $applicant->latestExamSchedule;
                        $data['latest_exam_schedule'] = [
                            'exam_schedule_id' => $schedule->exam_schedule_id,
                            'scheduled_date' => $schedule->scheduled_date ? $schedule->scheduled_date->format('Y-m-d') : null,
                            'scheduled_time' => $schedule->scheduled_time,
                            'venue' => $schedule->venue,
                            'special_instructions' => $schedule->special_instructions,
                            'status' => $schedule->status,
                        ];
                    } else {
                        $data['latest_exam_schedule'] = null;
                    }
                    
                    return $data;
                })->values()->all();
                
                return response()->json([
                    'applicants' => $applicantsData,
                    'pagination' => [
                        'current_page' => $applicants->currentPage(),
                        'last_page' => $applicants->lastPage(),
                        'per_page' => $applicants->perPage(),
                        'total' => $applicants->total(),
                        'from' => $applicants->firstItem(),
                        'to' => $applicants->lastItem(),
                    ],
                    'pagination_html' => $applicants->hasPages() ? $applicants->onEachSide(2)->appends($request->query())->links()->render() : '',
                ]);
            }
            
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
            'email_address' => [
                'required',
                'email',
                Rule::unique('applicants', 'email_address')->whereNull('deleted_at')
            ],
            'phone_number' => 'nullable|string|max:20',
            'assigned_instructor_id' => 'nullable|exists:users,user_id',
            'score' => 'nullable|numeric|min:0|max:100',
            'status' => 'nullable|in:pending,exam-completed,interview-scheduled,interview-completed,admitted,rejected',
            'verbal_description' => 'nullable|string|max:255',
            'generate_access_code' => 'boolean',
        ]);

        try {
            $applicant = null;
            DB::transaction(function () use ($validated, $request, &$applicant) {
                // Generate application number
                $validated['application_no'] = Applicant::generateApplicationNumber();
                
                // Assign school year from session or get current
                $schoolYearId = session('school_year_id');
                if (!$schoolYearId) {
                    $currentSchoolYear = \App\Models\SchoolYear::getCurrent();
                    $schoolYearId = $currentSchoolYear ? $currentSchoolYear->school_year_id : null;
                }
                $validated['school_year_id'] = $schoolYearId;

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
                    $interview = Interview::create([
                        'applicant_id' => $applicant->applicant_id,
                        'interviewer_id' => $request->assigned_instructor_id,
                        'status' => 'scheduled',
                    ]);
                    
                    // Dispatch interview scheduled event
                    \App\Helpers\BroadcastHelper::safeDispatch(new \App\Events\InterviewScheduled($interview->load(['applicant', 'interviewer'])));
                }
            });

            // Dispatch applicant created event
            \App\Helpers\BroadcastHelper::safeDispatch(new \App\Events\ApplicantCreated($applicant));
            
            // Dispatch statistics updated event
            $this->dispatchStatisticsUpdate();

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
            $applicant = Applicant::with([
                'assignedInstructor', 
                'accessCode.exam', 
                'latestInterview.interviewer', 
                'results.question',
                'basicInfo'
            ])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return back()->with('error', 'Applicant not found.');
        }

        // Get exam results if available
        $totalQuestions = 0;
        $correctAnswers = 0;
        if ($applicant->results && $applicant->results->count() > 0) {
            $totalQuestions = $applicant->results->count();
            $correctAnswers = $applicant->results->where('is_correct', true)->count();
        }

        // Get exam attempt for duration calculation
        $examAttempt = \App\Models\ExamAttempt::where('applicant_id', $applicant->applicant_id)
            ->where('status', 'completed')
            ->latest('completed_at')
            ->first();

        // Calculate exam duration if attempt exists
        $examDuration = null;
        if ($examAttempt && $examAttempt->started_at && $examAttempt->completed_at) {
            $duration = $examAttempt->started_at->diffInMinutes($examAttempt->completed_at);
            $hours = floor($duration / 60);
            $minutes = $duration % 60;
            if ($hours > 0) {
                $examDuration = $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ' . $minutes . ' minute' . ($minutes != 1 ? 's' : '');
            } else {
                $examDuration = $minutes . ' minute' . ($minutes != 1 ? 's' : '');
            }
        }

        // Get overall rating
        $overallRating = $applicant->getOverallRating();
        
        // Add verbal description if overall rating exists
        if ($overallRating) {
            $scoringService = app(\App\Services\AdmissionScoringService::class);
            $overallRating['verbal_description'] = $scoringService->getVerbalDescription($overallRating['overall_rating']);
        }

        // Build timeline
        $timeline = [];
        $timeline[] = [
            'date' => $applicant->created_at->format('M d, Y'), 
            'time' => $applicant->created_at->format('g:i A'), 
            'event' => 'Application submitted', 
            'type' => 'application'
        ];

        if ($applicant->accessCode && $applicant->accessCode->created_at) {
            $timeline[] = [
                'date' => $applicant->accessCode->created_at->format('M d, Y'), 
                'time' => $applicant->accessCode->created_at->format('g:i A'), 
                'event' => 'Access code generated', 
                'type' => 'update'
            ];
        }

        if ($applicant->exam_completed_at) {
            $score = $applicant->enrollassess_score ?? 0;
            $timeline[] = [
                'date' => $applicant->exam_completed_at->format('M d, Y'), 
                'time' => $applicant->exam_completed_at->format('g:i A'), 
                'event' => 'Entrance exam completed with ' . number_format($score, 2) . '% score', 
                'type' => 'exam'
            ];
        }

        $interview = $applicant->latestInterview;
        if ($interview) {
            if ($interview->status === 'scheduled' && $interview->schedule_date) {
                $interviewerName = $interview->interviewer ? $interview->interviewer->full_name : 'Instructor';
                $timeline[] = [
                    'date' => $interview->schedule_date->format('M d, Y'), 
                    'time' => $interview->schedule_date->format('g:i A'), 
                    'event' => 'Interview scheduled with ' . $interviewerName, 
                    'type' => 'interview'
                ];
            }
            if ($interview->status === 'completed' && $interview->updated_at) {
                $timeline[] = [
                    'date' => $interview->updated_at->format('M d, Y'), 
                    'time' => $interview->updated_at->format('g:i A'), 
                    'event' => 'Interview completed', 
                    'type' => 'interview'
                ];
            }
        }

        return view('admin.applicants.show', compact(
            'applicant',
            'totalQuestions',
            'correctAnswers',
            'examDuration',
            'overallRating',
            'timeline'
        ));
    }

    /**
     * Show exam details for a specific applicant
     */
    public function showExamDetails($id)
    {
        try {
            $applicant = Applicant::with([
                'results.question.options',
                'accessCode.exam',
                'basicInfo'
            ])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return back()->with('error', 'Applicant not found.');
        }

        // Get exam results
        $results = $applicant->results()->with('question.options')->orderBy('created_at', 'asc')->get();
        
        // Get exam attempt info
        $examAttempt = \App\Models\ExamAttempt::where('applicant_id', $applicant->applicant_id)
            ->where('status', 'completed')
            ->latest('completed_at')
            ->first();
        
        // Calculate statistics
        // IMPORTANT: Get total assigned questions from ExamAttempt, not just answered questions
        $totalAssignedQuestions = 0;
        if ($examAttempt && $examAttempt->question_ids && is_array($examAttempt->question_ids)) {
            $totalAssignedQuestions = count($examAttempt->question_ids);
        } else {
            // Fallback to results count if attempt data not available
            $totalAssignedQuestions = $results->count();
        }
        
        $answeredQuestions = $results->count();
        $correctAnswers = $results->where('is_correct', true)->count();
        $incorrectAnswers = $answeredQuestions - $correctAnswers;
        $unansweredQuestions = $totalAssignedQuestions - $answeredQuestions;

        return view('admin.applicants.exam-details', compact(
            'applicant',
            'results',
            'totalAssignedQuestions',
            'answeredQuestions',
            'correctAnswers',
            'incorrectAnswers',
            'unansweredQuestions',
            'examAttempt'
        ));
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
                'skipped' => 0,
                'errors' => [],
                'warnings' => [],
                'imported_applicants' => [],
            ];

            // Track emails processed in this import batch to detect duplicates within CSV
            $processedEmails = [];

            DB::transaction(function () use ($lines, $header, $headerMapping, $request, &$importResults, &$processedEmails) {
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

                        // Normalize email for duplicate checking
                        $emailAddress = isset($record['email_address']) ? strtolower(trim($record['email_address'])) : null;

                        // Check for duplicate email within this CSV file first - skip silently
                        if ($emailAddress && isset($processedEmails[$emailAddress])) {
                            $importResults['skipped']++;
                            $importResults['warnings'][] = "Line {$lineNumber}: Skipped duplicate email (first seen at line {$processedEmails[$emailAddress]}).";
                            continue;
                        }

                        // Validate required fields
                        $validator = Validator::make($record, [
                            'first_name' => 'required|string|max:255',
                            'middle_name' => 'nullable|string|max:255',
                            'last_name' => 'required|string|max:255',
                            'preferred_course' => 'nullable|string|max:255',
                            'email_address' => [
                                'required',
                                'email',
                                Rule::unique('applicants', 'email_address')->whereNull('deleted_at')
                            ],
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

                        // Track this email as processed (after validation passes, before creating applicant)
                        if ($emailAddress) {
                            $processedEmails[$emailAddress] = $lineNumber;
                        }

                        // Create applicant
                        $applicantData = $validator->validated();
                        
                        // Generate application number only if not provided in CSV
                        if (empty($applicantData['application_no'])) {
                            $applicantData['application_no'] = Applicant::generateApplicationNumber();
                        }
                        
                        $applicantData['assigned_instructor_id'] = $request->assigned_instructor_id;
                        
                        // Assign school year from session or get current
                        $schoolYearId = session('school_year_id');
                        if (!$schoolYearId) {
                            $currentSchoolYear = \App\Models\SchoolYear::getCurrent();
                            $schoolYearId = $currentSchoolYear ? $currentSchoolYear->school_year_id : null;
                        }
                        $applicantData['school_year_id'] = $schoolYearId;

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

            // Dispatch events for imported applicants
            foreach ($importResults['imported_applicants'] as $applicant) {
                \App\Helpers\BroadcastHelper::safeDispatch(new \App\Events\ApplicantCreated($applicant));
            }
            
            // Dispatch statistics update event
            $this->dispatchStatisticsUpdate();

            $message = "Import completed! {$importResults['successful']} applicants imported successfully.";
            if ($importResults['skipped'] > 0) {
                $message .= " {$importResults['skipped']} duplicate(s) skipped within CSV file.";
            }
            if ($importResults['failed'] > 0) {
                $message .= " {$importResults['failed']} record(s) failed.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
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
                    $applicant = Applicant::with(['assignedInstructor', 'accessCode'])->find($applicantId);
                    
                    if (!$applicant) {
                        $errors[] = "Applicant with ID {$applicantId} not found";
                        continue;
                    }
                    
                    // Check if applicant already has an access code
                    if ($applicant->accessCode) {
                        $existingCode = $applicant->accessCode;
                        
                        // Check if the code is expired (expires_at is in the past and not used)
                        $isExpired = false;
                        if ($existingCode->expires_at !== null) {
                            $isExpired = $existingCode->expires_at->isPast() && !$existingCode->is_used;
                        }
                        
                        if ($isExpired) {
                            // Delete expired code and generate a new one
                            $existingCode->delete();
                        } else {
                            // Code is still valid (not expired or already used), skip this applicant
                            $errors[] = "Applicant {$applicant->full_name} already has a valid access code";
                            continue;
                        }
                    }

                    // Create access code
                    $expiryHours = $request->expiry_hours ? (int)$request->expiry_hours : 72;
                    $accessCode = AccessCode::createForApplicant(
                        $applicantId,
                        'BSIT',
                        8,
                        $expiryHours
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
        $query = Applicant::with(['assignedInstructor', 'accessCode', 'latestInterview']);
        
        // Apply school year filter
        $this->applySchoolYearFilter($query);

        // Search filter
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email_address', 'like', "%{$search}%")
                  ->orWhere('application_no', 'like', "%{$search}%")
                  ->orWhereHas('assignedInstructor', function($instructorQuery) use ($search) {
                      $instructorQuery->where('full_name', 'like', "%{$search}%");
                  });
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

            // Return JSON for AJAX pagination requests only
            if ($request->ajax() && $request->header('Accept') && str_contains($request->header('Accept'), 'application/json')) {
            // Map applicants to include full_name and other accessors
            $applicantsData = $applicants->map(function($applicant) {
                $latestInterview = $applicant->latestInterview;
                return [
                    'applicant_id' => $applicant->applicant_id,
                    'application_no' => $applicant->application_no,
                    'formatted_applicant_no' => $applicant->formatted_applicant_no,
                    'full_name' => $applicant->full_name,
                    'email_address' => $applicant->email_address,
                    'status' => $applicant->status,
                    'assigned_instructor_id' => $applicant->assigned_instructor_id,
                    'assigned_instructor' => $applicant->assignedInstructor ? [
                        'user_id' => $applicant->assignedInstructor->user_id,
                        'full_name' => $applicant->assignedInstructor->full_name,
                    ] : null,
                    'interview_start' => $latestInterview && $latestInterview->interview_deadline_start 
                        ? $latestInterview->interview_deadline_start->format('Y-m-d') 
                        : null,
                    'interview_end' => $latestInterview && $latestInterview->interview_deadline_end 
                        ? $latestInterview->interview_deadline_end->format('Y-m-d') 
                        : null,
                ];
            });
            
            return response()->json([
                'applicants' => $applicantsData,
                'pagination' => [
                    'current_page' => $applicants->currentPage(),
                    'last_page' => $applicants->lastPage(),
                    'per_page' => $applicants->perPage(),
                    'total' => $applicants->total(),
                    'from' => $applicants->firstItem(),
                    'to' => $applicants->lastItem(),
                ],
                'pagination_html' => $applicants->hasPages() ? $applicants->onEachSide(2)->appends($request->query())->links()->render() : '',
            ]);
        }

        // Get delegation info if user is accessing via delegation
        $delegation = null;
        $isDelegated = false;
        if (Auth::check() && Auth::user()->role === 'instructor') {
            // Check for any applicants-related delegation (granular capabilities)
            $delegation = Auth::user()->delegatedPermissions()
                ->whereIn('permission', ['applicants.view', 'applicants.assign', 'applicants.bulk_operations', 'assign_applicants'])
                ->where('status', 'active')
                ->where(function($q) {
                    $q->whereNull('starts_at')
                      ->orWhere('starts_at', '<=', now());
                })
                ->first();
            
            if ($delegation && !$delegation->isExpired()) {
                $isDelegated = true;
            }
        }

        return view('admin.applicants.assign', compact('applicants', 'instructors', 'delegation', 'isDelegated'));
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
            'interview_start_date' => 'required|date|after_or_equal:today',
            'interview_end_date' => 'required|date|after_or_equal:interview_start_date',
            'notify_email' => 'nullable|boolean',
            'assignment_message' => 'nullable|string|max:1000',
        ]);

        $updated = 0;
        $interviewsCreated = 0;
        $emailsSent = 0;

        $instructor = User::findOrFail($request->instructor_id);
        $assignedApplicants = collect();
        
        DB::transaction(function () use ($request, &$updated, &$interviewsCreated, &$assignedApplicants, $instructor) {
            foreach ($request->applicant_ids as $applicantId) {
                $applicant = Applicant::findOrFail($applicantId);
                
                // Update instructor assignment
                $applicant->update(['assigned_instructor_id' => $request->instructor_id]);
                $updated++;
                $assignedApplicants->push($applicant->fresh(['assignedInstructor']));

                // Create or update interview record
                $interview = $applicant->latestInterview;
                if ($interview) {
                    $interview->update([
                        'interviewer_id' => $request->instructor_id,
                        'interview_deadline_start' => $request->interview_start_date,
                        'interview_deadline_end' => $request->interview_end_date,
                        'assignment_notes' => $request->assignment_message,
                    ]);
                } else {
                    Interview::create([
                        'applicant_id' => $applicantId,
                        'interviewer_id' => $request->instructor_id,
                        'status' => 'scheduled',
                        'interview_deadline_start' => $request->interview_start_date,
                        'interview_deadline_end' => $request->interview_end_date,
                        'assignment_notes' => $request->assignment_message,
                    ]);
                    $interviewsCreated++;
                }
            }
        });

        // Send email notification to instructor if requested
        if ($request->notify_email && $instructor->email) {
            try {
                Mail::to($instructor->email)->send(
                    new \App\Mail\InstructorAssignmentNotificationMail(
                        $instructor,
                        $assignedApplicants,
                        $request->interview_start_date,
                        $request->interview_end_date,
                        $request->assignment_message
                    )
                );
                $emailsSent = 1;
            } catch (\Exception $e) {
                // Log error but continue processing
                \Log::error("Failed to send email to instructor {$instructor->email}: " . $e->getMessage());
                $emailsSent = 0;
            }
        } else {
            $emailsSent = 0;
        }

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
     * Bulk delete applicants
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'applicant_ids' => 'required|array',
            'applicant_ids.*' => 'required|exists:applicants,applicant_id',
        ]);

        try {
            $deletedCount = 0;
            $errors = [];

            DB::transaction(function () use ($request, &$deletedCount, &$errors) {
                $applicants = Applicant::whereIn('applicant_id', $request->applicant_ids)
                    ->with(['accessCode', 'interviews', 'results'])
                    ->get();

                foreach ($applicants as $applicant) {
                    try {
                        // Delete related records
                        if ($applicant->accessCode) {
                            $applicant->accessCode->delete();
                        }
                        
                        // Delete interviews
                        $applicant->interviews()->delete();
                        
                        // Delete results
                        $applicant->results()->delete();
                        
                        // Delete the applicant
                        $applicant->delete();
                        $deletedCount++;
                    } catch (Exception $e) {
                        $errors[] = "Failed to delete applicant {$applicant->full_name}: " . $e->getMessage();
                        \Log::error("Bulk delete error for applicant {$applicant->applicant_id}: " . $e->getMessage());
                    }
                }
            });

            $message = "Deleted {$deletedCount} applicant(s) successfully.";
            if (count($errors) > 0) {
                $message .= " " . count($errors) . " error(s) occurred.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted_count' => $deletedCount,
                'errors' => $errors
            ]);
        } catch (Exception $e) {
            \Log::error('Bulk delete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during bulk delete: ' . $e->getMessage()
            ], 500);
        }
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
        
        // Apply school year filter
        $this->applySchoolYearFilter($query);

        // If specific applicant IDs are provided, filter by those IDs first
        if ($request->has('applicant_ids') && $request->applicant_ids) {
            $applicantIds = explode(',', $request->applicant_ids);
            $applicantIds = array_filter(array_map('trim', $applicantIds));
            if (!empty($applicantIds)) {
                $query->whereIn('id', $applicantIds);
            }
        }

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
     * Export scheduled applicants with access codes as PDF
     * Only accessible by department-head
     */
    public function exportAccessCodesPDF()
    {
        // Check if user is department-head
        if (auth()->user()->role !== 'department-head') {
            abort(403, 'Only department heads can export access codes.');
        }

        // Get all applicants with access codes, filtered by school year
        $query = Applicant::with(['accessCode', 'accessCode.exam'])
            ->whereHas('accessCode');
        
        // Apply school year filter
        $this->applySchoolYearFilter($query);
        
        // Sort alphabetically by last name, then first name (case-insensitive)
        $applicants = $query->orderByRaw('LOWER(last_name) ASC')
            ->orderByRaw('LOWER(first_name) ASC')
            ->get();

        // Prepare data for PDF
        $data = $applicants->map(function ($applicant) {
            // Format: LASTNAME, FIRSTNAME, MIDDLE INITIAL
            $nameParts = [];
            
            // Add last name
            if ($applicant->last_name) {
                $nameParts[] = strtoupper($applicant->last_name);
            }
            
            // Add first name
            if ($applicant->first_name) {
                $nameParts[] = $applicant->first_name;
            }
            
            // Add middle initial (first letter only, uppercase)
            if ($applicant->middle_name) {
                $middleInitial = strtoupper(substr(trim($applicant->middle_name), 0, 1)) . '.';
                $nameParts[] = $middleInitial;
            }
            
            // Join: LASTNAME, FIRSTNAME, M.
            $formattedName = implode(', ', $nameParts);
            
            return [
                'name' => $formattedName,
                'access_code' => $applicant->accessCode ? $applicant->accessCode->code : 'N/A',
            ];
        });

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.applicants.export.access-codes-pdf', [
            'applicants' => $data,
            'generated_at' => now()->format('F d, Y - g:i A'),
            'generated_by' => auth()->user()->full_name,
        ]);

        $filename = 'access_codes_export_' . now()->format('Y-m-d_H-i-s') . '.pdf';

        return $pdf->download($filename);
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
            
            // Apply school year filter
            $this->applySchoolYearFilter($query);

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

            // Sorting - Default to newest exam completions first
            $sortBy = $request->get('sort_by', 'exam_completed_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            $allowedSorts = [
                'created_at',
                'updated_at',
                'application_no',
                'first_name',
                'last_name',
                'email_address',
                'enrollassess_score',
                'interview_score',
                'score',
                'card_tor_gwa',
                'status',
                'exam_completed_at',
            ];
            
            // Handle overall_rating sorting (calculated field)
            if ($sortBy === 'overall_rating') {
                $collection = $query->get();
                $sorted = $collection->sortBy(function($applicant) {
                    $rating = $applicant->getOverallRating();
                    return $rating ? $rating['overall_rating'] : 0;
                }, SORT_REGULAR, $sortOrder === 'desc');
                
                $page = $request->get('page', 1);
                $perPage = 20;
                $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                    $sorted->forPage($page, $perPage),
                    $sorted->count(),
                    $perPage,
                    $page,
                    ['path' => $request->url(), 'query' => $request->query()]
                );
                $applicants = $paginator;
            } elseif (in_array($sortBy, $allowedSorts)) {
                // Handle exam_completed_at with NULLS LAST for proper newest-to-oldest sorting
                if ($sortBy === 'exam_completed_at') {
                    if ($sortOrder === 'desc') {
                        // Newest first: non-null values descending, then nulls (MySQL compatible)
                        $query->orderByRaw('ISNULL(exam_completed_at), exam_completed_at DESC');
                    } else {
                        // Oldest first: non-null values ascending, then nulls (MySQL compatible)
                        $query->orderByRaw('ISNULL(exam_completed_at), exam_completed_at ASC');
                    }
                } else {
                    $query->orderBy($sortBy, $sortOrder);
                }
                $applicants = $query->paginate(20);
            } else {
                // Default fallback: newest exam completions first (MySQL compatible)
                $query->orderByRaw('ISNULL(exam_completed_at), exam_completed_at DESC');
                $applicants = $query->paginate(20);
            }

            $statuses = [
                'exam-completed',
                'interview-available',
                'interview-claimed',
                'interview-scheduled',
                'interview-completed'
            ];

            // Statistics - 4 most important metrics (filtered by school year)
            $scoringService = app(\App\Services\AdmissionScoringService::class);
            $allApplicantsQuery = Applicant::whereNotNull('enrollassess_score');
            $this->applySchoolYearFilter($allApplicantsQuery);
            $allApplicants = $allApplicantsQuery->get();
            
            $qualifiersCount = $allApplicants->filter(function($applicant) use ($scoringService) {
                return $scoringService->hasAllRequiredScores($applicant);
            })->count();
            
            $overallRatings = $allApplicants->map(function($applicant) {
                $rating = $applicant->getOverallRating();
                return $rating ? $rating['overall_rating'] : null;
            })->filter()->values();
            
            $statsQuery = Applicant::query();
            $this->applySchoolYearFilter($statsQuery);
            
            $stats = [
                'qualifiers_count' => $qualifiersCount,
                'average_overall' => $overallRatings->count() > 0 ? round($overallRatings->avg(), 2) : 0,
                'average_uee' => round((clone $statsQuery)->whereNotNull('score')->avg('score') ?? 0, 2),
                'average_gwa' => round((clone $statsQuery)->whereNotNull('card_tor_gwa')->avg('card_tor_gwa') ?? 0, 2),
            ];

            // Return JSON for AJAX pagination requests only
            if ($request->ajax() && $request->header('Accept') && str_contains($request->header('Accept'), 'application/json')) {
                return response()->json([
                    'applicants' => $applicants->items(),
                    'pagination' => [
                        'current_page' => $applicants->currentPage(),
                        'last_page' => $applicants->lastPage(),
                        'per_page' => $applicants->perPage(),
                        'total' => $applicants->total(),
                        'from' => $applicants->firstItem(),
                        'to' => $applicants->lastItem(),
                    ],
                    'pagination_html' => $applicants->hasPages() ? $applicants->onEachSide(2)->appends($request->query())->links()->render() : '',
                ]);
            }

            return view('admin.applicants.exam-results', compact(
                'applicants',
                'statuses',
                'stats'
            ));

        } catch (Exception $e) {
            return redirect()->route('admin.applicants.index')
                ->with('error', 'Failed to load exam results: ' . $e->getMessage());
        }
    }

    /**
     * Export applicants to official EVSU XLSX format
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportEVSUResults(Request $request)
    {
        try {
            // Build query with filters
            $query = Applicant::with(['assignedInstructor', 'accessCode']);
            
            // Apply school year filter
            $this->applySchoolYearFilter($query);

            // Filter by status (only interview-completed by default)
            $status = $request->get('status', 'interview-completed');
            if ($status !== 'all') {
                $query->where('status', $status);
            }

            // Filter by instructor
            if ($request->has('instructor_id') && $request->instructor_id) {
                if ($request->instructor_id === 'unassigned') {
                    $query->whereNull('assigned_instructor_id');
                } else {
                    $query->where('assigned_instructor_id', $request->instructor_id);
                }
            }

            // Filter by program/course
            if ($request->has('preferred_course') && $request->preferred_course) {
                $query->where('preferred_course', $request->preferred_course);
            }

            // Parameters: limit and sort
            $limit = (int) $request->get('limit', 120);
            if ($limit <= 0) { $limit = 120; }
            $sort = $request->get('sort', 'overall_desc'); // overall_desc | overall_asc

            // Get applicants and sort by Overall Rating
            $collection = $query->get()->filter(function($applicant) {
                // Only include applicants with all required scores
                return $applicant->hasAllRequiredScores();
            });

            if ($sort === 'overall_asc') {
                $collection = $collection->sortBy(function($applicant) {
                    $rating = $applicant->getOverallRating();
                    return $rating ? $rating['overall_rating'] : 0;
                });
            } else { // default: overall_desc
                $collection = $collection->sortByDesc(function($applicant) {
                    $rating = $applicant->getOverallRating();
                    return $rating ? $rating['overall_rating'] : 0;
                });
            }

            // Apply Top N limit and reindex to ensure numbering starts at 1
            $applicants = $collection->take($limit)->values();

            // Prepare filters for export
            $exportFilters = [
                'campus' => $request->get('campus', 'Ormoc Campus'),
                'college' => $request->get('college', 'College'),
                'department' => $request->get('department', 'Department'),
                'program_code' => $request->get('program_code', 'BSIT'),
                'program_description' => $request->get('program_description', 'Bachelor of Science in Information Technology'),
                'academic_year' => $request->get('academic_year', date('Y') . '-' . (date('Y') + 1)),
                'release_date' => $request->get('release_date', now()->format('Y-m-d')),
            ];

            // Create export instance
            $export = new \App\Exports\EVSUResultsExport($applicants, $exportFilters);
            $tempFile = $export->export();

            // Generate filename
            $filename = 'EVSU_Entrance_Results_' . 
                       str_replace(' ', '_', $exportFilters['program_code']) . '_' . 
                       date('Y-m-d_His') . '.xlsx';

            return response()->download($tempFile, $filename)->deleteFileAfterSend(true);

        } catch (Exception $e) {
            \Log::error('EVSU export failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Failed to export results: ' . $e->getMessage());
        }
    }

    /**
     * Bulk schedule exams for applicants
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkScheduleExams(Request $request): JsonResponse
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'applicant_ids' => 'required|array|min:1',
                'applicant_ids.*' => 'exists:applicants,applicant_id',
                'scheduled_date' => 'required|date|after_or_equal:today',
                'scheduled_time' => 'required|date_format:H:i',
                'venue' => 'nullable|string|max:500',
                'special_instructions' => 'nullable|string|max:2000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            $applicantIds = $request->applicant_ids;
            $scheduledDate = $request->scheduled_date;
            $scheduledTime = $request->scheduled_time;
            $venue = $request->venue;
            $specialInstructions = $request->special_instructions;
            $scheduledBy = Auth::id();

            $scheduledCount = 0;
            $skippedCount = 0;
            $errors = [];

            // Check if exam availability window is set and validate schedule date is within window
            $activeExam = Exam::where('is_active', true)->first();
            if ($activeExam) {
                $scheduleDateTime = \Carbon\Carbon::parse($scheduledDate . ' ' . $scheduledTime);
                
                if ($activeExam->starts_at && $scheduleDateTime->lt($activeExam->starts_at)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Scheduled date/time must be after exam availability start: ' . 
                                    $activeExam->starts_at->setTimezone('Asia/Manila')->format('M d, Y g:i A')
                    ], 422);
                }
                
                if ($activeExam->ends_at && $scheduleDateTime->gt($activeExam->ends_at)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Scheduled date/time must be before exam availability end: ' . 
                                    $activeExam->ends_at->setTimezone('Asia/Manila')->format('M d, Y g:i A')
                    ], 422);
                }
            }

            DB::transaction(function () use (
                $applicantIds, 
                $scheduledDate, 
                $scheduledTime, 
                $venue, 
                $specialInstructions, 
                $scheduledBy,
                &$scheduledCount, 
                &$skippedCount, 
                &$errors
            ) {
                foreach ($applicantIds as $applicantId) {
                    try {
                        $applicant = Applicant::findOrFail($applicantId);

                        // Skip if already scheduled (status = exam-scheduled)
                        if ($applicant->status === 'exam-scheduled') {
                            // Check if there's an existing schedule
                            $existingSchedule = ExamSchedule::where('applicant_id', $applicantId)
                                ->where('status', 'scheduled')
                                ->first();
                            
                            if ($existingSchedule) {
                                $errors[] = "Applicant {$applicant->full_name} is already scheduled for {$existingSchedule->formatted_date_time}";
                                $skippedCount++;
                                continue;
                            } else {
                                // Status is exam-scheduled but no schedule record - fix inconsistency
                                $applicant->update(['status' => 'pending']);
                            }
                        }

                        // Skip if already completed exam
                        if ($applicant->status === 'exam-completed') {
                            $errors[] = "Applicant {$applicant->full_name} has already completed the exam.";
                            $skippedCount++;
                            continue;
                        }

                        // Cancel any existing schedules for this applicant
                        ExamSchedule::where('applicant_id', $applicantId)
                            ->where('status', 'scheduled')
                            ->update(['status' => 'cancelled']);

                        // Create new schedule
                        ExamSchedule::create([
                            'applicant_id' => $applicantId,
                            'scheduled_date' => $scheduledDate,
                            'scheduled_time' => $scheduledTime,
                            'venue' => $venue,
                            'special_instructions' => $specialInstructions,
                            'scheduled_by' => $scheduledBy,
                            'status' => 'scheduled',
                        ]);

                        // Update applicant status
                        $applicant->update(['status' => 'exam-scheduled']);

                        $scheduledCount++;
                    } catch (Exception $e) {
                        $errors[] = "Failed to schedule exam for applicant #{$applicantId}: {$e->getMessage()}";
                        $skippedCount++;
                    }
                }
            });

            $message = "Successfully scheduled {$scheduledCount} applicant(s) for exam.";
            if ($skippedCount > 0) {
                $message .= " {$skippedCount} skipped.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'scheduled_count' => $scheduledCount,
                    'skipped_count' => $skippedCount,
                    'errors' => $errors
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule exams: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reschedule/edit exam schedule
     *
     * @param Request $request
     * @param int $scheduleId
     * @return JsonResponse
     */
    public function rescheduleExam(Request $request, $scheduleId): JsonResponse
    {
        try {
            $schedule = ExamSchedule::findOrFail($scheduleId);

            // Validate request
            $validator = Validator::make($request->all(), [
                'scheduled_date' => 'required|date|after_or_equal:today',
                'scheduled_time' => 'required|date_format:H:i',
                'venue' => 'nullable|string|max:500',
                'special_instructions' => 'nullable|string|max:2000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if exam availability window is set and validate schedule date is within window
            $activeExam = Exam::where('is_active', true)->first();
            if ($activeExam) {
                $scheduleDateTime = \Carbon\Carbon::parse($request->scheduled_date . ' ' . $request->scheduled_time);
                
                if ($activeExam->starts_at && $scheduleDateTime->lt($activeExam->starts_at)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Scheduled date/time must be after exam availability start: ' . 
                                    $activeExam->starts_at->setTimezone('Asia/Manila')->format('M d, Y g:i A')
                    ], 422);
                }
                
                if ($activeExam->ends_at && $scheduleDateTime->gt($activeExam->ends_at)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Scheduled date/time must be before exam availability end: ' . 
                                    $activeExam->ends_at->setTimezone('Asia/Manila')->format('M d, Y g:i A')
                    ], 422);
                }
            }

            DB::transaction(function () use ($schedule, $request) {
                // Mark old schedule as cancelled and create new one
                $oldScheduleId = $schedule->exam_schedule_id;
                
                // Create new schedule record (keeping history)
                $newSchedule = ExamSchedule::create([
                    'applicant_id' => $schedule->applicant_id,
                    'scheduled_date' => $request->scheduled_date,
                    'scheduled_time' => $request->scheduled_time,
                    'venue' => $request->venue,
                    'special_instructions' => $request->special_instructions,
                    'scheduled_by' => Auth::id(),
                    'status' => 'scheduled',
                    'previous_schedule_id' => $oldScheduleId,
                    'reschedule_count' => $schedule->reschedule_count + 1,
                ]);

                // Cancel old schedule
                $schedule->update(['status' => 'cancelled']);

                // Reset notification status for new schedule
                $newSchedule->update([
                    'notification_sent' => false,
                    'notification_sent_at' => null,
                ]);
            });

            // Get the new schedule (latest one for this applicant)
            $newSchedule = ExamSchedule::where('applicant_id', $schedule->applicant_id)
                ->where('status', 'scheduled')
                ->latest()
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Exam schedule updated successfully!',
                'schedule' => $newSchedule
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Schedule not found.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reschedule exam: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get exam schedule for an applicant
     *
     * @param int $applicantId
     * @return JsonResponse
     */
    public function getApplicantSchedule($applicantId): JsonResponse
    {
        try {
            $schedule = ExamSchedule::where('applicant_id', $applicantId)
                ->where('status', 'scheduled')
                ->latest()
                ->first();

            if (!$schedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'No scheduled exam found for this applicant.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'schedule' => $schedule
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send exam notifications to scheduled applicants
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
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first()
                ], 422);
            }

            $applicantIds = $request->applicant_ids;
            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            // Get applicants with their access codes and schedules
            $applicants = Applicant::with(['accessCode', 'latestExamSchedule'])
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

                    // Check if applicant has a scheduled exam
                    $schedule = $applicant->latestExamSchedule;
                    if (!$schedule) {
                        $errors[] = "Applicant {$applicant->full_name} is not scheduled for an exam. Please schedule first.";
                        $failedCount++;
                        continue;
                    }

                    // Format date and time from schedule
                    $examDate = $schedule->scheduled_date->format('F j, Y');
                    $examTime = \Carbon\Carbon::parse($schedule->scheduled_time)->format('g:i A');
                    $examVenue = $schedule->venue ?? 'To Be Announced';
                    $specialInstructions = $schedule->special_instructions;

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

                    // Mark notification as sent
                    $schedule->markNotificationSent();

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

    /**
     * Dispatch statistics update event
     */
    protected function dispatchStatisticsUpdate()
    {
        // Statistics exclude archived applicants (SoftDeletes automatically excludes them)
        $stats = [
            'total' => Applicant::count(),
            'pending' => Applicant::where('status', 'pending')->count(),
            'exam_completed' => Applicant::where('status', 'exam-completed')->count(),
            'interview_scheduled' => Applicant::where('status', 'interview-scheduled')->count(),
            'interview_completed' => Applicant::where('status', 'interview-completed')->count(),
            'admitted' => Applicant::where('status', 'admitted')->count(),
            'rejected' => Applicant::where('status', 'rejected')->count(),
            'with_access_codes' => Applicant::whereHas('accessCode')->count(),
            'without_access_codes' => Applicant::whereDoesntHave('accessCode')->count(),
        ];

        \App\Helpers\BroadcastHelper::safeDispatch(new \App\Events\StatisticsUpdated($stats));
    }

    /**
     * Archive all applicants (soft delete)
     * 
     * DEPRECATED: Archiving applicants is no longer used.
     * Applicants are now filtered by school year instead.
     * This method is kept for backward compatibility but returns an error.
     */
    public function archiveAll()
    {
        return response()->json([
            'success' => false,
            'message' => 'Archiving applicants is no longer available. Use the school year filter to view applicants by academic year.',
        ], 410); // 410 Gone - resource no longer available
    }

    /**
     * Get archived applicants
     * 
     * DEPRECATED: Archiving applicants is no longer used.
     * Applicants are now filtered by school year instead.
     */
    public function archivedHistory(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Archiving applicants is no longer available. Use the school year filter to view applicants by academic year.',
            'applicants' => [],
            'pagination' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 20,
                'total' => 0,
                'from' => null,
                'to' => null,
            ],
        ], 410);
        
        /* OLD CODE - DISABLED
        try {
            $query = Applicant::onlyTrashed()
                ->with(['assignedInstructor', 'accessCode'])
                ->orderBy('deleted_at', 'desc');

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('middle_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email_address', 'like', "%{$search}%")
                      ->orWhere('application_no', 'like', "%{$search}%");
                });
            }

            $applicants = $query->paginate(20);

            // Transform paginator items
            $items = $applicants->getCollection()->map(function ($applicant) {
                return [
                    'applicant_id' => $applicant->applicant_id,
                    'application_no' => $applicant->application_no ?: $applicant->formatted_applicant_no,
                    'full_name' => $applicant->full_name,
                    'email_address' => $applicant->email_address,
                    'phone_number' => $applicant->phone_number,
                    'status' => $applicant->status,
                    'assigned_instructor' => $applicant->assignedInstructor ? [
                        'user_id' => $applicant->assignedInstructor->user_id,
                        'full_name' => $applicant->assignedInstructor->full_name,
                    ] : null,
                    'created_at' => $applicant->created_at->format('M d, Y g:i A'),
                    'deleted_at' => $applicant->deleted_at->format('M d, Y g:i A'),
                ];
            })->values();

            return response()->json([
                'success' => true,
                'applicants' => $items,
                'pagination' => [
                    'current_page' => $applicants->currentPage(),
                    'last_page' => $applicants->lastPage(),
                    'per_page' => $applicants->perPage(),
                    'total' => $applicants->total(),
                    'from' => $applicants->firstItem(),
                    'to' => $applicants->lastItem(),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to load archived applicants: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load archived applicants: ' . $e->getMessage(),
            ], 500);
        }
        */
    }

    /**
     * Restore all archived applicants
     * 
     * DEPRECATED: Archiving applicants is no longer used.
     * Applicants are now filtered by school year instead.
     */
    public function restoreAll()
    {
        return response()->json([
            'success' => false,
            'message' => 'Archiving applicants is no longer available. Use the school year filter to view applicants by academic year.',
        ], 410);
    }

    /**
     * Permanently delete all archived applicants
     * 
     * DEPRECATED: Archiving applicants is no longer used.
     * Applicants are now filtered by school year instead.
     */
    public function permanentlyDeleteAll()
    {
        return response()->json([
            'success' => false,
            'message' => 'Archiving applicants is no longer available. Use the school year filter to view applicants by academic year.',
        ], 410);
    }
}