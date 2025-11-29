<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use App\Models\Exam;
use App\Models\ActivityLog;
use App\Services\MailConfigurationService;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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

        // Get recent audit logs (limit to 20 for initial load)
        $auditLogs = ActivityLog::with('user')->latest()->take(20)->get();

        return view('admin.settings.index', compact('emailSettings', 'auditLogs'));
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
                    'value' => 'resend',
                    'group' => 'email',
                    'type' => 'select',
                    'description' => 'Mail driver (resend for Resend, ses for Amazon SES)',
                ],
                [
                    'key' => 'mail_from_address',
                    'value' => '',
                    'group' => 'email',
                    'type' => 'text',
                    'description' => 'From email address',
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
                [
                    'key' => 'resend_api_key',
                    'value' => '',
                    'group' => 'email',
                    'type' => 'password',
                    'description' => 'Resend API Key (get from https://resend.com/api-keys)',
                ],
                [
                    'key' => 'brevo_api_key',
                    'value' => '',
                    'group' => 'email',
                    'type' => 'password',
                    'description' => 'Brevo API Key (get from https://app.brevo.com/settings/keys/api)',
                ],
            ];

            foreach ($requiredSettings as $setting) {
                // Only create if it doesn't exist, don't overwrite existing values
                $existing = Settings::where('key', $setting['key'])->first();
                if (!$existing) {
                    Settings::create($setting);
                } else {
                    // Only update type and description if they're missing, preserve value
                    $update = [];
                    if (empty($existing->type)) {
                        $update['type'] = $setting['type'];
                    }
                    if (empty($existing->description)) {
                        $update['description'] = $setting['description'];
                    }
                    if (!empty($update)) {
                        $existing->update($update);
                    }
                }
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
                    // For password fields, empty string means "don't change", so skip it
                    if ($setting->type === 'password') {
                        // If value is empty or just whitespace, skip updating (preserve existing value)
                        if (empty(trim($value ?? ''))) {
                            continue;
                        }
                        // Only update if a new value was provided
                    }
                    
                    // Handle text/email fields - trim whitespace but allow empty strings
                    if (in_array($setting->type, ['text', 'email'])) {
                        $value = trim($value ?? '');
                    }

                    Settings::setSetting($key, $value, $setting->group);
                    $updatedCount++;
                } else {
                    // Setting doesn't exist, create it
                    // This shouldn't happen if ensureEmailSettingsExist runs, but handle it anyway
                    Log::warning("Setting '{$key}' not found, creating it");
                    Settings::create([
                        'key' => $key,
                        'value' => trim($value ?? ''),
                        'group' => 'email',
                        'type' => 'text',
                        'description' => 'Auto-created setting',
                    ]);
                    $updatedCount++;
                }
            }

            // Clear all settings cache
            Settings::clearCache();

            // Reload mail configuration from database
            $this->reloadMailConfig();

            ActivityLogger::log('update_settings', "Updated {$updatedCount} system settings.", ['count' => $updatedCount], Auth::id());

            return redirect()->route('admin.settings.index')
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

            // Check if mail configuration is set up
            $mailerType = Settings::getSetting('mail_mailer', 'resend');
            $fromAddress = Settings::getSetting('mail_from_address', '');
            
            if (empty($fromAddress)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please set a "From Address" in email settings before testing.'
                ], 400);
            }
            
            if ($mailerType === 'resend') {
                $resendApiKey = Settings::getSetting('resend_api_key', '');
                if (empty($resendApiKey)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please set your Resend API key in email settings before testing.'
                    ], 400);
                }
            }
            
            if ($mailerType === 'brevo') {
                $brevoApiKey = Settings::getSetting('brevo_api_key', '');
                if (empty($brevoApiKey)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please set your Brevo API key in email settings before testing.'
                    ], 400);
                }
            }

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
            Log::error('Test email exception: ' . $e->getTraceAsString());

            // Check if this is an SES sandbox mode error
            $errorMessage = 'Failed to send test email: ' . $e->getMessage();
            
            // Check for common configuration errors
            if (stripos($e->getMessage(), 'authentication') !== false || 
                stripos($e->getMessage(), '530') !== false ||
                stripos($e->getMessage(), 'check configuration') !== false ||
                stripos($e->getMessage(), 'api key') !== false) {
                $errorMessage = 'Email configuration error. Please check: ' . 
                    '<br>1. API key is set correctly' . 
                    '<br>2. From Address is set' . 
                    '<br>3. Mail Driver is set correctly' .
                    '<br><br>Error details: ' . $e->getMessage();
            }
            
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

            return redirect()->route('admin.settings.index')
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
