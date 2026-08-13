<?php

namespace App\Http\Requests\V1\ActivitySchedule;

use App\Enums\ActivityScheduleStatusEnum;
use App\Enums\HttpStatusCode;
use App\Enums\WeekDayEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateActivityScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'activityId' => [
                'sometimes',
                'required',
                'integer',
                'exists:activities,id',
            ],

            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'startDate' => [
                'sometimes',
                'required',
                'date_format:Y-m-d',
            ],

            'endDate' => [
                'sometimes',
                'required',
                'date_format:Y-m-d',
            ],

            'startTime' => [
                'sometimes',
                'required',
                'date_format:H:i',
            ],

            'endTime' => [
                'sometimes',
                'required',
                'date_format:H:i',
            ],

            'weekDays' => [
                'sometimes',
                'required',
                'array',
                'min:1',
            ],

            'weekDays.*' => [
                'required',
                'integer',
                Rule::enum(WeekDayEnum::class),
            ],

            'status' => [
                'sometimes',
                Rule::enum(ActivityScheduleStatusEnum::class),
            ],

            'notes' => [
                'sometimes',
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
