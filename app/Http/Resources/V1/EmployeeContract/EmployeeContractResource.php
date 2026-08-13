<?php

namespace App\Http\Resources\V1\EmployeeContract;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeContractResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'startDate' => $this->start_date?->format('Y-m-d'),
            'endDate' => $this->end_date?->format('Y-m-d'),

            'salaryType' => $this->salary_type->value,

            'basicSalary' => $this->basic_salary,
            'hourlyRate' => $this->hourly_rate,
            'sessionRate' => $this->session_rate,

            'requiredMonthlyHours' => $this->required_monthly_hours,

            'workStartTime' => $this->work_start_time,
            'workEndTime' => $this->work_end_time,
            'workDays' => $this->work_days,

            'status' => $this->status->value,

            'notes' => $this->notes,

            'createdAt' => $this->created_at,
        ];
    }
}
