<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CafeProductIngredient extends Model
{
    use CreatedUpdatedBy;
    protected $fillable = [
        'cafe_product_id',
        'inventory_item_id',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(
            CafeProduct::class,
            'cafe_product_id'
        );
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(
            InventoryItem::class
        );
    }
}
