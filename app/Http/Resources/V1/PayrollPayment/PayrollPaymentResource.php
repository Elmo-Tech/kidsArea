<?php

namespace App\Http\Resources\V1\PayrollPayment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollPaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'employeePayrollId' =>
                $this->employee_payroll_id,

            'amount' =>
                $this->amount,

            'paidAt' =>
                $this->paid_at,

            'reference' =>
                $this->reference,

            'notes' =>
                $this->notes,

            'createdBy' => $this->createdBy
                ? [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->username,
                ]
                : null,

            'createdAt' =>
                $this->created_at
        ];
    }
}
