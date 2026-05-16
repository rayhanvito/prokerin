<?php

declare(strict_types=1);

namespace App\DTOs\Proposal;

final readonly class ProposalDraftData
{
    /**
     * @param  array<int, array{heading: string, body: string}>  $sections
     */
    public function __construct(
        public string $title,
        public string $subtitle,
        public array $sections,
    ) {}

    /**
     * @return array{title: string, subtitle: string, sections: array<int, array{heading: string, body: string}>}
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'sections' => $this->sections,
        ];
    }
}
