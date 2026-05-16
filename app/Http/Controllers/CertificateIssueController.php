<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Certificate\IssueCertificateBatchAction;
use App\Http\Requests\IssueCertificateRequest;
use Illuminate\Http\RedirectResponse;

final class CertificateIssueController extends Controller
{
    public function store(
        IssueCertificateRequest $request,
        IssueCertificateBatchAction $issueCertificateBatch,
    ): RedirectResponse {
        /** @var array{template_id: int, project_id?: int|null, meeting_id?: int|null, recipients: array<int, array{user_id?: int|null, recipient_name: string, recipient_email?: string|null}>} $data */
        $data = $request->validated();

        $result = $issueCertificateBatch->execute(
            actorUserId: (int) $request->user()->id,
            templateId: (int) $data['template_id'],
            recipients: $data['recipients'],
            projectId: isset($data['project_id']) ? (int) $data['project_id'] : null,
            meetingId: isset($data['meeting_id']) ? (int) $data['meeting_id'] : null,
        );

        return back()->with('success', $result['issued'].' sertifikat berhasil diterbitkan dan PDF masuk antrean.');
    }
}
