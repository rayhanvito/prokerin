<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Certificate\CreateCertificateTemplateAction;
use App\Http\Requests\StoreCertificateTemplateRequest;
use Illuminate\Http\RedirectResponse;

final class CertificateTemplateController extends Controller
{
    public function store(
        StoreCertificateTemplateRequest $request,
        CreateCertificateTemplateAction $createCertificateTemplate,
    ): RedirectResponse {
        $createCertificateTemplate->execute(
            actorUserId: (int) $request->user()->id,
            data: $request->validated(),
        );

        return back()->with('success', 'Template sertifikat berhasil dibuat.');
    }

    public function update(
        StoreCertificateTemplateRequest $request,
        int $template,
        CreateCertificateTemplateAction $createCertificateTemplate,
    ): RedirectResponse {
        $createCertificateTemplate->execute(
            actorUserId: (int) $request->user()->id,
            data: $request->validated(),
            templateId: $template,
        );

        return back()->with('success', 'Template sertifikat berhasil diperbarui.');
    }
}
