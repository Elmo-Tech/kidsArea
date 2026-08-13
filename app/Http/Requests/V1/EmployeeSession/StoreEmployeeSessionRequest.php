<?php

namespace App\Http\Requests\V1\EmployeeSession;

use App\Enums\HttpStatusCode;
use App\Enums\EmployeeSessionStatusEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreEmployeeSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employeeId' => [
                'required',
                'integer',
                'exists:employees,id',
            ],

            'sessionDate' => [
                'required',
                'date',
            ],

            'startTime' => [
                'nullable',
                'date_format:H:i',
            ],

            'endTime' => [
                'nullable',
                'date_format:H:i',
                'after:startTime',
            ],

            'title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'sometimes',
                Rule::enum(EmployeeSessionStatusEnum::class),
            ],

            'notes' => [
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
