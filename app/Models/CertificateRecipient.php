<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CertificateRecipient extends Model
{
    protected $table = 'certificate_recipients';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'template_id',
        'user_id',
        'recipient_name',
        'recipient_email',
        'project_id',
        'meeting_id',
        'certificate_number',
        'issued_at',
        'issued_by',
        'verification_token',
        'pdf_path',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
