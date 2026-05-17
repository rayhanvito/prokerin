<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Letter\BulkIssueParticipationCertificatesAction;
use App\Http\Requests\BulkIssueLettersRequest;
use Illuminate\Http\RedirectResponse;

final class LetterBulkIssueController extends Controller
{
    public function store(
        BulkIssueLettersRequest $request,
        int $project,
        BulkIssueParticipationCertificatesAction $bulkIssueParticipationCertificates,
    ): RedirectResponse {
        $letterIds = $bulkIssueParticipationCertificates->execute(
            actorUserId: (int) $request->user()->id,
            projectId: $project,
            recipientUserIds: $request->validated('recipient_user_ids'),
        );

        return back()->with('success', count($letterIds).' surat partisipasi berhasil dibuat.');
    }
}
