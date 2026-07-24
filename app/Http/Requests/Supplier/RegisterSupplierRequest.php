<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class RegisterSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [

            'company_name' => ['required','string','max:255'],

            'business_type' => [
                'required',
                'in:manufacturer,wholesaler,distributor,exporter,retailer'
            ],

            'contact_person' => ['required','string','max:255'],

            'email' => [
                'required',
                'email'
            ],

            'phone' => [
                'required',
                'string',
                'max:30'
            ],

            'website' => [
                'nullable',
                'url'
            ],

            'registration_number' => [
                'nullable',
                'string'
            ],

            'tax_number' => [
                'nullable',
                'string'
            ],

            'description' => [
                'nullable',
                'string'
            ],

        ];
    }
}
