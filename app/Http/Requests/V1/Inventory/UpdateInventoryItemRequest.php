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

class UpdateInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'baseUnit' => [
                'sometimes',
                'required',
                Rule::enum(InventoryUnitEnum::class),
            ],

            'minimumQuantity' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
            ],

            'unitCost' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
            ],

            'isActive' => [
                'sometimes',
                'boolean',
            ],

            'notes' => [
                'sometimes',
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
