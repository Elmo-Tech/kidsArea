<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\ActivitySessionEmployeeAttendance;

use App\Enums\ActivitySessionEmployeeAttendanceStatusEnum;
use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreActivitySessionEmployeeAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'activitySessionId' => [
                'required',
                'integer',
                'exists:activity_sessions,id',
            ],

            'employeeId' => [
                'required',
                'integer',
                'exists:employees,id',
            ],

            'checkInAt' => [
                'nullable',
                'date_format:Y-m-d H:i:s',
            ],

            'checkOutAt' => [
                'nullable',
                'date_format:Y-m-d H:i:s',
                'after:checkInAt',
            ],

            'status' => [
                'required',
                Rule::enum(ActivitySessionEmployeeAttendanceStatusEnum::class),
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    protected function failedValidation(Validator $validator): void
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
