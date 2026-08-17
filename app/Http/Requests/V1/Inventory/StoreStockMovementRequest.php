<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Inventory;

use App\Enums\HttpStatusCode;
use App\Enums\StockMovementTypeEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreStockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inventoryItemId' => [
                'required',
                'integer',
                'exists:inventory_items,id',
            ],

            'type' => [
                'required',
                Rule::enum(StockMovementTypeEnum::class),
            ],

            'quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'unitCost' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'movementAt' => [
                'nullable',
                'date_format:Y-m-d H:i:s',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    protected function failedValidation(
        Validator $validator
    ): void {
        throw new HttpResponseException(
            ApiResponse::error(
                '',
                $validator->errors()->toArray(),
                HttpStatusCode::UNPROCESSABLE_ENTITY
            )
        );
    }
}
