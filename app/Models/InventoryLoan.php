<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Inventory\LoanReturnCondition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class InventoryLoan extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'item_id',
        'borrower_user_id',
        'project_id',
        'status',
        'loaned_at',
        'expected_return_at',
        'returned_at',
        'return_condition',
        'notes',
        'approved_by_user_id',
        'overdue_notified_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'loaned_at' => 'datetime',
            'expected_return_at' => 'datetime',
            'returned_at' => 'datetime',
            'return_condition' => LoanReturnCondition::class,
            'overdue_notified_at' => 'datetime',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'borrower_user_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
