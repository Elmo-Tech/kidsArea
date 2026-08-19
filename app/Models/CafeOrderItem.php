<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CafeOrderItem extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'cafe_order_id',
        'cafe_product_id',
        'product_name',
        'unit_price',
        'quantity',
        'total',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(
            CafeOrder::class,
            'cafe_order_id'
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(
            CafeProduct::class,
            'cafe_product_id'
        );
    }
}
