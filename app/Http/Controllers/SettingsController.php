<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    /**
     * Display the settings page
     */
    public function index()
    {
        // Get all settings grouped by their category
        $emailSettings = Settings::where('group', 'email')->get();
        $systemSettings = Settings::where('group', 'system')->get();
        $examSettings = Settings::where('group', 'exam')->get();
        $notificationSettings = Settings::where('group', 'notifications')->get();
        $interviewSettings = Settings::where('group', 'interview')->get();

        return view('admin.settings.index', compact(
            'emailSettings',
            'systemSettings',
            'examSettings',
            'notificationSettings',
            'interviewSettings'
        ));
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

            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reload mail configuration from database
     */
    protected function reloadMailConfig()
    {
        try {
            // Get current email settings from database
            $emailSettings = Settings::getGroup('email');

            if ($emailSettings->isNotEmpty()) {
                // Update mail configuration
                config([
                    'mail.default' => $emailSettings->get('mail_mailer', env('MAIL_MAILER', 'smtp')),
                    'mail.mailers.smtp' => [
                        'transport' => 'smtp',
                        'host' => $emailSettings->get('mail_host', env('MAIL_HOST', 'smtp.gmail.com')),
                        'port' => (int) $emailSettings->get('mail_port', env('MAIL_PORT', 587)),
                        'username' => $emailSettings->get('mail_username', env('MAIL_USERNAME')),
                        'password' => $emailSettings->get('mail_password', env('MAIL_PASSWORD')),
                        'encryption' => $emailSettings->get('mail_encryption', env('MAIL_ENCRYPTION', 'tls')),
                        'timeout' => null,
                        'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
                    ],
                    'mail.from' => [
                        'address' => $emailSettings->get('mail_from_address', env('MAIL_FROM_ADDRESS', 'hello@example.com')),
                        'name' => $emailSettings->get('mail_from_name', env('MAIL_FROM_NAME', 'EnrollAssess System')),
                    ],
                ]);
            }
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
}
