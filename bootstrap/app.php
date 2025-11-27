<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'ajax.auth' => \App\Http\Middleware\AjaxAuthMiddleware::class,
            'no.cache' => \App\Http\Middleware\NoCacheMiddleware::class,
            'rate.limit' => \App\Http\Middleware\RateLimitPublicRoutes::class,
            'capability' => \App\Http\Middleware\CheckDelegatedPermission::class,
            'school.year' => \App\Http\Middleware\SetSchoolYear::class,
        ]);
        
        // Apply school year middleware to admin routes
        $middleware->web(append: [
            \App\Http\Middleware\SetSchoolYear::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
