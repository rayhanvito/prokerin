<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OrganizationInvitation extends Model
{
    protected $table = 'organization_invitations';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'email',
        'role',
        'status',
        'token',
        'expires_at',
        'invited_by_user_id',
        'accepted_by_user_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
