<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureAdminIpAllowed
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedIps = config('admin.allowed_ips', []);

        if (is_array($allowedIps) && $allowedIps !== [] && ! in_array($request->ip(), $allowedIps, true)) {
            abort(403);
        }

        return $next($request);
    }
}
