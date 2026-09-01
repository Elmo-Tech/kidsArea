<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Expense;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'amount' => $this->amount,
            'expenseAt' => $this->expense_at,

            'cashRegister' => $this->whenLoaded('register', fn () => [
                'id' => $this->register->id,
                'name' => $this->register->name,
                'isMain' => $this->register->is_main,
            ]),

            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ]),

            'cashShiftId' => $this->cash_shift_id,
            'notes' => $this->notes,
            'createdAt' => $this->created_at,
        ];
    }
}
