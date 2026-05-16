<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use Illuminate\Support\Facades\DB;

final class GetMeetingMinutePayloadAction
{
    /**
     * @return array{
     *     metrics: array<int, array{label: string, value: string, note: string}>,
     *     meetings: array<int, array{
     *         id: int,
     *         title: string,
     *         agenda: string,
     *         project: string,
     *         projectId: int|null,
     *         startsAt: string,
     *         endsAt: string|null,
     *         location: string,
     *         status: string,
     *         attendeeCount: int,
     *         presentCount: int,
     *         hasMinutes: bool,
     *         minutes: array{
     *             id: int,
     *             summary: string,
     *             decisions: array<int, string>,
     *             actionItems: array<int, array{task: string, owner: string, due: string, status: string}>,
     *             publishedAt: string|null
     *         }|null,
     *         attendees: array<int, array{id: int, name: string, role: string|null, attendanceStatus: string, userId: int|null}>
     *     }>,
     *     latestMinutes: array<int, array{
     *         id: int,
     *         meetingTitle: string,
     *         summary: string,
     *         decisions: array<int, string>,
     *         actionItems: array<int, array{task: string, owner: string, due: string, status: string}>,
     *         publishedAt: string|null
     *     }>,
     *     formOptions: array{
     *         canManage: bool,
     *         projects: array<int, array{id: int, name: string}>,
     *         organizationMembers: array<int, array{id: int, name: string, role: string}>,
     *         statusOptions: array<int, array{value: string, label: string}>,
     *         attendanceStatusOptions: array<int, array{value: string, label: string}>
     *     },
     *     activeOrganizationId: int|null
     * }
     */
    public function execute(int $userId): array
    {
        $organizationIds = DB::table('organization_members')
            ->where('user_id', $userId)
            ->pluck('organization_id');

        $activeOrganization = DB::table('organization_members')
            ->where('user_id', $userId)
            ->orderBy('id')
            ->first(['organization_id', 'role']);

        $activeOrganizationId = $activeOrganization === null ? null : (int) $activeOrganization->organization_id;
        $activeRole = $activeOrganization === null ? null : (string) $activeOrganization->role;
        $canManage = $activeRole !== null && in_array($activeRole, ['organization_owner', 'organization_admin', 'secretary'], true);

        $meetingRows = DB::table('meetings')
            ->leftJoin('projects', 'projects.id', '=', 'meetings.project_id')
            ->leftJoin('meeting_minutes', 'meeting_minutes.meeting_id', '=', 'meetings.id')
            ->whereIn('meetings.organization_id', $organizationIds)
            ->orderBy('meetings.starts_at')
            ->limit(20)
            ->get([
                'meetings.id',
                'meetings.title',
                'meetings.agenda',
                'meetings.location',
                'meetings.starts_at',
                'meetings.ends_at',
                'meetings.status',
                'meetings.project_id',
                'projects.name as project_name',
                'meeting_minutes.id as minutes_id',
                'meeting_minutes.summary as minutes_summary',
                'meeting_minutes.decisions as minutes_decisions',
                'meeting_minutes.action_items as minutes_action_items',
                'meeting_minutes.published_at as minutes_published_at',
            ]);

        $meetingIds = $meetingRows->pluck('id');

        $attendeeCounts = DB::table('meeting_attendees')
            ->whereIn('meeting_id', $meetingIds)
            ->selectRaw('meeting_id, count(*) as attendee_count, sum(case when attendance_status = ? then 1 else 0 end) as present_count', ['present'])
            ->groupBy('meeting_id')
            ->get()
            ->keyBy('meeting_id');

        $attendeesByMeeting = DB::table('meeting_attendees')
            ->whereIn('meeting_id', $meetingIds)
            ->orderBy('id')
            ->get(['id', 'meeting_id', 'user_id', 'name', 'role', 'attendance_status'])
            ->groupBy('meeting_id');

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

        $projects = $activeOrganizationId === null ? collect() : DB::table('projects')
            ->where('organization_id', $activeOrganizationId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $organizationMembers = $activeOrganizationId === null ? collect() : DB::table('organization_members')
            ->join('users', 'users.id', '=', 'organization_members.user_id')
            ->where('organization_members.organization_id', $activeOrganizationId)
            ->orderBy('users.name')
            ->get(['users.id', 'users.name', 'organization_members.role']);

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
                ->map(function (object $meeting) use ($attendeeCounts, $attendeesByMeeting): array {
                    $counts = $attendeeCounts->get($meeting->id);
                    $attendees = $attendeesByMeeting->get($meeting->id, collect());

                    $minutes = $meeting->minutes_id === null ? null : [
                        'id' => (int) $meeting->minutes_id,
                        'summary' => (string) ($meeting->minutes_summary ?? ''),
                        'decisions' => $this->decodeStringList((string) ($meeting->minutes_decisions ?? '[]')),
                        'actionItems' => $this->decodeActionItems((string) ($meeting->minutes_action_items ?? '[]')),
                        'publishedAt' => $meeting->minutes_published_at === null ? null : (string) $meeting->minutes_published_at,
                    ];

                    return [
                        'id' => (int) $meeting->id,
                        'title' => (string) $meeting->title,
                        'agenda' => (string) $meeting->agenda,
                        'project' => (string) ($meeting->project_name ?? 'Agenda organisasi'),
                        'projectId' => $meeting->project_id === null ? null : (int) $meeting->project_id,
                        'startsAt' => (string) $meeting->starts_at,
                        'endsAt' => $meeting->ends_at === null ? null : (string) $meeting->ends_at,
                        'location' => (string) ($meeting->location ?? 'Belum ditentukan'),
                        'status' => (string) $meeting->status,
                        'attendeeCount' => (int) ($counts->attendee_count ?? 0),
                        'presentCount' => (int) ($counts->present_count ?? 0),
                        'hasMinutes' => $meeting->minutes_id !== null,
                        'minutes' => $minutes,
                        'attendees' => $attendees
                            ->map(static fn (object $attendee): array => [
                                'id' => (int) $attendee->id,
                                'name' => (string) $attendee->name,
                                'role' => $attendee->role === null ? null : (string) $attendee->role,
                                'attendanceStatus' => (string) $attendee->attendance_status,
                                'userId' => $attendee->user_id === null ? null : (int) $attendee->user_id,
                            ])
                            ->all(),
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
            'formOptions' => [
                'canManage' => $canManage,
                'projects' => $projects
                    ->map(static fn (object $project): array => [
                        'id' => (int) $project->id,
                        'name' => (string) $project->name,
                    ])
                    ->all(),
                'organizationMembers' => $organizationMembers
                    ->map(static fn (object $member): array => [
                        'id' => (int) $member->id,
                        'name' => (string) $member->name,
                        'role' => (string) $member->role,
                    ])
                    ->all(),
                'statusOptions' => [
                    ['value' => 'planned', 'label' => 'Planned'],
                    ['value' => 'in_progress', 'label' => 'In Progress'],
                    ['value' => 'completed', 'label' => 'Completed'],
                    ['value' => 'cancelled', 'label' => 'Cancelled'],
                ],
                'attendanceStatusOptions' => [
                    ['value' => 'invited', 'label' => 'Invited'],
                    ['value' => 'present', 'label' => 'Present'],
                    ['value' => 'absent', 'label' => 'Absent'],
                    ['value' => 'excused', 'label' => 'Excused'],
                ],
            ],
            'activeOrganizationId' => $activeOrganizationId,
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
