<?php

namespace App\Http\Requests\V1\User;

use App\Enums\HttpStatusCode;
use App\Enums\StatusEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
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
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'status' => ['nullable', Rule::enum(StatusEnum::class),],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'password' => ['required', 'string', 'min:8'],
            'roleId' => ['required', 'integer', 'exists:roles,id'],
            'employeeId' => ['nullable', 'integer', 'exists:employees,id', Rule::unique('users', 'employee_id')->ignore($this->employeeId)],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::error(
                __('messages.validation_failed'),
                $validator->errors()->toArray(),
                HttpStatusCode::UNPROCESSABLE_ENTITY
            )
        );
    }

    public function messages()
    {
        return [
            'roleId.required' => __('validation.custom.roleId.required'),
            'employeeId.unique' => __('validation.custom.employeeId.unique'),
        ];
    }

}
