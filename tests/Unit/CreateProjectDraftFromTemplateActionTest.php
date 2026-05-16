<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\Project\CreateProjectDraftFromTemplateAction;
use App\Domain\Project\ProjectStatus;
use App\Domain\Project\ProjectTemplateType;
use App\DTOs\Project\CreateProjectDraftData;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CreateProjectDraftFromTemplateActionTest extends TestCase
{
    public function test_it_creates_project_draft_from_template(): void
    {
        $draft = (new CreateProjectDraftFromTemplateAction)->execute(
            new CreateProjectDraftData(
                name: 'Seminar Karier Digital',
                description: 'Seminar bersama praktisi industri digital.',
                organizationName: 'BEM Fakultas Teknologi',
                projectLeadName: 'Dimas Aji',
                templateType: ProjectTemplateType::Seminar,
                startsAt: new DateTimeImmutable('2026-06-12'),
                endsAt: new DateTimeImmutable('2026-06-12'),
            ),
        );

        $this->assertSame(ProjectStatus::Draft, $draft->status);
        $this->assertSame(ProjectTemplateType::Seminar, $draft->templatePlan->templateType);
        $this->assertNotEmpty($draft->templatePlan->tasks);
        $this->assertNotEmpty($draft->templatePlan->budgetLines);
    }

    public function test_it_rejects_invalid_date_range(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new CreateProjectDraftFromTemplateAction)->execute(
            new CreateProjectDraftData(
                name: 'Makrab Angkatan 2026',
                description: 'Agenda internal pengurus.',
                organizationName: 'UKM Kreatif',
                projectLeadName: 'Raka Pratama',
                templateType: ProjectTemplateType::Makrab,
                startsAt: new DateTimeImmutable('2026-08-12'),
                endsAt: new DateTimeImmutable('2026-08-10'),
            ),
        );
    }
}
