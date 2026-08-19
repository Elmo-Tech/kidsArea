<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CafeOrderStatusEnum;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class CafeOrder extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'visit_id',
        'order_number',
        'subtotal',
        'discount',
        'total',
        'status',
        'confirmed_at',
        'completed_at',
        'cancelled_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',

            'status' => CafeOrderStatusEnum::class,

            'confirmed_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(
            Visit::class
        );
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            CafeOrderItem::class
        );
    }


    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function invoice(): MorphOne
    {
        return $this->morphOne(
            Invoice::class,
            'invoiceable'
        );
    }
}
