<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Expense extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'cash_register_id',
        'cash_shift_id',
        'title',
        'amount',
        'expense_at',
        'notes',
        'expense_category_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_at' => 'datetime',
        ];
    }

    public function register(): BelongsTo
    {
        return $this->belongsTo(
            CashRegister::class,
            'cash_register_id'
        );
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(
            CashShift::class,
            'cash_shift_id'
        );
    }

    public function cashTransaction(): MorphOne
    {
        return $this->morphOne(
            CashTransaction::class,
            'sourceable'
        );
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            ExpenseCategory::class,
            'expense_category_id'
        );
    }
}
