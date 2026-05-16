<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\SuperAdmin\LogActivityAction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Lab404\Impersonate\Services\ImpersonateManager;

final class StopImpersonationController extends Controller
{
    public function take(Request $request, ImpersonateManager $manager, int $id, ?string $guardName = null): RedirectResponse
    {
        $current = $request->user();
        $guardName ??= $manager->getDefaultSessionGuard();

        if (! $current instanceof User || ! $current->canImpersonate()) {
            abort(403);
        }

        if ($manager->isImpersonating()) {
            abort(403);
        }

        if ((int) $current->getAuthIdentifier() === $id && $manager->getCurrentAuthGuardName() === $guardName) {
            abort(403);
        }

        $target = $manager->findUserById($id, $guardName);

        if (! $target instanceof User || ! $target->canBeImpersonated()) {
            abort(403);
        }

        app(LogActivityAction::class)->execute('impersonate.start', $target, [
            'target_user_id' => $target->id,
            'target_user_email' => $target->email,
        ], (int) $current->getKey());

        if (! $manager->take($current, $target, $guardName)) {
            abort(403);
        }

        session()->put('impersonate_started_at', now()->toIso8601String());

        return redirect()->route('dashboard');
    }

    public function leave(ImpersonateManager $manager): RedirectResponse
    {
        $impersonatedUser = auth()->user();
        $impersonatorId = $manager->getImpersonatorId();

        if (! $manager->isImpersonating()) {
            return redirect()->route('dashboard');
        }

        if ($impersonatedUser instanceof User && is_numeric($impersonatorId)) {
            app(LogActivityAction::class)->execute('impersonate.stop', $impersonatedUser, [
                'target_user_id' => $impersonatedUser->getKey(),
            ], (int) $impersonatorId);
        }

        $manager->leave();
        session()->forget('impersonate_started_at');

        return redirect()->to('/internal-admin/users');
    }

    public function store(ImpersonateManager $manager): RedirectResponse
    {
        return $this->leave($manager);
    }
}
