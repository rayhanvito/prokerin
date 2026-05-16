<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\SuperAdmin\LogActivityAction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Lab404\Impersonate\Services\ImpersonateManager;

final class StopImpersonationController extends Controller
{
    public function store(ImpersonateManager $manager): RedirectResponse
    {
        $impersonatedUser = auth()->user();

        if (! $manager->isImpersonating()) {
            return redirect()->route('dashboard');
        }

        if ($impersonatedUser instanceof User) {
            app(LogActivityAction::class)->execute('impersonate.stop', $impersonatedUser, [
                'target_user_id' => $impersonatedUser->getKey(),
            ]);
        }

        $manager->leave();

        return redirect()->to('/internal-admin/users');
    }
}
