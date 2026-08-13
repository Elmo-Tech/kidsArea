<?php

namespace App\Http\Requests\V1\Employee;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $employee = $this->route('employee');

        return [

            'email' => ['nullable', 'required', 'string', 'max:255', Rule::unique('users', 'email')->ignore($employee),],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'nationalId' => ['nullable', 'string', 'max:20'],
            'departmentId' => ['required', 'integer', 'exists:departments,id'],
            'jobTitleId' => ['required', 'integer', 'exists:job_titles,id']
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::error(
                __('messages.validation_failed'),
                $validator->errors()->toArray(),
                HttpStatusCode::UNPROCESSABLE_ENTITY
            )
        );
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'nationalId.required' => __('validation.custom.nationalId.required'),
            'nationalId.digits' => __('validation.custom.nationalId.digits'),
            'nationalId.unique' => __('validation.custom.nationalId.unique'),
            'jobTitleId.required' => __('validation.custom.jobTitleId.required'),
        ];
    }
}
