<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VisitCheckoutStatusEnum;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VisitCheckout extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'visit_id',
        'activity_total',
        'cafe_total',
        'subtotal',
        'discount',
        'total',
        'status',
        'finalized_at',
        'cancelled_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'activity_total' => 'decimal:2',
            'cafe_total' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',

            'status' => VisitCheckoutStatusEnum::class,

            'finalized_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(VisitPayment::class);
    }
}
