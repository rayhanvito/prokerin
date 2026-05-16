<?php

declare(strict_types=1);

namespace App\Actions\Project;

use App\Domain\Project\ProjectStatus;
use App\DTOs\Project\CreateProjectDraftData;
use App\DTOs\Project\ProjectDraftData;
use InvalidArgumentException;

final readonly class CreateProjectDraftFromTemplateAction
{
    public function __construct(private BuildProjectTemplatePlanAction $buildTemplatePlan = new BuildProjectTemplatePlanAction) {}

    public function execute(CreateProjectDraftData $data): ProjectDraftData
    {
        if ($data->endsAt < $data->startsAt) {
            throw new InvalidArgumentException('Project end date cannot be before start date.');
        }

        return new ProjectDraftData(
            name: $data->name,
            organizationName: $data->organizationName,
            projectLeadName: $data->projectLeadName,
            status: ProjectStatus::Draft,
            templatePlan: $this->buildTemplatePlan->execute($data->templateType),
        );
    }
}
