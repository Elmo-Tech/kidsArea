<?php

namespace App\Http\Requests\V1\ActivityUsage;

use App\Enums\ActivityUsageTypeEnum;
use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ChangeActivityUsageTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'usageType' => [
                'required',
                Rule::enum(ActivityUsageTypeEnum::class),
            ],

            'plannedDurationMinutes' => [
                Rule::requiredIf(
                    (int) $this->input('usageType') ===
                    ActivityUsageTypeEnum::FIXED_DURATION->value
                ),
                'nullable',
                'integer',
                'min:1',
            ],
        ];
    }

    protected function failedValidation(
        Validator $validator
    ) {
        throw new HttpResponseException(
            ApiResponse::error(
                '',
                $validator->errors()->toArray(),
                HttpStatusCode::UNPROCESSABLE_ENTITY
            )
        );
    }
}
