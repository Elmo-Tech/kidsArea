<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\CashRegister;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllCashRegisterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status->value,
            'isMain' => $this->is_main,
            'hasOpenShift' => $this->open_shift_exists ?? false,
        ];
    }
}
