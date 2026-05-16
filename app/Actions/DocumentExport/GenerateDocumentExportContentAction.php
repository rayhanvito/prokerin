<?php

declare(strict_types=1);

namespace App\Actions\DocumentExport;

use Dompdf\Dompdf;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

final class GenerateDocumentExportContentAction
{
    public function execute(object $export): string
    {
        $document = $this->buildDocument($export);

        return match ((string) $export->format) {
            'pdf' => $this->toPdf($document),
            'docx' => $this->toDocx($document),
            default => $this->toPlainText($document),
        };
    }

    /**
     * @return array{title: string, subtitle: string, sections: array<int, array{title: string, body: string}>}
     */
    private function buildDocument(object $export): array
    {
        $project = DB::table('projects')
            ->leftJoin('organizations', 'organizations.id', '=', 'projects.organization_id')
            ->where('projects.id', $export->project_id)
            ->select('projects.*', 'organizations.name as organization_name')
            ->first();

        if ((string) $export->document_type === 'proposal') {
            return $this->proposalDocument($export, $project);
        }

        if ((string) $export->document_type === 'lpj') {
            return $this->lpjDocument($export, $project);
        }

        if ((string) $export->document_type === 'handover') {
            return $this->handoverDocument($export);
        }

        if ((string) $export->document_type === 'event_registration') {
            return $this->eventRegistrationDocument($export, $project);
        }

        if ((string) $export->document_type === 'meeting_minutes') {
            return $this->meetingMinutesDocument($export);
        }

        return [
            'title' => (string) $export->document_title,
            'subtitle' => $project === null ? 'Prokerin export' : (string) $project->name,
            'sections' => [
                [
                    'title' => 'Ringkasan',
                    'body' => 'Dokumen export dibuat dari antrean Prokerin.',
                ],
            ],
        ];
    }

    /**
     * @return array{title: string, subtitle: string, sections: array<int, array{title: string, body: string}>}
     */
    private function eventRegistrationDocument(object $export, ?object $project): array
    {
        $registrations = DB::table('event_registrations')
            ->where('project_id', $export->project_id)
            ->orderBy('participant_name')
            ->get([
                'participant_name',
                'participant_email',
                'phone',
                'institution',
                'status',
                'registered_at',
            ])
            ->map(static function (object $registration): string {
                return sprintf(
                    '%s | %s | %s | %s | %s | %s',
                    (string) $registration->participant_name,
                    (string) $registration->participant_email,
                    (string) ($registration->phone ?? '-'),
                    (string) ($registration->institution ?? '-'),
                    (string) $registration->status,
                    (string) $registration->registered_at,
                );
            })
            ->all();

        return [
            'title' => (string) $export->document_title,
            'subtitle' => $project === null ? 'Daftar peserta event' : (string) $project->name,
            'sections' => [
                [
                    'title' => 'Informasi Event',
                    'body' => $this->projectSummary($project),
                ],
                [
                    'title' => 'Daftar Peserta',
                    'body' => $registrations === []
                        ? 'Belum ada peserta terdaftar.'
                        : implode(PHP_EOL, $registrations),
                ],
            ],
        ];
    }

    /**
     * @return array{title: string, subtitle: string, sections: array<int, array{title: string, body: string}>}
     */
    private function handoverDocument(object $export): array
    {
        $packageId = $this->handoverPackageId($export);
        $package = $packageId === null
            ? null
            : DB::table('handover_packages')
                ->join('organizations', 'organizations.id', '=', 'handover_packages.organization_id')
                ->leftJoin('organization_periods', 'organization_periods.id', '=', 'handover_packages.from_period_id')
                ->where('handover_packages.id', $packageId)
                ->first([
                    'handover_packages.*',
                    'organizations.name as organization_name',
                    'organization_periods.name as period_name',
                ]);

        if ($package === null) {
            return [
                'title' => (string) $export->document_title,
                'subtitle' => 'Paket handover tidak ditemukan.',
                'sections' => [],
            ];
        }

        $items = DB::table('handover_items')
            ->leftJoin('users', 'users.id', '=', 'handover_items.assignee_id')
            ->where('handover_items.package_id', $package->id)
            ->orderBy('handover_items.id')
            ->get([
                'handover_items.category',
                'handover_items.label',
                'handover_items.description',
                'handover_items.status',
                'users.name as assignee_name',
            ])
            ->map(static function (object $item): string {
                return sprintf(
                    '[%s] %s - %s%s',
                    (string) $item->status === 'done' ? 'x' : ' ',
                    (string) $item->label,
                    (string) ($item->description ?? '-'),
                    $item->assignee_name === null ? '' : ' (PIC: '.(string) $item->assignee_name.')',
                );
            })
            ->all();

        $snapshot = json_decode((string) $package->snapshot, true) ?: [];

        return [
            'title' => (string) $export->document_title,
            'subtitle' => sprintf(
                '%s | Periode %s | Status %s',
                (string) $package->organization_name,
                (string) ($package->period_name ?? '-'),
                (string) $package->status,
            ),
            'sections' => [
                [
                    'title' => 'Ringkasan Snapshot',
                    'body' => implode(PHP_EOL, [
                        'Task terbuka: '.(string) ($snapshot['open_tasks'] ?? 0),
                        'Dokumen arsip: '.(string) ($snapshot['documents'] ?? 0),
                        'Rencana budget: Rp'.number_format((int) ($snapshot['planned_budget'] ?? 0), 0, ',', '.'),
                        'Realisasi budget: Rp'.number_format((int) ($snapshot['realized_budget'] ?? 0), 0, ',', '.'),
                        'LPJ wajib belum lengkap: '.(string) ($snapshot['outstanding_lpj_items'] ?? 0),
                    ]),
                ],
                [
                    'title' => 'Checklist Handover',
                    'body' => implode(PHP_EOL, $items),
                ],
            ],
        ];
    }

    private function handoverPackageId(object $export): ?int
    {
        preg_match('/handover-package-(\d+)/', (string) $export->output_path, $matches);

        return isset($matches[1]) ? (int) $matches[1] : null;
    }

    /**
     * @return array{title: string, subtitle: string, sections: array<int, array{title: string, body: string}>}
     */
    private function meetingMinutesDocument(object $export): array
    {
        $meetingId = $this->meetingId($export);

        if ($meetingId === null) {
            return [
                'title' => (string) $export->document_title,
                'subtitle' => 'Notulen rapat tidak ditemukan.',
                'sections' => [],
            ];
        }

        $meeting = DB::table('meetings')
            ->leftJoin('organizations', 'organizations.id', '=', 'meetings.organization_id')
            ->leftJoin('projects', 'projects.id', '=', 'meetings.project_id')
            ->where('meetings.id', $meetingId)
            ->first([
                'meetings.*',
                'organizations.name as organization_name',
                'projects.name as project_name',
            ]);

        if ($meeting === null) {
            return [
                'title' => (string) $export->document_title,
                'subtitle' => 'Notulen rapat tidak ditemukan.',
                'sections' => [],
            ];
        }

        $minutes = DB::table('meeting_minutes')
            ->where('meeting_id', $meetingId)
            ->first();

        $attendees = DB::table('meeting_attendees')
            ->where('meeting_id', $meetingId)
            ->orderBy('id')
            ->get(['name', 'role', 'attendance_status']);

        $attendeeRows = $attendees
            ->map(static fn (object $row): string => sprintf(
                '%s | %s | %s',
                (string) $row->name,
                (string) ($row->role ?? '-'),
                (string) $row->attendance_status,
            ))
            ->all();

        $decisions = $minutes === null ? [] : (json_decode((string) $minutes->decisions, true) ?: []);
        $actionItems = $minutes === null ? [] : (json_decode((string) $minutes->action_items, true) ?: []);

        $decisionLines = array_filter(array_map(
            static fn ($decision): ?string => is_string($decision) && trim($decision) !== '' ? '- '.trim($decision) : null,
            $decisions,
        ));

        $actionLines = array_filter(array_map(
            static function ($item): ?string {
                if (! is_array($item)) {
                    return null;
                }

                return sprintf(
                    '- %s | PIC: %s | Due: %s | Status: %s',
                    (string) ($item['task'] ?? ''),
                    (string) ($item['owner'] ?? '-'),
                    (string) ($item['due'] ?? '-'),
                    (string) ($item['status'] ?? 'open'),
                );
            },
            $actionItems,
        ));

        return [
            'title' => (string) $export->document_title,
            'subtitle' => sprintf(
                '%s · %s · %s',
                (string) ($meeting->organization_name ?? '-'),
                (string) ($meeting->project_name ?? 'Agenda organisasi'),
                (string) $meeting->starts_at,
            ),
            'sections' => array_values(array_filter([
                [
                    'title' => 'Agenda',
                    'body' => (string) $meeting->agenda,
                ],
                [
                    'title' => 'Daftar Hadir',
                    'body' => $attendeeRows === [] ? 'Belum ada peserta.' : implode(PHP_EOL, $attendeeRows),
                ],
                [
                    'title' => 'Ringkasan',
                    'body' => $minutes === null ? 'Notulen belum dipublikasikan.' : (string) $minutes->summary,
                ],
                [
                    'title' => 'Keputusan',
                    'body' => $decisionLines === [] ? 'Tidak ada keputusan tercatat.' : implode(PHP_EOL, $decisionLines),
                ],
                [
                    'title' => 'Action Items',
                    'body' => $actionLines === [] ? 'Tidak ada tindak lanjut.' : implode(PHP_EOL, $actionLines),
                ],
            ])),
        ];
    }

    private function meetingId(object $export): ?int
    {
        preg_match('/meeting-(\d+)/', (string) $export->output_path, $matches);

        return isset($matches[1]) ? (int) $matches[1] : null;
    }

    /**
     * @return array{title: string, subtitle: string, sections: array<int, array{title: string, body: string}>}
     */
    private function proposalDocument(object $export, ?object $project): array
    {
        $draft = DB::table('proposal_drafts')
            ->where('project_id', $export->project_id)
            ->orderByDesc('id')
            ->first();

        $sections = collect(json_decode((string) ($draft->sections ?? '[]'), true))
            ->filter(static fn (mixed $section): bool => is_array($section))
            ->map(static fn (array $section): array => [
                'title' => (string) ($section['title'] ?? 'Bagian Proposal'),
                'body' => (string) ($section['body'] ?? ''),
            ])
            ->values()
            ->all();

        return [
            'title' => (string) ($draft->title ?? $export->document_title),
            'subtitle' => (string) ($draft->subtitle ?? ($project->name ?? 'Proposal kegiatan')),
            'sections' => $sections === [] ? $this->defaultProjectSections($project) : $sections,
        ];
    }

    /**
     * @return array{title: string, subtitle: string, sections: array<int, array{title: string, body: string}>}
     */
    private function lpjDocument(object $export, ?object $project): array
    {
        $items = DB::table('lpj_checklist_items')
            ->where('project_id', $export->project_id)
            ->orderBy('id')
            ->get()
            ->map(static function (object $item): string {
                $marker = (bool) $item->is_complete ? '[x]' : '[ ]';

                return sprintf('%s %s', $marker, (string) $item->title);
            })
            ->all();

        return [
            'title' => (string) $export->document_title,
            'subtitle' => $project === null ? 'Laporan pertanggungjawaban' : (string) $project->name,
            'sections' => [
                [
                    'title' => 'Informasi Kegiatan',
                    'body' => $this->projectSummary($project),
                ],
                [
                    'title' => 'Checklist LPJ',
                    'body' => implode(PHP_EOL, $items),
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{title: string, body: string}>
     */
    private function defaultProjectSections(?object $project): array
    {
        return [
            [
                'title' => 'Informasi Kegiatan',
                'body' => $this->projectSummary($project),
            ],
        ];
    }

    private function projectSummary(?object $project): string
    {
        if ($project === null) {
            return 'Data kegiatan tidak ditemukan.';
        }

        return implode(PHP_EOL, array_filter([
            'Organisasi: '.(string) ($project->organization_name ?? '-'),
            'Nama proker: '.(string) $project->name,
            'Status: '.(string) $project->status,
            'Mulai: '.(string) ($project->starts_at ?? '-'),
            'Selesai: '.(string) ($project->ends_at ?? '-'),
            'Deskripsi: '.(string) ($project->description ?? '-'),
        ]));
    }

    /**
     * @param  array{title: string, subtitle: string, sections: array<int, array{title: string, body: string}>}  $document
     */
    private function toPdf(array $document): string
    {
        $dompdf = new Dompdf;
        $dompdf->loadHtml($this->toHtml($document));
        $dompdf->setPaper('A4');
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * @param  array{title: string, subtitle: string, sections: array<int, array{title: string, body: string}>}  $document
     */
    private function toDocx(array $document): string
    {
        $word = new PhpWord;
        $section = $word->addSection();
        $section->addTitle($document['title'], 1);
        $section->addText($document['subtitle']);

        foreach ($document['sections'] as $item) {
            $section->addTitle($item['title'], 2);

            foreach (explode(PHP_EOL, $item['body']) as $line) {
                $section->addText($line === '' ? ' ' : $line);
            }
        }

        $path = tempnam(sys_get_temp_dir(), 'prokerin-docx-');
        IOFactory::createWriter($word)->save($path);

        $content = (string) file_get_contents($path);
        @unlink($path);

        return $content;
    }

    /**
     * @param  array{title: string, subtitle: string, sections: array<int, array{title: string, body: string}>}  $document
     */
    private function toPlainText(array $document): string
    {
        return implode(PHP_EOL.PHP_EOL, [
            $document['title'],
            $document['subtitle'],
            ...array_map(
                static fn (array $section): string => $section['title'].PHP_EOL.$section['body'],
                $document['sections'],
            ),
        ]);
    }

    /**
     * @param  array{title: string, subtitle: string, sections: array<int, array{title: string, body: string}>}  $document
     */
    private function toHtml(array $document): string
    {
        $sections = array_map(
            static fn (array $section): string => sprintf(
                '<h2>%s</h2><p>%s</p>',
                e($section['title']),
                nl2br(e($section['body'])),
            ),
            $document['sections'],
        );

        return sprintf(
            '<!doctype html><html><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans,sans-serif;font-size:12px;line-height:1.6;color:#242934}h1{font-size:22px;margin-bottom:4px}h2{font-size:16px;margin-top:24px;color:#24695c}.subtitle{color:#59667a}</style></head><body><h1>%s</h1><p class="subtitle">%s</p>%s</body></html>',
            e($document['title']),
            e($document['subtitle']),
            implode('', $sections),
        );
    }
}
