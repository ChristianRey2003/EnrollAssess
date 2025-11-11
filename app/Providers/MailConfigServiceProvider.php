<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Services\MailConfigurationService;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the MailConfigurationService as a singleton
        $this->app->singleton(MailConfigurationService::class, function ($app) {
            return new MailConfigurationService();
        });
    }

    /**
     * Bootstrap services.
     * Load mail configuration from database settings
     */
    public function boot(): void
    {
        // Always try to load mail config from database
        // Only skip if table doesn't exist (handled in try-catch)
        // This allows queue workers and scheduled tasks to send emails

        try {
            // Check if settings table exists
            if (!Schema::hasTable('system_settings')) {
                return;
            }

            // Use MailConfigurationService to load configuration
            $mailConfigService = $this->app->make(MailConfigurationService::class);
            $mailConfigService->loadFromDatabase();

        } catch (\Exception $e) {
            // If anything goes wrong, just use ENV settings (fallback)
            // This prevents errors during migrations or when database is not ready
            report($e);
        }
    }
}
