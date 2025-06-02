<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
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

            // Guardian information (conditional)
            'guardian_id' => 'nullable|exists:guardians,id',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_email' => 'nullable|email',
            'guardian_phone' => 'nullable|string|max:20',
            'guardian_relationship' => 'nullable|string|max:50',

            // Student profile information
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
            'address' => 'nullable|string',
            'admission_date' => 'nullable|date',
            'status' => 'required|string|in:active,alumni,suspended',
        ];
    }
}
