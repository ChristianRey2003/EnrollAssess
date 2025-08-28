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
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('email_address', 'like', "%{$search}%")
                      ->orWhere('application_no', 'like', "%{$search}%");
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
            'full_name' => 'required|string|max:255',
            'email_address' => 'required|email|unique:applicants,email_address',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'education_background' => 'nullable|string|max:255',
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
        $applicant = Applicant::with(['examSet.exam', 'accessCode', 'interviews', 'results'])
                              ->findOrFail($id);
        
        return view('admin.applicants.show', compact('applicant'));
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
            'full_name' => 'required|string|max:255',
            'email_address' => 'required|email|unique:applicants,email_address,' . $id . ',applicant_id',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'education_background' => 'nullable|string|max:255',
            'exam_set_id' => 'nullable|exists:exam_sets,exam_set_id',
            'status' => 'required|in:pending,exam-completed,interview-scheduled,interview-completed,admitted,rejected',
            'score' => 'nullable|numeric|min:0|max:9999.99',
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
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
            'exam_set_id' => 'nullable|exists:exam_sets,exam_set_id',
            'generate_access_codes' => 'boolean',
            'access_code_expiry_hours' => 'nullable|integer|min:1|max:720', // Max 30 days
        ]);

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
            $header = str_getcsv($lines[0]);
            
            $importResults = [
                'total' => 0,
                'successful' => 0,
                'failed' => 0,
                'errors' => [],
                'imported_applicants' => [],
            ];

            DB::transaction(function () use ($lines, $header, $request, &$importResults) {
                for ($i = 1; $i < count($lines); $i++) {
                    $line = trim($lines[$i]);
                    if (empty($line)) continue;

                    $importResults['total']++;
                    $lineNumber = $i + 1;

                    try {
                        $data = str_getcsv($line);
                        $record = array_combine($header, $data);

                        // Validate required fields
                        $validator = Validator::make($record, [
                            'full_name' => 'required|string|max:255',
                            'email_address' => 'required|email|unique:applicants,email_address',
                            'phone_number' => 'nullable|string|max:20',
                            'address' => 'nullable|string',
                            'education_background' => 'nullable|string|max:255',
                        ]);

                        if ($validator->fails()) {
                            $importResults['failed']++;
                            $importResults['errors'][] = "Line {$lineNumber}: " . implode(', ', $validator->errors()->all());
                            continue;
                        }

                        // Create applicant
                        $applicantData = $validator->validated();
                        $applicantData['application_no'] = Applicant::generateApplicationNumber();
                        $applicantData['exam_set_id'] = $request->exam_set_id;

                        $applicant = Applicant::create($applicantData);

                        // Generate access code if requested
                        if ($request->boolean('generate_access_codes')) {
                            $accessCode = AccessCode::createForApplicant(
                                $applicant->applicant_id,
                                'BSIT',
                                8,
                                $request->access_code_expiry_hours ?? 72
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
        $csv = "full_name,email_address,phone_number,address,education_background\n";
        $csv .= "Juan dela Cruz,juan.delacruz@email.com,09123456789,123 Main St City Province,Senior High School Graduate\n";
        $csv .= "Maria Santos,maria.santos@email.com,09987654321,456 Oak Ave City Province,Technical-Vocational Graduate\n";

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
}