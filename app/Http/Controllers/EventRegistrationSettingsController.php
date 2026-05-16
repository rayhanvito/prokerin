<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\EventRegistration\UpdateEventRegistrationSettingsAction;
use App\Http\Requests\UpdateEventRegistrationSettingsRequest;
use Illuminate\Http\RedirectResponse;

final class EventRegistrationSettingsController extends Controller
{
    public function update(
        UpdateEventRegistrationSettingsRequest $request,
        int $project,
        UpdateEventRegistrationSettingsAction $updateSettings,
    ): RedirectResponse {
        $updateSettings->execute(
            actorUserId: (int) $request->user()->id,
            projectId: $project,
            data: $request->validated(),
        );

        return back()->with('success', 'Pengaturan registrasi event berhasil diperbarui.');
    }
}
