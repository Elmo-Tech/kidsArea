<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Cafe;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCafeProductRequest extends FormRequest
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

            'description' => [
                'sometimes',
                'nullable',
                'string',
                'max:5000',
            ],

            'sellingPrice' => [
                'sometimes',
                'required',
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

            'ingredients' => [
                'sometimes',
                'array',
            ],

            'ingredients.*.inventoryItemId' => [
                'required',
                'integer',
                'distinct',
                'exists:inventory_items,id',
            ],

            'ingredients.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
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
