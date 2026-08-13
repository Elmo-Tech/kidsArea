<?php

namespace App\Http\Requests\V1\EmployeeSession;

use App\Enums\HttpStatusCode;
use App\Enums\EmployeeSessionStatusEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateEmployeeSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employeeId' => [
                'sometimes',
                'required',
                'integer',
                'exists:employees,id',
            ],

            'sessionDate' => [
                'sometimes',
                'required',
                'date',
            ],

            'startTime' => [
                'sometimes',
                'nullable',
                'date_format:H:i',
            ],

            'endTime' => [
                'sometimes',
                'nullable',
                'date_format:H:i',
            ],

            'title' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'sometimes',
                Rule::enum(EmployeeSessionStatusEnum::class),
            ],

            'notes' => [
                'sometimes',
                'nullable',
                'string',
                'max:2000',
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
