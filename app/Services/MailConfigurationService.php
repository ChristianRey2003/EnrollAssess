<?php

namespace App\Services;

use App\Models\Settings;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class MailConfigurationService
{
    /**
     * Load mail configuration from database and apply to config
     *
     * @return bool True if configuration was loaded successfully
     */
    public function loadFromDatabase(): bool
    {
        try {
            // Get email settings from database
            $emailSettings = Settings::getGroup('email');

            if ($emailSettings->isEmpty()) {
                return false;
            }

            // Get the mailer type (smtp, ses, log, etc.)
            $mailerType = $emailSettings->get('mail_mailer', env('MAIL_MAILER', 'smtp'));
            
            // Set default mailer
            Config::set('mail.default', $mailerType);

            // Configure specific mailer based on type
            $this->configureMailer($mailerType, $emailSettings);

            // Configure from address (common to all mailers)
            Config::set('mail.from', [
                'address' => $emailSettings->get('mail_from_address', env('MAIL_FROM_ADDRESS', 'hello@example.com')),
                'name' => $emailSettings->get('mail_from_name', env('MAIL_FROM_NAME', 'EnrollAssess System')),
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to load mail configuration from database: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Configure specific mailer based on type
     *
     * @param string $mailerType The type of mailer (smtp, ses, log, etc.)
     * @param \Illuminate\Support\Collection $settings Email settings from database
     * @return void
     */
    protected function configureMailer(string $mailerType, $settings): void
    {
        switch ($mailerType) {
            case 'smtp':
                $this->configureSMTP($settings);
                break;
            
            case 'ses':
                $this->configureSES($settings);
                break;
            
            case 'log':
                // Log mailer doesn't need additional configuration
                break;
            
            default:
                Log::warning("Unsupported mailer type: {$mailerType}, falling back to ENV configuration");
                break;
        }
    }

    /**
     * Configure SMTP mailer
     *
     * @param \Illuminate\Support\Collection $settings Email settings from database
     * @return void
     */
    protected function configureSMTP($settings): void
    {
        Config::set('mail.mailers.smtp', [
            'transport' => 'smtp',
            'host' => $settings->get('mail_host', env('MAIL_HOST', 'smtp.gmail.com')),
            'port' => (int) $settings->get('mail_port', env('MAIL_PORT', 587)),
            'username' => $settings->get('mail_username', env('MAIL_USERNAME')),
            'password' => $settings->get('mail_password', env('MAIL_PASSWORD')),
            'encryption' => $settings->get('mail_encryption', env('MAIL_ENCRYPTION', 'tls')),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ]);
    }

    /**
     * Configure Amazon SES mailer
     *
     * @param \Illuminate\Support\Collection $settings Email settings from database
     * @return void
     */
    protected function configureSES($settings): void
    {
        // Configure AWS SES credentials in services config
        Config::set('services.ses', [
            'key' => $settings->get('aws_access_key_id', env('AWS_ACCESS_KEY_ID')),
            'secret' => $settings->get('aws_secret_access_key', env('AWS_SECRET_ACCESS_KEY')),
            'region' => $settings->get('aws_region', env('AWS_DEFAULT_REGION', 'ap-southeast-1')),
        ]);

        // SES mailer configuration is already defined in config/mail.php
        // It automatically uses the services.ses configuration
    }

    /**
     * Get current mail configuration summary
     *
     * @return array Configuration summary
     */
    public function getConfigurationSummary(): array
    {
        $emailSettings = Settings::getGroup('email');
        $mailerType = $emailSettings->get('mail_mailer', env('MAIL_MAILER', 'smtp'));

        $summary = [
            'mailer_type' => $mailerType,
            'from_address' => $emailSettings->get('mail_from_address', env('MAIL_FROM_ADDRESS')),
            'from_name' => $emailSettings->get('mail_from_name', env('MAIL_FROM_NAME')),
        ];

        if ($mailerType === 'smtp') {
            $summary['smtp'] = [
                'host' => $emailSettings->get('mail_host', env('MAIL_HOST')),
                'port' => $emailSettings->get('mail_port', env('MAIL_PORT')),
                'encryption' => $emailSettings->get('mail_encryption', env('MAIL_ENCRYPTION')),
                'username' => $emailSettings->get('mail_username', env('MAIL_USERNAME')),
            ];
        } elseif ($mailerType === 'ses') {
            $summary['ses'] = [
                'region' => $emailSettings->get('aws_region', env('AWS_DEFAULT_REGION')),
                'has_credentials' => !empty($emailSettings->get('aws_access_key_id')) && !empty($emailSettings->get('aws_secret_access_key')),
            ];
        }

        return $summary;
    }

    /**
     * Check if SES error is due to sandbox mode/unverified email
     *
     * @param \Exception $exception The exception thrown during email sending
     * @return bool True if error is due to unverified email
     */
    public function isSandboxModeError(\Exception $exception): bool
    {
        $errorMessage = $exception->getMessage();
        
        // Common SES sandbox error messages
        $sandboxIndicators = [
            'Email address is not verified',
            'MessageRejected',
            'not verified',
            'sandbox',
        ];

        foreach ($sandboxIndicators as $indicator) {
            if (stripos($errorMessage, $indicator) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get user-friendly error message for SES sandbox mode
     *
     * @return string User-friendly error message
     */
    public function getSandboxModeErrorMessage(): string
    {
        return 'Amazon SES is in sandbox mode. Please verify the recipient email address in your AWS SES Console, ' .
               'or request production access to send emails to any address. ' .
               'Visit: https://console.aws.amazon.com/ses/';
    }
}

