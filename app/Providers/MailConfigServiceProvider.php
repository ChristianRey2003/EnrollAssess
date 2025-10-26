<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use App\Models\Settings;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     * Load mail configuration from database settings
     */
    public function boot(): void
    {
        // Only load mail config from database after migrations have run
        if ($this->app->runningInConsole() && !$this->app->runningUnitTests()) {
            // Skip during migrations and other console commands that don't need mail
            return;
        }

        try {
            // Check if settings table exists
            if (!Schema::hasTable('system_settings')) {
                return;
            }

            // Load email settings from database
            $emailSettings = Settings::getGroup('email');

            // If we have email settings in database, use them
            if ($emailSettings->isNotEmpty()) {
                // Update mail configuration
                Config::set('mail.default', $emailSettings->get('mail_mailer', env('MAIL_MAILER', 'smtp')));
                
                // Update SMTP configuration
                Config::set('mail.mailers.smtp', [
                    'transport' => 'smtp',
                    'host' => $emailSettings->get('mail_host', env('MAIL_HOST', 'smtp.gmail.com')),
                    'port' => (int) $emailSettings->get('mail_port', env('MAIL_PORT', 587)),
                    'username' => $emailSettings->get('mail_username', env('MAIL_USERNAME')),
                    'password' => $emailSettings->get('mail_password', env('MAIL_PASSWORD')),
                    'encryption' => $emailSettings->get('mail_encryption', env('MAIL_ENCRYPTION', 'tls')),
                    'timeout' => null,
                    'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
                ]);

                // Update from address
                Config::set('mail.from', [
                    'address' => $emailSettings->get('mail_from_address', env('MAIL_FROM_ADDRESS', 'hello@example.com')),
                    'name' => $emailSettings->get('mail_from_name', env('MAIL_FROM_NAME', 'EnrollAssess System')),
                ]);
            }
        } catch (\Exception $e) {
            // If anything goes wrong, just use ENV settings (fallback)
            // This prevents errors during migrations or when database is not ready
            report($e);
        }
    }
}
