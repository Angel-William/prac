<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * [EXAM] PSRS Part 1.e - "APIs exposed to third-party services must be secured
 *        using API key authentication."
 *
 * [LEARN] Third-party servers cannot log in with a session cookie - they have
 *         no browser. So they send a shared secret in a header instead.
 *         This is the simplest form of machine-to-machine auth.
 */
class ApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        // [LEARN] READ THE KEY FROM config(), NEVER FROM env().
        //         In production you run `php artisan config:cache`, and from
        //         that moment env() returns NULL everywhere outside config/
        //         files. Your API would start rejecting every valid key and
        //         you would have no idea why. See config/services.php.
        if ($request->header('X-API-Key') !== config('services.api_key')) {

            // [EXAM] PSRS implementation guideline - "Provide proper error
            //        handling and logging for authentication." This one line
            //        is that requirement. Show it in the demo.
            Log::warning('Rejected API request: bad or missing X-API-Key', [
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);

            // [LEARN] 401 Unauthorized = "I do not know who you are."
            //         403 Forbidden    = "I know who you are, and no."
            //         An API returns JSON, never an HTML error page.
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
