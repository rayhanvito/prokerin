<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Ai\SummarizeLpjWithAiAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class LpjAiSummaryController extends Controller
{
    public function store(
        Request $request,
        int $project,
        SummarizeLpjWithAiAction $summarizeLpjWithAi,
    ): RedirectResponse {
        $summary = $summarizeLpjWithAi->execute(
            actorUserId: (int) $request->user()->id,
            projectId: $project,
        );

        return back()
            ->with('success', 'Ringkasan AI LPJ berhasil dibuat.')
            ->with('aiSuggestion', [
                'type' => 'lpj_summary',
                'projectId' => $project,
                'summary' => $summary['summary'],
                'recommendations' => $summary['recommendations'],
                'promptHash' => $summary['promptHash'],
            ]);
    }
}
