<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'company_name' => ['sometimes', 'required', 'string', 'max:255'],

            'business_type' => [
                'sometimes',
                'required',
                'in:manufacturer,wholesaler,distributor,exporter,retailer'
            ],

            'contact_person' => ['sometimes', 'required', 'string', 'max:255'],

            'email' => ['sometimes', 'required', 'email'],

            'phone' => ['sometimes', 'required', 'string', 'max:30'],

            'website' => ['nullable', 'url'],

            'registration_number' => ['nullable', 'string'],

            'tax_number' => ['nullable', 'string'],

            'description' => ['nullable', 'string'],

            'fulfillment_region' => ['nullable', 'string', 'max:255'],

            'typical_lead_time' => ['nullable', 'string', 'max:255'],

            'commission_rate' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],

            'logo' => ['nullable', 'image', 'max:2048'],

            'banner' => ['nullable', 'image', 'max:4096'],

            'status' => ['sometimes', 'required', 'in:pending,approved,rejected,suspended'],

        ];
    }
}
