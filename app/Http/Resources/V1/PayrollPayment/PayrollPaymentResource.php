<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\PayrollPayment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollPaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'employeePayrollId' => $this->employee_payroll_id,

            'amount' => $this->amount,

            'paymentMethod' => $this->payment_method->value,

            'cashShift' => $this->cashShift
                ? [
                    'id' => $this->cashShift->id,

                    'register' => [
                        'id' => $this->cashShift->register?->id,
                        'name' => $this->cashShift->register?->name,
                        'isMain' => $this->cashShift->register?->is_main,
                    ],
                ]
                : null,

            'paidAt' => $this->paid_at,

            'reference' => $this->reference,

            'notes' => $this->notes,

            'createdBy' => $this->createdBy
                ? [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                ]
                : null,

            'createdAt' => $this->created_at
        ];
    }
}
