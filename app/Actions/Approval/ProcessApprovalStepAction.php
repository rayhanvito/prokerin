<?php

declare(strict_types=1);

namespace App\Actions\Approval;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ProcessApprovalStepAction
{
    public function __construct(
        private readonly SyncApprovalWorkflowSubjectAction $syncSubject,
        private readonly NotifyApprovalWorkflowStepAction $notifyStep,
    ) {}

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function execute(int $actorUserId, int $instanceId, string $decision, ?string $note = null): void
    {
        abort_unless(in_array($decision, ['approved', 'rejected', 'revision_requested'], true), 422);

        DB::transaction(function () use ($actorUserId, $instanceId, $decision, $note): void {
            $instance = DB::table('approval_instances')
                ->where('id', $instanceId)
                ->lockForUpdate()
                ->first(['id', 'status', 'current_step']);

            if ($instance === null) {
                throw ValidationException::withMessages(['approval' => 'Approval instance tidak ditemukan.']);
            }

            if ((string) $instance->status !== 'pending') {
                throw ValidationException::withMessages(['approval' => 'Approval instance sudah selesai diproses.']);
            }

            $step = DB::table('approval_step_records')
                ->where('instance_id', $instanceId)
                ->where('step_order', $instance->current_step)
                ->lockForUpdate()
                ->first(['id', 'approver_id', 'decision']);

            if ($step === null || (int) $step->approver_id !== $actorUserId) {
                throw new AuthorizationException('You are not the active approver for this workflow step.');
            }

            if ((string) $step->decision !== 'pending') {
                throw ValidationException::withMessages(['approval' => 'Approval step sudah memiliki keputusan final.']);
            }

            $now = now();

            DB::table('approval_step_records')
                ->where('id', $step->id)
                ->update([
                    'decision' => $decision,
                    'note' => $note,
                    'decided_at' => $now,
                    'updated_at' => $now,
                ]);

            if ($decision !== 'approved') {
                DB::table('approval_instances')
                    ->where('id', $instanceId)
                    ->update([
                        'status' => $decision === 'rejected' ? 'rejected' : 'revision_requested',
                        'updated_at' => $now,
                    ]);

                $this->syncSubject->execute($instanceId);

                return;
            }

            $nextStepExists = DB::table('approval_step_records')
                ->where('instance_id', $instanceId)
                ->where('step_order', '>', (int) $instance->current_step)
                ->exists();

            DB::table('approval_instances')
                ->where('id', $instanceId)
                ->update([
                    'status' => $nextStepExists ? 'pending' : 'approved',
                    'current_step' => $nextStepExists ? (int) $instance->current_step + 1 : (int) $instance->current_step,
                    'updated_at' => $now,
                ]);

            if (! $nextStepExists) {
                $this->syncSubject->execute($instanceId);

                return;
            }

            $this->notifyStep->execute($instanceId);
        });
    }
}
