<?php

namespace App\Http\Requests\V1\Employee;

use App\Enums\HttpStatusCode;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;

class StoreEmployeeRequest extends FormRequest
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
        return [
            'email' => ['nullable', 'string', 'max:255', 'unique:employees,email'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'nationalId' => ['required', 'string', 'digits:14', 'unique:employees,national_id'],
            'departmentId' => ['nullable', 'integer', 'exists:departments,id'],
            'jobTitleId' => ['required', 'integer', 'exists:job_titles,id']
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

    public function messages()
    {
        return [
            'jobTitleId.required' => __('validation.custom.jobTitleId.required'),
            'nationalId.required' => __('validation.custom.nationalId.required'),
            'nationalId.digits' => __('validation.custom.nationalId.digits'),
            'nationalId.unique' => __('validation.custom.nationalId.unique'),
        ];
    }

}
