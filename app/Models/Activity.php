<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActivityStatusEnum;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'name',
        'description',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => ActivityStatusEnum::class,
        ];
    }

    public function pricingPlans(): HasMany
    {
        return $this->hasMany(
            ActivityPricingPlan::class
        );
    }
}
