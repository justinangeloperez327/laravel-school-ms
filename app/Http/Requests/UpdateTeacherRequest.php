<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeacherRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // User information
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'nullable|min:8',

            // Teacher profile information
            'employee_no' => 'required|string|max:50',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
            'address' => 'nullable|string',
            'hire_date' => 'nullable|date',
            'status' => 'required|string|in:active,retired,suspended',
            'department' => 'nullable|string|max:100',
        ];
    }
}
