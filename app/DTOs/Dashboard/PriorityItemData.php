<?php

declare(strict_types=1);

namespace App\DTOs\Dashboard;

final readonly class PriorityItemData
{
    public function __construct(
        public string $title,
        public string $meta,
        public string $status,
        public int $progress,
        public ?string $href = null,
    ) {}

    /**
     * @return array{title: string, meta: string, status: string, progress: int, href: string|null}
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'meta' => $this->meta,
            'status' => $this->status,
            'progress' => $this->progress,
            'href' => $this->href,
        ];
    }
}
