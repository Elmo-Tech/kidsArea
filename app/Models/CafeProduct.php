<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CafeProduct extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'name',
        'description',
        'selling_price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'selling_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(
            CafeProductIngredient::class
        );
    }
}
