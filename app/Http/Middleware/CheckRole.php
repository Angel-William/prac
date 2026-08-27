<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * [EXAM] PSRS Part 1.c - "Access to specific routes or resources must be
 *        restricted based on user roles."
 *
 * [REUSE] Same file shape as the starter kit's app/Http/Middleware/
 *         HandleAppearance.php - open that one to compare.
 *
 * [LEARN] Middleware is a GATE IN FRONT OF A ROUTE. It runs before the
 *         controller. Either it calls $next($request) (let them through) or it
 *         stops the request dead (abort / redirect).
 *
 * [LEARN] Registered as the alias 'role' in bootstrap/app.php.
 *         Since Laravel 11 there is NO app/Http/Kernel.php (this project is on
 *         Laravel 13) - any tutorial telling you to edit Kernel.php is for
 *         Laravel 10 or older.
 *
 * Usage in routes/web.php:
 *     Route::middleware('role:admin,editor')->group(...)
 *                          ^^^^^^^^^^^^^^ everything after the colon,
 *                          split on commas, arrives as $roles below.
 */
class CheckRole
{
    /**
     * @param  string  ...$roles  variadic - accepts any number of role names
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // [LEARN] Check the user exists FIRST. If 'auth' middleware were ever
        //         removed from the group, $request->user() would be null and
        //         ->role would throw a confusing 500 instead of a clean 403.
        if (! $request->user() || ! $request->user()->hasRole(...$roles)) {
            abort(403, 'Your role does not allow this action.');
        }

        return $next($request);
    }
}
