<?php

namespace App\Http\Resources\V1\Payroll;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllLeavePayrollPolicyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'leaveType' => [
                'id' => $this->leaveType->id,
                'name' => $this->leaveType->name,
            ],

            'salaryType' => $this->salary_type,
            'effect' => $this->effect,

            'createdAt' => $this->created_at,
        ];
    }
}
