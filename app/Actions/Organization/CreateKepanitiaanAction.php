<?php

declare(strict_types=1);

namespace App\Actions\Organization;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CreateKepanitiaanAction
{
    public function execute(
        int $actorUserId,
        string $name,
        CarbonImmutable $eventDate,
        ?string $description = null,
    ): int {
        return DB::transaction(function () use ($actorUserId, $name, $eventDate, $description): int {
            $now = now();
            $organizationId = (int) DB::table('organizations')->insertGetId([
                'name' => $name,
                'description' => $description,
                'slug' => $this->resolveSlug($name),
                'status' => 'active',
                'plan_tier' => 'free',
                'mode' => 'kepanitiaan',
                'event_date' => $eventDate->toDateString(),
                'auto_archive_at' => $eventDate->addDays(90)->endOfDay(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('organization_members')->insert([
                'organization_id' => $organizationId,
                'user_id' => $actorUserId,
                'role' => 'organization_owner',
                'joined_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return $organizationId;
        });
    }

    private function resolveSlug(string $source): string
    {
        $base = Str::slug($source);
        $base = $base === '' ? 'kepanitiaan' : $base;

        if (! DB::table('organizations')->where('slug', $base)->exists()) {
            return $base;
        }

        do {
            $candidate = sprintf('%s-%02d', $base, random_int(1, 99));
        } while (DB::table('organizations')->where('slug', $candidate)->exists());

        return $candidate;
    }
}
