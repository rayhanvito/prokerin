<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Microsite\UploadMicrositeAssetAction;
use App\Http\Requests\UploadMicrositeAssetRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class MicrositeAssetController extends Controller
{
    public function banner(
        UploadMicrositeAssetRequest $request,
        string $project,
        UploadMicrositeAssetAction $uploadMicrositeAsset,
    ): RedirectResponse {
        $uploadMicrositeAsset->uploadBanner(
            actorUserId: (int) $request->user()->id,
            projectSlug: $project,
            file: $request->file('image'),
        );

        return back()->with('success', 'Banner microsite berhasil diunggah.');
    }

    public function gallery(
        UploadMicrositeAssetRequest $request,
        string $project,
        UploadMicrositeAssetAction $uploadMicrositeAsset,
    ): RedirectResponse {
        $uploadMicrositeAsset->uploadGalleryItem(
            actorUserId: (int) $request->user()->id,
            projectSlug: $project,
            file: $request->file('image'),
            caption: $request->validated('caption') !== null ? (string) $request->validated('caption') : null,
        );

        return back()->with('success', 'Foto galeri microsite berhasil ditambahkan.');
    }

    public function destroyGalleryItem(
        Request $request,
        string $project,
        int $item,
        UploadMicrositeAssetAction $uploadMicrositeAsset,
    ): RedirectResponse {
        $uploadMicrositeAsset->deleteGalleryItem((int) $request->user()->id, $project, $item);

        return back()->with('success', 'Foto galeri microsite berhasil dihapus.');
    }
}
