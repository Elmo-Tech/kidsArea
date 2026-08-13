<?php

namespace App\Http\Requests\V1\Activity;

use App\Enums\ActivityStatusEnum;
use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreActivityRequest extends FormRequest
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
                'unique:activities,name',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'status' => [
                'sometimes',
                Rule::enum(ActivityStatusEnum::class),
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    protected function failedValidation(Validator $validator)
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
