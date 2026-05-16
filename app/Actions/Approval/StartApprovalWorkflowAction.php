<?php

declare(strict_types=1);

namespace App\Actions\Approval;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class StartApprovalWorkflowAction
{
    /**
     * @return array{id: int, current_step: int}
     *
     * @throws ValidationException
     */
    public function execute(
        int $organizationId,
        string $workflowType,
        string $subjectType,
        int $subjectId,
        int $submittedByUserId,
    ): array {
        return DB::transaction(function () use ($organizationId, $workflowType, $subjectType, $subjectId, $submittedByUserId): array {
            $definition = DB::table('approval_workflow_definitions')
                ->where('organization_id', $organizationId)
                ->where('workflow_type', $workflowType)
                ->where('is_active', true)
                ->first(['id', 'steps']);

            if ($definition === null) {
                throw ValidationException::withMessages([
                    'workflow' => 'Workflow approval aktif belum tersedia.',
                ]);
            }

            $steps = $this->steps((string) $definition->steps);

            if ($steps === []) {
                throw ValidationException::withMessages([
                    'workflow' => 'Workflow approval harus memiliki minimal satu step.',
                ]);
            }

            $existingInstanceId = DB::table('approval_instances')
                ->where('subject_type', $subjectType)
                ->where('subject_id', $subjectId)
                ->whereIn('status', ['pending'])
                ->value('id');

            if ($existingInstanceId !== null) {
                return [
                    'id' => (int) $existingInstanceId,
                    'current_step' => (int) DB::table('approval_instances')->where('id', $existingInstanceId)->value('current_step'),
                ];
            }

            $now = now();
            $instanceId = (int) DB::table('approval_instances')->insertGetId([
                'workflow_definition_id' => $definition->id,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'status' => 'pending',
                'current_step' => 1,
                'submitted_by_user_id' => $submittedByUserId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('approval_step_records')->insert(array_map(
                static fn (array $step): array => [
                    'instance_id' => $instanceId,
                    'step_order' => (int) $step['step_order'],
                    'approver_id' => (int) $step['approver_id'],
                    'decision' => 'pending',
                    'note' => null,
                    'decided_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                $steps,
            ));

            return [
                'id' => $instanceId,
                'current_step' => 1,
            ];
        });
    }

    /**
     * @return array<int, array{step_order: int, approver_id: int}>
     */
    private function steps(string $json): array
    {
        $steps = json_decode($json, true);

        if (! is_array($steps)) {
            return [];
        }

        return collect($steps)
            ->filter(static fn (mixed $step): bool => is_array($step) && isset($step['step_order'], $step['approver_id']))
            ->sortBy('step_order')
            ->values()
            ->all();
    }
}
