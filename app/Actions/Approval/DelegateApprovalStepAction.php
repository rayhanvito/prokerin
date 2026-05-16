<?php

declare(strict_types=1);

namespace App\Actions\Approval;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class DelegateApprovalStepAction
{
    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function execute(int $actorUserId, int $instanceId, int $delegateUserId, ?string $note = null): void
    {
        DB::transaction(function () use ($actorUserId, $instanceId, $delegateUserId, $note): void {
            $instance = DB::table('approval_instances')
                ->join('approval_workflow_definitions', 'approval_workflow_definitions.id', '=', 'approval_instances.workflow_definition_id')
                ->where('approval_instances.id', $instanceId)
                ->where('approval_instances.status', 'pending')
                ->lockForUpdate()
                ->first([
                    'approval_instances.id',
                    'approval_instances.current_step',
                    'approval_workflow_definitions.organization_id',
                ]);

            if ($instance === null) {
                throw ValidationException::withMessages(['approval' => 'Approval instance aktif tidak ditemukan.']);
            }

            $step = DB::table('approval_step_records')
                ->where('instance_id', $instanceId)
                ->where('step_order', $instance->current_step)
                ->where('decision', 'pending')
                ->lockForUpdate()
                ->first(['id', 'approver_id']);

            if ($step === null || (int) $step->approver_id !== $actorUserId) {
                throw new AuthorizationException('You are not the active approver for this workflow step.');
            }

            $delegateBelongsToOrganization = DB::table('organization_members')
                ->where('organization_id', $instance->organization_id)
                ->where('user_id', $delegateUserId)
                ->exists();

            if (! $delegateBelongsToOrganization) {
                throw ValidationException::withMessages(['delegate' => 'Delegate harus anggota organisasi yang sama.']);
            }

            $now = now();

            DB::table('approval_step_records')
                ->where('id', $step->id)
                ->update([
                    'approver_id' => $delegateUserId,
                    'updated_at' => $now,
                ]);

            DB::table('approval_delegations')->insert([
                'step_record_id' => $step->id,
                'delegated_from_user_id' => $actorUserId,
                'delegated_to_user_id' => $delegateUserId,
                'note' => $note,
                'delegated_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
    }
}
