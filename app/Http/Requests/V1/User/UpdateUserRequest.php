<?php

namespace App\Http\Requests\V1\User;

use App\Enums\HttpStatusCode;
use App\Enums\StatusEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');

        return [


            'username' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($user),
            ],

            'status' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::enum(StatusEnum::class),
            ],

            'avatar' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,gif,webp',
                'max:2048',
            ],

            'password' => [
                'sometimes',
                'nullable',
                'string',
                'min:8',
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

            'username.string' => __('validation.string', [
                'attribute' => __('validation.attributes.username'),
            ]),

            'username.max' => __('validation.max.string', [
                'attribute' => __('validation.attributes.username'),
                'max' => 255,
            ]),

            'username.unique' => __('validation.unique', [
                'attribute' => __('validation.attributes.username'),
            ]),

            'status.enum' => __('validation.enum', [
                'attribute' => __('validation.attributes.status'),
            ]),

            'avatar.image' => __('validation.image', [
                'attribute' => __('validation.attributes.avatar'),
            ]),

            'avatar.mimes' => __('validation.mimes', [
                'attribute' => __('validation.attributes.avatar'),
            ]),

            'avatar.max' => __('validation.max.file', [
                'attribute' => __('validation.attributes.avatar'),
                'max' => 2048,
            ]),

            'password.string' => __('validation.string', [
                'attribute' => __('validation.attributes.password'),
            ]),

            'password.min' => __('validation.min.string', [
                'attribute' => __('validation.attributes.password'),
                'min' => 8,
            ]),
        ];
    }
}
