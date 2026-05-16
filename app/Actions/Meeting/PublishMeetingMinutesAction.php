<?php

declare(strict_types=1);

namespace App\Actions\Meeting;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PublishMeetingMinutesAction
{
    /**
     * @param  array{
     *     summary: string,
     *     decisions: array<int, string>,
     *     action_items: array<int, array{task: string, owner: string, due: string, status: string}>,
     *     publish: bool,
     * }  $input
     */
    public function execute(int $actorUserId, int $meetingId, array $input): int
    {
        $meeting = DB::table('meetings')->where('id', $meetingId)->first();

        if ($meeting === null) {
            throw new NotFoundHttpException('Meeting not found.');
        }

        $this->guardEditor($actorUserId, (int) $meeting->organization_id);

        $now = now();
        $publishedAt = $input['publish'] === true ? $now : null;

        $existingId = (int) DB::table('meeting_minutes')
            ->where('meeting_id', $meetingId)
            ->value('id');

        $payload = [
            'meeting_id' => $meetingId,
            'created_by_user_id' => $actorUserId,
            'summary' => trim((string) $input['summary']),
            'decisions' => json_encode($this->cleanDecisions($input['decisions'])),
            'action_items' => json_encode($this->cleanActionItems($input['action_items'])),
            'updated_at' => $now,
        ];

        if ($input['publish'] === true) {
            $payload['published_at'] = $publishedAt;
        }

        if ($existingId === 0) {
            $payload['created_at'] = $now;
            $payload['published_at'] = $publishedAt;

            $existingId = (int) DB::table('meeting_minutes')->insertGetId($payload);
        } else {
            DB::table('meeting_minutes')
                ->where('id', $existingId)
                ->update($payload);
        }

        return $existingId;
    }

    /**
     * @param  array<int, string>  $decisions
     * @return array<int, string>
     */
    private function cleanDecisions(array $decisions): array
    {
        $cleaned = [];

        foreach ($decisions as $decision) {
            if (! is_string($decision)) {
                continue;
            }

            $trimmed = trim($decision);

            if ($trimmed !== '') {
                $cleaned[] = $trimmed;
            }
        }

        return $cleaned;
    }

    /**
     * @param  array<int, array{task: string, owner: string, due: string, status: string}>  $items
     * @return array<int, array{task: string, owner: string, due: string, status: string}>
     */
    private function cleanActionItems(array $items): array
    {
        $cleaned = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $task = trim((string) ($item['task'] ?? ''));

            if ($task === '') {
                continue;
            }

            $cleaned[] = [
                'task' => $task,
                'owner' => trim((string) ($item['owner'] ?? 'Belum ditentukan')) ?: 'Belum ditentukan',
                'due' => trim((string) ($item['due'] ?? '-')) ?: '-',
                'status' => in_array((string) ($item['status'] ?? 'open'), ['open', 'in_progress', 'done'], true)
                    ? (string) $item['status']
                    : 'open',
            ];
        }

        return $cleaned;
    }

    private function guardEditor(int $actorUserId, int $organizationId): void
    {
        $role = (string) DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->where('organization_id', $organizationId)
            ->value('role');

        $allowed = ['organization_owner', 'organization_admin', 'secretary'];

        if (! in_array($role, $allowed, true)) {
            throw new AuthorizationException('Hanya owner, admin, atau sekretaris yang dapat mempublikasikan notulen.');
        }
    }
}
