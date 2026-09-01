<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\CashRegister;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashRegisterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status->value,
            'notes' => $this->notes,
            'isMain' => $this->is_main,

            'openShift' => $this->whenLoaded('openShift', function (): ?array {
                if (! $this->openShift) {
                    return null;
                }

                return [
                    'id' => $this->openShift->id,
                    'openedAt' => $this->openShift->opened_at,
                    'openingBalance' => $this->openShift->opening_balance,
                ];
            }),

            'createdAt' => $this->created_at,
        ];
    }
}
