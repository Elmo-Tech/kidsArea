<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InventoryUnitEnum;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    use CreatedUpdatedBy;
    protected $fillable = [
        'name',
        'base_unit',
        'current_quantity',
        'minimum_quantity',
        'unit_cost',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'base_unit' =>
                InventoryUnitEnum::class,

            'current_quantity' =>
                'decimal:3',

            'minimum_quantity' =>
                'decimal:3',

            'unit_cost' =>
                'decimal:4',

            'is_active' =>
                'boolean',
        ];
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(
            StockMovement::class
        );
    }
}
