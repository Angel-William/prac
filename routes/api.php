<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\JobController;

/*
|--------------------------------------------------------------------------
| API ROUTES - for third-party services
|--------------------------------------------------------------------------
| [EXAM] PSRS Part 1.e - "APIs exposed to third-party services must be secured
|        using API key authentication."
|
| [LEARN] This file did NOT exist. Laravel 11 onward ships without routes/api.php
|         on purpose. The usual advice is `php artisan install:api`, but that
|         also installs Sanctum, which we do not need for a shared-key API.
|         Instead we registered this file by hand in bootstrap/app.php:
|
|             api: __DIR__.'/../routes/api.php',
|
|         Laravel then prefixes every route below with /api automatically.
|         So `Route::get('jobs', ...)` here answers on /api/jobs.
|
| [LEARN] No session, no cookies, no CSRF token - a machine has no browser.
|         Authentication is the X-API-Key header, checked by
|         App\Http\Middleware\ApiKey.
|
| DEMO SCRIPT - run both, show the 401 then the 200:
|     curl -i http://localhost:8000/api/jobs
|     curl -i -H "X-API-Key: psrs-secret-key-2026" http://localhost:8000/api/jobs
*/

Route::middleware('apikey')->group(function () {
    Route::get('jobs', [JobController::class, 'apiIndex'])->name('api.jobs.index');

    Route::post('applications', [ApplicationController::class, 'apiStore'])
        ->name('api.applications.store');
});
