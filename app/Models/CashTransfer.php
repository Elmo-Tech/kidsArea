<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashTransfer extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'from_cash_shift_id',
        'to_cash_register_id',
        'amount',
        'transferred_at',
        'notes',
    ];
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transferred_at' => 'datetime',
        ];
    }

    public function fromShift(): BelongsTo
    {
        return $this->belongsTo(
            CashShift::class,
            'from_cash_shift_id'
        );
    }

    public function toRegister(): BelongsTo
    {
        return $this->belongsTo(
            CashRegister::class,
            'to_cash_register_id'
        );
    }
}
