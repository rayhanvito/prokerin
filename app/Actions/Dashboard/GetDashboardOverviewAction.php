<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Actions\Finance\CalculateBudgetSummaryAction;
use App\Actions\Report\CalculateLpjReadinessAction;
use App\Actions\Task\CalculateTaskBoardSummaryAction;
use App\Domain\Finance\BudgetStatus;
use App\Domain\Project\ProjectStatus;
use App\Domain\Task\TaskStatus;
use App\DTOs\Dashboard\DashboardTone;
use App\DTOs\Dashboard\MetricCardData;
use App\DTOs\Dashboard\PriorityItemData;
use App\DTOs\Finance\BudgetLineData;
use App\DTOs\Report\LpjChecklistItemData;
use App\DTOs\Task\TaskLineData;
use App\Support\ValueObjects\Money;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

final readonly class GetDashboardOverviewAction
{
    public function __construct(
        private CalculateTaskBoardSummaryAction $taskSummary,
        private CalculateBudgetSummaryAction $budgetSummary,
        private CalculateLpjReadinessAction $lpjReadiness,
    ) {}

    /**
     * @return array{
     *     metrics: array<int, array{label: string, value: string, note: string, tone: string}>,
     *     priorityProjects: array<int, array{title: string, meta: string, status: string, progress: int, href: string|null}>,
     *     weeklyFocus: array<int, string>,
     *     memberSummary: array{value: string, note: string}
     * }
     */
    public function execute(): array
    {
        $budgetSummary = $this->budgetSummary->execute($this->budgetLines());
        $taskSummary = $this->taskSummary->execute($this->taskLines(), new DateTimeImmutable);
        $lpjReadiness = $this->lpjReadiness->execute($this->lpjItems());

        return [
            'metrics' => $this->metrics($taskSummary, $budgetSummary, $lpjReadiness),
            'priorityProjects' => $this->priorityProjects(),
            'weeklyFocus' => $this->weeklyFocus(),
            'memberSummary' => [
                'value' => DB::table('organization_members')->count().' anggota aktif',
                'note' => 'Role dan akses disiapkan dari seed multi-role MVP.',
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, value: string, note: string, tone: string}>
     */
    private function metrics($taskSummary, $budgetSummary, $lpjReadiness): array
    {
        return [
            (new MetricCardData(
                'Active Proker',
                (string) DB::table('projects')
                    ->whereNotIn('status', [ProjectStatus::Completed->value, ProjectStatus::Archived->value])
                    ->count(),
                DB::table('projects')->where('status', ProjectStatus::ProposalReview->value)->count().' masuk fase proposal',
                DashboardTone::Primary,
            ))->toArray(),
            (new MetricCardData(
                'Open Tasks',
                (string) $taskSummary->openTasks,
                $taskSummary->dueSoonTasks.' deadline minggu ini',
                $taskSummary->overdueTasks > 0 ? DashboardTone::Danger : DashboardTone::Success,
            ))->toArray(),
            (new MetricCardData(
                'RAB Draft',
                $budgetSummary->plannedTotal->formatted(),
                $budgetSummary->approvedLineCount.' item approved',
                $budgetSummary->hasOverspend ? DashboardTone::Danger : DashboardTone::Warning,
            ))->toArray(),
            (new MetricCardData(
                'LPJ Readiness',
                $lpjReadiness->completionProgress->percentage.'%',
                count($lpjReadiness->missingRequiredItems).' item belum lengkap',
                $lpjReadiness->isReadyForReview ? DashboardTone::Success : DashboardTone::Default,
            ))->toArray(),
        ];
    }

    /**
     * @return array<int, array{title: string, meta: string, status: string, progress: int, href: string|null}>
     */
    private function priorityProjects(): array
    {
        return DB::table('projects')
            ->join('organizations', 'organizations.id', '=', 'projects.organization_id')
            ->select('projects.name', 'projects.status', 'projects.progress', 'organizations.name as organization_name')
            ->orderByDesc('projects.progress')
            ->limit(3)
            ->get()
            ->map(fn (object $project): array => (new PriorityItemData(
                title: (string) $project->name,
                meta: (string) $project->organization_name.' · '.$this->projectStatusLabel((string) $project->status),
                status: $this->projectStatusLabel((string) $project->status),
                progress: (int) $project->progress,
                href: $this->projectStatusHref((string) $project->status),
            ))->toArray())
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function weeklyFocus(): array
    {
        return DB::table('project_tasks')
            ->join('projects', 'projects.id', '=', 'project_tasks.project_id')
            ->where('project_tasks.status', '!=', TaskStatus::Done->value)
            ->orderBy('project_tasks.due_at')
            ->limit(4)
            ->get(['project_tasks.title', 'projects.name as project_name'])
            ->map(static fn (object $task): string => (string) $task->title.' · '.(string) $task->project_name)
            ->all();
    }

    /**
     * @return array<int, TaskLineData>
     */
    private function taskLines(): array
    {
        return DB::table('project_tasks')
            ->join('projects', 'projects.id', '=', 'project_tasks.project_id')
            ->leftJoin('users', 'users.id', '=', 'project_tasks.pic_user_id')
            ->get(['project_tasks.title', 'project_tasks.status', 'project_tasks.due_at', 'projects.name as project_name', 'users.name as pic_name'])
            ->map(static fn (object $task): TaskLineData => new TaskLineData(
                title: (string) $task->title,
                projectName: (string) $task->project_name,
                picName: (string) ($task->pic_name ?? 'Unassigned'),
                status: TaskStatus::tryFrom((string) $task->status) ?? TaskStatus::Backlog,
                dueAt: new DateTimeImmutable((string) ($task->due_at ?? 'today')),
            ))
            ->all();
    }

    /**
     * @return array<int, BudgetLineData>
     */
    private function budgetLines(): array
    {
        return DB::table('budget_lines')
            ->get()
            ->map(static fn (object $line): BudgetLineData => new BudgetLineData(
                name: (string) $line->name,
                category: (string) $line->category,
                plannedAmount: Money::rupiah((int) $line->planned_amount),
                realizedAmount: Money::rupiah((int) $line->realized_amount),
                status: BudgetStatus::tryFrom((string) $line->status) ?? BudgetStatus::Draft,
            ))
            ->all();
    }

    /**
     * @return array<int, LpjChecklistItemData>
     */
    private function lpjItems(): array
    {
        return DB::table('lpj_checklist_items')
            ->get()
            ->map(static fn (object $item): LpjChecklistItemData => new LpjChecklistItemData(
                title: (string) $item->title,
                isComplete: (bool) $item->is_complete,
                isRequired: (bool) $item->is_required,
            ))
            ->all();
    }

    private function projectStatusLabel(string $status): string
    {
        return ProjectStatus::tryFrom($status)?->label() ?? ucfirst(str_replace('_', ' ', $status));
    }

    private function projectStatusHref(string $status): string
    {
        return match (ProjectStatus::tryFrom($status)) {
            ProjectStatus::RabApproval => route('finance.approval'),
            ProjectStatus::Draft => route('tasks.kanban'),
            default => route('proker.show'),
        };
    }
}
