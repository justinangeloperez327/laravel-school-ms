<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
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
            'student_id' => 'sometimes|required|exists:students,id',
            'school_class_id' => 'sometimes|required|exists:school_classes,id',
            'attendance_date' => 'sometimes|required|date',
            'status' => 'required|in:present,absent,late,excused',
            'teacher_id' => 'required|exists:teachers,id',
            'remarks' => 'nullable|string',
        ];
    }
}
