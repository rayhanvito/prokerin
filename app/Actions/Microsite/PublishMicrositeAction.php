<?php

declare(strict_types=1);

namespace App\Actions\Microsite;

use App\Actions\Microsite\Concerns\AuthorizesMicrositeManagement;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class PublishMicrositeAction
{
    use AuthorizesMicrositeManagement;

    /**
     * @throws AuthorizationException|ValidationException
     */
    public function execute(int $actorUserId, string $projectSlug): void
    {
        $project = $this->authorizeProjectManager($actorUserId, $projectSlug);
        $micrositeId = $this->ensureMicrosite((int) $project->id, $actorUserId);
        $microsite = DB::table('proker_microsites')
            ->join('projects', 'projects.id', '=', 'proker_microsites.project_id')
            ->where('proker_microsites.id', $micrositeId)
            ->first(['projects.name', 'projects.starts_at', 'proker_microsites.description_md']);

        if ($microsite === null || blank($microsite->name) || blank($microsite->starts_at) || blank($microsite->description_md)) {
            throw ValidationException::withMessages([
                'microsite' => 'Lengkapi judul proker, tanggal, dan deskripsi microsite sebelum publish.',
            ]);
        }

        DB::table('proker_microsites')
            ->where('id', $micrositeId)
            ->update([
                'is_published' => true,
                'published_at' => now(),
                'updated_by_user_id' => $actorUserId,
                'updated_at' => now(),
            ]);

        Cache::forget("microsite:{$project->organization_slug}:{$project->slug}");
    }
}
