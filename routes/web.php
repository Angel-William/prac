<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\VacancyController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| JOB PORTAL - web routes
|--------------------------------------------------------------------------
| [LEARN] READ THIS FILE FIRST when you open the project. Routes are the
|         table of contents of a Laravel app: every feature is one line here.
|
| [LEARN] Wayfinder watches this file. Every named route becomes a typed
|         TypeScript helper under resources/js/routes/. `jobs.index` here
|         becomes `import jobs from '@/routes/jobs'` -> `jobs.index()` there.
|         If a helper is missing in React, it is because the route is missing
|         here, or because `npm run dev` is not running.
*/

// [REUSE] Straight from the starter kit - Route::inertia() renders a page with
//         no controller at all. Perfect for static pages.
Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    /*
    | [REUSE] 'auth' and 'verified' came with the starter kit and are
    |         [EXAM] PSRS Part 1.a and 1.d - only logged-in, email-verified
    |         users get past this line. You wrote none of it.
    */

    Route::inertia('dashboard', 'dashboard')->name('dashboard');



    /*
    |----------------------------------------------------------------------
    | JOB PORTAL
    |----------------------------------------------------------------------
    */

    // --- Anyone signed in may browse jobs and apply -----------------------
    // [LEARN] ->only() registers just those two of the seven resource routes.
    //         We skip create and edit because our form is inline on the index
    //         page, and we split store/update/destroy into the guarded group
    //         below. Splitting one resource across two groups is legal as long
    //         as the sets do not overlap.
    Route::resource('jobs', JobController::class)->only(['index', 'show']);

    // [EXAM] PSRS 2.II - the apply endpoint.
    // [LEARN] {job} matches the `Job $job` type hint in the controller, so
    //         Laravel fetches the record for you (route model binding).
    Route::post('jobs/{job}/apply', [ApplicationController::class, 'store'])
        ->name('jobs.apply');

    /*
    | [LEARN] A "MY SOMETHING" PAGE. No role middleware - every applicant may
    |         open it. What protects the data is the where() clause in the
    |         controller scoping it to the signed-in user. Middleware cannot
    |         express "only your own rows"; a query can.
    */
    Route::get('my-applications', [ApplicationController::class, 'mine'])
        ->name('applications.mine');

    // --- Admin + editor only ---------------------------------------------
    /*
    | [EXAM] PSRS Part 1.c - Role-Based Access Control, enforced here.
    | [LEARN] 'role:admin,editor' -> App\Http\Middleware\CheckRole, with
    |         "admin" and "editor" passed as the $roles argument. The alias
    |         'role' is registered in bootstrap/app.php.
    |         A viewer who types /jobs/1/edit gets a clean 403. Demo that.
    */
    Route::middleware('role:admin,editor')->group(function () {
        Route::resource('jobs', JobController::class)->only(['store', 'update', 'destroy']);

        // [EXAM] PSRS 2.IV.d - the review summary with the five named columns.
        Route::get('applications/review', [ApplicationController::class, 'review'])
            ->name('applications.review');

        // [EXAM] UTUMISHI - "Report & View".
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    });

    // --- Admin only -------------------------------------------------------
    /*
    | [EXAM] PSRS 1.c - assigning roles to users.
    | [LEARN] The middleware here is 'role:admin' with NO editor. Compare it
    |         with the group above. Editors manage jobs; only admins manage
    |         people. Different guards on different groups IS the R in RBAC.
    */
    Route::middleware('role:admin')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');

        Route::patch('users/{user}/role', [UserController::class, 'updateRole'])
            ->name('users.role');
    });
});

// [REUSE] Starter kit: profile, password, 2FA, passkeys. Do not touch.
require __DIR__.'/settings.php';
