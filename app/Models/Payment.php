<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Enums\PaymentMethodEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Payment extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'payable_type',
        'payable_id',
        'amount',
        'paid_at',
        'reference',
        'notes',
        'payment_method',
        'cash_shift_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'payment_method' => PaymentMethodEnum::class,
        ];
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function cashShift(): BelongsTo
    {
        return $this->belongsTo(CashShift::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }
}
