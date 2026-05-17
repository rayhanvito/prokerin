<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class FailedJob extends Model
{
    protected $table = 'failed_jobs';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'connection',
        'queue',
        'payload',
        'exception',
        'failed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'failed_at' => 'datetime',
        ];
    }

    public function getJobNameAttribute(): string
    {
        $payload = json_decode((string) $this->payload, true);

        if (is_array($payload) && isset($payload['displayName']) && is_string($payload['displayName'])) {
            return $payload['displayName'];
        }

        return 'Unknown Job';
    }

    public function getExceptionFirstLineAttribute(): string
    {
        $exception = (string) $this->exception;
        $first = strtok($exception, "\n");

        return $first === false ? $exception : $first;
    }
}
