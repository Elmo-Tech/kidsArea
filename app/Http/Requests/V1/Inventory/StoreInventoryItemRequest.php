<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Inventory;

use App\Enums\HttpStatusCode;
use App\Enums\InventoryUnitEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'baseUnit' => [
                'required',
                Rule::enum(InventoryUnitEnum::class),
            ],

            'minimumQuantity' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'unitCost' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'isActive' => [
                'sometimes',
                'boolean',
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
