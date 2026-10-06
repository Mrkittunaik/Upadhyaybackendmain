<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BranchRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route is protected by role:company_branch
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('city_code')) {
            $this->merge(['city_code' => strtoupper(trim((string) $this->input('city_code')))]);
        }
    }

    public function rules(): array
    {
        return [
            'company_id' => ['required', 'integer', Rule::exists('companies', 'id')->whereNull('deleted_at')],
            'branch_name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'city_code' => ['nullable', 'string', 'max:10', 'alpha_num'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
        ];
    }
}
