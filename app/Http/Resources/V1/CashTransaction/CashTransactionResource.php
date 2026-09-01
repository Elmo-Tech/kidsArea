<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\CashTransaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cashRegisterId' => $this->cash_register_id,
            'cashShiftId' => $this->cash_shift_id,

            'type' => $this->type->value,
            'amount' => $this->amount,
            'source' => $this->source->value,

            'sourceable' => $this->sourceable
                ? [
                    'type' => class_basename($this->sourceable_type),
                    'id' => $this->sourceable_id,
                ]
                : null,

            'transactionAt' => $this->transaction_at,
            'notes' => $this->notes,

            'createdBy' => $this->createdBy
                ? [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                ]
                : null,

            'createdAt' => $this->created_at,
        ];
    }
}
