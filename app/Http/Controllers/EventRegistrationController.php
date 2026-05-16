<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\EventRegistration\GetPublicEventRegistrationPayloadAction;
use App\Actions\EventRegistration\RegisterPublicEventAction;
use App\Http\Requests\StoreEventRegistrationRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class EventRegistrationController extends Controller
{
    public function show(string $project, GetPublicEventRegistrationPayloadAction $payload): Response
    {
        return Inertia::render('Events/Register', $payload->execute($project));
    }

    public function store(
        StoreEventRegistrationRequest $request,
        string $project,
        RegisterPublicEventAction $registerPublicEvent,
    ): RedirectResponse {
        $registerPublicEvent->execute($project, $request->validated());

        return back()->with('success', 'Registrasi berhasil dikirim. Silakan cek email konfirmasi Anda.');
    }
}
