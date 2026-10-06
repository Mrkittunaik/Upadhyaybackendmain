<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge(['email' => strtolower(trim((string) $this->input('email')))]);
        }
        if ($this->has('city_code')) {
            $this->merge(['city_code' => strtoupper(trim((string) $this->input('city_code')))]);
        }
    }

    public function rules(): array
    {
        $phone = ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'];
        if (config('upadhyay.phone_unique')) {
            $phone[] = Rule::unique('users', 'phone');
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')],
            'phone' => $phone,
            'password' => ['required', 'string', Password::min(8)->letters()->numbers()],
            // admin can never self-register
            'role' => ['required', Rule::in([UserRole::Faculty->value, UserRole::CompanyMain->value, UserRole::CompanyBranch->value])],
            // company_main only
            'company_name' => ['required_if:role,company_main', 'nullable', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:100'],
            'city_code' => ['nullable', 'string', 'max:10', 'alpha_num'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:255'],
        ];
    }
}
