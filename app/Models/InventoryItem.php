<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Inventory\InventoryCondition;
use App\Domain\Inventory\InventoryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class InventoryItem extends Model
{
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'name',
        'category',
        'description',
        'photo_path',
        'location',
        'condition',
        'status',
        'qr_token',
        'purchased_at',
        'purchase_amount',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'condition' => InventoryCondition::class,
            'status' => InventoryStatus::class,
            'purchased_at' => 'date',
            'purchase_amount' => 'integer',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(InventoryLoan::class, 'item_id');
    }
}
