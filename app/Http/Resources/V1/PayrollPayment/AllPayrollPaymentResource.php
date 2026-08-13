<?php

namespace App\Http\Resources\V1\PayrollPayment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllPayrollPaymentResource extends JsonResource
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
            'amount' => $this->amount,
            'paidAt' => $this->paid_at,

            'reference' =>
                $this->reference,

            'createdBy' => $this->createdBy
                ? [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->username,
                ]
                : null,

            'createdAt' => $this->created_at
        ];
    }

}
