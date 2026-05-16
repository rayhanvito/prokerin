<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use Illuminate\Support\Facades\DB;

final class GetMeetingMinutePayloadAction
{
    /**
     * @return array{
     *     metrics: array<int, array{label: string, value: string, note: string}>,
     *     meetings: array<int, array{id: int, title: string, project: string, startsAt: string, location: string, status: string, attendeeCount: int, presentCount: int, hasMinutes: bool}>,
     *     latestMinutes: array<int, array{id: int, meetingTitle: string, summary: string, decisions: array<int, string>, actionItems: array<int, array{task: string, owner: string, due: string, status: string}>, publishedAt: string|null}>
     * }
     */
    public function execute(int $userId): array
    {
        $organizationIds = DB::table('organization_members')
            ->where('user_id', $userId)
            ->pluck('organization_id');

        $meetingRows = DB::table('meetings')
            ->leftJoin('projects', 'projects.id', '=', 'meetings.project_id')
            ->leftJoin('meeting_minutes', 'meeting_minutes.meeting_id', '=', 'meetings.id')
            ->whereIn('meetings.organization_id', $organizationIds)
            ->orderBy('meetings.starts_at')
            ->limit(12)
            ->get([
                'meetings.id',
                'meetings.title',
                'meetings.location',
                'meetings.starts_at',
                'meetings.status',
                'projects.name as project_name',
                'meeting_minutes.id as minutes_id',
            ]);

        $meetingIds = $meetingRows->pluck('id');

        $attendeeCounts = DB::table('meeting_attendees')
            ->whereIn('meeting_id', $meetingIds)
            ->selectRaw('meeting_id, count(*) as attendee_count, sum(case when attendance_status = ? then 1 else 0 end) as present_count', ['present'])
            ->groupBy('meeting_id')
            ->get()
            ->keyBy('meeting_id');

        $latestMinutes = DB::table('meeting_minutes')
            ->join('meetings', 'meetings.id', '=', 'meeting_minutes.meeting_id')
            ->whereIn('meetings.organization_id', $organizationIds)
            ->orderByDesc('meeting_minutes.published_at')
            ->orderByDesc('meeting_minutes.updated_at')
            ->limit(5)
            ->get([
                'meeting_minutes.id',
                'meeting_minutes.summary',
                'meeting_minutes.decisions',
                'meeting_minutes.action_items',
                'meeting_minutes.published_at',
                'meetings.title as meeting_title',
            ]);

        $publishedCount = DB::table('meeting_minutes')
            ->join('meetings', 'meetings.id', '=', 'meeting_minutes.meeting_id')
            ->whereIn('meetings.organization_id', $organizationIds)
            ->whereNotNull('meeting_minutes.published_at')
            ->count();

        $pendingMinutes = $meetingRows
            ->filter(static fn (object $meeting): bool => $meeting->status === 'completed' && $meeting->minutes_id === null)
            ->count();

        return [
            'metrics' => [
                [
                    'label' => 'Rapat aktif',
                    'value' => (string) $meetingRows->count(),
                    'note' => 'Agenda tenant yang bisa diakses user',
                ],
                [
                    'label' => 'Notulen publish',
                    'value' => (string) $publishedCount,
                    'note' => 'Sudah siap dibagikan ke pengurus',
                ],
                [
                    'label' => 'Perlu notulen',
                    'value' => (string) $pendingMinutes,
                    'note' => 'Rapat selesai tanpa catatan final',
                ],
            ],
            'meetings' => $meetingRows
                ->map(function (object $meeting) use ($attendeeCounts): array {
                    $counts = $attendeeCounts->get($meeting->id);

                    return [
                        'id' => (int) $meeting->id,
                        'title' => (string) $meeting->title,
                        'project' => (string) ($meeting->project_name ?? 'Agenda organisasi'),
                        'startsAt' => (string) $meeting->starts_at,
                        'location' => (string) ($meeting->location ?? 'Belum ditentukan'),
                        'status' => (string) $meeting->status,
                        'attendeeCount' => (int) ($counts->attendee_count ?? 0),
                        'presentCount' => (int) ($counts->present_count ?? 0),
                        'hasMinutes' => $meeting->minutes_id !== null,
                    ];
                })
                ->all(),
            'latestMinutes' => $latestMinutes
                ->map(fn (object $minute): array => [
                    'id' => (int) $minute->id,
                    'meetingTitle' => (string) $minute->meeting_title,
                    'summary' => (string) $minute->summary,
                    'decisions' => $this->decodeStringList((string) $minute->decisions),
                    'actionItems' => $this->decodeActionItems((string) $minute->action_items),
                    'publishedAt' => $minute->published_at === null ? null : (string) $minute->published_at,
                ])
                ->all(),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function decodeStringList(string $json): array
    {
        $items = json_decode($json, true);

        if (! is_array($items)) {
            return [];
        }

        return array_values(array_filter($items, 'is_string'));
    }

    /**
     * @return array<int, array{task: string, owner: string, due: string, status: string}>
     */
    private function decodeActionItems(string $json): array
    {
        $items = json_decode($json, true);

        if (! is_array($items)) {
            return [];
        }

        return array_values(array_map(static fn (array $item): array => [
            'task' => (string) ($item['task'] ?? ''),
            'owner' => (string) ($item['owner'] ?? 'Belum ditentukan'),
            'due' => (string) ($item['due'] ?? '-'),
            'status' => (string) ($item['status'] ?? 'open'),
        ], array_filter($items, 'is_array')));
    }
}
