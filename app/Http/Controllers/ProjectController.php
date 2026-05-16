<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Project\ArchiveProjectAction;
use App\Actions\Project\CreateProjectAction;
use App\Actions\Project\UpdateProjectAction;
use App\Domain\Project\ProjectTemplateType;
use App\Http\Requests\CreateProjectRequest;
use App\Http\Requests\DeleteProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use Illuminate\Http\RedirectResponse;

final class ProjectController extends Controller
{
    public function store(CreateProjectRequest $request, CreateProjectAction $createProject): RedirectResponse
    {
        $project = $createProject->execute(
            actorUserId: (int) $request->user()->id,
            name: (string) $request->validated('name'),
            description: $request->validated('description') !== null ? (string) $request->validated('description') : null,
            templateType: ProjectTemplateType::from((string) $request->validated('template_type')),
            startsAt: (string) $request->validated('starts_at'),
            endsAt: (string) $request->validated('ends_at'),
            projectLeadId: $request->validated('project_lead_id') !== null ? (int) $request->validated('project_lead_id') : null,
        );

        return redirect()
            ->route('proker.detail', ['project' => $project['slug']])
            ->with('success', 'Draft proker berhasil dibuat.');
    }

    public function update(
        UpdateProjectRequest $request,
        string $project,
        UpdateProjectAction $updateProject,
    ): RedirectResponse {
        $updatedProject = $updateProject->execute(
            actorUserId: (int) $request->user()->id,
            projectSlug: $project,
            name: (string) $request->validated('name'),
            description: $request->validated('description') !== null ? (string) $request->validated('description') : null,
            templateType: ProjectTemplateType::from((string) $request->validated('template_type')),
            startsAt: (string) $request->validated('starts_at'),
            endsAt: (string) $request->validated('ends_at'),
            projectLeadId: $request->validated('project_lead_id') !== null ? (int) $request->validated('project_lead_id') : null,
        );

        return redirect()
            ->route('proker.detail', ['project' => $updatedProject['slug']])
            ->with('success', 'Data proker berhasil diperbarui.');
    }

    public function destroy(
        DeleteProjectRequest $request,
        string $project,
        ArchiveProjectAction $archiveProject,
    ): RedirectResponse {
        $archiveProject->execute((int) $request->user()->id, $project);

        return redirect()
            ->route('proker.index')
            ->with('success', 'Proker berhasil diarsipkan.');
    }
}
