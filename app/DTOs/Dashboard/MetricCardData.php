<?php

declare(strict_types=1);

namespace App\DTOs\Dashboard;

final readonly class MetricCardData
{
    public function __construct(
        public string $label,
        public string $value,
        public string $note,
        public DashboardTone $tone = DashboardTone::Default,
    ) {}

    /**
     * @return array{label: string, value: string, note: string, tone: string}
     */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'value' => $this->value,
            'note' => $this->note,
            'tone' => $this->tone->value,
        ];
    }
}
