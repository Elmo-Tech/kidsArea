<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Visit;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CheckoutVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'discount' => [
                'sometimes',
                'numeric',
                'min:0',
            ],

            'amount' => [
                'nullable',
                'numeric',
                'gt:0',
            ],

            'paidAt' => [
                'sometimes',
                'nullable',
                'date_format:Y-m-d H:i:s',
            ],

            'reference' => [
                'nullable',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::error(
                '',
                $validator->errors()->toArray(),
                HttpStatusCode::UNPROCESSABLE_ENTITY
            )
        );
    }
}
