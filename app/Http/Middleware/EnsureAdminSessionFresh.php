<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

final class EnsureAdminSessionFresh
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $idleMinutes = (int) config('admin.session_idle_minutes', 30);
        $lastActivity = $request->session()->get('admin_last_activity_at');

        if (is_string($lastActivity) && Carbon::parse($lastActivity)->addMinutes($idleMinutes)->isPast()) {
            $request->session()->flush();

            return redirect()->to('/internal-admin/login');
        }

        $request->session()->put('admin_last_activity_at', now()->toIso8601String());

        return $next($request);
    }
}
