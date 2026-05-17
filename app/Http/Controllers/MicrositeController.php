<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Microsite\GetPublicMicrositePayloadAction;
use Inertia\Inertia;
use Inertia\Response;

final class MicrositeController extends Controller
{
    public function show(
        string $orgSlug,
        string $prokerSlug,
        GetPublicMicrositePayloadAction $payload,
    ): Response {
        return Inertia::render('Microsite/Show', $payload->execute($orgSlug, $prokerSlug));
    }
}
