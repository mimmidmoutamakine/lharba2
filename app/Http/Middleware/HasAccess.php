<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasAccess
{
    /**
     * Gates a route behind an approved AccessRequest.
     * Admins always pass. Users without an approved request are redirected:
     *  - to /access/pending if they have a pending request
     *  - to /access/new otherwise
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->guest(route('login'));
        }

        if ($user->is_admin) {
            return $next($request);
        }

        if ($user->currentAccess()) {
            return $next($request);
        }

        if ($user->pendingAccess()) {
            return redirect()->route('access.pending');
        }

        return redirect()->route('access.create');
    }
}
