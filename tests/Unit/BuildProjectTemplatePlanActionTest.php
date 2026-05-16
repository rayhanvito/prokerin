<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\Project\BuildProjectTemplatePlanAction;
use App\Domain\Project\ProjectTemplateType;
use PHPUnit\Framework\TestCase;

final class BuildProjectTemplatePlanActionTest extends TestCase
{
    public function test_it_builds_seminar_template_plan(): void
    {
        $plan = (new BuildProjectTemplatePlanAction)->execute(ProjectTemplateType::Seminar);

        $this->assertSame(ProjectTemplateType::Seminar, $plan->templateType);
        $this->assertCount(5, $plan->tasks);
        $this->assertCount(3, $plan->budgetLines);
        $this->assertContains('Daftar hadir peserta', $plan->lpjChecklist);
        $this->assertSame(6500000, $plan->budgetLines[1]->plannedAmount->amount);
    }

    public function test_each_template_produces_initial_tasks_budget_and_lpj_checklist(): void
    {
        $action = new BuildProjectTemplatePlanAction;

        foreach (ProjectTemplateType::cases() as $templateType) {
            $plan = $action->execute($templateType);

            $this->assertNotEmpty($plan->proposalOutline);
            $this->assertNotEmpty($plan->tasks);
            $this->assertNotEmpty($plan->budgetLines);
            $this->assertNotEmpty($plan->lpjChecklist);
        }
    }

    public function test_plan_can_be_serialized_for_inertia_payloads(): void
    {
        $payload = (new BuildProjectTemplatePlanAction)
            ->execute(ProjectTemplateType::Workshop)
            ->toArray();

        $this->assertSame('workshop', $payload['templateType']);
        $this->assertSame('backlog', $payload['tasks'][0]['status']);
        $this->assertSame(2500000, $payload['budgetLines'][0]['plannedAmount']);
    }
}
