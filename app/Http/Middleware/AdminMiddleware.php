<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Simple check: user must be logged in and have is_admin flag.
        // Replace with your own auth logic (e.g., role/permission package).
        if (!$request->user() || !$request->user()->is_admin) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
