<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Report\SubmitLpjForReviewAction;
use App\Http\Requests\SubmitLpjReviewRequest;
use Illuminate\Http\RedirectResponse;

final class LpjReviewController extends Controller
{
    public function store(
        SubmitLpjReviewRequest $request,
        int $project,
        SubmitLpjForReviewAction $submitLpjForReview,
    ): RedirectResponse {
        $submitLpjForReview->execute(
            actorUserId: (int) $request->user()->id,
            projectId: $project,
        );

        return back()->with('success', 'LPJ berhasil dikirim ke review.');
    }
}
