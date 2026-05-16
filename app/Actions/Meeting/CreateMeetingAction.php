<?php

declare(strict_types=1);

namespace App\Actions\Meeting;

use App\Domain\Meeting\AttendanceStatus;
use App\Domain\Meeting\MeetingStatus;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class CreateMeetingAction
{
    /**
     * @param  array{
     *     title: string,
     *     agenda: string,
     *     starts_at: string,
     *     ends_at: ?string,
     *     location: ?string,
     *     project_id: ?int,
     *     attendee_user_ids: array<int, int>,
     * }  $input
     */
    public function execute(int $actorUserId, int $organizationId, array $input): int
    {
        $this->guardAuthor($actorUserId, $organizationId);

        if (($input['project_id'] ?? null) !== null) {
            $projectExists = DB::table('projects')
                ->where('id', $input['project_id'])
                ->where('organization_id', $organizationId)
                ->exists();

            if (! $projectExists) {
                throw new AuthorizationException('Project tidak terhubung ke organisasi aktif.');
            }
        }

        return DB::transaction(function () use ($actorUserId, $organizationId, $input): int {
            $now = now();

            $meetingId = (int) DB::table('meetings')->insertGetId([
                'organization_id' => $organizationId,
                'project_id' => $input['project_id'] ?? null,
                'created_by_user_id' => $actorUserId,
                'title' => trim((string) $input['title']),
                'agenda' => trim((string) $input['agenda']),
                'location' => $input['location'] === null ? null : trim((string) $input['location']),
                'starts_at' => $input['starts_at'],
                'ends_at' => $input['ends_at'],
                'status' => MeetingStatus::Planned->value,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($input['attendee_user_ids'] as $attendeeUserId) {
                $member = DB::table('organization_members')
                    ->join('users', 'users.id', '=', 'organization_members.user_id')
                    ->where('organization_members.organization_id', $organizationId)
                    ->where('organization_members.user_id', $attendeeUserId)
                    ->first(['users.id', 'users.name', 'organization_members.role']);

                if ($member === null) {
                    continue;
                }

                DB::table('meeting_attendees')->insert([
                    'meeting_id' => $meetingId,
                    'user_id' => (int) $member->id,
                    'name' => (string) $member->name,
                    'role' => (string) $member->role,
                    'attendance_status' => AttendanceStatus::Invited->value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            return $meetingId;
        });
    }

    private function guardAuthor(int $actorUserId, int $organizationId): void
    {
        $role = (string) DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->where('organization_id', $organizationId)
            ->value('role');

        $allowed = ['organization_owner', 'organization_admin', 'secretary'];

        if (! in_array($role, $allowed, true)) {
            throw new AuthorizationException('Hanya owner, admin, atau sekretaris yang dapat membuat rapat.');
        }
    }
}
