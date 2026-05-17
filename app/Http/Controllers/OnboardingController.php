<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Onboarding\CompleteOnboardingAction;
use App\Actions\Onboarding\CompleteOnboardingStepAction;
use App\Actions\Onboarding\SkipOnboardingAction;
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

    public function completeStep(
        Request $request,
        int $step,
        CompleteOnboardingStepAction $completeStep,
    ): RedirectResponse {
        $completeStep->execute((int) $request->user()->id, $step);

        return back()->with('success', 'Step onboarding selesai.');
    }

    public function skip(
        Request $request,
        SkipOnboardingAction $skipOnboarding,
    ): RedirectResponse {
        $skipOnboarding->execute((int) $request->user()->id);

        return back()->with('status', 'Onboarding dilewati. Kamu bisa lanjut eksplor Prokerin.');
    }
}
