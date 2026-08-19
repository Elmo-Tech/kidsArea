<?php

namespace Database\Seeders;

use App\Enums\LeavePayrollEffectEnum;
use App\Enums\SalaryTypeEnum;
use App\Models\LeavePayrollPolicy;
use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeavePayrollPolicySeeder extends Seeder
{
    public function run(): void
    {
        $annualLeave = LeaveType::query()
            ->where('name', 'إجازة سنوية')
            ->first();

        $sickLeave = LeaveType::query()
            ->where('name', 'إجازة مرضية')
            ->first();

        $emergencyLeave = LeaveType::query()
            ->where('name', 'إجازة طارئة')
            ->first();

        if ($annualLeave) {
            $this->createPolicies(
                $annualLeave->id,
                LeavePayrollEffectEnum::PAID
            );
        }

        if ($sickLeave) {
            $this->createPolicies(
                $sickLeave->id,
                LeavePayrollEffectEnum::PAID
            );
        }

        if ($emergencyLeave) {
            $this->createPolicies(
                $emergencyLeave->id,
                LeavePayrollEffectEnum::UNPAID
            );
        }
    }

    private function createPolicies(
        int $leaveTypeId,
        LeavePayrollEffectEnum $effect
    ): void {
        foreach (SalaryTypeEnum::cases() as $salaryType) {
            LeavePayrollPolicy::query()->firstOrCreate(
                [
                    'leave_type_id' => $leaveTypeId,
                    'salary_type' => $salaryType->value,
                ],
                [
                    'effect' => $effect->value,
                ]
            );
        }
    }
}
