<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use Illuminate\Support\Facades\DB;

final class GetProposalDraftPayloadAction
{
    /**
     * @return array{title: string, subtitle: string, sections: array<int, array{heading: string, body: string}>}
     */
    public function execute(): array
    {
        $draft = DB::table('proposal_drafts')->orderBy('id')->first();

        if ($draft === null) {
            return [
                'title' => 'Proposal',
                'subtitle' => 'Belum ada draft',
                'sections' => [],
            ];
        }

        return [
            'title' => (string) $draft->title,
            'subtitle' => (string) $draft->subtitle,
            'sections' => json_decode((string) $draft->sections, true) ?: [],
        ];
    }
}
