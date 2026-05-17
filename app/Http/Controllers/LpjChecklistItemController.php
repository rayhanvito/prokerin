<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Report\ToggleLpjChecklistItemAction;
use App\Http\Requests\UpdateLpjChecklistItemRequest;
use Illuminate\Http\RedirectResponse;

final class LpjChecklistItemController extends Controller
{
    public function update(
        UpdateLpjChecklistItemRequest $request,
        int $item,
        ToggleLpjChecklistItemAction $toggleChecklistItem,
    ): RedirectResponse {
        $toggleChecklistItem->execute(
            actorUserId: (int) $request->user()->id,
            itemId: $item,
            isComplete: (bool) $request->validated('is_complete'),
        );

        return back()->with('success', 'Checklist LPJ berhasil diperbarui.');
    }
}
