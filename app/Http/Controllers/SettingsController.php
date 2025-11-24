<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use App\Models\Exam;
use App\Services\MailConfigurationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    /**
     * Mail Configuration Service
     *
     * @var MailConfigurationService
     */
    protected $mailConfigService;

    /**
     * Constructor
     */
    public function __construct(MailConfigurationService $mailConfigService)
    {
        $this->mailConfigService = $mailConfigService;
    }

    /**
     * Display the settings page
     */
    public function index()
    {
        // Ensure all required email settings exist
        $this->ensureEmailSettingsExist();
        
        // Get only email settings
        $emailSettings = Settings::where('group', 'email')->get();

        return view('admin.settings.index', compact('emailSettings'));
    }

    /**
     * Ensure all required email settings exist in database
     * Creates missing settings with default values
     */
    protected function ensureEmailSettingsExist()
    {
        try {
            $requiredSettings = [
                [
                    'key' => 'mail_mailer',
                    'value' => 'ses',
                    'group' => 'email',
                    'type' => 'select',
                    'description' => 'Mail driver (ses for Amazon SES, log for testing)',
                ],
                [
                    'key' => 'mail_from_address',
                    'value' => '',
                    'group' => 'email',
                    'type' => 'text',
                    'description' => 'From email address (must be verified in SES)',
                ],
                [
                    'key' => 'mail_from_name',
                    'value' => 'EnrollAssess System',
                    'group' => 'email',
                    'type' => 'text',
                    'description' => 'Display name for sent emails',
                ],
                [
                    'key' => 'aws_access_key_id',
                    'value' => '',
                    'group' => 'email',
                    'type' => 'password',
                    'description' => 'AWS Access Key ID for Amazon SES (required for SES mailer)',
                ],
                [
                    'key' => 'aws_secret_access_key',
                    'value' => '',
                    'group' => 'email',
                    'type' => 'password',
                    'description' => 'AWS Secret Access Key for Amazon SES (required for SES mailer)',
                ],
                [
                    'key' => 'aws_region',
                    'value' => 'ap-southeast-1',
                    'group' => 'email',
                    'type' => 'select',
                    'description' => 'AWS Region for SES (ap-southeast-1: Singapore - recommended for Philippines)',
                ],
            ];

            foreach ($requiredSettings as $setting) {
                Settings::updateOrCreate(
                    ['key' => $setting['key']],
                    $setting
                );
            }
            
            // Clear cache to ensure fresh data
            Settings::clearCache();
        } catch (\Exception $e) {
            Log::error('Failed to ensure email settings exist: ' . $e->getMessage());
            // Don't throw - let the page load even if settings creation fails
        }
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        try {
            // Validate based on setting types
            $validator = Validator::make($request->all(), [
                'settings' => 'required|array',
                'settings.*' => 'nullable',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->with('error', 'Invalid settings data provided.');
            }

            $settings = $request->input('settings', []);
            $updatedCount = 0;

            foreach ($settings as $key => $value) {
                // Get the setting to determine its type
                $setting = Settings::where('key', $key)->first();
                
                if ($setting) {
                    // Handle boolean values
                    if ($setting->type === 'boolean') {
                        $value = $value ? 'true' : 'false';
                    }
                    
                    // Handle password fields - only update if not empty
                    if ($setting->type === 'password' && empty($value)) {
                        continue;
                    }

                    Settings::setSetting($key, $value, $setting->group);
                    $updatedCount++;
                }
            }

            // Clear all settings cache
            Settings::clearCache();

            // Reload mail configuration from database
            $this->reloadMailConfig();

            return redirect()->route('admin.settings')
                ->with('success', "Successfully updated {$updatedCount} settings. Mail configuration reloaded.");

        } catch (\Exception $e) {
            Log::error('Settings update failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to update settings. Please try again.')
                ->withInput();
        }
    }

    /**
     * Test email configuration
     */
    public function testEmail(Request $request)
    {
        try {
            $request->validate([
                'test_email' => 'required|email',
            ]);

            $testEmail = $request->input('test_email');

            // Reload mail configuration to ensure we're using latest settings
            $this->reloadMailConfig();

            // Send test email
            Mail::raw('This is a test email from EnrollAssess System. If you received this, your email configuration is working correctly!', function ($message) use ($testEmail) {
                $message->to($testEmail)
                    ->subject('EnrollAssess - Email Configuration Test');
            });

            return response()->json([
                'success' => true,
                'message' => "Test email sent successfully to {$testEmail}! Please check your inbox."
            ]);

        } catch (\Exception $e) {
            Log::error('Test email failed: ' . $e->getMessage());

            // Check if this is an SES sandbox mode error
            $errorMessage = 'Failed to send test email: ' . $e->getMessage();
            
            if ($this->mailConfigService->isSandboxModeError($e)) {
                $errorMessage = $this->mailConfigService->getSandboxModeErrorMessage();
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 500);
        }
    }

    /**
     * Reload mail configuration from database
     */
    protected function reloadMailConfig()
    {
        try {
            // Use MailConfigurationService to load configuration
            $this->mailConfigService->loadFromDatabase();
        } catch (\Exception $e) {
            Log::error('Failed to reload mail config: ' . $e->getMessage());
        }
    }

    /**
     * Reset settings to defaults
     */
    public function reset(Request $request)
    {
        try {
            $group = $request->input('group');

            if ($group) {
                // Reset specific group
                Settings::where('group', $group)->delete();
            } else {
                // Reset all settings
                Settings::truncate();
            }

            // Re-run seeder to restore defaults
            \Artisan::call('db:seed', ['--class' => 'SystemSettingsSeeder']);

            Settings::clearCache();

            return redirect()->route('admin.settings')
                ->with('success', 'Settings have been reset to default values.');

        } catch (\Exception $e) {
            Log::error('Settings reset failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to reset settings. Please try again.');
        }
    }

    /**
     * Get archived (inactive) exams (question banks)
     */
    public function archivedQuestions()
    {
        try {
            $exams = Exam::where('is_active', false)
                ->withCount(['questions', 'activeQuestions'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            // Transform paginator items
            $items = $exams->getCollection()->map(function ($exam) {
                return [
                    'id' => $exam->exam_id,
                    'title' => $exam->title,
                    'description' => $exam->description,
                    'duration_minutes' => $exam->duration_minutes,
                    'total_items' => $exam->total_items,
                    'mcq_quota' => $exam->mcq_quota,
                    'tf_quota' => $exam->tf_quota,
                    'total_questions' => $exam->questions_count,
                    'active_questions' => $exam->active_questions_count,
                    'created_at' => $exam->created_at->format('M d, Y g:i A'),
                    'starts_at' => $exam->starts_at ? $exam->starts_at->format('M d, Y g:i A') : null,
                    'ends_at' => $exam->ends_at ? $exam->ends_at->format('M d, Y g:i A') : null,
                ];
            })->values();

            return response()->json([
                'success' => true,
                'exams' => $items,
                'pagination' => [
                    'current_page' => $exams->currentPage(),
                    'last_page' => $exams->lastPage(),
                    'total' => $exams->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load archived exams: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load archived exams: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restore/publish archived exam (make it active)
     */
    public function restoreArchivedQuestions(Request $request)
    {
        try {
            $examId = $request->input('exam_id');
            
            if (!$examId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Exam ID is required.',
                ], 400);
            }

            $exam = Exam::findOrFail($examId);

            if ($exam->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'This exam is already active.',
                ], 400);
            }

            // Deactivate all other exams and activate this one
            DB::transaction(function () use ($exam) {
                Exam::where('exam_id', '!=', $exam->exam_id)->update(['is_active' => false]);
                $exam->update(['is_active' => true]);
            });

            return response()->json([
                'success' => true,
                'message' => "Successfully restored and published '{$exam->title}'. This is now the active exam.",
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to restore archived exam: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore exam: ' . $e->getMessage(),
            ], 500);
        }
    }
}
