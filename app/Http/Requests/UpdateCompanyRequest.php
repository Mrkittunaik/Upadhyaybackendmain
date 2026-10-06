<?php

namespace App\Http\Requests;

class UpdateCompanyRequest extends StoreCompanyRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['name'] = ['sometimes', 'string', 'max:255'];

        return $rules;
    }
}
