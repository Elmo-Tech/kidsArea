<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\CafeOrder;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCafeOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'visitId' => [
                'nullable',
                'integer',
                'exists:visits,id',
            ],

            'discount' => [
                'sometimes',
                'numeric',
                'min:0',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.cafeProductId' => [
                'required',
                'integer',
                'distinct',
                'exists:cafe_products,id',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'items.*.notes' => [
                'nullable',
                'string',
                'max:2000',
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
