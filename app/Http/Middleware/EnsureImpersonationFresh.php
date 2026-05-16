<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Actions\SuperAdmin\LogActivityAction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Http\Request;
use Lab404\Impersonate\Services\ImpersonateManager;
use Symfony\Component\HttpFoundation\Response;

final class EnsureImpersonationFresh
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var ImpersonateManager $manager */
        $manager = app(ImpersonateManager::class);

        if (! $manager->isImpersonating()) {
            return $next($request);
        }

        $startedAt = $request->session()->get('impersonate_started_at');
        $maxHours = (int) config('prokerin.impersonate.max_duration_hours', 2);

        $shouldExpire = false;

        if (! is_string($startedAt) || $startedAt === '') {
            // Tidak punya timestamp; treat as stale untuk safety.
            $shouldExpire = true;
        } else {
            try {
                $start = CarbonImmutable::parse($startedAt);

                if ($start->addHours($maxHours)->lessThanOrEqualTo(now())) {
                    $shouldExpire = true;
                }
            } catch (\Throwable) {
                $shouldExpire = true;
            }
        }

        if (! $shouldExpire) {
            return $next($request);
        }

        $impersonatedUser = auth()->user();

        if ($impersonatedUser instanceof User) {
            app(LogActivityAction::class)->execute('impersonate.expired', $impersonatedUser, [
                'target_user_id' => $impersonatedUser->getKey(),
                'reason' => 'inactivity',
            ]);
        }

        $manager->leave();
        $request->session()->forget('impersonate_started_at');

        return redirect()
            ->to('/internal-admin/users')
            ->with('error', 'Sesi impersonasi telah kedaluwarsa. Silakan mulai ulang.');
    }
}
