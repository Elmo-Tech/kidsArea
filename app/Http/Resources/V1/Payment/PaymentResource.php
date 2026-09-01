<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Payment;

use BackedEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,

            'paymentMethod' => $this->payment_method instanceof BackedEnum
                ? $this->payment_method->value
                : (int) $this->payment_method,

            'cashShiftId' => $this->cash_shift_id
                ? (int) $this->cash_shift_id
                : null,

            'paidAt' => $this->paid_at,
            'reference' => $this->reference,
            'notes' => $this->notes,
            'createdAt' => $this->created_at,
        ];
    }
}
