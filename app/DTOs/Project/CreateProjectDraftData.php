<?php

declare(strict_types=1);

namespace App\DTOs\Project;

use App\Domain\Project\ProjectTemplateType;
use DateTimeImmutable;

final readonly class CreateProjectDraftData
{
    public function __construct(
        public string $name,
        public string $description,
        public string $organizationName,
        public string $projectLeadName,
        public ProjectTemplateType $templateType,
        public DateTimeImmutable $startsAt,
        public DateTimeImmutable $endsAt,
    ) {}
}
