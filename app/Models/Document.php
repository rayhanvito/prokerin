<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

final class Document extends Model
{
    use Searchable;

    protected $table = 'documents';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'project_id',
        'owner_user_id',
        'name',
        'folder',
        'storage_path',
        'mime_type',
        'size_kb',
        'visibility',
        'status',
    ];

    /**
     * @return array<string, string|int|null>
     */
    public function toSearchableArray(): array
    {
        return [
            'organization_id' => (int) $this->organization_id,
            'project_id' => $this->project_id === null ? null : (int) $this->project_id,
            'name' => (string) $this->name,
            'folder' => (string) $this->folder,
            'visibility' => (string) $this->visibility,
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }
}
