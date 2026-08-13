<?php

namespace App\Http\Requests\V1\ActivitySchedule;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class GenerateActivitySessionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employeeIds' => [
                'sometimes',
                'array',
            ],

            'employeeIds.*' => [
                'required',
                'integer',
                'distinct',
                'exists:employees,id',
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
