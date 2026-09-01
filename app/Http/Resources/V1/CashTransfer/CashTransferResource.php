<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\CashTransfer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashTransferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'from' => [
                'cashShiftId' => $this->from_cash_shift_id,
                'register' => [
                    'id' => $this->fromShift?->register?->id,
                    'name' => $this->fromShift?->register?->name,
                ],
            ],

            'to' => [
                'cashShiftId' => $this->to_cash_shift_id,
                'register' => [
                    'id' => $this->toRegister?->register?->id,
                    'name' => $this->toRegister?->register?->name,
                ],
            ],

            'amount' => $this->amount,

            'transferredAt' =>
                $this->transferred_at,

            'notes' => $this->notes,

            'createdBy' => $this->createdBy
                ? [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                ]
                : null,
        ];
    }
}
