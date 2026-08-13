<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use App\Models\LeavePayrollPolicy;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class LeavePayrollPolicyService
{
    public function all(Request $request): LengthAwarePaginator
    {
        $perPage = min(
            max((int) $request->integer('perPage', 15), 1),
            100
        );

        return QueryBuilder::for(LeavePayrollPolicy::class)
            ->with([
                'leaveType',
            ])
            ->allowedFilters(
                AllowedFilter::exact(
                    'leaveTypeId',
                    'leave_type_id'
                ),

                AllowedFilter::exact(
                    'salaryType',
                    'salary_type'
                ),

                AllowedFilter::exact(
                    'effect'
                ),
            )
            ->latest('id')
            ->paginate($perPage);
    }

    public function createLeavePayrollPolicy(
        array $data
    ): LeavePayrollPolicy {
        return DB::transaction(function () use ($data): LeavePayrollPolicy {

            $policy = LeavePayrollPolicy::create([
                'leave_type_id' => $data['leaveTypeId'],
                'salary_type' => $data['salaryType'],
                'effect' => $data['effect'],
            ]);

            return $policy->load([
                'leaveType',
            ]);
        });
    }

    public function editLeavePayrollPolicy(
        LeavePayrollPolicy $policy
    ): LeavePayrollPolicy {
        return $policy->load([
            'leaveType',
        ]);
    }

    public function updateLeavePayrollPolicy(
        LeavePayrollPolicy $policy,
        array $data
    ): LeavePayrollPolicy {
        return DB::transaction(
            function () use ($policy, $data): LeavePayrollPolicy {

                $policy->update([
                    'leave_type_id' =>
                        $data['leaveTypeId']
                        ?? $policy->leave_type_id,

                    'salary_type' =>
                        $data['salaryType']
                        ?? $policy->salary_type->value,

                    'effect' =>
                        $data['effect']
                        ?? $policy->effect->value,
                ]);

                return $policy
                    ->refresh()
                    ->load([
                        'leaveType',
                    ]);
            }
        );
    }

    public function deleteLeavePayrollPolicy(
        LeavePayrollPolicy $policy
    ): bool {
        return DB::transaction(
            fn (): bool => (bool) $policy->delete()
        );
    }
}
