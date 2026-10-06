<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFacultyProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route is protected by role:faculty
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'designation' => ['sometimes', 'nullable', 'string', 'max:255'],
            'department' => ['sometimes', 'nullable', 'string', 'max:255'],
            'college' => ['sometimes', 'nullable', 'string', 'max:255'],
            'qualification' => ['sometimes', 'nullable', 'string', 'max:255'],
            'experience_years' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:70'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'nullable', 'string', 'max:100'],

            'education' => ['sometimes', 'array', 'max:20'],
            'education.*.degree' => ['required', 'string', 'max:255'],
            'education.*.institution' => ['required', 'string', 'max:255'],
            'education.*.field_of_study' => ['nullable', 'string', 'max:255'],
            'education.*.start_year' => ['nullable', 'integer', 'between:1950,2100'],
            'education.*.end_year' => ['nullable', 'integer', 'between:1950,2100'],

            'experience' => ['sometimes', 'array', 'max:30'],
            'experience.*.title' => ['required', 'string', 'max:255'],
            'experience.*.organization' => ['required', 'string', 'max:255'],
            'experience.*.location' => ['nullable', 'string', 'max:255'],
            'experience.*.start_date' => ['nullable', 'date'],
            'experience.*.end_date' => ['nullable', 'date'],
            'experience.*.is_current' => ['nullable', 'boolean'],
            'experience.*.description' => ['nullable', 'string', 'max:3000'],

            'skills' => ['sometimes', 'array', 'max:50'],
            'skills.*' => ['string', 'max:100'],
        ];
    }
}
