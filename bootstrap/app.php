<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Webkul\Installer\Http\Middleware\CanInstall;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(CanInstall::class);

        $middleware->encryptCookies(except: [
            'dark_mode',
            'sidebar_collapsed',
        ]);

        /**
         * Public inbound email and embedded web-form endpoints are not browser-session endpoints.
         * Keep the configured admin-path exception and the actual public web-form route aligned
         * with their registrations so provider/website submissions are not rejected by CSRF.
         */
        $adminPath = trim(env('APP_ADMIN_PATH', 'admin'), '/');

        $middleware->validateCsrfTokens(except: [
            $adminPath.'/mail/inbound-parse',
            $adminPath.'/web-forms/forms/*',
            'web-forms/forms/*',
        ]);

        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
