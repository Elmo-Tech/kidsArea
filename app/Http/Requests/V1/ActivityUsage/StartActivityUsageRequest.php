<?php

namespace App\Http\Requests\V1\ActivityUsage;

use App\Enums\ActivityUsageTypeEnum;
use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StartActivityUsageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'visitId' => [
                'nullable',
                'integer',
                'exists:visits,id',
            ],

            'childId' => [
                'required',
                'integer',
                'exists:children,id',
            ],

            'pricingPlanId' => [
                'required',
                'integer',
                'exists:activity_pricing_plans,id',
            ],

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

            'notes' => [
                'nullable',
                'string',
                'max:5000',
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
