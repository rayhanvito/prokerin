<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Workspace\GetInventoryPayloadAction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class InventoryQrController extends Controller
{
    public function show(Request $request, string $token, GetInventoryPayloadAction $payload): Response
    {
        $activeOrganizationId = $request->session()->get('active_organization_id');

        return Inertia::render('Inventory/QrLookup', $payload->execute(
            actorUserId: (int) $request->user()->id,
            preferredOrganizationId: is_numeric($activeOrganizationId) ? (int) $activeOrganizationId : null,
            qrToken: $token,
        ));
    }
}
