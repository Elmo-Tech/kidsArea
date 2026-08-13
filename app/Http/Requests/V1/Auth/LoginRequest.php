<?php

namespace App\Http\Requests\V1\Auth;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'max:255',
            ],

            'password' => [
                'required',
                'string',
            ],
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::error(
                __('messages.validation_failed'),
                $validator->errors()->toArray(),
                HttpStatusCode::UNPROCESSABLE_ENTITY
            )
        );
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'username.required' => __('validation.required', [
                'attribute' => __('validation.attributes.username'),
            ]),

            'username.string' => __('validation.string', [
                'attribute' => __('validation.attributes.username'),
            ]),

            'username.max' => __('validation.max.string', [
                'attribute' => __('validation.attributes.username'),
                'max' => 255,
            ]),

            'password.required' => __('validation.required', [
                'attribute' => __('validation.attributes.password'),
            ]),

            'password.string' => __('validation.string', [
                'attribute' => __('validation.attributes.password'),
            ]),
        ];
    }
}
