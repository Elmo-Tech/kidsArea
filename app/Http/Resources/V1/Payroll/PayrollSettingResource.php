<?php

namespace App\Http\Resources\V1\Payroll;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'prorationMethod' =>
                $this->proration_method,

            'lateDeductionMethod' =>
                $this->late_deduction_method,

            'earlyLeaveDeductionMethod' =>
                $this->early_leave_deduction_method,

            'absenceDeductionMethod' =>
                $this->absence_deduction_method,

            'blockFinalizeOnIncompleteAttendance' =>
                $this->block_finalize_on_incomplete_attendance,
        ];
    }
}
