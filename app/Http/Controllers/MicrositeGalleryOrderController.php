<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Microsite\ReorderGalleryAction;
use App\Http\Requests\ReorderMicrositeGalleryRequest;
use Illuminate\Http\RedirectResponse;

final class MicrositeGalleryOrderController extends Controller
{
    public function update(
        ReorderMicrositeGalleryRequest $request,
        string $project,
        ReorderGalleryAction $reorderGallery,
    ): RedirectResponse {
        $reorderGallery->execute((int) $request->user()->id, $project, $request->validated('items'));

        return back()->with('success', 'Urutan galeri microsite berhasil diperbarui.');
    }
}
