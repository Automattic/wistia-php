<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

// Fresh-app scaffold: keep Laravel's normal web/console routes and add the
// webhook file explicitly. Merge this addition into an existing app's bootstrap.
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: static function (): void {
            // Only this route file is outside "web"; normal web routes retain CSRF.
            // A sender cannot supply a browser-session CSRF token. The controller
            // authenticates these machine-to-machine requests with the webhook HMAC.
            Route::group([], base_path('routes/wistia.php'));
        },
    )
    ->withMiddleware(static function (Middleware $middleware): void {
        //
    })
    ->withExceptions(static function (Exceptions $exceptions): void {
        //
    })->create();
