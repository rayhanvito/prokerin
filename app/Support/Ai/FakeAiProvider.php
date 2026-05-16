<?php

declare(strict_types=1);

namespace App\Support\Ai;

use App\DTOs\Ai\AiProviderResponse;

final class FakeAiProvider implements AiProvider
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function complete(string $actionType, array $payload, string $model): AiProviderResponse
    {
        $content = match ($actionType) {
            'proposal_draft' => $this->proposalDraft($payload),
            'lpj_summary' => $this->lpjSummary($payload),
            default => ['summary' => 'Saran AI belum tersedia untuk aksi ini.'],
        };

        return new AiProviderResponse(
            content: $content,
            promptTokens: $this->tokenEstimate($payload),
            completionTokens: $this->tokenEstimate($content),
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{sections: array<int, array{heading: string, body: string}>}
     */
    private function proposalDraft(array $payload): array
    {
        $project = is_array($payload['project'] ?? null) ? $payload['project'] : [];
        $sections = is_array($payload['sections'] ?? null) ? $payload['sections'] : [];
        $projectName = (string) ($project['name'] ?? 'program kerja');

        return [
            'sections' => array_values(array_map(
                static fn (array $section): array => [
                    'heading' => (string) ($section['heading'] ?? 'Bagian Proposal'),
                    'body' => sprintf(
                        'Saran awal untuk %s: jelaskan konteks, tujuan, alur pelaksanaan, dan indikator keberhasilan %s secara ringkas.',
                        (string) ($section['heading'] ?? 'bagian ini'),
                        $projectName,
                    ),
                ],
                $sections,
            )),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{summary: string, recommendations: array<int, string>}
     */
    private function lpjSummary(array $payload): array
    {
        $project = is_array($payload['project'] ?? null) ? $payload['project'] : [];
        $readiness = is_array($payload['readiness'] ?? null) ? $payload['readiness'] : [];
        $missingItems = is_array($readiness['missing_required_items'] ?? null) ? $readiness['missing_required_items'] : [];
        $projectName = (string) ($project['name'] ?? 'program kerja');
        $progress = (int) ($readiness['completion_progress'] ?? 0);

        return [
            'summary' => sprintf(
                'LPJ %s sudah berada pada kesiapan %d%% berdasarkan checklist yang tersedia. Narasi akhir sebaiknya menonjolkan capaian, kendala, dan tindak lanjut.',
                $projectName,
                $progress,
            ),
            'recommendations' => array_values(array_map(
                static fn (mixed $item): string => sprintf('Lengkapi item wajib: %s.', (string) $item),
                $missingItems,
            )),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function tokenEstimate(array $payload): int
    {
        return max(1, (int) ceil(str_word_count(json_encode($payload, JSON_THROW_ON_ERROR)) * 1.3));
    }
}
