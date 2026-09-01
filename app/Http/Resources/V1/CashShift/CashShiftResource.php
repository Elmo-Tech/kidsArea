<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\CashShift;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashShiftResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'register' => [
                'id' => $this->cash_register_id,
                'name' => $this->register?->name,
            ],

            'openedBy' => [
                'id' => $this->opened_by,
                'name' => $this->openedBy?->name,
            ],

            'openedAt' => $this->opened_at,
            'openingBalance' => $this->opening_balance,

            'closedBy' => $this->closedBy
                ? [
                    'id' => $this->closed_by,
                    'name' => $this->closedBy?->name,
                ]
                : null,

            'closedAt' => $this->closed_at,

            'expectedClosingBalance' => $this->expected_closing_balance,
            'actualClosingBalance' => $this->actual_closing_balance,
            'difference' => $this->difference,

            'status' => $this->status->value,
            'notes' => $this->notes,

            'createdAt' => $this->created_at,
        ];
    }
}
