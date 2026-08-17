<?php

namespace App\Http\Requests\V1\EmployeeAttendance;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateEmployeeAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attendanceDate' => [
                'sometimes',
                'required',
                'date_format:Y-m-d',
            ],

            'checkInAt' => [
                'sometimes',
                'required',
                'date_format:H:i',
            ],

            'checkOutAt' => [
                'sometimes',
                'nullable',
                'date_format:H:i',
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
