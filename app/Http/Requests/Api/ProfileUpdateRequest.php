<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
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
        $userId = auth()->id();

        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId),
            ],

            // User details validation
            'student_id' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('user_details', 'student_id')->ignore($userId, 'user_id'),
            ],
            'faculty_code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('user_details', 'faculty_code')->ignore($userId, 'user_id'),
            ],
            'program' => 'nullable|string|max:100',
            'semester' => 'nullable|string|max:50',
            'intake' => 'nullable|string|max:50',
            'cgpa' => 'nullable|numeric|min:0|max:4.0',
            'department' => 'nullable|string|max:100',
            'designation' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}