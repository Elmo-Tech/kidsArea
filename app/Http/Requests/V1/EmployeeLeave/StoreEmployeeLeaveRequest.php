<?php

namespace App\Http\Requests\V1\EmployeeLeave;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreEmployeeLeaveRequest extends FormRequest
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

            'leaveTypeId' => [
                'required',
                'integer',
                'exists:leave_types,id',
            ],

            'startDate' => [
                'required',
                'date',
            ],

            'endDate' => [
                'required',
                'date',
                'after_or_equal:startDate',
            ],

            'reason' => [
                'nullable',
                'string',
                'max:500',
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
