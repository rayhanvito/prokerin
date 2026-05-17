<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Inventory\ArchiveInventoryItemAction;
use App\Actions\Inventory\CreateInventoryItemAction;
use App\Actions\Inventory\UpdateInventoryItemAction;
use App\Actions\Workspace\GetInventoryPayloadAction;
use App\Http\Requests\StoreInventoryItemRequest;
use App\Http\Requests\UpdateInventoryItemRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class InventoryController extends Controller
{
    public function index(Request $request, GetInventoryPayloadAction $payload): Response
    {
        $activeOrganizationId = $request->session()->get('active_organization_id');

        return Inertia::render('Inventory/Index', $payload->execute(
            actorUserId: (int) $request->user()->id,
            preferredOrganizationId: is_numeric($activeOrganizationId) ? (int) $activeOrganizationId : null,
        ));
    }

    public function create(Request $request, GetInventoryPayloadAction $payload): Response
    {
        $activeOrganizationId = $request->session()->get('active_organization_id');

        return Inertia::render('Inventory/Create', $payload->execute(
            actorUserId: (int) $request->user()->id,
            preferredOrganizationId: is_numeric($activeOrganizationId) ? (int) $activeOrganizationId : null,
        ));
    }

    public function store(StoreInventoryItemRequest $request, CreateInventoryItemAction $createItem): RedirectResponse
    {
        $itemId = $createItem->execute((int) $request->user()->id, $request->validated());

        return redirect()->route('inventory.show', ['item' => $itemId])->with('success', 'Inventaris berhasil ditambahkan.');
    }

    public function show(Request $request, int $item, GetInventoryPayloadAction $payload): Response
    {
        $activeOrganizationId = $request->session()->get('active_organization_id');

        return Inertia::render('Inventory/Show', $payload->execute(
            actorUserId: (int) $request->user()->id,
            preferredOrganizationId: is_numeric($activeOrganizationId) ? (int) $activeOrganizationId : null,
            itemId: $item,
        ));
    }

    public function edit(Request $request, int $item, GetInventoryPayloadAction $payload): Response
    {
        $activeOrganizationId = $request->session()->get('active_organization_id');

        return Inertia::render('Inventory/Edit', $payload->execute(
            actorUserId: (int) $request->user()->id,
            preferredOrganizationId: is_numeric($activeOrganizationId) ? (int) $activeOrganizationId : null,
            itemId: $item,
        ));
    }

    public function update(UpdateInventoryItemRequest $request, int $item, UpdateInventoryItemAction $updateItem): RedirectResponse
    {
        $updateItem->execute((int) $request->user()->id, $item, $request->validated());

        return redirect()->route('inventory.show', ['item' => $item])->with('success', 'Inventaris berhasil diperbarui.');
    }

    public function destroy(Request $request, int $item, ArchiveInventoryItemAction $archiveItem): RedirectResponse
    {
        $archiveItem->execute((int) $request->user()->id, $item);

        return redirect()->route('inventory.index')->with('success', 'Inventaris diarsipkan.');
    }
}
