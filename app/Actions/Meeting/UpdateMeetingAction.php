<?php

declare(strict_types=1);

namespace App\Actions\Meeting;

use App\Domain\Meeting\MeetingStatus;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UpdateMeetingAction
{
    /**
     * @param  array{
     *     title?: string,
     *     agenda?: string,
     *     starts_at?: string,
     *     ends_at?: ?string,
     *     location?: ?string,
     *     project_id?: ?int,
     *     status?: string,
     * }  $input
     */
    public function execute(int $actorUserId, int $meetingId, array $input): void
    {
        $meeting = DB::table('meetings')->where('id', $meetingId)->first();

        if ($meeting === null) {
            throw new NotFoundHttpException('Meeting not found.');
        }

        $this->guardEditor($actorUserId, (int) $meeting->organization_id);

        if (array_key_exists('project_id', $input) && $input['project_id'] !== null) {
            $projectExists = DB::table('projects')
                ->where('id', $input['project_id'])
                ->where('organization_id', $meeting->organization_id)
                ->exists();

            if (! $projectExists) {
                throw new AuthorizationException('Project tidak terhubung ke organisasi aktif.');
            }
        }

        if (array_key_exists('status', $input)) {
            $valid = array_map(static fn (MeetingStatus $status): string => $status->value, MeetingStatus::cases());

            if (! in_array((string) $input['status'], $valid, true)) {
                throw new AuthorizationException('Status rapat tidak valid.');
            }
        }

        $patch = [];

        foreach (['title', 'agenda', 'starts_at', 'ends_at', 'location', 'project_id', 'status'] as $field) {
            if (array_key_exists($field, $input)) {
                $patch[$field] = is_string($input[$field]) ? trim($input[$field]) : $input[$field];
            }
        }

        if ($patch === []) {
            return;
        }

        $patch['updated_at'] = now();

        DB::table('meetings')
            ->where('id', $meetingId)
            ->update($patch);
    }

    private function guardEditor(int $actorUserId, int $organizationId): void
    {
        $role = (string) DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->where('organization_id', $organizationId)
            ->value('role');

        $allowed = ['organization_owner', 'organization_admin', 'secretary'];

        if (! in_array($role, $allowed, true)) {
            throw new AuthorizationException('Hanya owner, admin, atau sekretaris yang dapat mengubah rapat.');
        }
    }
}
