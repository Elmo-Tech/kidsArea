<?php

declare(strict_types=1);

namespace App\Services\Employee;

use App\Models\LeaveType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class LeaveTypeService
{
    public function all(int $perPage = 15): LengthAwarePaginator
    {
        $perPage = min(
            max($perPage, 1),
            100
        );

        return QueryBuilder::for(LeaveType::class)
            ->allowedFilters(
                AllowedFilter::partial('name'),
                AllowedFilter::exact('status'),
            )
            ->latest('id')
            ->paginate($perPage);
    }

    public function createLeaveType(array $data): LeaveType
    {
        return DB::transaction(function () use ($data): LeaveType {
            return LeaveType::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? null,
            ]);
        });
    }

    public function editLeaveType(LeaveType $leaveType): LeaveType
    {
        return $leaveType;
    }

    public function updateLeaveType(
        LeaveType $leaveType,
        array $data
    ): LeaveType {
        return DB::transaction(function () use ($leaveType, $data): LeaveType {
            $leaveType->update([
                'name' => $data['name'] ?? $leaveType->name,

                'description' => array_key_exists('description', $data)
                    ? $data['description']
                    : $leaveType->description,

                'status' => array_key_exists('status', $data)
                    ? $data['status']
                    : $leaveType->status,
            ]);

            return $leaveType->refresh();
        });
    }

    public function deleteLeaveType(LeaveType $leaveType): bool
    {
        return DB::transaction(
            fn (): bool => (bool) $leaveType->delete()
        );
    }
}
