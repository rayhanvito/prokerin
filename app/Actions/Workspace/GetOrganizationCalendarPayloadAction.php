<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class GetOrganizationCalendarPayloadAction
{
    /**
     * @return array{month: string, metrics: array<int, array{label: string, value: string, note: string}>, events: array<int, array{id: int, type: string, title: string, startsAt: string, endsAt: string|null, link: string}>, focus: array<int, string>}
     */
    public function execute(int $actorUserId, ?int $preferredOrganizationId = null, ?string $month = null): array
    {
        $organizationId = $this->activeOrganizationId($actorUserId, $preferredOrganizationId);
        $selectedMonth = $this->selectedMonth($month);
        $startsAt = $selectedMonth->startOfMonth();
        $endsAt = $selectedMonth->endOfMonth();

        if ($organizationId === null) {
            return [
                'month' => $selectedMonth->format('Y-m'),
                'metrics' => [
                    ['label' => 'Agenda', 'value' => '0', 'note' => 'Belum ada organisasi aktif'],
                    ['label' => 'Deadline', 'value' => '0', 'note' => 'Proposal dan LPJ'],
                    ['label' => 'Active Proker', 'value' => '0', 'note' => 'Periode berjalan'],
                ],
                'events' => [],
                'focus' => ['Buat organisasi terlebih dahulu untuk melihat kalender.'],
            ];
        }

        $projects = DB::table('projects')
            ->leftJoin('users as leads', 'leads.id', '=', 'projects.project_lead_id')
            ->where('projects.organization_id', $organizationId)
            ->whereDate('projects.starts_at', '<=', $endsAt->toDateString())
            ->whereDate('projects.ends_at', '>=', $startsAt->toDateString())
            ->orderBy('projects.starts_at')
            ->get([
                'projects.id',
                'projects.name',
                'projects.slug',
                'projects.status',
                'projects.starts_at',
                'projects.ends_at',
                'leads.name as lead_name',
            ]);
        $meetings = DB::table('meetings')
            ->where('organization_id', $organizationId)
            ->whereBetween('starts_at', [$startsAt->startOfDay(), $endsAt->endOfDay()])
            ->orderBy('starts_at')
            ->get(['id', 'title', 'starts_at', 'ends_at']);
        $attendanceSessions = DB::table('attendance_sessions')
            ->where('attendance_sessions.organization_id', $organizationId)
            ->whereBetween('attendance_sessions.starts_at', [$startsAt->startOfDay(), $endsAt->endOfDay()])
            ->orderBy('attendance_sessions.starts_at')
            ->get([
                'attendance_sessions.id',
                'attendance_sessions.title',
                'attendance_sessions.starts_at',
                'attendance_sessions.ends_at',
            ]);

        $deadlineCount = DB::table('proposal_drafts')
            ->join('projects', 'projects.id', '=', 'proposal_drafts.project_id')
            ->where('projects.organization_id', $organizationId)
            ->whereIn('proposal_drafts.status', ['draft', 'revision_requested'])
            ->count()
            + DB::table('lpj_checklist_items')
                ->join('projects', 'projects.id', '=', 'lpj_checklist_items.project_id')
                ->where('projects.organization_id', $organizationId)
                ->where('lpj_checklist_items.is_complete', false)
                ->count();

        $events = [
            ...$projects->map(static fn (object $project): array => [
                'id' => (int) $project->id,
                'type' => 'project',
                'title' => (string) $project->name,
                'startsAt' => (string) $project->starts_at,
                'endsAt' => $project->ends_at === null ? null : (string) $project->ends_at,
                'link' => route('proker.detail', ['project' => (string) $project->slug], false),
            ])->all(),
            ...$meetings->map(static fn (object $meeting): array => [
                'id' => (int) $meeting->id,
                'type' => 'meeting',
                'title' => (string) $meeting->title,
                'startsAt' => (string) $meeting->starts_at,
                'endsAt' => $meeting->ends_at === null ? null : (string) $meeting->ends_at,
                'link' => route('meetings.index', absolute: false),
            ])->all(),
            ...$attendanceSessions->map(static fn (object $session): array => [
                'id' => (int) $session->id,
                'type' => 'attendance',
                'title' => (string) $session->title,
                'startsAt' => (string) $session->starts_at,
                'endsAt' => $session->ends_at === null ? null : (string) $session->ends_at,
                'link' => route('attendance.index', absolute: false),
            ])->all(),
        ];

        return [
            'month' => $selectedMonth->format('Y-m'),
            'metrics' => [
                ['label' => 'Agenda', 'value' => (string) count($events), 'note' => 'Bulan ini'],
                ['label' => 'Deadline', 'value' => (string) $deadlineCount, 'note' => 'Proposal dan LPJ'],
                [
                    'label' => 'Active Proker',
                    'value' => (string) $projects->whereIn('status', ['active', 'planning'])->count(),
                    'note' => 'Periode berjalan',
                ],
            ],
            'events' => $events,
            'focus' => [
                'Jadwal proker diambil dari database organisasi aktif.',
                'Meeting dan sesi presensi masuk ke kalender bulan yang sama.',
                'Gunakan switcher organisasi untuk mengganti konteks kalender.',
            ],
        ];
    }

    private function selectedMonth(?string $month): CarbonImmutable
    {
        if (is_string($month) && preg_match('/^\d{4}-\d{2}$/', $month) === 1) {
            return CarbonImmutable::createFromFormat('Y-m-d', $month.'-01')->startOfMonth();
        }

        return CarbonImmutable::now()->startOfMonth();
    }

    private function activeOrganizationId(int $actorUserId, ?int $preferredOrganizationId): ?int
    {
        $membership = DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->when($preferredOrganizationId !== null, static function ($query) use ($preferredOrganizationId): void {
                $query->where('organization_id', $preferredOrganizationId);
            })
            ->orderBy('id')
            ->first(['organization_id']);

        if ($membership !== null) {
            return (int) $membership->organization_id;
        }

        if ($preferredOrganizationId === null) {
            return null;
        }

        $fallback = DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->orderBy('id')
            ->value('organization_id');

        return $fallback === null ? null : (int) $fallback;
    }
}
