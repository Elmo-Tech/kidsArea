<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusEnum::class,
        ];
    }

    public function employeeLeaves(): HasMany
    {
        return $this->hasMany(EmployeeLeave::class);
    }

    public function isActive(): bool
    {
        return $this->status === StatusEnum::ACTIVE;
    }

    public function scopeActive($query)
    {
        return $query->where('status', StatusEnum::ACTIVE);
    }
}
