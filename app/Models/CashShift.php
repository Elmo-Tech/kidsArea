<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CashShiftStatusEnum;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CashShift extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'cash_register_id',
        'opened_by',
        'opened_at',
        'opening_balance',
        'closed_by',
        'closed_at',
        'expected_closing_balance',
        'actual_closing_balance',
        'difference',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',

            'opening_balance' => 'decimal:2',
            'expected_closing_balance' => 'decimal:2',
            'actual_closing_balance' => 'decimal:2',
            'difference' => 'decimal:2',

            'status' => CashShiftStatusEnum::class,
        ];
    }

    public function register(): BelongsTo
    {
        return $this->belongsTo(
            CashRegister::class,
            'cash_register_id'
        );
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'opened_by'
        );
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'closed_by'
        );
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CashTransaction::class);
    }

    public function transfer(): HasOne
    {
        return $this->hasOne(
            CashTransfer::class,
            'from_cash_shift_id'
        );
    }

}
