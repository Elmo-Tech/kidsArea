<?php

namespace App\Http\Requests\V1\EmployeeAttendance;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreEmployeeAttendanceRequest extends FormRequest
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

            'attendanceDate' => [
                'required',
                'date',
            ],

            'checkInAt' => [
                'required',
                'date_format:H:i',
            ],

            'checkOutAt' => [
                'nullable',
                'date_format:H:i',
                'after:checkInAt',
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
