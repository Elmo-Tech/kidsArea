<?php

namespace App\Http\Resources\V1\Payroll;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollAdjustmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'type' => $this->type->value,

            'amount' => $this->amount,

            'reason' => $this->reason,

            'notes' => $this->notes,

            'createdAt' => $this->created_at?->format('Y-m-d H:i:s'),

            'updatedAt' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
