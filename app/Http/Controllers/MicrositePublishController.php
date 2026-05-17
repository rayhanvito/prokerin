<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Microsite\PublishMicrositeAction;
use App\Actions\Microsite\UnpublishMicrositeAction;
use App\Http\Requests\PublishMicrositeRequest;
use Illuminate\Http\RedirectResponse;

final class MicrositePublishController extends Controller
{
    public function publish(
        PublishMicrositeRequest $request,
        string $project,
        PublishMicrositeAction $publishMicrosite,
    ): RedirectResponse {
        $publishMicrosite->execute((int) $request->user()->id, $project);

        return back()->with('success', 'Microsite proker berhasil dipublish.');
    }

    public function unpublish(
        PublishMicrositeRequest $request,
        string $project,
        UnpublishMicrositeAction $unpublishMicrosite,
    ): RedirectResponse {
        $unpublishMicrosite->execute((int) $request->user()->id, $project);

        return back()->with('success', 'Microsite proker ditarik dari publik.');
    }
}
