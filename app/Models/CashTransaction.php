<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CashTransactionSourceEnum;
use App\Enums\CashTransactionTypeEnum;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CashTransaction extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'cash_shift_id',
        'type',
        'amount',
        'source',
        'sourceable_type',
        'sourceable_id',
        'transaction_at',
        'notes',
        'cash_register_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => CashTransactionTypeEnum::class,
            'source' => CashTransactionSourceEnum::class,
            'amount' => 'decimal:2',
            'transaction_at' => 'datetime',
        ];
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(
            CashShift::class,
            'cash_shift_id'
        );
    }

    public function sourceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function register(): BelongsTo
    {
        return $this->belongsTo(
            CashRegister::class,
            'cash_register_id'
        );
    }
}
