<?php

declare(strict_types=1);

namespace App\DTOs\DocumentExport;

final readonly class ExportQueuePlanData
{
    public function __construct(
        public string $queueName,
        public string $engine,
        public string $storageDisk,
        public string $outputPath,
        public bool $shouldQueue,
    ) {}

    /**
     * @return array{queueName: string, engine: string, storageDisk: string, outputPath: string, shouldQueue: bool}
     */
    public function toArray(): array
    {
        return [
            'queueName' => $this->queueName,
            'engine' => $this->engine,
            'storageDisk' => $this->storageDisk,
            'outputPath' => $this->outputPath,
            'shouldQueue' => $this->shouldQueue,
        ];
    }
}
