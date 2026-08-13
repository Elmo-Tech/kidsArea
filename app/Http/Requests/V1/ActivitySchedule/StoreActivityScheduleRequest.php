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

class StoreActivityScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'activityId' => [
                'required',
                'integer',
                'exists:activities,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'startDate' => [
                'required',
                'date_format:Y-m-d',
            ],

            'endDate' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:startDate',
            ],

            'startTime' => [
                'required',
                'date_format:H:i',
            ],

            'endTime' => [
                'required',
                'date_format:H:i',
                'after:startTime',
            ],

            'weekDays' => [
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
