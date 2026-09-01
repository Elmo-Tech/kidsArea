<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Refund;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RefundResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'paymentId' => $this->payment_id,
            'amount' => $this->amount,
            'refundedAt' => $this->refunded_at,
            'reason' => $this->reason,
            'notes' => $this->notes,

            'cashRegister' => $this->whenLoaded('register', fn () => $this->register
                ? [
                    'id' => $this->register->id,
                    'name' => $this->register->name,
                ]
                : null
            ),

            'cashShiftId' => $this->cash_shift_id,
            'createdAt' => $this->created_at,
        ];
    }
}
