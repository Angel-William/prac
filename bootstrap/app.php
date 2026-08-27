<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        // [EXAM] PSRS 1.e - registers routes/api.php under the /api prefix.
        // [LEARN] This one line replaces `php artisan install:api` and skips
        //         installing Sanctum, which a shared-key API does not need.
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        /*
        | [LEARN] MIDDLEWARE ALIASES LIVE HERE IN LARAVEL 11 AND 12.
        |         There is no app/Http/Kernel.php any more. Any tutorial that
        |         tells you to edit Kernel.php is written for Laravel 10.
        |
        |         'role'   -> used as 'role:admin,editor' in routes/web.php
        |         'apikey' -> used in routes/api.php
        */
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'apikey' => \App\Http\Middleware\ApiKey::class,
        ]);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
