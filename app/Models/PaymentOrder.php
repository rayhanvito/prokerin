<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class PaymentOrder extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'registration_id',
        'tier_id',
        'amount',
        'status',
        'provider_order_id',
        'paid_at',
        'expires_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}
