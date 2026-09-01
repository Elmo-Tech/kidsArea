<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CashRegisterStatusEnum;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Enums\CashShiftStatusEnum;
class CashRegister extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'name',
        'status',
        'notes',
        'is_main'
    ];

    protected function casts(): array
    {
        return [
            'status' => CashRegisterStatusEnum::class,
            'is_main' => 'boolean',
        ];
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(CashShift::class);
    }

    public function openShift(): HasOne
    {
        return $this->hasOne(CashShift::class)
            ->where('status', CashShiftStatusEnum::OPEN->value)
            ->latestOfMany();
    }
}
