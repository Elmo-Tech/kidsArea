<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StockMovementTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'inventory_item_id',
        'type',
        'quantity',
        'unit_cost',
        'total_cost',
        'source_type',
        'source_id',
        'movement_at',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'type' =>
                StockMovementTypeEnum::class,

            'quantity' =>
                'decimal:3',

            'unit_cost' =>
                'decimal:4',

            'total_cost' =>
                'decimal:4',

            'movement_at' =>
                'datetime',
        ];
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(
            InventoryItem::class
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}
