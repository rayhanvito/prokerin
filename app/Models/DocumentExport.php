<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class DocumentExport extends Model
{
    protected $table = 'document_exports';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'project_id',
        'requested_by_user_id',
        'document_title',
        'document_type',
        'format',
        'queue_name',
        'engine',
        'storage_disk',
        'output_path',
        'status',
    ];
}
