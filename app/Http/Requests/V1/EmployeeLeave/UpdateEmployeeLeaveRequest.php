<?php

namespace App\Http\Requests\V1\EmployeeLeave;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateEmployeeLeaveRequest extends FormRequest
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

            'leaveTypeId' => [
                'sometimes',
                'required',
                'integer',
                'exists:leave_types,id',
            ],

            'startDate' => [
                'sometimes',
                'required',
                'date',
            ],

            'endDate' => [
                'sometimes',
                'required',
                'date',
            ],

            'reason' => [
                'sometimes',
                'nullable',
                'string',
                'max:500',
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
