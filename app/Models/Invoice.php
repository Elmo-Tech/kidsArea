<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Invoice extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'invoiceable_type',
        'invoiceable_id',
        'invoice_number',
        'subtotal',
        'discount',
        'total',
        'issued_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'issued_at' => 'datetime',
        ];
    }

    public function invoiceable(): MorphTo
    {
        return $this->morphTo();
    }
}
