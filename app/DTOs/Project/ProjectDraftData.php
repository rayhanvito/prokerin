<?php

declare(strict_types=1);

namespace App\DTOs\Project;

use App\Domain\Project\ProjectStatus;

final readonly class ProjectDraftData
{
    public function __construct(
        public string $name,
        public string $organizationName,
        public string $projectLeadName,
        public ProjectStatus $status,
        public ProjectTemplatePlanData $templatePlan,
    ) {}

    /**
     * @return array{name: string, organizationName: string, projectLeadName: string, status: string, templateType: string}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'organizationName' => $this->organizationName,
            'projectLeadName' => $this->projectLeadName,
            'status' => $this->status->value,
            'templateType' => $this->templatePlan->templateType->value,
        ];
    }
}
