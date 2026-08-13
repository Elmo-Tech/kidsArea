<?php

namespace App\Http\Requests\V1\LeaveType;

use App\Enums\HttpStatusCode;
use App\Enums\StatusEnum;
use App\Helpers\ApiResponse;
use App\Models\LeaveType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var LeaveType|null $leaveType */
        $leaveType = $this->route('leaveType');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('leave_types', 'name')
                    ->ignore($leaveType?->id),
            ],

            'description' => [
                'sometimes',
                'nullable',
                'string',
                'max:1000',
            ],

            'status' => [
                'sometimes',
                Rule::enum(StatusEnum::class),
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
