<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Microsite\GetMicrositeSettingsPayloadAction;
use App\Actions\Microsite\UpdateMicrositeSettingsAction;
use App\Http\Requests\UpdateMicrositeSettingsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class MicrositeSettingsController extends Controller
{
    public function edit(
        Request $request,
        string $project,
        GetMicrositeSettingsPayloadAction $payload,
    ): Response {
        return Inertia::render('Microsite/Settings', $payload->execute((int) $request->user()->id, $project));
    }

    public function update(
        UpdateMicrositeSettingsRequest $request,
        string $project,
        UpdateMicrositeSettingsAction $updateMicrositeSettings,
    ): RedirectResponse {
        $updateMicrositeSettings->execute((int) $request->user()->id, $project, $request->validated());

        return back()->with('success', 'Pengaturan microsite berhasil disimpan.');
    }
}
