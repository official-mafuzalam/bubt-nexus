<?php

namespace App\Http\Requests\Settings;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
        ];

        // Student-specific rules
        if ($this->user()->hasRole('student')) {
            $rules = array_merge($rules, [
                'semester' => ['required', 'string', 'max:20'],
                'intake' => ['required', 'integer'],
                'section' => ['nullable', 'string', 'max:5'],
                'cgpa' => ['nullable', 'numeric', 'between:0,4.00'],
            ]);
        }

        // Faculty-specific rules
        if ($this->user()->hasRole('faculty')) {
            $rules = array_merge($rules, [
                'department' => ['required', 'string', 'max:255'],
                'faculty_code' => ['required', 'string', 'max:50'],
                'designation' => ['nullable', 'string', 'max:255'],
            ]);
        }

        return $rules;
    }
}

