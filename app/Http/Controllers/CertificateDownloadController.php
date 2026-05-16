<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Certificate\CreateCertificateDownloadUrlAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class CertificateDownloadController extends Controller
{
    public function show(
        Request $request,
        string $certificateNumber,
        CreateCertificateDownloadUrlAction $createCertificateDownloadUrl,
    ): RedirectResponse {
        return redirect()->away($createCertificateDownloadUrl->execute(
            actorUserId: (int) $request->user()->id,
            certificateNumber: $certificateNumber,
        ));
    }
}
