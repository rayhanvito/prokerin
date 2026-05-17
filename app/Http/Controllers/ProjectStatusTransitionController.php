<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Project\TransitionProjectStatusAction;
use App\Domain\Project\ProjectStatus;
use App\Http\Requests\TransitionProjectStatusRequest;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ProjectStatusTransitionController extends Controller
{
    public function update(
        TransitionProjectStatusRequest $request,
        string $project,
        TransitionProjectStatusAction $transitionProjectStatus,
    ): RedirectResponse {
        $projectRecord = DB::table('projects')
            ->join('organization_members', 'organization_members.organization_id', '=', 'projects.organization_id')
            ->where('projects.slug', $project)
            ->where('organization_members.user_id', $request->user()->id)
            ->first(['projects.id', 'projects.status']);

        if ($projectRecord === null) {
            throw new NotFoundHttpException('Project was not found for the active workspace.');
        }

        try {
            $targetStatus = $transitionProjectStatus->execute(
                ProjectStatus::from((string) $projectRecord->status),
                ProjectStatus::from((string) $request->validated('status')),
            );
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        DB::table('projects')
            ->where('id', $projectRecord->id)
            ->update([
                'status' => $targetStatus->value,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Status proker berhasil diperbarui.');
    }
}
