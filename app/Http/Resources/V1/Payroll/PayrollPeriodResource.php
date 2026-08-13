<?php

namespace App\Http\Resources\V1\Payroll;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollPeriodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'year' => $this->year,
            'month' => $this->month,

            'startDate' =>
                $this->start_date,

            'endDate' =>
                $this->end_date,

            'prorationMethod' =>
                $this->proration_method,

            'status' =>
                $this->status,

            'finalizedBy' => $this->finalizedBy
                ? [
                    'id' => $this->finalizedBy->id,
                    'username' => $this->finalizedBy->username,
                ]
                : null,

            'finalizedAt' =>
                $this->finalized_at,

            'notes' => $this->notes,

            'createdAt' =>
                $this->created_at,
        ];
    }
}
