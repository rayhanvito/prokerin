<?php

declare(strict_types=1);

namespace App\Actions\Calendar;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class BuildIcsFeedAction
{
    public function execute(string $calendarSyncToken): string
    {
        $user = DB::table('users')->where('calendar_sync_token', $calendarSyncToken)->first(['id', 'name', 'email']);

        if ($user === null) {
            return $this->calendar([]);
        }

        $userId = (int) $user->id;
        $organizationIds = DB::table('organization_members')
            ->where('user_id', $userId)
            ->pluck('organization_id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        if ($organizationIds === []) {
            return $this->calendar([]);
        }

        $from = CarbonImmutable::now()->subDays(30)->startOfDay();
        $until = CarbonImmutable::now()->addDays(90)->endOfDay();

        $events = collect()
            ->merge($this->meetingEvents($organizationIds, $from, $until))
            ->merge($this->projectDeadlineEvents($userId, $organizationIds, $from, $until))
            ->merge($this->taskDeadlineEvents($userId, $organizationIds, $from, $until))
            ->sortBy('starts_at')
            ->values();

        return $this->calendar($events->all());
    }

    /**
     * @param  array<int, int>  $organizationIds
     * @return Collection<int, array<string, mixed>>
     */
    private function meetingEvents(array $organizationIds, CarbonImmutable $from, CarbonImmutable $until): Collection
    {
        return DB::table('meetings')
            ->leftJoin('projects', 'projects.id', '=', 'meetings.project_id')
            ->whereIn('meetings.organization_id', $organizationIds)
            ->whereBetween('meetings.starts_at', [$from, $until])
            ->orderBy('meetings.starts_at')
            ->get([
                'meetings.id',
                'meetings.title',
                'meetings.agenda',
                'meetings.location',
                'meetings.starts_at',
                'meetings.ends_at',
                'projects.name as project_name',
            ])
            ->map(fn (object $meeting): array => [
                'uid' => 'meeting-'.$meeting->id.'@prokerin',
                'title' => 'Rapat: '.(string) $meeting->title,
                'description' => trim((string) $meeting->agenda."\n".($meeting->project_name === null ? '' : 'Proker: '.$meeting->project_name)),
                'location' => $meeting->location === null ? '' : (string) $meeting->location,
                'starts_at' => CarbonImmutable::parse((string) $meeting->starts_at),
                'ends_at' => $meeting->ends_at === null ? CarbonImmutable::parse((string) $meeting->starts_at)->addHour() : CarbonImmutable::parse((string) $meeting->ends_at),
            ]);
    }

    /**
     * @param  array<int, int>  $organizationIds
     * @return Collection<int, array<string, mixed>>
     */
    private function projectDeadlineEvents(int $userId, array $organizationIds, CarbonImmutable $from, CarbonImmutable $until): Collection
    {
        return DB::table('projects')
            ->leftJoin('project_members', function ($join) use ($userId): void {
                $join->on('project_members.project_id', '=', 'projects.id')
                    ->where('project_members.user_id', $userId);
            })
            ->whereIn('projects.organization_id', $organizationIds)
            ->whereNull('projects.deleted_at')
            ->where(function ($query) use ($userId): void {
                $query->where('projects.project_lead_id', $userId)
                    ->orWhereNotNull('project_members.id');
            })
            ->whereBetween('projects.ends_at', [$from->toDateString(), $until->toDateString()])
            ->orderBy('projects.ends_at')
            ->get(['projects.id', 'projects.name', 'projects.description', 'projects.ends_at'])
            ->map(fn (object $project): array => [
                'uid' => 'project-deadline-'.$project->id.'@prokerin',
                'title' => 'Deadline Proker: '.(string) $project->name,
                'description' => $project->description === null ? '' : (string) $project->description,
                'location' => '',
                'starts_at' => CarbonImmutable::parse((string) $project->ends_at)->startOfDay(),
                'ends_at' => CarbonImmutable::parse((string) $project->ends_at)->endOfDay(),
            ]);
    }

    /**
     * @param  array<int, int>  $organizationIds
     * @return Collection<int, array<string, mixed>>
     */
    private function taskDeadlineEvents(int $userId, array $organizationIds, CarbonImmutable $from, CarbonImmutable $until): Collection
    {
        return DB::table('project_tasks')
            ->join('projects', 'projects.id', '=', 'project_tasks.project_id')
            ->where('project_tasks.pic_user_id', $userId)
            ->whereIn('projects.organization_id', $organizationIds)
            ->whereNull('projects.deleted_at')
            ->whereBetween('project_tasks.due_at', [$from->toDateString(), $until->toDateString()])
            ->orderBy('project_tasks.due_at')
            ->get(['project_tasks.id', 'project_tasks.title', 'project_tasks.division', 'project_tasks.due_at', 'projects.name as project_name'])
            ->map(fn (object $task): array => [
                'uid' => 'task-deadline-'.$task->id.'@prokerin',
                'title' => 'Deadline Task: '.(string) $task->title,
                'description' => trim('Proker: '.(string) $task->project_name."\nDivisi: ".(string) ($task->division ?? '-')),
                'location' => '',
                'starts_at' => CarbonImmutable::parse((string) $task->due_at)->startOfDay(),
                'ends_at' => CarbonImmutable::parse((string) $task->due_at)->endOfDay(),
            ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $events
     */
    private function calendar(array $events): string
    {
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Prokerin//Calendar Sync//ID',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:Prokerin Calendar',
        ];

        foreach ($events as $event) {
            $startsAt = $event['starts_at'];
            $endsAt = $event['ends_at'];

            if (! $startsAt instanceof CarbonImmutable || ! $endsAt instanceof CarbonImmutable) {
                continue;
            }

            array_push(
                $lines,
                'BEGIN:VEVENT',
                'UID:'.$this->escape((string) $event['uid']),
                'DTSTAMP:'.$this->dateTime(CarbonImmutable::now()),
                'DTSTART:'.$this->dateTime($startsAt),
                'DTEND:'.$this->dateTime($endsAt),
                'SUMMARY:'.$this->escape((string) $event['title']),
                'DESCRIPTION:'.$this->escape((string) $event['description']),
                'LOCATION:'.$this->escape((string) $event['location']),
                'END:VEVENT',
            );
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines)."\r\n";
    }

    private function dateTime(CarbonImmutable $date): string
    {
        return $date->utc()->format('Ymd\THis\Z');
    }

    private function escape(string $value): string
    {
        return str_replace(['\\', "\r\n", "\n", ';', ','], ['\\\\', '\n', '\n', '\;', '\,'], $value);
    }
}
