<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Onboarding\CompleteOnboardingAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class OnboardingController extends Controller
{
    public function complete(
        Request $request,
        CompleteOnboardingAction $completeOnboarding,
    ): RedirectResponse {
        $completeOnboarding->execute((int) $request->user()->id);

        return redirect()->route('dashboard')->with('success', 'Onboarding selesai. Selamat datang di Prokerin!');
    }
}
