<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use Illuminate\Support\Facades\DB;
use Throwable;

final class GetPlatformHealthAction
{
    /**
     * @return array<string, array{status: string, detail: string}>
     */
    public function execute(): array
    {
        return [
            'database' => $this->database(),
            'queue' => $this->queue(),
            'storage' => $this->configured('filesystem', (string) config('filesystems.default')),
            'mail' => $this->configured('mail', (string) config('mail.default')),
        ];
    }

    /**
     * @return array{status: string, detail: string}
     */
    private function database(): array
    {
        try {
            $startedAt = microtime(true);
            DB::select('select 1');

            return [
                'status' => 'up',
                'detail' => sprintf('%d ms', (int) ((microtime(true) - $startedAt) * 1000)),
            ];
        } catch (Throwable $exception) {
            return [
                'status' => 'down',
                'detail' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @return array{status: string, detail: string}
     */
    private function queue(): array
    {
        if (! DB::getSchemaBuilder()->hasTable('jobs')) {
            return ['status' => 'unknown', 'detail' => 'jobs table unavailable'];
        }

        $depth = (int) DB::table('jobs')->count();

        return [
            'status' => $depth > 100 ? 'warning' : 'up',
            'detail' => sprintf('%d queued jobs', $depth),
        ];
    }

    /**
     * @return array{status: string, detail: string}
     */
    private function configured(string $service, string $value): array
    {
        return [
            'status' => $value === '' ? 'warning' : 'up',
            'detail' => $value === '' ? "{$service} not configured" : $value,
        ];
    }
}
