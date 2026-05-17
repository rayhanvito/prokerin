<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

final class FeatureFlag extends Model
{
    public const CACHE_KEY = 'feature_flags.all';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'is_enabled_globally',
        'enabled_organization_ids',
        'enabled_plan_tiers',
        'description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_enabled_globally' => 'boolean',
            'enabled_organization_ids' => 'array',
            'enabled_plan_tiers' => 'array',
        ];
    }

    protected static function booted(): void
    {
        self::saved(static fn (): bool => Cache::forget(self::CACHE_KEY));
        self::deleted(static fn (): bool => Cache::forget(self::CACHE_KEY));
    }
}
