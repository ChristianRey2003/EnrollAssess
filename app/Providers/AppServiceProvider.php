<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Brevo mailer only if the package is installed
        if (class_exists(\Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoTransportFactory::class)) {
            try {
                Mail::extend('brevo', function (array $config = []) {
                    $factory = new \Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoTransportFactory();
                    return $factory->create(
                        new \Symfony\Component\Mailer\Transport\Dsn(
                            'brevo+api',
                            'default',
                            config('services.brevo.key')
                        )
                    );
                });
            } catch (\Exception $e) {
                Log::warning('Failed to register Brevo mailer: ' . $e->getMessage());
            }
        }
    }
}
