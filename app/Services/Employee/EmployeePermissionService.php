<?php

declare(strict_types=1);

namespace App\Services\Employee;

use App\Enums\EmployeePermissionStatusEnum;
use App\Exceptions\Employee\PermissionNotPendingException;
use App\Models\EmployeePermission;
use App\QueryFilters\EmployeePermissionDateFromFilter;
use App\QueryFilters\EmployeePermissionDateToFilter;
use App\QueryFilters\EmployeePermissionSearchFilter;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EmployeePermissionService
{
    /**
     * Get all employee permissions.
     */
    public function all(Request $request): LengthAwarePaginator
    {
        $perPage = min(
            max((int) $request->integer('perPage', 15), 1),
            100
        );

        return QueryBuilder::for(EmployeePermission::class)
            ->with([
                'employee.jobTitle',
                'approvedBy',
            ])
            ->allowedFilters(
                AllowedFilter::custom(
                    'search',
                    new EmployeePermissionSearchFilter()
                ),

                AllowedFilter::exact(
                    'employeeId',
                    'employee_id'
                ),

                AllowedFilter::exact(
                    'type'
                ),

                AllowedFilter::exact(
                    'status'
                ),

                AllowedFilter::custom(
                    'dateFrom',
                    new EmployeePermissionDateFromFilter()
                ),

                AllowedFilter::custom(
                    'dateTo',
                    new EmployeePermissionDateToFilter()
                ),
            )
            ->latest('id')
            ->paginate($perPage);
    }

    /**
     * Create employee permission.
     */
    public function createPermission(array $data): EmployeePermission
    {
        return DB::transaction(function () use ($data): EmployeePermission {
            $permission = EmployeePermission::create([
                'employee_id' => $data['employeeId'],

                'permission_date' => $data['permissionDate'],
                'type' => $data['type'],

                'from_time' => $data['fromTime'],
                'to_time' => $data['toTime'],

                'minutes' => $this->calculateMinutes(
                    $data['fromTime'],
                    $data['toTime']
                ),

                'reason' => $data['reason'] ?? null,

                'status' => EmployeePermissionStatusEnum::PENDING->value,

                'approved_by' => null,
                'approved_at' => null,

                'notes' => $data['notes'] ?? null,
            ]);

            return $permission->load([
                'employee.jobTitle',
                'approvedBy',
            ]);
        });
    }

    /**
     * Show employee permission.
     */
    public function showPermission(
        EmployeePermission $permission
    ): EmployeePermission {
        return $permission->load([
            'employee.jobTitle',
            'approvedBy',
        ]);
    }

    /**
     * Update employee permission.
     */
    public function updatePermission(
        EmployeePermission $permission,
        array $data
    ): EmployeePermission {
        return DB::transaction(
            function () use ($permission, $data): EmployeePermission {

                $this->ensurePermissionIsPending($permission);

                $fromTime = $data['fromTime']
                    ?? $permission->from_time;

                $toTime = $data['toTime']
                    ?? $permission->to_time;

                $permission->update([
                    'employee_id' => $data['employeeId']
                        ?? $permission->employee_id,

                    'permission_date' => $data['permissionDate']
                        ?? $permission->permission_date,

                    'type' => $data['type']
                        ?? $permission->type,

                    'from_time' => $fromTime,
                    'to_time' => $toTime,

                    'minutes' => $this->calculateMinutes(
                        $fromTime,
                        $toTime
                    ),

                    'reason' => array_key_exists('reason', $data)
                        ? $data['reason']
                        : $permission->reason,

                    'notes' => array_key_exists('notes', $data)
                        ? $data['notes']
                        : $permission->notes,
                ]);

                return $permission
                    ->refresh()
                    ->load([
                        'employee.jobTitle',
                        'approvedBy',
                    ]);
            }
        );
    }

    /**
     * Delete employee permission.
     */
    public function deletePermission(
        EmployeePermission $permission
    ): bool {
        return DB::transaction(
            function () use ($permission): bool {

                $this->ensurePermissionIsPending($permission);

                return (bool) $permission->delete();
            }
        );
    }

    /**
     * Approve employee permission.
     */
    public function approvePermission(
        EmployeePermission $permission
    ): EmployeePermission {
        return DB::transaction(
            function () use ($permission): EmployeePermission {

                $this->ensurePermissionIsPending($permission);

                $permission->update([
                    'status' =>
                        EmployeePermissionStatusEnum::APPROVED->value,

                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                ]);

                return $permission
                    ->refresh()
                    ->load([
                        'employee.jobTitle',
                        'approvedBy',
                    ]);
            }
        );
    }

    /**
     * Reject employee permission.
     */
    public function rejectPermission(
        EmployeePermission $permission
    ): EmployeePermission {
        return DB::transaction(
            function () use ($permission): EmployeePermission {

                $this->ensurePermissionIsPending($permission);

                $permission->update([
                    'status' =>
                        EmployeePermissionStatusEnum::REJECTED->value,

                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                ]);

                return $permission
                    ->refresh()
                    ->load([
                        'employee.jobTitle',
                        'approvedBy',
                    ]);
            }
        );
    }

    /**
     * Calculate permission duration in minutes.
     */
    private function calculateMinutes(
        string $fromTime,
        string $toTime
    ): int {
        $from = Carbon::parse($fromTime);
        $to = Carbon::parse($toTime);

        return (int) $from->diffInMinutes($to);
    }
    /**
     * Ensure only pending permissions can be changed.
     */
    private function ensurePermissionIsPending(
        EmployeePermission $permission
    ): void {
        if (
            $permission->status !==
            EmployeePermissionStatusEnum::PENDING
        ) {
            throw new PermissionNotPendingException();
        }
    }
}
