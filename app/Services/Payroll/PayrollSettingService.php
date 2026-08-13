<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use App\Models\PayrollSetting;
use Illuminate\Support\Facades\DB;

class PayrollSettingService
{
    public function getSettings(): PayrollSetting
    {
        return PayrollSetting::query()
            ->firstOrCreate([]);
    }

    public function updateSettings(
        array $data
    ): PayrollSetting {
        return DB::transaction(function () use ($data): PayrollSetting {
            $settings = $this->getSettings();

            $settings->update([
                'proration_method' =>
                    $data['prorationMethod']
                    ?? $settings->proration_method,

                'late_deduction_method' =>
                    $data['lateDeductionMethod']
                    ?? $settings->late_deduction_method,

                'early_leave_deduction_method' =>
                    $data['earlyLeaveDeductionMethod']
                    ?? $settings->early_leave_deduction_method,

                'absence_deduction_method' =>
                    $data['absenceDeductionMethod']
                    ?? $settings->absence_deduction_method,

                'block_finalize_on_incomplete_attendance' =>
                    array_key_exists(
                        'blockFinalizeOnIncompleteAttendance',
                        $data
                    )
                        ? $data['blockFinalizeOnIncompleteAttendance']
                        : $settings
                            ->block_finalize_on_incomplete_attendance,
            ]);

            return $settings->refresh();
        });
    }
}
