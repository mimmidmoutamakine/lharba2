<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceCanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        if (str_starts_with($host, 'www.')) {
            $target = 'https://' . substr($host, 4) . $request->getRequestUri();
            return redirect()->away($target, 301);
        }

        return $next($request);
    }
}
