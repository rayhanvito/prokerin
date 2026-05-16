<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Project\GenerateProjectFromTemplateAction;
use App\Domain\Project\ProjectTemplateType;
use App\Http\Requests\GenerateProjectFromTemplateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

final class ProjectTemplateGenerationController extends Controller
{
    public function store(
        GenerateProjectFromTemplateRequest $request,
        string $template,
        GenerateProjectFromTemplateAction $generateProject,
    ): RedirectResponse {
        $templateType = ProjectTemplateType::from($template);
        $startsAt = (string) ($request->validated('starts_at') ?? Carbon::now()->addDays(30)->toDateString());
        $endsAt = (string) ($request->validated('ends_at') ?? $startsAt);
        $name = (string) ($request->validated('name') ?? 'Draft '.$templateType->label().' '.Carbon::parse($startsAt)->format('d M Y'));

        $project = $generateProject->execute(
            actorUserId: (int) $request->user()->id,
            templateType: $templateType,
            name: $name,
            description: (string) ($request->validated('description') ?? 'Draft proker dari template '.$templateType->label().'.'),
            startsAt: $startsAt,
            endsAt: $endsAt,
            targetAudience: (string) ($request->validated('target_audience') ?? 'Mahasiswa aktif dan pengurus organisasi kampus.'),
            projectLeadId: $request->validated('project_lead_id') !== null ? (int) $request->validated('project_lead_id') : null,
        );

        return redirect()
            ->route('proker.detail', ['project' => $project['slug']])
            ->with('success', 'Draft proker berhasil dibuat dari template.');
    }
}
