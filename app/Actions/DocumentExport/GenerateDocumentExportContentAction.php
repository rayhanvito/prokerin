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
