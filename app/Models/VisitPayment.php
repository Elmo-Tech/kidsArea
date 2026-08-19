<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitPayment extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'visit_checkout_id',
        'amount',
        'paid_at',
        'reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function checkout(): BelongsTo
    {
        return $this->belongsTo(
            VisitCheckout::class,
            'visit_checkout_id'
        );
    }
}
